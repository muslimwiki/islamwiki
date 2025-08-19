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
use IslamWiki\Core\Http\Exceptions\HttpException;
use IslamWiki\Core\View\TwigRenderer;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Error Handling Middleware
 *
 * Provides comprehensive error handling including:
 * - Exception catching and logging
 * - Debug information in development
 * - User-friendly error pages
 * - Performance monitoring
 */
class ErrorHandlingMiddleware
{
    /**
     * @var LoggerInterface Logger instance
     */
    private LoggerInterface $logger;

    /**
     * @var bool Debug mode flag
     */
    private bool $debug;

    /**
     * @var string Application environment
     */
    private string $environment;

    /**
     * @var TwigRenderer|null Twig renderer instance
     */
    private ?TwigRenderer $twigRenderer = null;

    /**
     * Create a new error handling middleware instance.
     */
    public function __construct(LoggerInterface $logger, bool $debug = false, string $environment = 'production')
    {
        $this->logger = $logger;
        $this->debug = $debug;
        $this->environment = $environment;
    }

    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);
        
        error_log("ErrorHandlingMiddleware: handle called with path: " . $request->getUri()->getPath());

        try {
            // Process the request
            error_log("ErrorHandlingMiddleware: About to call next middleware");
            $response = $next($request);
            
            // Check if response is null (which can happen with middleware)
            if ($response === null) {
                throw new \Exception("Middleware returned null response");
            }
            
            error_log("ErrorHandlingMiddleware: Next middleware returned response with status: " . $response->getStatusCode());

            // Log successful request
            $this->logRequest($request, $response, microtime(true) - $startTime);

            return $response;
        } catch (HttpException $e) {
            // Handle HTTP exceptions (4xx, 5xx)
            error_log("ErrorHandlingMiddleware: Caught HttpException: " . $e->getMessage());
            $this->logHttpException($request, $e);
            return $this->createHttpErrorResponse($e);
        } catch (Throwable $e) {
            // Handle all other exceptions
            error_log("ErrorHandlingMiddleware: Caught Throwable: " . $e->getMessage());
            error_log("ErrorHandlingMiddleware: Exception trace: " . $e->getTraceAsString());
            $this->logException($request, $e);
            return $this->createExceptionResponse($e);
        }
    }

    /**
     * Make the middleware callable for SabilRouting.
     */
    public function __invoke(Request $request, callable $next): Response
    {
        return $this->handle($request, $next);
    }

    /**
     * Log successful request.
     */
    private function logRequest(Request $request, Response $response, float $processingTime): void
    {
        $this->logger->info('Request completed successfully', [
            'ip' => $this->getClientIp($request),
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->getPath(),
            'status_code' => $response->getStatusCode(),
            'processing_time' => round($processingTime * 1000, 2) . 'ms',
            'memory_usage' => $this->formatBytes(memory_get_usage()),
            'peak_memory' => $this->formatBytes(memory_get_peak_usage()),
        ]);
    }

    /**
     * Log HTTP exception.
     */
    private function logHttpException(Request $request, HttpException $e): void
    {
        $this->logger->warning('HTTP exception occurred', [
            'ip' => $this->getClientIp($request),
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->getPath(),
            'status_code' => $e->getStatusCode(),
            'message' => $e->getMessage(),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'referer' => $request->getHeaderLine('Referer'),
        ]);
    }

    /**
     * Log general exception.
     */
    private function logException(Request $request, Throwable $e): void
    {
        $this->logger->error('Unhandled exception occurred', [
            'ip' => $this->getClientIp($request),
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->getPath(),
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'referer' => $request->getHeaderLine('Referer'),
            'server_info' => $this->getServerInfo(),
        ]);
    }

    /**
     * Create HTTP error response.
     */
    private function createHttpErrorResponse(HttpException $e): Response
    {
        $statusCode = $e->getStatusCode();
        $message = $e->getMessage();

        // Create user-friendly error page
        $html = $this->renderErrorPage($statusCode, $message, $this->debug ? $e : null);

        return new Response(
            status: $statusCode,
            headers: ['Content-Type' => 'text/html; charset=utf-8'],
            body: $html
        );
    }

    /**
     * Create exception response.
     */
    private function createExceptionResponse(Throwable $e): Response
    {
        $statusCode = 500;
        $message = $this->debug ? $e->getMessage() : 'An internal server error occurred.';

        // Create error page
        $html = $this->renderErrorPage($statusCode, $message, $this->debug ? $e : null);

        return new Response(
            status: $statusCode,
            headers: ['Content-Type' => 'text/html; charset=utf-8'],
            body: $html
        );
    }

    /**
     * Render error page using Twig templates.
     */
    private function renderErrorPage(int $statusCode, string $message, ?Throwable $exception = null): string
    {
        try {
            // Try to use Twig template first
            return $this->renderTwigErrorPage($statusCode, $message, $exception);
        } catch (Throwable $e) {
            // Fallback to basic HTML if Twig fails
            error_log("Failed to render Twig error page: " . $e->getMessage());
            return $this->renderBasicErrorPage($statusCode, $message, $exception);
        }
    }

    /**
     * Render error page using Twig templates.
     */
    private function renderTwigErrorPage(int $statusCode, string $message, ?Throwable $exception = null): string
    {
        // Get or create Twig renderer
        if ($this->twigRenderer === null) {
            $this->twigRenderer = $this->createTwigRenderer();
        }

        // Get the appropriate template path
        $templatePath = $this->getErrorTemplatePath($statusCode);
        
        // Check if template exists
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$templatePath}");
        }

        // Prepare template data
        $templateData = $this->prepareTemplateData($statusCode, $message, $exception);
        
        // Get relative template path for Twig
        $relativeTemplatePath = 'errors/' . basename($templatePath);
        
        // Render using Twig
        return $this->twigRenderer->render($relativeTemplatePath, $templateData);
    }

    /**
     * Create a Twig renderer instance.
     */
    private function createTwigRenderer(): TwigRenderer
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__)));
        $templatePath = $basePath . '/resources/views';
        $cachePath = $basePath . '/storage/framework/views';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        return new TwigRenderer(
            $templatePath,
            false, // Disable cache for now
            $this->debug
        );
    }

    /**
     * Get the path to the error template for the given status code.
     */
    private function getErrorTemplatePath(int $statusCode): string
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__)));
        $basePath .= '/resources/views/errors/';
        
        switch ($statusCode) {
            case 400:
                return $basePath . '400.twig';
            case 401:
                return $basePath . '401.twig';
            case 403:
                return $basePath . '403.twig';
            case 404:
                return $basePath . '404.twig';
            case 422:
                return $basePath . '422.twig';
            case 429:
                return $basePath . '429.twig';
            case 500:
                return $basePath . '500.twig';
            case 503:
                return $basePath . '503.twig';
            default:
                return $basePath . '500.twig';
        }
    }

    /**
     * Prepare data for the Twig template.
     */
    private function prepareTemplateData(int $statusCode, string $message, ?Throwable $exception = null): array
    {
        $data = [
            'status_code' => $statusCode,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'request_ip' => $this->getClientIpFromServer(),
            'request_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_limit' => ini_get('memory_limit'),
            'debug' => $this->debug,
        ];

        if ($exception) {
            $data['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        // Add status-specific data
        switch ($statusCode) {
            case 401:
                $data['session_status'] = session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive';
                $data['user_id'] = $_SESSION['user_id'] ?? 'Not Logged In';
                $data['user_role'] = $_SESSION['user_role'] ?? 'None';
                $data['last_activity'] = $_SESSION['last_activity'] ?? 'Unknown';
                $data['required_role'] = 'Any Authenticated User';
                break;
            case 403:
                $data['user_id'] = $_SESSION['user_id'] ?? 'Not Logged In';
                $data['user_role'] = $_SESSION['user_role'] ?? 'None';
                $data['required_role'] = 'Administrator';
                $data['resource_type'] = 'Protected Resource';
                $data['permission_level'] = 'Admin Only';
                break;
            case 429:
                $data['rate_limit'] = '100 requests per hour';
                $data['rate_remaining'] = '0';
                $data['rate_reset'] = date('Y-m-d H:i:s', time() + 3600);
                $data['rate_window'] = '1 hour';
                $data['retry_after'] = '3600';
                break;
            case 503:
                $data['maintenance_status'] = 'Scheduled maintenance in progress';
                $data['estimated_completion'] = '2-3 hours';
                $data['maintenance_start'] = date('Y-m-d H:i:s', time() - 1800);
                $data['maintenance_duration'] = '2-3 hours';
                $data['maintenance_reason'] = 'System improvements';
                $data['service_name'] = 'IslamWiki';
                $data['service_status'] = 'Maintenance';
                break;
        }

        return $data;
    }

    /**
     * Fallback to basic HTML error page.
     */
    private function renderBasicErrorPage(int $statusCode, string $message, ?Throwable $exception = null): string
    {
        $title = $this->getErrorTitle($statusCode);
        $icon = $this->getErrorIcon($statusCode);
        $color = $this->getErrorColor($statusCode);
        $suggestions = $this->getErrorSuggestions($statusCode);

        $debugInfo = '';
        if ($this->debug && $exception) {
            $debugInfo = $this->renderDebugInfo($exception);
        }

        $requestInfo = $this->getRequestInfo();
        $serverInfo = $this->getServerInfo();

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' - IslamWiki</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #2d3748;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        
        .error-header {
            background: linear-gradient(135deg, ' . $color . ' 0%, ' . $this->adjustBrightness($color, -20) . ' 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .error-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .error-icon {
            font-size: 6rem;
            margin-bottom: 20px;
            display: block;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .error-code {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .error-message {
            font-size: 1.1rem;
            opacity: 0.8;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .error-content {
            padding: 40px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            border-left: 4px solid ' . $color . ';
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .info-card h3 {
            color: ' . $color . ';
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-card h3::before {
            content: "📋";
            font-size: 1.1rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
            color: #4a5568;
        }
        
        .info-value {
            font-family: "SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace;
            color: #2d3748;
            font-size: 0.9rem;
            text-align: right;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .suggestions {
            background: linear-gradient(135deg, #f0fff4 0%, #dcfce7 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 40px;
            border-left: 4px solid #10b981;
        }
        
        .suggestions h3 {
            color: #059669;
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .suggestions h3::before {
            content: "💡";
            font-size: 1.1rem;
        }
        
        .suggestions ul {
            list-style: none;
            padding: 0;
        }
        
        .suggestions li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
        }
        
        .suggestions li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }
        
        .actions {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 0 10px;
            background: ' . $color . ';
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .btn-secondary {
            background: #6b7280;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .debug-info {
            background: #1a202c;
            color: #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            font-family: "SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace;
            font-size: 0.9rem;
        }
        
        .debug-info h3 {
            color: #fbbf24;
            margin-bottom: 20px;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .debug-info h3::before {
            content: "🐛";
            font-size: 1.1rem;
        }
        
        .debug-info h4 {
            color: #60a5fa;
            margin: 20px 0 10px 0;
            font-size: 1.1rem;
        }
        
        .debug-info p {
            margin: 8px 0;
            padding: 8px 0;
            border-bottom: 1px solid #2d3748;
        }
        
        .debug-info p:last-child {
            border-bottom: none;
        }
        
        .debug-info strong {
            color: #fbbf24;
        }
        
        .debug-info pre {
            background: #2d3748;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 15px 0;
            border-left: 4px solid #fbbf24;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        
        .stack-trace {
            background: #2d3748;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            max-height: 400px;
            overflow-y: auto;
            border-left: 4px solid #ef4444;
        }
        
        .stack-trace::-webkit-scrollbar {
            width: 8px;
        }
        
        .stack-trace::-webkit-scrollbar-track {
            background: #1a202c;
            border-radius: 4px;
        }
        
        .stack-trace::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 4px;
        }
        
        .stack-trace::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }
        
        .source-code-container {
            background: #2d3748;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #60a5fa;
        }
        
        .source-code-container p {
            margin-bottom: 15px;
            color: #60a5fa;
            font-weight: 500;
        }
        
        .code-lines {
            background: #1a202c;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .code-line {
            display: flex;
            padding: 4px 15px;
            border-bottom: 1px solid #2d3748;
            transition: background-color 0.2s ease;
        }
        
        .code-line:last-child {
            border-bottom: none;
        }
        
        .code-line:hover {
            background: #2d3748;
        }
        
        .code-line.error-line {
            background: #7f1d1d;
            border-left: 4px solid #ef4444;
        }
        
        .code-line.error-line .line-number {
            color: #ef4444;
            font-weight: bold;
        }
        
        .line-number {
            color: #6b7280;
            font-size: 0.85rem;
            min-width: 40px;
            user-select: none;
            opacity: 0.7;
        }
        
        .line-content {
            color: #e2e8f0;
            font-family: "SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, "Courier New", monospace;
            font-size: 0.85rem;
            white-space: pre;
            overflow-x: auto;
        }
        
        .additional-server-info {
            background: #2d3748;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #10b981;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #718096;
            font-size: 0.9rem;
            border-top: 1px solid #e2e8f0;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .error-header {
                padding: 40px 20px;
            }
            
            .error-code {
                font-size: 3rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-content {
                padding: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .actions .btn {
                display: block;
                margin: 10px auto;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-header">
            <span class="error-icon">' . $icon . '</span>
        <h1 class="error-code">' . $statusCode . '</h1>
        <h2 class="error-title">' . htmlspecialchars($title) . '</h2>
        <p class="error-message">' . htmlspecialchars($message) . '</p>
        </div>
        
        <div class="error-content">
            <div class="info-grid">
                <div class="info-card">
                    <h3>Request Information</h3>
                    <div class="info-item">
                        <span class="info-label">URL:</span>
                        <span class="info-value" title="' . htmlspecialchars($requestInfo['uri']) . '">' . htmlspecialchars($requestInfo['uri']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Method:</span>
                        <span class="info-value">' . htmlspecialchars($requestInfo['method']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">IP Address:</span>
                        <span class="info-value">' . htmlspecialchars($requestInfo['ip']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">User Agent:</span>
                        <span class="info-value" title="' . htmlspecialchars($requestInfo['user_agent']) . '">' . htmlspecialchars(substr($requestInfo['user_agent'], 0, 50)) . (strlen($requestInfo['user_agent']) > 50 ? '...' : '') . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Timestamp:</span>
                        <span class="info-value">' . htmlspecialchars($requestInfo['timestamp']) . '</span>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3>Server Information</h3>
                    <div class="info-item">
                        <span class="info-label">PHP Version:</span>
                        <span class="info-value">' . htmlspecialchars($serverInfo['php_version']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Server Software:</span>
                        <span class="info-value">' . htmlspecialchars($serverInfo['server_software']) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Memory Usage:</span>
                        <span class="info-value">' . $this->formatBytes(memory_get_usage()) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Peak Memory:</span>
                        <span class="info-value">' . $this->formatBytes(memory_get_peak_usage()) . '</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Memory Limit:</span>
                        <span class="info-value">' . htmlspecialchars($serverInfo['memory_limit']) . '</span>
                    </div>
                </div>
            </div>
            
            <div class="suggestions">
                <h3>What you can do:</h3>
                <ul>
                    ' . implode('', array_map(function($suggestion) { return '<li>' . htmlspecialchars($suggestion) . '</li>'; }, $suggestions)) . '
                </ul>
            </div>
        
        <div class="actions">
                <a href="/" class="btn">🏠 Go to Homepage</a>
                <a href="/pages" class="btn btn-secondary">📚 Browse Pages</a>
                <a href="javascript:history.back()" class="btn btn-secondary">⬅️ Go Back</a>
            </div>
            
            ' . $debugInfo . '
        </div>
        
        <div class="footer">
            <p>IslamWiki - Islamic Knowledge Platform</p>
            <p>If you continue to experience issues, please contact our support team.</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Get error title based on status code.
     */
    private function getErrorTitle(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Page Not Found',
            405 => 'Method Not Allowed',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => 'Error',
        };
    }

    /**
     * Get error icon based on status code.
     */
    private function getErrorIcon(int $statusCode): string
    {
        return match ($statusCode) {
            400 => '🔴',
            401 => '🚫',
            403 => '🚫',
            404 => '🔍',
            405 => '⚠️',
            429 => '⚠️',
            500 => '💥',
            502 => '💥',
            503 => '💥',
            504 => '⏰',
            default => '❓',
        };
    }

    /**
     * Get error color based on status code.
     */
    private function getErrorColor(int $statusCode): string
    {
        return match ($statusCode) {
            400 => '#e74c3c',
            401 => '#3498db',
            403 => '#e67e22',
            404 => '#2ecc71',
            405 => '#f1c40f',
            429 => '#f39c12',
            500 => '#c0392b',
            502 => '#e67e22',
            503 => '#3498db',
            504 => '#f39c12',
            default => '#95a5a6',
        };
    }

    /**
     * Adjust color brightness.
     */
    private function adjustBrightness(string $hex, int $steps): string
    {
        // Convert hex to RGB
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        // Convert back to hex
        return '#' . sprintf('%02x', $r) . sprintf('%02x', $g) . sprintf('%02x', $b);
    }

    /**
     * Get error suggestions based on status code.
     */
    private function getErrorSuggestions(int $statusCode): array
    {
        return match ($statusCode) {
            400 => [
                'Check the URL for typos.',
                'Ensure all required parameters are provided.',
                'Verify the request method (e.g., GET, POST).',
            ],
            401 => [
                'Ensure your API key is valid and active.',
                'Check your authentication credentials.',
                'Verify your session is still valid.',
            ],
            403 => [
                'Review your permissions and access rights.',
                'Check if you are logged in and have the correct role.',
                'Verify the resource you are trying to access is allowed.',
            ],
            404 => [
                'Double-check the URL path and try again.',
                'Ensure the resource you are looking for exists.',
                'Check if the page you are trying to access is correct.',
            ],
            405 => [
                'Verify the HTTP method you are using is allowed for this endpoint.',
                'Check if the resource supports the requested method.',
                'Ensure you are using the correct HTTP verb (GET, POST, etc.).',
            ],
            429 => [
                'You have made too many requests. Please wait a moment.',
                'Check your rate limiting settings.',
                'Reduce the frequency of your requests.',
            ],
            500 => [
                'This is a server-side error. Please try again later.',
                'Check the server logs for more details.',
                'Ensure all dependencies are up-to-date.',
            ],
            502 => [
                'The server is acting as a gateway or proxy and received an invalid response from an upstream server.',
                'Check the upstream servers and their status.',
                'Verify the server configuration.',
            ],
            503 => [
                'The server is currently unavailable. Please try again later.',
                'Check if the service is undergoing maintenance.',
                'Verify the server status.',
            ],
            504 => [
                'The server did not receive a timely response from an upstream server.',
                'Check the upstream servers and their status.',
                'Verify the server configuration.',
            ],
            default => [
                'Please try again later.',
                'If the issue persists, please contact support.',
                'Check your internet connection.',
            ],
        };
    }

    /**
     * Get request information.
     */
    private function getRequestInfo(): array
    {
        return [
            'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'ip' => $this->getClientIpFromServer(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get client IP address from server variables.
     */
    private function getClientIpFromServer(): string
    {
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwardedFor) {
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }

        $realIp = $_SERVER['HTTP_X_REAL_IP'] ?? '';
        if ($realIp) {
            return $realIp;
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get source code around the error line.
     */
    private function getSourceCodeAroundError(string $file, int $line, int $linesAround = 5): string
    {
        if (!file_exists($file) || !is_readable($file)) {
            return '<p><em>Source file not available or not readable</em></p>';
        }

        try {
            $source = file($file);
            $start = max(0, $line - $linesAround - 1);
            $end = min(count($source), $line + $linesAround);

            $result = '<div class="source-code-container">';
            $result .= '<p><strong>Source file:</strong> ' . htmlspecialchars($file) . '</p>';
            $result .= '<div class="code-lines">';
            
            for ($i = $start; $i < $end; $i++) {
                $lineNumber = $i + 1;
                $lineContent = htmlspecialchars(rtrim($source[$i]), ENT_QUOTES, 'UTF-8');
                $isErrorLine = ($lineNumber === $line);
                
                $result .= '<div class="code-line' . ($isErrorLine ? ' error-line' : '') . '">';
                $result .= '<span class="line-number">' . $lineNumber . '</span>';
                $result .= '<span class="line-content">' . $lineContent . '</span>';
                $result .= '</div>';
            }
            
            $result .= '</div></div>';
            return $result;
        } catch (\Exception $e) {
            return '<p><em>Unable to load source code: ' . htmlspecialchars($e->getMessage()) . '</em></p>';
        }
    }

    /**
     * Get additional server information.
     */
    private function getAdditionalServerInfo(): string
    {
        $info = [];
        
        // PHP Extensions
        $extensions = get_loaded_extensions();
        $info[] = '<p><strong>Loaded Extensions:</strong> ' . count($extensions) . ' extensions loaded</p>';
        
        // PHP Configuration
        $info[] = '<p><strong>Display Errors:</strong> ' . (ini_get('display_errors') ? 'On' : 'Off') . '</p>';
        $info[] = '<p><strong>Log Errors:</strong> ' . (ini_get('log_errors') ? 'On' : 'Off') . '</p>';
        $info[] = '<p><strong>Error Reporting:</strong> ' . ini_get('error_reporting') . '</p>';
        
        // Server Variables
        $info[] = '<p><strong>Document Root:</strong> ' . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . '</p>';
        $info[] = '<p><strong>Script Name:</strong> ' . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A') . '</p>';
        $info[] = '<p><strong>Server Name:</strong> ' . htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'N/A') . '</p>';
        $info[] = '<p><strong>Server Port:</strong> ' . htmlspecialchars($_SERVER['SERVER_PORT'] ?? 'N/A') . '</p>';
        
        // Time and Date
        $info[] = '<p><strong>Current Time:</strong> ' . date('Y-m-d H:i:s T') . '</p>';
        $info[] = '<p><strong>Timezone:</strong> ' . date_default_timezone_get() . '</p>';
        
        return implode('', $info);
    }

    /**
     * Render debug information.
     */
    private function renderDebugInfo(Throwable $e): string
    {
        $trace = $e->getTraceAsString();
        $serverInfo = $this->getServerInfo();
        $requestInfo = $this->getRequestInfo();
        
        // Get source code around the error line
        $sourceCode = $this->getSourceCodeAroundError($e->getFile(), $e->getLine());
        
        // Get additional server information
        $additionalServerInfo = $this->getAdditionalServerInfo();

        return '<div class="debug-info">
            <h3>🐛 Debug Information</h3>
            
            <h4>🚨 Exception Details</h4>
            <p><strong>Type:</strong> ' . htmlspecialchars(get_class($e)) . '</p>
            <p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>
            <p><strong>Line:</strong> ' . $e->getLine() . '</p>
            <p><strong>Code:</strong> ' . $e->getCode() . '</p>
            
            <h4>📁 Source Code Context</h4>
            <div class="source-code">
                ' . $sourceCode . '
            </div>
            
            <h4>🔍 Request Details</h4>
            <p><strong>URL:</strong> ' . htmlspecialchars($requestInfo['uri']) . '</p>
            <p><strong>Method:</strong> ' . htmlspecialchars($requestInfo['method']) . '</p>
            <p><strong>IP Address:</strong> ' . htmlspecialchars($requestInfo['ip']) . '</p>
            <p><strong>User Agent:</strong> ' . htmlspecialchars($requestInfo['user_agent']) . '</p>
            <p><strong>Timestamp:</strong> ' . htmlspecialchars($requestInfo['timestamp']) . '</p>
            
            <h4>⚙️ Server Environment</h4>
            <p><strong>PHP Version:</strong> ' . htmlspecialchars($serverInfo['php_version']) . '</p>
            <p><strong>Server Software:</strong> ' . htmlspecialchars($serverInfo['server_software']) . '</p>
            <p><strong>Memory Usage:</strong> ' . $this->formatBytes(memory_get_usage()) . '</p>
            <p><strong>Peak Memory:</strong> ' . $this->formatBytes(memory_get_peak_usage()) . '</p>
            <p><strong>Memory Limit:</strong> ' . htmlspecialchars($serverInfo['memory_limit']) . '</p>
            <p><strong>Max Execution Time:</strong> ' . htmlspecialchars($serverInfo['max_execution_time']) . 's</p>
            <p><strong>Upload Max Filesize:</strong> ' . htmlspecialchars($serverInfo['upload_max_filesize']) . '</p>
            <p><strong>Post Max Size:</strong> ' . htmlspecialchars($serverInfo['post_max_size']) . '</p>
            
            <h4>🔧 Additional Server Info</h4>
            <div class="additional-server-info">
                ' . $additionalServerInfo . '
            </div>
            
            <h4>📚 Stack Trace</h4>
            <div class="stack-trace">
            <pre>' . htmlspecialchars($trace) . '</pre>
            </div>
        </div>';
    }

    /**
     * Get server information.
     */
    private function getServerInfo(): array
    {
        return [
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }

    /**
     * Get client IP address.
     */
    private function getClientIp(Request $request): string
    {
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

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
