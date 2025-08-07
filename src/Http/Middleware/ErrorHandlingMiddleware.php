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

        try {
            // Process the request
            $response = $next($request);

            // Log successful request
            $this->logRequest($request, $response, microtime(true) - $startTime);

            return $response;
        } catch (HttpException $e) {
            // Handle HTTP exceptions (4xx, 5xx)
            $this->logHttpException($request, $e);
            return $this->createHttpErrorResponse($e);
        } catch (Throwable $e) {
            // Handle all other exceptions
            $this->logException($request, $e);
            return $this->createExceptionResponse($e);
        }
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
     * Render error page.
     */
    private function renderErrorPage(int $statusCode, string $message, ?Throwable $exception = null): string
    {
        $title = $this->getErrorTitle($statusCode);

        $debugInfo = '';
        if ($this->debug && $exception) {
            $debugInfo = $this->renderDebugInfo($exception);
        }

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' - IslamWiki</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #2c3e50;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 4em;
            font-weight: bold;
            color: #e74c3c;
            margin: 0;
            text-align: center;
        }
        .error-title {
            font-size: 1.5em;
            color: #2c3e50;
            margin: 10px 0;
            text-align: center;
        }
        .error-message {
            color: #7f8c8d;
            text-align: center;
            margin: 20px 0;
        }
        .debug-info {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            font-family: "Courier New", monospace;
            font-size: 0.9em;
            overflow-x: auto;
        }
        .debug-info h3 {
            margin-top: 0;
            color: #e74c3c;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .actions {
            text-align: center;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error-code">' . $statusCode . '</h1>
        <h2 class="error-title">' . htmlspecialchars($title) . '</h2>
        <p class="error-message">' . htmlspecialchars($message) . '</p>
        
        <div class="actions">
            <a href="/" class="btn">Go to Homepage</a>
            <a href="/pages" class="btn">Browse Pages</a>
            <a href="javascript:history.back()" class="btn">Go Back</a>
        </div>
        
        ' . $debugInfo . '
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
     * Render debug information.
     */
    private function renderDebugInfo(Throwable $e): string
    {
        $trace = $e->getTraceAsString();
        $serverInfo = $this->getServerInfo();

        return '<div class="debug-info">
            <h3>Debug Information</h3>
            <p><strong>Exception:</strong> ' . htmlspecialchars(get_class($e)) . '</p>
            <p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>
            <p><strong>Line:</strong> ' . $e->getLine() . '</p>
            <p><strong>Server:</strong> ' . htmlspecialchars($serverInfo['server_software']) . '</p>
            <p><strong>PHP Version:</strong> ' . htmlspecialchars($serverInfo['php_version']) . '</p>
            <p><strong>Memory Usage:</strong> ' . $this->formatBytes(memory_get_usage()) . '</p>
            <p><strong>Peak Memory:</strong> ' . $this->formatBytes(memory_get_peak_usage()) . '</p>
            <h4>Stack Trace:</h4>
            <pre>' . htmlspecialchars($trace) . '</pre>
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
