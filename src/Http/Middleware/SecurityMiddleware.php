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
use IslamWiki\Core\Http\Exceptions\HttpException;
use Psr\Log\LoggerInterface;

/**
 * Security Middleware
 * 
 * Provides comprehensive security features including:
 * - Security headers
 * - Input validation and sanitization
 * - Rate limiting
 * - XSS protection
 * - SQL injection prevention
 */
class SecurityMiddleware
{
    /**
     * @var LoggerInterface Logger instance
     */
    private LoggerInterface $logger;
    
    /**
     * @var array Rate limiting storage (in production, use Redis)
     */
    private static array $rateLimitStore = [];
    
    /**
     * @var array Security headers configuration
     */
    private array $securityHeaders = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://unpkg.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com;",
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    ];
    
    /**
     * @var array Rate limiting configuration
     */
    private array $rateLimitConfig = [
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
        'burst_limit' => 10,
    ];
    
    /**
     * Create a new security middleware instance.
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, callable $next): Response
    {
        error_log('SecurityMiddleware: Starting security check');
        $startTime = microtime(true);
        
        try {
            // 1. Rate limiting check
            $this->checkRateLimit($request);
            
            // 2. Input validation and sanitization
            $this->validateAndSanitizeInput($request);
            
            // 3. Process the request
            $response = $next($request);
            
            // 4. Add security headers
            $this->addSecurityHeaders($response);
            
            // 5. Log security event
            $this->logSecurityEvent($request, $response, microtime(true) - $startTime);
            
            return $response;
            
        } catch (HttpException $e) {
            $this->logger->warning('Security middleware blocked request', [
                'ip' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'uri' => $request->getUri()->getPath(),
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Security middleware error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new HttpException(500, 'Internal server error');
        }
    }
    
    /**
     * Check rate limiting for the request.
     */
    private function checkRateLimit(Request $request): void
    {
        $clientIp = $this->getClientIp($request);
        $currentTime = time();
        
        // Initialize rate limit data for this IP
        if (!isset(self::$rateLimitStore[$clientIp])) {
            self::$rateLimitStore[$clientIp] = [
                'requests' => [],
                'burst_requests' => [],
            ];
        }
        
        $rateData = &self::$rateLimitStore[$clientIp];
        
        // Clean old requests
        $rateData['requests'] = array_filter(
            $rateData['requests'],
            fn($time) => $time > $currentTime - 60
        );
        
        $rateData['burst_requests'] = array_filter(
            $rateData['burst_requests'],
            fn($time) => $time > $currentTime - 1
        );
        
        // Check burst limit (requests per second)
        if (count($rateData['burst_requests']) >= $this->rateLimitConfig['burst_limit']) {
            throw new HttpException(429, 'Too many requests (burst limit exceeded)');
        }
        
        // Check minute limit
        if (count($rateData['requests']) >= $this->rateLimitConfig['requests_per_minute']) {
            throw new HttpException(429, 'Too many requests (rate limit exceeded)');
        }
        
        // Add current request
        $rateData['requests'][] = $currentTime;
        $rateData['burst_requests'][] = $currentTime;
    }
    
    /**
     * Validate and sanitize input data.
     */
    private function validateAndSanitizeInput(Request $request): void
    {
        // Validate and sanitize GET parameters
        $getParams = $request->getQueryParams();
        foreach ($getParams as $key => $value) {
            if (is_string($value)) {
                $getParams[$key] = $this->sanitizeInput($value);
            }
        }
        
        // Validate and sanitize POST parameters
        $postParams = $request->getParsedBody();
        if (is_array($postParams)) {
            foreach ($postParams as $key => $value) {
                if (is_string($value)) {
                    $postParams[$key] = $this->sanitizeInput($value);
                }
            }
        }
        
        // Check for suspicious patterns
        $this->detectSuspiciousPatterns($request);
    }
    
    /**
     * Sanitize input string.
     */
    private function sanitizeInput(string $input): string
    {
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Normalize line endings
        $input = str_replace(["\r\n", "\r"], "\n", $input);
        
        // Remove control characters except newlines and tabs
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        return $input;
    }
    
    /**
     * Detect suspicious patterns in the request.
     */
    private function detectSuspiciousPatterns(Request $request): void
    {
        $uri = $request->getUri()->getPath();
        $userAgent = $request->getHeaderLine('User-Agent');
        $queryString = $request->getUri()->getQuery();
        
        error_log('SecurityMiddleware: Checking URI: ' . $uri);
        error_log('SecurityMiddleware: Checking query: ' . $queryString);
        
        // Check for SQL injection patterns
        $sqlPatterns = [
            '/union\s*select/i',
            '/union\+select/i',
            '/union%20select/i',
            '/drop\s+table/i',
            '/delete\s+from/i',
            '/insert\s+into/i',
            '/update\s+set/i',
            '/exec\s*\(/i',
            '/eval\s*\(/i',
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $uri) || preg_match($pattern, $userAgent) || preg_match($pattern, $queryString)) {
                error_log('SecurityMiddleware: Suspicious pattern detected: ' . $pattern);
                $this->logger->warning('Potential SQL injection attempt detected', [
                    'ip' => $this->getClientIp($request),
                    'uri' => $uri,
                    'user_agent' => $userAgent,
                    'pattern' => $pattern,
                ]);
                
                throw new HttpException(403, 'Suspicious request detected');
            }
        }
        
        // Check for XSS patterns
        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                $this->logger->warning('Potential XSS attempt detected', [
                    'ip' => $this->getClientIp($request),
                    'uri' => $uri,
                    'pattern' => $pattern,
                ]);
                
                throw new HttpException(403, 'Suspicious request detected');
            }
        }
        
        // Check for directory traversal
        if (strpos($uri, '..') !== false || strpos($uri, '//') !== false) {
            $this->logger->warning('Potential directory traversal attempt detected', [
                'ip' => $this->getClientIp($request),
                'uri' => $uri,
            ]);
            
            throw new HttpException(403, 'Suspicious request detected');
        }
    }
    
    /**
     * Add security headers to the response.
     */
    private function addSecurityHeaders(Response $response): void
    {
        foreach ($this->securityHeaders as $header => $value) {
            $response = $response->withHeader($header, $value);
        }
        
        // Add cache control headers for sensitive pages
        $path = parse_url($response->getHeaderLine('Location') ?: '', PHP_URL_PATH);
        if (strpos($path, '/admin') === 0 || strpos($path, '/profile') === 0) {
            $response = $response->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response = $response->withHeader('Pragma', 'no-cache');
        }
    }
    
    /**
     * Log security event.
     */
    private function logSecurityEvent(Request $request, Response $response, float $processingTime): void
    {
        $this->logger->info('Request processed by security middleware', [
            'ip' => $this->getClientIp($request),
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->getPath(),
            'status_code' => $response->getStatusCode(),
            'processing_time' => round($processingTime * 1000, 2) . 'ms',
            'user_agent' => $request->getHeaderLine('User-Agent'),
        ]);
    }
    
    /**
     * Get client IP address.
     */
    private function getClientIp(Request $request): string
    {
        // Check for forwarded headers
        $forwardedFor = $request->getHeaderLine('X-Forwarded-For');
        if ($forwardedFor) {
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }
        
        $realIp = $request->getHeaderLine('X-Real-IP');
        if ($realIp) {
            return $realIp;
        }
        
        return $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
    }
} 