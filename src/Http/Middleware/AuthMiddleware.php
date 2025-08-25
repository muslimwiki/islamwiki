<?php

declare(strict_types=1);

namespace IslamWiki\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\Container;

/**
 * Authentication Middleware
 * Checks if user is authenticated before allowing access to protected routes
 */
class AuthMiddleware
{
    private Container $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Process the request
     */
    public function process(Request $request, callable $next): Response
    {
        // Check if user is authenticated
        if (!$this->isAuthenticated($request)) {
            // Redirect to login page
            return new Response(302, ["Location" => "/en/login"], "");
        }
        
        // User is authenticated, continue to next middleware/route
        return $next($request);
    }
    
    /**
     * Check if user is authenticated
     */
    private function isAuthenticated(Request $request): bool
    {
        // For now, just check if there's a session cookie
        // In a real application, this would check the session and validate the user
        $cookies = $request->getCookieParams();
        
        // Check if user is accessing admin routes
        $path = $request->getUri()->getPath();
        if (strpos($path, "/admin") !== false) {
            // Admin routes require special authentication
            return $this->isAdmin($request);
        }
        
        // For now, allow all non-admin routes (simplified for testing)
        return true;
    }
    
    /**
     * Check if user is admin
     */
    private function isAdmin(Request $request): bool
    {
        // For now, just check if there's an admin cookie
        // In a real application, this would check user roles and permissions
        $cookies = $request->getCookieParams();
        
        // Simplified admin check - in production this would validate against database
        return isset($cookies["admin"]) && $cookies["admin"] === "true";
    }
}

/**
 * Admin Middleware
 * Checks if user has admin privileges
 */
class AdminMiddleware
{
    private Container $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Process the request
     */
    public function process(Request $request, callable $next): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            // Return 403 Forbidden
            return new Response(403, ["Content-Type" => "text/html"], "Access Denied - Admin privileges required");
        }
        
        // User is admin, continue to next middleware/route
        return $next($request);
    }
    
    /**
     * Check if user is admin
     */
    private function isAdmin(Request $request): bool
    {
        // Simplified admin check - in production this would validate against database
        $cookies = $request->getCookieParams();
        return isset($cookies["admin"]) && $cookies["admin"] === "true";
    }
}

/**
 * Guest Middleware
 * Ensures only non-authenticated users can access certain routes (like login/register)
 */
class GuestMiddleware
{
    private Container $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Process the request
     */
    public function process(Request $request, callable $next): Response
    {
        // Check if user is already authenticated
        if ($this->isAuthenticated($request)) {
            // Redirect to dashboard if already logged in
            return new Response(302, ["Location" => "/en/dashboard"], "");
        }
        
        // User is not authenticated, continue to next middleware/route
        return $next($request);
    }
    
    /**
     * Check if user is authenticated
     */
    private function isAuthenticated(Request $request): bool
    {
        // Simplified check - in production this would validate against database
        $cookies = $request->getCookieParams();
        return isset($cookies["user"]) && $cookies["user"] === "true";
    }
} 