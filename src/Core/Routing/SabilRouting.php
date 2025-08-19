<?php

/**
 * SabilRouting - Core routing system for IslamWiki
 *
 * @category  IslamWiki
 * @package   Core\Routing
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://islam.wiki
 * @since     0.0.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * SabilRouting - Core routing system for IslamWiki
 *
 * @category  IslamWiki
 * @package   Core\Routing
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://islam.wiki
 * @since     0.0.1
 */
class SabilRouting implements RequestHandlerInterface
{
    private $_container;
    private $_routes = [];
    private $_middlewareStack;

    /**
     * Constructor
     *
     * @param mixed $container The dependency injection container
     */
    public function __construct($container)
    {
        $this->_container = $container;
    }

    /**
     * Get the container instance.
     *
     * @return mixed
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * Map a route with the given HTTP methods, path, and handler.
     *
     * @param string|array $methods   HTTP method(s) (GET, POST, etc.)
     * @param string       $route     The route pattern
     * @param mixed        $handler   The handler for the route
     * @param array        $middleware Middleware to apply to the route
     *
     * @return $this
     */
    public function map($methods, $route, $handler, array $middleware = [])
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        $routeData = [
            'methods' => array_map('strtoupper', $methods),
            'route' => $route,
            'handler' => $handler,
            'middleware' => $middleware
        ];

        $this->_routes[] = $routeData;

