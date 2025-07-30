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
        error_log('IslamRouter::handle() entered');
        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();
        error_log('IslamRouter::handle() method=' . $httpMethod . ' uri=' . $uri);
        
        // Strip query string and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        
        // Find matching route
        $routeMatch = $this->findRoute($httpMethod, $uri);
        
        if (!$routeMatch) {
            return new Response(404, ['Content-Type' => 'text/html'], $this->renderErrorPage(404, '404 Not Found'));
        }
        
        $handler = $routeMatch['handler'];
        $vars = $routeMatch['vars'];
        
        // Initialize middleware stack if not already done
        if (!$this->middlewareStack) {
            $this->initializeMiddlewareStack();
        }
        
        error_log('IslamRouter: Executing handler for route: ' . $uri);
        
        // Create the final handler function
        $finalHandler = function($request) use ($handler, $vars) {
            // Handle controller@action
            if (is_string($handler) && str_contains($handler, '@')) {
                [$controllerClass, $method] = explode('@', $handler, 2);
                error_log("IslamRouter: Attempting to resolve controller: $controllerClass, method: $method");
                if (!class_exists($controllerClass)) {
                    error_log("IslamRouter: Controller class $controllerClass not found");
                    throw new \RuntimeException("Controller {$controllerClass} not found");
                }
                
                try {
                    // Use ControllerFactory if available
                    $controllerFactory = null;
                    if (method_exists($this->container, 'get') && $this->container->has('controller.factory')) {
                        $controllerFactory = $this->container->get('controller.factory');
                    }
                    
                    if ($controllerFactory && method_exists($controllerFactory, 'create')) {
                        error_log("IslamRouter: Using ControllerFactory to create $controllerClass");
                        $controller = $controllerFactory->create($controllerClass);
                    } elseif ($this->container->has($controllerClass)) {
                        error_log("IslamRouter: Using container to get $controllerClass");
                        $controller = $this->container->get($controllerClass);
                    } else {
                        error_log("IslamRouter: Instantiating $controllerClass directly");
                        $controller = new $controllerClass();
                    }
                    
                    error_log("IslamRouter: Controller instance created: " . get_class($controller));
                    if (!method_exists($controller, $method)) {
                        error_log("IslamRouter: Method $method not found in $controllerClass");
                        throw new \RuntimeException("Method {$method} not found in controller {$controllerClass}");
                    }
                    error_log("IslamRouter: Calling $controllerClass->$method");
                    
                    // Convert GuzzleHttp\Psr7\ServerRequest to IslamWiki\Core\Http\Request
                    $convertedRequest = \IslamWiki\Core\Http\Request::capture();
                    
                    $response = $controller->$method($convertedRequest, ...array_values($vars));
                    error_log("IslamRouter: $controllerClass->$method call completed");
                    error_log('IslamRouter::handle() exiting');
                    return $response;
                } catch (\Throwable $e) {
                    error_log("IslamRouter: Exception during controller dispatch: " . $e->getMessage());
                    error_log("IslamRouter: Stack trace: " . $e->getTraceAsString());
                    return new Response(500, ['Content-Type' => 'text/plain'], 'Internal Server Error: ' . $e->getMessage());
                }
            }
            
            // Handle callable
            if (is_callable($handler)) {
                error_log("IslamRouter: Calling route handler closure");
                return $handler($request, ...array_values($vars));
            }
            
            error_log("IslamRouter: Invalid route handler");
            throw new \RuntimeException('Invalid route handler');
        };
        
        // Convert PSR-7 request to our Request class for middleware
        $ourRequest = \IslamWiki\Core\Http\Request::fromPsr7($request);
        
        // Temporarily bypass middleware stack to test if that's causing the issue
        error_log('IslamRouter: Bypassing middleware stack for testing');
        $response = $finalHandler($ourRequest);
        error_log('IslamRouter: Direct handler execution completed');
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
        
        error_log('IslamRouter: Initializing middleware stack');
        
        try {
            $logger = $this->container->get(\Psr\Log\LoggerInterface::class);
            $this->middlewareStack = new \IslamWiki\Http\Middleware\MiddlewareStack($logger);
            
            // Add global middleware with error handling
            try {
                $this->middlewareStack->add(new \IslamWiki\Http\Middleware\ErrorHandlingMiddleware(
                    $logger,
                    $this->container->has('app.debug') ? $this->container->get('app.debug') : false,
                    $this->container->has('app.env') ? $this->container->get('app.env') : 'production'
                ));
                error_log('IslamRouter: ErrorHandlingMiddleware added');
            } catch (\Throwable $e) {
                error_log('IslamRouter: Error adding ErrorHandlingMiddleware: ' . $e->getMessage());
            }
            
            try {
                $this->middlewareStack->add(new \IslamWiki\Http\Middleware\SecurityMiddleware($logger));
                error_log('IslamRouter: SecurityMiddleware added');
            } catch (\Throwable $e) {
                error_log('IslamRouter: Error adding SecurityMiddleware: ' . $e->getMessage());
            }
            
            try {
                $sessionManager = $this->container->has(\IslamWiki\Core\Session\SessionManager::class) 
                    ? $this->container->get(\IslamWiki\Core\Session\SessionManager::class)
                    : new \IslamWiki\Core\Session\SessionManager();
                $this->middlewareStack->add(new \IslamWiki\Http\Middleware\CsrfMiddleware($sessionManager));
                error_log('IslamRouter: CsrfMiddleware added');
            } catch (\Throwable $e) {
                error_log('IslamRouter: Error adding CsrfMiddleware: ' . $e->getMessage());
            }
            
            error_log('IslamRouter: Middleware stack initialized with ' . $this->middlewareStack->count() . ' middleware');
        } catch (\Throwable $e) {
            error_log('IslamRouter: Error initializing middleware stack: ' . $e->getMessage());
            // Create a minimal middleware stack if there's an error
            $this->middlewareStack = new \IslamWiki\Http\Middleware\MiddlewareStack(
                $this->container->get(\Psr\Log\LoggerInterface::class)
            );
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