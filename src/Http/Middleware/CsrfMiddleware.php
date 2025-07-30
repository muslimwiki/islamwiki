<?php

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

declare(strict_types=1);

namespace IslamWiki\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Session\SessionManager;

/**
 * CSRF Protection Middleware
 * 
 * Protects forms from cross-site request forgery attacks.
 */
class CsrfMiddleware
{
    /**
     * @var SessionManager Session manager instance
     */
    private SessionManager $session;
    
    /**
     * @var array Routes that should be excluded from CSRF protection
     */
    private array $excludedRoutes = [
        '/api/',
        '/webhook/',
    ];
    
    /**
     * Create a new CSRF middleware instance.
     */
    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }
    
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, callable $next): Response
    {
        // Skip CSRF check for excluded routes
        if ($this->shouldSkipCsrfCheck($request)) {
            return $next($request);
        }
        
        // Only check CSRF for POST, PUT, PATCH, DELETE requests
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $this->getTokenFromRequest($request);
            
            if (!$token || !$this->session->verifyCsrfToken($token)) {
                return new Response(
                    status: 403,
                    headers: ['Content-Type' => 'text/html'],
                    body: $this->renderCsrfError()
                );
            }
        }
        
        return $next($request);
    }
    
    /**
     * Check if CSRF check should be skipped for this request.
     */
    private function shouldSkipCsrfCheck(Request $request): bool
    {
        $path = $request->getUri()->getPath();
        
        foreach ($this->excludedRoutes as $excludedRoute) {
            if (str_starts_with($path, $excludedRoute)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get CSRF token from the request.
     */
    private function getTokenFromRequest(Request $request): ?string
    {
        // Check for token in POST data
        $token = $request->getPostParam('_token');
        if ($token) {
            return $token;
        }
        
        // Check for token in headers
        $token = $request->getHeader('X-CSRF-TOKEN');
        if ($token) {
            return $token;
        }
        
        // Check for token in X-XSRF-TOKEN header (Laravel style)
        $token = $request->getHeader('X-XSRF-TOKEN');
        if ($token) {
            return urldecode($token);
        }
        
        return null;
    }
    
    /**
     * Render CSRF error page.
     */
    private function renderCsrfError(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>CSRF Token Mismatch</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .error { color: #d32f2f; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Security Error</h1>
    <div class="error">
        <p>CSRF token mismatch. This could be due to:</p>
        <ul style="text-align: left; display: inline-block;">
            <li>Your session has expired</li>
            <li>The form was submitted from a different page</li>
            <li>A security issue with your request</li>
        </ul>
    </div>
    <p><a href="/" class="btn">Return to Homepage</a></p>
</body>
</html>';
    }
} 