        return $this;
    }

    /**
     * Add a GET route.
     *
     * @param string $route      The route pattern
     * @param mixed  $handler    The handler for the route
     * @param array  $middleware Middleware to apply to the route
     *
     * @return $this
     */
    public function get($route, $handler, array $middleware = [])
    {
        return $this->map('GET', $route, $handler, $middleware);
    }

    /**
     * Add a POST route.
     *
     * @param string $route      The route pattern
     * @param mixed  $handler    The handler for the route
     * @param array  $middleware Middleware to apply to the route
     *
     * @return $this
     */
    public function post($route, $handler, array $middleware = [])
    {
        return $this->map('POST', $route, $handler, $middleware);
    }

    /**
     * Add a PUT route.
     *
     * @param string $route      The route pattern
     * @param mixed  $handler    The handler for the route
     * @param array  $middleware Middleware to apply to the route
     *
     * @return $this
     */
    public function put($route, $handler, array $middleware = [])
    {
        return $this->map('PUT', $route, $handler, $middleware);
    }

    /**
     * Add a DELETE route.
     *
     * @param string $route      The route pattern
     * @param mixed  $handler    The handler for the route
     * @param array  $middleware Middleware to apply to the route
     *
     * @return $this
     */
    public function delete($route, $handler, array $middleware = [])
    {
        return $this->map('DELETE', $route, $handler, $middleware);
    }

    /**
     * Add a PATCH route.
     *
     * @param string $route      The route pattern
     * @param mixed  $handler    The handler for the route
     * @param array  $middleware Middleware to apply to the route
     *
     * @return $this
     */
    public function patch($route, $handler, array $middleware = [])
    {
        return $this->map('PATCH', $route, $handler, $middleware);
    }

    /**
     * Add a route that matches any HTTP method.
     *
     * @param string $route      The route pattern
     * @param mixed  $handler    The handler for the route
     * @param array  $middleware Middleware to apply to the route
     *
     * @return $this
     */
    public function any($route, $handler, array $middleware = [])
    {
        return $this->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $route, $handler, $middleware);
    }

    /**
     * Handle the incoming request.
     *
     * @param ServerRequestInterface $request The incoming request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        


        $uri = $request->getUri()->getPath();

        // Find matching route
        $route = $this->findRoute($method, $uri);

        if ($route === null) {
            return new Response(404, [], $this->renderErrorPage(404, 'Page not found'));
        }

        // Get parameters from the route (already extracted in findRoute)
        $params = $route['params'] ?? [];

        // Add parameters to request
        $request = $request->withAttribute('params', $params);

        // Execute middleware stack
        $this->initializeMiddlewareStack();
        
        // Execute global middleware
        $middlewareResponse = $this->executeMiddlewareStack($request);
        if ($middlewareResponse !== null) {
            return $middlewareResponse;
        }

        // Execute handler
        $handler = $route['handler'];

        if (is_callable($handler)) {
            // Try to call with parameters, fallback to just request if it fails
            try {
                $methodParams = array_values($params);
                array_unshift($methodParams, $request);
                $response = call_user_func_array($handler, $methodParams);
            } catch (\ArgumentCountError $e) {
                // Fallback to just passing the request
                $response = call_user_func($handler, $request);
            }
        } elseif (is_string($handler)) {
            // Controller@method format
            if (strpos($handler, '@') !== false) {
                [$controller, $method] = explode('@', $handler);
                
                error_log("ROUTER: Creating controller instance: $controller");
                
                // Get database and container from container
                $db = $this->_container->get('db');
                $container = $this->_container;
                
                error_log("ROUTER: Got db and container, creating controller");
                
                try {
                    $controllerInstance = new $controller($db, $container);
                    error_log("ROUTER: Controller created successfully: " . get_class($controllerInstance));
                    
                    // Pass route parameters to the method
                    $methodParams = array_values($params);
                    array_unshift($methodParams, $request);
                    error_log("ROUTER: Calling method $method with params: " . print_r($methodParams, true));
                    
                    $response = call_user_func_array([$controllerInstance, $method], $methodParams);
                    error_log("ROUTER: Method call successful, response type: " . gettype($response));
                } catch (\Throwable $e) {
                    error_log("ROUTER: Exception creating/calling controller: " . $e->getMessage());
                    error_log("ROUTER: Exception trace: " . $e->getTraceAsString());
                    throw $e;
                }
            } else {
                // Just controller class
                $db = $this->_container->get('db');
                $container = $this->_container;
                
                $controllerInstance = new $handler($db, $container);
                $response = $controllerInstance->handle($request);
            }
        } else {
            return new Response(500, [], $this->renderErrorPage(500, 'Invalid handler'));
        }

        if (!$response instanceof ResponseInterface) {
            return new Response(500, [], $this->renderErrorPage(500, 'Handler did not return a valid response'));
        }

        return $response;
    }

    /**
     * Find a route that matches the given method and URI.
     *
     * @param string $method The HTTP method
     * @param string $uri    The URI to match
     *
     * @return array|null
     */
    private function findRoute(string $method, string $uri): ?array
    {
        foreach ($this->_routes as $route) {
            if (!in_array($method, $route['methods'])) {
                continue;
            }
            
            $params = $this->matchRoute($route['route'], $uri);
            
            if ($params !== null) {
                // Store the parameters in the route array
                $route['params'] = $params;
                return $route;
            }
        }

        return null;
    }

    /**
     * Match a route pattern against a URI.
     *
     * @param string $pattern The route pattern
     * @param string $uri     The URI to match
     *
     * @return array|null
     */
    private function matchRoute(string $pattern, string $uri): ?array
    {
        $regex = $this->patternToRegex($pattern);

        if (preg_match($regex, $uri, $matches)) {
            // Remove the full match from the beginning
            array_shift($matches);

            // Convert numeric keys to parameter names
            $params = [];
            preg_match_all('/\{([^}]+)\}/', $pattern, $paramNames);

            foreach ($paramNames[1] as $index => $paramName) {
                // Support tokens like name:regex by stripping the regex part for the param key
                if (strpos($paramName, ':') !== false) {
                    [$paramName] = explode(':', $paramName, 2);
                }
                if (isset($matches[$index])) {
                    $params[$paramName] = $matches[$index];
                }
            }

            return $params;
        }

        return null;
    }

    /**
     * Convert a route pattern to a regex pattern.
     *
     * @param string $pattern The route pattern
     *
     * @return string
     */
    private function patternToRegex(string $pattern): string
    {
        // Replace {name} with default capture and {name:regex} with custom capture
        $pattern = preg_replace_callback('/\{([^}]+)\}/', function ($m) {
            $token = $m[1];
            $name = $token;
            $regex = '[^/]+'; // default: segment without slash
            
            if (strpos($token, ':') !== false) {
                [$name, $regexPart] = explode(':', $token, 2);
                if ($regexPart !== '') {
                    $regex = $regexPart;
                }
            }
            
            return '(' . $regex . ')';
        }, $pattern);

        // Escape forward slashes
        $escapedPattern = str_replace('/', '\/', $pattern);
        $finalRegex = '/^' . $escapedPattern . '$/';
        
        return $finalRegex;
    }

    /**
     * Add middleware to the global middleware stack.
     *
     * @param callable $middleware The middleware to add
     *
     * @return void
     */
    public function addMiddleware(callable $middleware): void
    {
        $this->initializeMiddlewareStack();
        $this->_middlewareStack[] = $middleware;
    }

    /**
     * Execute the middleware stack.
     *
     * @param ServerRequestInterface $request The request to process
     *
     * @return ResponseInterface|null
     */
    private function executeMiddlewareStack(ServerRequestInterface $request): ?ResponseInterface
    {
        if (empty($this->_middlewareStack)) {
            return null;
        }

        // Create a final next function that will execute the route handler
        $finalNext = function (ServerRequestInterface $request) {
            // This will be called by the last middleware
            // Return null to indicate we should continue to the route handler
            return null;
        };

        // Build middleware chain from last to first
        $next = $finalNext;
        for ($i = count($this->_middlewareStack) - 1; $i >= 0; $i--) {
            $middleware = $this->_middlewareStack[$i];
            $currentNext = $next;
            $next = function (ServerRequestInterface $request) use ($middleware, $currentNext) {
                return $middleware($request, $currentNext);
            };
        }

        // Execute the first middleware in the chain
        $response = $next($request);
        
        // If middleware returns null, continue to route handler
        // If middleware returns a response, return it immediately
        return $response;
    }

    /**
     * Initialize the middleware stack.
     *
     * @return void
     */
    private function initializeMiddlewareStack(): void
    {
        if ($this->_middlewareStack === null) {
            $this->_middlewareStack = [];
        }
    }

    /**
     * Render an error page.
     *
     * @param int    $status  The HTTP status code
     * @param string $message The error message
     *
     * @return string
     */
    protected function renderErrorPage(int $status, string $message): string
    {
        try {
            // Try to use Twig template for better error pages
            $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__DIR__));
            $templatePath = $basePath . '/resources/views/errors/' . $status . '.twig';
            
            if (file_exists($templatePath)) {
                // Create Twig renderer
                $twigRenderer = new \IslamWiki\Core\View\TwigRenderer(
                    $basePath . '/resources/views',
                    false, // Disable cache for now
                    true // Debug mode
                );
                
                // Prepare template data
                $templateData = [
                    'status_code' => $status,
                    'message' => $message,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                    'request_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                    'request_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    'php_version' => PHP_VERSION,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
                    'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                    'memory_limit' => ini_get('memory_limit'),
                    'debug' => true, // Always show debug info for errors
                ];
                
                // Render the template
                return $twigRenderer->render('errors/' . $status . '.twig', $templateData);
            }
        } catch (\Exception $e) {
            // If Twig rendering fails, fall back to basic HTML
            error_log("Failed to render Twig error page: " . $e->getMessage());
        }
        
        // Fallback to basic HTML if template doesn't exist or Twig fails
        return "<!DOCTYPE html>
<html>
<head>
    <title>Error {$status}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .error { color: #721c24; background-color: #f8d7da; padding: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Error {$status}</h1>
    <div class='error'>{$message}</div>
</body>
</html>";
    }
    
    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes     The number of bytes
     * @param int $precision The number of decimal places
     *
     * @return string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
