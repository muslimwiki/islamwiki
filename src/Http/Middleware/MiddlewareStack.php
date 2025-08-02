<?php
declare(strict_types=1);

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace IslamWiki\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use Psr\Log\LoggerInterface;

/**
 * Middleware Stack
 * 
 * Manages the execution order of middleware components.
 * Middleware is executed in the order they are added.
 */
class MiddlewareStack
{
    /**
     * @var array Array of middleware instances
     */
    private array $middleware = [];
    
    /**
     * @var LoggerInterface Logger instance
     */
    private LoggerInterface $logger;
    
    /**
     * Create a new middleware stack instance.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Add middleware to the stack.
     */
    public function add($middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    /**
     * Add multiple middleware to the stack.
     */
    public function addMultiple(array $middleware): self
    {
        foreach ($middleware as $mw) {
            $this->add($mw);
        }
        return $this;
    }
    
    /**
     * Execute the middleware stack.
     */
    public function execute(Request $request, callable $handler): Response
    {
        $this->logger->debug('Executing middleware stack', [
            'middleware_count' => count($this->middleware),
            'uri' => $request->getUri()->getPath(),
        ]);
        
        error_log("MiddlewareStack::execute - Starting with " . count($this->middleware) . " middleware");
        
        // Create the middleware chain
        $chain = $this->buildChain($handler);
        
        // Execute the chain
        $response = $chain($request);
        
        error_log("MiddlewareStack::execute - Completed, response status: " . $response->getStatusCode());
        
        return $response;
    }
    
    /**
     * Build the middleware chain.
     */
    private function buildChain(callable $handler): callable
    {
        // Start with the final handler
        $chain = $handler;
        
        // Add middleware in reverse order (last added is executed first)
        for ($i = count($this->middleware) - 1; $i >= 0; $i--) {
            $middleware = $this->middleware[$i];
            $chain = $this->wrapMiddleware($middleware, $chain);
        }
        
        return $chain;
    }
    
    /**
     * Wrap middleware with error handling.
     */
    private function wrapMiddleware($middleware, callable $next): callable
    {
        return function (Request $request) use ($middleware, $next) {
            try {
                $this->logger->debug('Executing middleware', [
                    'middleware_class' => get_class($middleware),
                    'uri' => $request->getUri()->getPath(),
                ]);
                
                error_log("MiddlewareStack::wrapMiddleware - Executing " . get_class($middleware));
                
                if (is_callable($middleware)) {
                    return $middleware($request, $next);
                }
                
                if (method_exists($middleware, 'handle')) {
                    return $middleware->handle($request, $next);
                }
                
                throw new \RuntimeException('Invalid middleware: ' . get_class($middleware));
                
            } catch (\Throwable $e) {
                $this->logger->error('Middleware execution failed', [
                    'middleware_class' => get_class($middleware),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                error_log("MiddlewareStack::wrapMiddleware - Error in " . get_class($middleware) . ": " . $e->getMessage());
                
                throw $e;
            }
        };
    }
    
    /**
     * Get the number of middleware in the stack.
     */
    public function count(): int
    {
        return count($this->middleware);
    }
    
    /**
     * Clear all middleware from the stack.
     */
    public function clear(): self
    {
        $this->middleware = [];
        return $this;
    }
    
    /**
     * Get all middleware in the stack.
     */
    public function getAll(): array
    {
        return $this->middleware;
    }
} 