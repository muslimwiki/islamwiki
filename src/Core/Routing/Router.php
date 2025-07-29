<?php
declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Application;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

/**
 * Router class that handles HTTP routing and request handling.
 * 
 * This class implements PSR-15's RequestHandlerInterface and uses FastRoute
 * internally for route matching and dispatching.
 */
class Router implements RequestHandlerInterface
{
    protected static ?self $instance = null;
    protected static ?Application $staticApp = null;
    protected static ?\Psr\Log\LoggerInterface $logger = null;
    protected static bool $routesInitialized = false;
    protected static array $routes = [];
    protected $dispatcher;
    protected static array $routeQueue = [];
    protected static bool $initialized = false;
    protected static ?ControllerFactory $controllerFactory = null;
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->initDispatcher();
        $this->processQueuedRoutes();
    }
    
    /**
     * Process any routes that were queued before the router was initialized.
     * 
     * @return void
     */
    protected function processQueuedRoutes(): void
    {
        if (!empty(self::$routeQueue)) {
            foreach (self::$routeQueue as $route) {
                $this->map(...$route);
            }
            self::$routeQueue = [];
            $this->initDispatcher(); // Re-initialize dispatcher with all routes
        }
    }

    protected function initDispatcher(): void
    {
        $this->dispatcher = simpleDispatcher(function(RouteCollector $r) {
            foreach (self::$routes as $route) {
                $r->addRoute($route['method'], $route['path'], $route['handler']);
            }
        });
    }

    public static function getInstance(?Application $app = null): self
    {
        if (self::$instance === null) {
            if ($app === null) {
                if (self::$staticApp === null) {
                    throw new \RuntimeException('No application instance provided to Router::getInstance()');
                }
                $app = self::$staticApp;
            } else {
                self::$staticApp = $app;
            }
            self::$instance = new self($app);
            self::$initialized = true;
            
            if (!empty(self::$routeQueue)) {
                foreach (self::$routeQueue as $route) {
                    self::map(...$route);
                }
                self::$routeQueue = [];
                self::$instance->initDispatcher();
            }
        }
        return self::$instance;
    }

    public static function setLogger(\Psr\Log\LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function setApplication(Application $app): void
    {
        self::$staticApp = $app;
    }

    public static function setControllerFactory(ControllerFactory $factory): void
    {
        self::$controllerFactory = $factory;
    }

    protected static function getControllerFactory(): ControllerFactory
    {
        if (self::$controllerFactory === null) {
            throw new \RuntimeException('Controller factory has not been set. Call Router::setControllerFactory() first.');
        }
        return self::$controllerFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Implementation of handle method
        // This is a simplified version - add your actual implementation here
        return new Response(200, [], 'Response from router');
    }
    
    /**
     * Map a route with the given HTTP methods and handler.
     *
     * @param string|array $methods HTTP method(s) to match (GET, POST, etc.)
     * @param string $path The URL path pattern to match
     * @param callable|string $handler The handler for the route
     * @param array $middleware Middleware to apply to the route
     * @return void
     */
    public static function map($methods, string $path, $handler, array $middleware = []): void
    {
        // Ensure methods is an array
        $httpMethods = (array) $methods;
        
        // If the router is already initialized, add the route directly
        if (self::$initialized && self::$instance !== null) {
            foreach ($httpMethods as $method) {
                self::$routes[] = [
                    'method' => strtoupper($method),
                    'path' => $path,
                    'handler' => $handler,
                    'middleware' => $middleware
                ];
            }
            // Re-initialize the dispatcher with the new routes
            self::$instance->initDispatcher();
        } else {
            // Queue the route to be added when the router is initialized
            foreach ($httpMethods as $method) {
                self::$routeQueue[] = [
                    strtoupper($method),
                    $path,
                    $handler,
                    $middleware
                ];
            }
        }
    }
}
