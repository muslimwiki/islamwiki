<?php

declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Logging\ShahidLogger;

/**
 * Routing - Simple, working routing system
 * 
 * Simple, working routing system that actually works
 * that actually works and is easy to debug.
 */
class Routing
{
    /**
     * The logging system.
     */
    protected ShahidLogger $logger;

    /**
     * Registered routes.
     */
    protected array $routes = [];

    /**
     * Constructor.
     */
    public function __construct(ShahidLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Register a GET route.
     */
    public function get(string $path, $handler, array $options = []): self
    {
        return $this->addRoute('GET', $path, $handler, $options);
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, $handler, array $options = []): self
    {
        return $this->addRoute('POST', $path, $handler, $options);
    }

    /**
     * Register a PUT route.
     */
    public function put(string $path, $handler, array $options = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $options);
    }

    /**
     * Register a DELETE route.
     */
    public function delete(string $path, $handler, array $options = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $options);
    }

    /**
     * Add a route.
     */
    protected function addRoute(string $method, string $path, $handler, array $options = []): self
    {
        $route = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'options' => $options,
        ];

        $this->routes[] = $route;
        $this->logger->info("Route registered: {$method} {$path}", [
            'total_routes' => count($this->routes),
            'all_routes' => array_map(function($r) { return $r['method'] . ' ' . $r['path']; }, $this->routes)
        ]);

        return $this;
    }

    /**
     * Find a route by method and path.
     */
    public function findRoute(string $method, string $path): ?array
    {
        $method = strtoupper($method);
        
        // Handle HEAD requests by treating them as GET requests
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        
        // Debug logging
        $this->logger->info('Route lookup attempt', [
            'original_method' => $method,
            'path' => $path,
            'total_routes' => count($this->routes),
            'available_routes' => array_map(function($r) { return $r['method'] . ' ' . $r['path']; }, $this->routes),
            'routes_array' => $this->routes
        ]);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $this->logger->info('Route found', ['route' => $route]);
                return $route;
            }
        }

        $this->logger->info('Route not found');
        return null;
    }

    /**
     * Match a route path against a request path.
     */
    protected function matchPath(string $routePath, string $requestPath): bool
    {
        // Debug logging
        $this->logger->info('Path matching attempt', [
            'routePath' => $routePath,
            'requestPath' => $requestPath
        ]);

        // Exact match
        if ($routePath === $requestPath) {
            $this->logger->info('Exact match found');
            return true;
        }
        
        // Handle dynamic routes with parameters like {slug}
        if (strpos($routePath, '{') !== false) {
            $routeParts = explode('/', trim($routePath, '/'));
            $requestParts = explode('/', trim($requestPath, '/'));
            
            $this->logger->info('Dynamic route matching', [
                'routeParts' => $routeParts,
                'requestParts' => $requestParts
            ]);
            
            // Must have same number of parts
            if (count($routeParts) !== count($requestParts)) {
                $this->logger->info('Part count mismatch', [
                    'routeParts' => count($routeParts),
                    'requestParts' => count($requestParts)
                ]);
                return false;
            }
            
            // Check each part
            for ($i = 0; $i < count($routeParts); $i++) {
                $routePart = $routeParts[$i];
                $requestPart = $requestParts[$i] ?? '';
                
                $this->logger->info('Checking part', [
                    'index' => $i,
                    'routePart' => $routePart,
                    'requestPart' => $requestPart
                ]);
                
                // If route part is a parameter (starts with {), it matches anything
                if (strpos($routePart, '{') === 0 && strpos($routePart, '}') !== false) {
                    $this->logger->info('Parameter part matches anything');
                    continue; // Parameter matches anything
                }
                
                // Exact match required for non-parameters
                if ($routePart !== $requestPart) {
                    $this->logger->info('Part mismatch');
                    return false;
                }
            }
            
            $this->logger->info('Dynamic route matched successfully');
            return true;
        }
        
        $this->logger->info('No match found');
        return false;
    }

    /**
     * Get all registered routes.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Clear all routes.
     */
    public function clearRoutes(): self
    {
        $this->routes = [];
        return $this;
    }
} 