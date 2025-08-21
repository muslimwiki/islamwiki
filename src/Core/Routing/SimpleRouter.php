<?php

declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\AsasContainer;

/**
 * Simple Router for IslamWiki
 * 
 * This router provides a simple interface that matches what the route files expect.
 * It's designed to work with the existing route files while providing basic routing functionality.
 */
class SimpleRouter
{
    /**
     * The service container.
     */
    protected AsasContainer $container;
    
    /**
     * Registered routes.
     */
    protected array $routes = [];
    
    /**
     * Constructor.
     */
    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
    }
    
    /**
     * Register a GET route.
     */
    public function get(string $path, $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }
    
    /**
     * Register a POST route.
     */
    public function post(string $path, $handler): self
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }
    
    /**
     * Register a PUT route.
     */
    public function put(string $path, $handler): self
    {
        $this->routes['PUT'][$path] = $handler;
        return $this;
    }
    
    /**
     * Register a DELETE route.
     */
    public function delete(string $path, $handler): self
    {
        $this->routes['DELETE'][$path] = $handler;
        return $this;
    }
    
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request): Response
    {
        $path = $request->getPath();
        $method = $request->getMethod();
        
        // Look for a matching route
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            
            // Execute the route handler
            if (is_callable($handler)) {
                $response = call_user_func($handler, $request);
                if ($response instanceof Response) {
                    return $response;
                }
            } elseif (is_array($handler)) {
                // Controller method call
                [$controller, $method] = $handler;
                if (is_object($controller) && method_exists($controller, $method)) {
                    $response = $controller->$method($request);
                    if ($response instanceof Response) {
                        return $response;
                    }
                }
            }
        }
        
        // If no route found, return 404 response
        return new Response('Page not found', 404);
    }
} 