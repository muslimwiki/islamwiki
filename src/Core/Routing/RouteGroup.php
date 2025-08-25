<?php

declare(strict_types=1);

namespace IslamWiki\Core\Routing;

/**
 * Route Group for organizing related routes
 */
class RouteGroup
{
    private Router $router;
    private string $prefix;
    private array $middleware;
    private array $attributes;
    
    public function __construct(Router $router, string $prefix = '', array $middleware = [], array $attributes = [])
    {
        $this->router = $router;
        $this->prefix = $prefix;
        $this->middleware = $middleware;
        $this->attributes = $attributes;
    }
    
    /**
     * Add a GET route to the group
     */
    public function get(string $path, $handler): self
    {
        $fullPath = $this->prefix . $path;
        $this->router->get($fullPath, $handler);
        return $this;
    }
    
    /**
     * Add a POST route to the group
     */
    public function post(string $path, $handler): self
    {
        $fullPath = $this->prefix . $path;
        $this->router->post($fullPath, $handler);
        return $this;
    }
    
    /**
     * Add a PUT route to the group
     */
    public function put(string $path, $handler): self
    {
        $fullPath = $this->prefix . $path;
        $this->router->put($fullPath, $path);
        return $this;
    }
    
    /**
     * Add a DELETE route to the group
     */
    public function delete(string $path, $handler): self
    {
        $fullPath = $this->prefix . $path;
        $this->router->delete($fullPath, $handler);
        return $this;
    }
    
    /**
     * Add middleware to the group
     */
    public function middleware(array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }
    
    /**
     * Add attributes to the group
     */
    public function attributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }
    
    /**
     * Get the router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
    
    /**
     * Get the group prefix
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
    
    /**
     * Get the group middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
    
    /**
     * Get the group attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
} 