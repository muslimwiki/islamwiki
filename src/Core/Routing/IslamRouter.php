<?php
declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IslamRouter implements RequestHandlerInterface
{
    private $container;
    private $routes = [];
    private $middlewareStack;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    /**
     * Get the container instance.
     *
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }
    
    /**
     * Map a route with the given HTTP methods, path, and handler.
     *
     * @param string|array $methods HTTP method(s) (GET, POST, etc.)
     * @param string $route The route pattern
     * @param mixed $handler The handler for the route
     * @param array $middleware Middleware to apply to the route
     * @return $this
     */
    public function map($methods, $route, $handler, array $middleware = [])
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }
        
        $this->routes[] = [
            'methods' => array_map('strtoupper', $methods),
            'route' => $route,
            'handler' => $handler,
            'middleware' => $middleware
        ];
        
        return $this;
    }
    
    /**
     * Add a GET route.
     *
     * @param string $route The route pattern
     * @param mixed $handler The handler for the route
     * @param array $middleware Middleware to apply to the route
     * @return $this
     */
    public function get($route, $handler, array $middleware = [])
    {
        return $this->map('GET', $route, $handler, $middleware);
    }
    
    /**
     * Add a POST route.
     *
     * @param string $route The route pattern
     * @param mixed $handler The handler for the route
     * @param array $middleware Middleware to apply to the route
     * @return $this
     */
    public function post($route, $handler, array $middleware = [])
    {
        return $this->map('POST', $route, $handler, $middleware);
    }
    
    /**
     * Add a PUT route.
     *
     * @param string $route The route pattern
     * @param mixed $handler The handler for the route
     * @param array $middleware Middleware to apply to the route
     * @return $this
     */
    public function put($route, $handler, array $middleware = [])
    {
        return $this->map('PUT', $route, $handler, $middleware);
    }
    
    /**
     * Add a DELETE route.
     *
     * @param string $route The route pattern
     * @param mixed $handler The handler for the route
     * @param array $middleware Middleware to apply to the route
     * @return $this
     */
    public function delete($route, $handler, array $middleware = [])
    {
        return $this->map('DELETE', $route, $handler, $middleware);
    }
    
    /**
     * Add a PATCH route.
     *
     * @param string $route The route pattern
     * @param mixed $handler The handler for the route
     * @param array $middleware Middleware to apply to the route
     * @return $this
     */
    public function patch($route, $handler, array $middleware = [])
    {
        return $this->map('PATCH', $route, $handler, $middleware);
    }
    
    /**
     * Add a route that matches any HTTP method.
     *
     * @param string $route The route pattern
     * @param mixed $handler The handler for the route
     * @param array $middleware Middleware to apply to the route
     * @return $this
     */
    public function any($route, $handler, array $middleware = [])
    {
        return $this->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $route, $handler, $middleware);
    }
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();
        
        // Initialize middleware stack if not already done
        if (!$this->middlewareStack) {
            error_log("IslamRouter::handle - Initializing middleware stack");
            $this->initializeMiddlewareStack();
            error_log("IslamRouter::handle - Middleware stack initialized: " . ($this->middlewareStack ? 'yes' : 'no'));
        }
        
        // Strip query string and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        
        // Find matching route
        error_log("IslamRouter::handle - Looking for route: {$httpMethod} {$uri}");
        error_log("IslamRouter::handle - Registered routes: " . count($this->routes));
        $routeMatch = $this->findRoute($httpMethod, $uri);
        if ($routeMatch) {
            error_log("IslamRouter::handle - Route matched: " . json_encode($routeMatch));
        } else {
            error_log("IslamRouter::handle - No route found for {$httpMethod} {$uri}");
        }
        
        // Create the final handler function
        $finalHandler = function($request) use ($routeMatch, $uri) {
            if (!$routeMatch) {
                return new Response(404, ['Content-Type' => 'text/html'], $this->renderErrorPage(404, '404 Not Found'));
            }
            
            $handler = $routeMatch['handler'];
            $vars = $routeMatch['vars'];
            
            // Handle controller@action
            if (is_string($handler) && str_contains($handler, '@')) {
                [$controllerClass, $method] = explode('@', $handler, 2);
                if (!class_exists($controllerClass)) {
                    throw new \RuntimeException("Controller {$controllerClass} not found");
                }
                
                try {
                    // Use ControllerFactory if available
                    $controllerFactory = null;
                    if (method_exists($this->container, 'get') && $this->container->has('controller.factory')) {
                        $controllerFactory = $this->container->get('controller.factory');
                    }
                    
                    if ($controllerFactory && method_exists($controllerFactory, 'create')) {
                        $controller = $controllerFactory->create($controllerClass);
                    } elseif ($this->container->has($controllerClass)) {
                        $controller = $this->container->get($controllerClass);
                    } else {
                        $controller = new $controllerClass();
                    }
                    
                    if (!method_exists($controller, $method)) {
                        throw new \RuntimeException("Method {$method} not found in controller {$controllerClass}");
                    }
                    
                    // Convert GuzzleHttp\Psr7\ServerRequest to IslamWiki\Core\Http\Request
                    $convertedRequest = \IslamWiki\Core\Http\Request::capture();
                    
                    $response = $controller->$method($convertedRequest, ...array_values($vars));
                    return $response;
                } catch (\Throwable $e) {
                    return new Response(500, ['Content-Type' => 'text/plain'], 'Internal Server Error: ' . $e->getMessage());
                }
            }
            
            // Handle callable
            if (is_callable($handler)) {
                return $handler($request, ...array_values($vars));
            }
            
            throw new \RuntimeException('Invalid route handler');
        };
        
        // Convert PSR-7 request to our Request class for middleware
        $ourRequest = \IslamWiki\Core\Http\Request::fromPsr7($request);
        
        // TEMPORARILY DISABLE MIDDLEWARE FOR TESTING
        // TODO: Re-enable middleware when routing issues are fixed
        error_log("IslamRouter::handle - Bypassing middleware for testing");
        $response = $finalHandler($ourRequest);
        
        /*
        // Execute middleware stack if available
        if ($this->middlewareStack) {
            error_log("IslamRouter::handle - Middleware stack available, executing");
            $response = $this->middlewareStack->execute($ourRequest, $finalHandler);
        } else {
            error_log("IslamRouter::handle - No middleware stack, executing final handler directly");
            $response = $finalHandler($ourRequest);
        }
        */
        
        return $response;
    }
    
    /**
     * Find a matching route for the given method and URI.
     *
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return array|null Route match with handler and variables
     */
    private function findRoute(string $method, string $uri): ?array
    {
        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'])) {
                continue;
            }
            
            $pattern = $route['route'];
            $vars = $this->matchRoute($pattern, $uri);
            
            if ($vars !== null) {
                return [
                    'handler' => $route['handler'],
                    'vars' => $vars,
                    'middleware' => $route['middleware']
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Match a route pattern against a URI.
     *
     * @param string $pattern Route pattern
     * @param string $uri Request URI
     * @return array|null Matched variables or null if no match
     */
    private function matchRoute(string $pattern, string $uri): ?array
    {
        // Convert route pattern to regex
        $regex = $this->patternToRegex($pattern);
        
        if (preg_match($regex, $uri, $matches)) {
            // Remove the full match from the beginning
            array_shift($matches);
            
            // Extract named parameters
            $vars = [];
            preg_match_all('/\{([^}]+)\}/', $pattern, $paramNames);
            
            foreach ($paramNames[1] as $index => $paramName) {
                if (isset($matches[$index])) {
                    $vars[$paramName] = $matches[$index];
                }
            }
            
            return $vars;
        }
        
        return null;
    }
    
    /**
     * Convert a route pattern to a regex pattern.
     *
     * @param string $pattern Route pattern
     * @return string Regex pattern
     */
    private function patternToRegex(string $pattern): string
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        
        // Replace parameter placeholders with regex groups
        $pattern = preg_replace('/\{([^}]+)\}/', '([^\/]+)', $pattern);
        
        // Add start and end anchors
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Initialize the middleware stack.
     */
    private function initializeMiddlewareStack(): void
    {
        if ($this->middlewareStack) {
            return;
        }
        
        try {
            error_log("IslamRouter::initializeMiddlewareStack - Starting initialization");
            
            // Check if logger is available in container
            if (!$this->container->has(\Psr\Log\LoggerInterface::class)) {
                error_log("IslamRouter::initializeMiddlewareStack - LoggerInterface not found in container");
                return;
            }
            
            error_log("IslamRouter::initializeMiddlewareStack - LoggerInterface found, creating middleware stack");
            
            $logger = $this->container->get(\Psr\Log\LoggerInterface::class);
            $this->middlewareStack = new \IslamWiki\Http\Middleware\MiddlewareStack($logger);
            
            // Add global middleware with error handling
            try {
                $this->middlewareStack->add(new \IslamWiki\Http\Middleware\ErrorHandlingMiddleware(
                    $logger,
                    $this->container->has('app.debug') ? $this->container->get('app.debug') : false,
                    $this->container->has('app.env') ? $this->container->get('app.env') : 'production'
                ));
            } catch (\Throwable $e) {
                //
            }
            
            try {
                $this->middlewareStack->add(new \IslamWiki\Http\Middleware\SecurityMiddleware($logger));
            } catch (\Throwable $e) {
                //
            }
            
            try {
                        $sessionManager = $this->container->has(\IslamWiki\Core\Session\Wisal::class)
            ? $this->container->get(\IslamWiki\Core\Session\Wisal::class)
            : new \IslamWiki\Core\Session\Wisal();
                $this->middlewareStack->add(new \IslamWiki\Http\Middleware\CsrfMiddleware($sessionManager));
            } catch (\Throwable $e) {
                //
            }
            
            try {
                $app = $this->container->has(\IslamWiki\Core\Application::class) 
                    ? $this->container->get(\IslamWiki\Core\Application::class)
                    : null;
                if ($app) {
                    // Create SkinMiddleware directly without dependency injection
                    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
                    $this->middlewareStack->add($skinMiddleware);
                }
            } catch (\Throwable $e) {
                error_log("IslamRouter::initializeMiddlewareStack - Error adding SkinMiddleware: " . $e->getMessage());
            }
        } catch (\Throwable $e) {
            error_log("IslamRouter::initializeMiddlewareStack - Error initializing middleware stack: " . $e->getMessage());
            // Don't create a middleware stack if there's an error - just skip middleware
            $this->middlewareStack = null;
        }
    }

    /**
     * Render a pretty error page for 404/500/etc.
     */
    protected function renderErrorPage(int $status, string $message): string
    {
        $html = "<html><head><title>{$status} Error</title><style>body{font-family:sans-serif;background:#f8fafc;color:#222;text-align:center;padding:40px;}h1{font-size:3em;margin-bottom:0.5em;}p{font-size:1.5em;}code{background:#eee;padding:2px 6px;border-radius:4px;}</style></head><body>";
        $html .= "<h1>{$status}</h1><p>{$message}</p>";
        if ($status === 500) {
            $html .= "<p><code>Check logs for more details.</code></p>";
        }
        $html .= "<hr><p><a href='/'>Return to homepage</a></p></body></html>";
        return $html;
    }
} 