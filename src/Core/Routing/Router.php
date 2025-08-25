<?php

/**
 * Core Router
 *
 * Centralized routing system for IslamWiki.
 * Handles HTTP routing and request dispatching.
 *
 * @package IslamWiki\Core\Routing
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Core Router - Centralized Routing System
 *
 * This class provides comprehensive routing capabilities for
 * handling HTTP requests and dispatching to appropriate handlers.
 */
class Router
{
    /**
     * The logging system instance.
     */
    protected Logger $logger;

    /**
     * Registered routes.
     */
    private array $routes = [];

    /**
     * Route groups.
     */
    private array $groups = [];

    /**
     * Create a new router instance.
     *
     * @param Logger $logger The logging system
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->logger->info('Router system initialized');
    }

    /**
     * Register a GET route.
     *
     * @param string $path The route path
     * @param callable|array $handler The route handler
     * @return self
     */
    public function get(string $path, $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route.
     *
     * @param string $path The route path
     * @param callable|array $handler The route handler
     * @return self
     */
    public function post(string $path, $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route.
     *
     * @param string $path The route path
     * @param callable|array $handler The route handler
     * @return self
     */
    public function put(string $path, $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Register a DELETE route.
     *
     * @param string $path The route path
     * @param callable|array $handler The route handler
     * @return self
     */
    public function delete(string $path, $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add a route.
     *
     * @param string $method The HTTP method
     * @param string $path The route path
     * @param callable|array $handler The route handler
     * @return self
     */
    private function addRoute(string $method, string $path, $handler): self
    {
        $this->routes[$method][$path] = $handler;
        $this->logger->info("Route registered: {$method} {$path}");
        
        return $this;
    }

    /**
     * Dispatch a request to the appropriate handler.
     *
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $this->logger->info("Dispatching request: {$method} {$path}");

        // Check for exact match
        if (isset($this->routes[$method][$path])) {
            return $this->executeHandler($this->routes[$method][$path], $request);
        }

        // Check for pattern match
        foreach ($this->routes[$method] ?? [] as $routePath => $handler) {
            if ($this->matchPattern($routePath, $path)) {
                return $this->executeHandler($handler, $request);
            }
        }

        // No route found
        $this->logger->warning("No route found for: {$method} {$path}");
        return new Response(404, [], 'Route not found');
    }

    /**
     * Execute a route handler.
     *
     * @param callable|array $handler The route handler
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    private function executeHandler($handler, Request $request): Response
    {
        try {
            if (is_callable($handler)) {
                return $handler($request);
            }

            if (is_array($handler) && count($handler) === 2) {
                [$controller, $method] = $handler;
                if (is_object($controller) && method_exists($controller, $method)) {
                    return $controller->$method($request);
                }
            }

            throw new \InvalidArgumentException('Invalid route handler');
        } catch (\Exception $e) {
            $this->logger->error('Route handler execution failed', [
                'error' => $e->getMessage(),
                'handler' => is_array($handler) ? (is_object($handler[0]) ? get_class($handler[0]) . '::' . $handler[1] : implode('::', $handler)) : 'closure'
            ]);
            
            return new Response(500, [], 'Internal server error');
        }
    }

    /**
     * Check if a route pattern matches a path.
     *
     * @param string $pattern The route pattern
     * @param string $path The request path
     * @return bool True if pattern matches
     */
    private function matchPattern(string $pattern, string $path): bool
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        return (bool) preg_match($pattern, $path);
    }

    /**
     * Get all registered routes.
     *
     * @return array The registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * Create a route group
     */
    public function group(string $prefix = '', array $middleware = [], array $attributes = []): \IslamWiki\Core\Routing\RouteGroup
    {
        return new \IslamWiki\Core\Routing\RouteGroup($this, $prefix, $middleware, $attributes);
    }

    /**
     * Clear all routes.
     */
    public function clearRoutes(): void
    {
        $this->routes = [];
        $this->logger->info('All routes cleared');
    }
} 