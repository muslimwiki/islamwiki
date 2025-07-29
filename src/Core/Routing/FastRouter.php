<?php
declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use IslamWiki\Core\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function FastRoute\simpleDispatcher;

class FastRouter implements RequestHandlerInterface
{
    private $dispatcher;
    private $container;
    private $routes = [];
    
    public function __construct($container)
    {
        $this->container = $container;
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
        error_log('FastRouter::handle() entered');
        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();
        error_log('FastRouter::handle() method=' . $httpMethod . ' uri=' . $uri);
        
        // Strip query string and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        
        // Create dispatcher
        $this->dispatcher = simpleDispatcher(function(RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['methods'], $route['route'], $route['handler']);
            }
        });
        
        // Dispatch
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return new Response(404, ['Content-Type' => 'text/html'], $this->renderErrorPage(404, '404 Not Found'));
                
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new Response(405, ['Content-Type' => 'text/html'], $this->renderErrorPage(405, '405 Method Not Allowed'));
                
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // Handle controller@action
                if (is_string($handler) && str_contains($handler, '@')) {
                    [$controllerClass, $method] = explode('@', $handler, 2);
                    error_log("FastRouter: Attempting to resolve controller: $controllerClass, method: $method");
                    if (!class_exists($controllerClass)) {
                        error_log("FastRouter: Controller class $controllerClass not found");
                        throw new \RuntimeException("Controller {$controllerClass} not found");
                    }
                    // Use ControllerFactory if available
                    $controllerFactory = null;
                    if (method_exists($this->container, 'get') && $this->container->has('controller.factory')) {
                        $controllerFactory = $this->container->get('controller.factory');
                    } elseif (class_exists('IslamWiki\\Core\\Routing\\ControllerFactory')) {
                        $controllerFactory = null;
                    }
                    try {
                        if ($controllerFactory && method_exists($controllerFactory, 'create')) {
                            error_log("FastRouter: Using ControllerFactory to create $controllerClass");
                            $controller = $controllerFactory->create($controllerClass);
                        } elseif ($this->container->has($controllerClass)) {
                            error_log("FastRouter: Using container to get $controllerClass");
                            $controller = $this->container->get($controllerClass);
                        } else {
                            error_log("FastRouter: Instantiating $controllerClass directly");
                            $controller = new $controllerClass();
                        }
                        error_log("FastRouter: Controller instance created: " . get_class($controller));
                        if (!method_exists($controller, $method)) {
                            error_log("FastRouter: Method $method not found in $controllerClass");
                            throw new \RuntimeException("Method {$method} not found in controller {$controllerClass}");
                        }
                        error_log("FastRouter: Calling $controllerClass->$method");
                        
                        // Convert GuzzleHttp\Psr7\ServerRequest to IslamWiki\Core\Http\Request
                        $convertedRequest = \IslamWiki\Core\Http\Request::capture();
                        
                        $response = $controller->$method($convertedRequest, ...array_values($vars));
                        error_log("FastRouter: $controllerClass->$method call completed");
                        error_log('FastRouter::handle() exiting');
                        return $response;
                    } catch (\Throwable $e) {
                        error_log("FastRouter: Exception during controller dispatch: " . $e->getMessage());
                        error_log("FastRouter: Stack trace: " . $e->getTraceAsString());
                        return new Response(500, ['Content-Type' => 'text/plain'], 'Internal Server Error: ' . $e->getMessage());
                    }
                }
                // Handle callable
                if (is_callable($handler)) {
                    error_log("FastRouter: Calling route handler closure");
                    return $handler($request, ...array_values($vars));
                }
                error_log("FastRouter: Invalid route handler");
                throw new \RuntimeException('Invalid route handler');
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
