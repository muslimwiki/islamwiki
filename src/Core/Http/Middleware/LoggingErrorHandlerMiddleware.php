<?php

declare(strict_types=1);

namespace IslamWiki\Core\Http\Middleware;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use LoggingService;\Logger

/**
 * Logging Error Handler Middleware
 * 
 * Handles errors and exceptions, providing comprehensive logging
 * and user-friendly error pages.
 * 
 * @package IslamWiki\Core\Http\Middleware
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class LoggerErrorHandlerMiddleware
{
    private LoggingService $logger;
    private array $config;

    public function __construct(LoggingService $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'display_errors' => false,
            'log_errors' => true,
            'error_pages' => [
                400 => 'errors/400.twig',
                401 => 'errors/401.twig',
                403 => 'errors/403.twig',
                404 => 'errors/404.twig',
                500 => 'errors/500.twig',
                503 => 'errors/503.twig',
            ],
            'default_error_page' => 'errors/500.twig',
        ], $config);
    }

    /**
     * Process the request through middleware
     */
    public function process(Request $request, callable $next): Response
    {
        try {
            // Process the request
            $response = $next($request);
            
            // Check for error status codes
            if ($response->getStatusCode() >= 400) {
                $this->handleErrorResponse($request, $response);
            }
            
            return $response;
            
        } catch (\Throwable $exception) {
            return $this->handleException($request, $exception);
        }
    }

    /**
     * Handle exceptions
     */
    private function handleException(Request $request, \Throwable $exception): Response
    {
        // Log the exception
        if ($this->config['log_errors']) {
            $this->logger->critical('Uncaught Exception: ' . $exception->getMessage(), [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString(),
                'request_url' => $request->getUri(),
                'request_method' => $request->getMethod(),
                'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ]);
        }

        // Determine error code
        $errorCode = $this->getErrorCode($exception);
        
        // Create error response
        $response = new Response();
        $response->setStatusCode($errorCode);
        
        // Set error headers
        $response->setHeader('X-Error-Type', get_class($exception));
        $response->setHeader('X-Error-Message', $exception->getMessage());
        
        // Render error page
        $errorPage = $this->getErrorPage($errorCode);
        if ($errorPage) {
                    // Check for enhanced debug info in session
        $debugInfo = null;
        
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['debug_error_info'])) {
            $debugInfo = $_SESSION['debug_error_info'];
            // Clear debug info from session after use
            unset($_SESSION['debug_error_info']);
        }
        
        // If no debug info in session, try to generate basic debug info
        if (!$debugInfo) {
            $debugInfo = [
                'timestamp' => date('Y-m-d H:i:s'),
                'context' => 'Error Handler Middleware',
                'error_type' => get_class($exception),
                'error_message' => $exception->getMessage(),
                'error_code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'stack_trace' => $exception->getTraceAsString(),
                'request_info' => [
                    'method' => $request->getMethod(),
                    'uri' => $request->getUri(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                    'http_host' => $_SERVER['HTTP_HOST'] ?? 'Unknown'
                ],
                'session_info' => [
                    'session_id' => session_id() ?: 'NO_SESSION',
                    'session_status' => session_status(),
                    'session_data' => $_SESSION ?? [],
                    'session_name' => session_name()
                ],
                'authentication_info' => [
                    'auth_service_available' => 'UNKNOWN',
                    'user_data' => null,
                    'session_user_data' => [
                        'user_id' => $_SESSION['user_id'] ?? 'NOT_SET',
                        'username' => $_SESSION['username'] ?? 'NOT_SET',
                        'is_admin' => $_SESSION['is_admin'] ?? 'NOT_SET',
                        'logged_in_at' => $_SESSION['logged_in_at'] ?? 'NOT_SET'
                    ]
                ],
                'database_info' => [
                    'database_available' => 'UNKNOWN',
                    'connection_status' => 'UNKNOWN',
                    'tables_exist' => []
                ],
                'container_info' => [
                    'container_available' => 'UNKNOWN',
                    'services' => [],
                    'providers' => []
                ],
                'memory_usage' => [
                    'memory_usage' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'memory_limit' => ini_get('memory_limit')
                ],
                'php_info' => [
                    'php_version' => PHP_VERSION,
                    'extensions' => get_loaded_extensions(),
                    'error_reporting' => error_reporting(),
                    'display_errors' => ini_get('display_errors'),
                    'log_errors' => ini_get('log_errors')
                ]
            ];
        }
            
            $response->setContent($this->renderErrorPage($errorPage, [
                'error_code' => $errorCode,
                'error_message' => $exception->getMessage(),
                'error_file' => $exception->getFile(),
                'error_line' => $exception->getLine(),
                'error_trace' => $exception->getTraceAsString(),
                'request_url' => $request->getUri(),
                'request_method' => $request->getMethod(),
                'client_ip' => $request->getClientIp(),
                'user_agent' => $request->getUserAgent(),
                'timestamp' => date('Y-m-d H:i:s T'),
                'error_id' => 'ERR_' . uniqid(),
                'debug_info' => $debugInfo,
                'exception' => [
                    '__class__' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'traceAsString' => $exception->getTraceAsString(),
                ],
            ]));
        } else {
            // Fallback error content
            $response->setContent($this->getFallbackErrorContent($errorCode, $exception));
        }
        
        return $response;
    }

    /**
     * Handle error responses
     */
    private function handleErrorResponse(Request $request, Response $response): void
    {
        $statusCode = $response->getStatusCode();
        
        if ($this->config['log_errors']) {
            $this->logger->warning('HTTP Error Response: ' . $statusCode, [
                'status_code' => $statusCode,
                'request_url' => $request->getUri(),
                'request_method' => $request->getMethod(),
                'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ]);
        }
    }

    /**
     * Get error code from exception
     */
    private function getErrorCode(\Throwable $exception): int
    {
        // Map exception types to HTTP status codes
        if ($exception instanceof \InvalidArgumentException) {
            return 400;
        }
        
        if ($exception instanceof \RuntimeException) {
            return 500;
        }
        
        if ($exception instanceof \PDOException) {
            return 500;
        }
        
        if ($exception instanceof \ErrorException) {
            $severity = $exception->getSeverity();
            
            if (in_array($severity, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                return 500;
            }
            
            if (in_array($severity, [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING])) {
                return 500;
            }
            
            if (in_array($severity, [E_NOTICE, E_USER_NOTICE])) {
                return 500;
            }
        }
        
        // Default to 500 for unknown exceptions
        return 500;
    }

    /**
     * Get error page template
     */
    private function getErrorPage(int $errorCode): ?string
    {
        return $this->config['error_pages'][$errorCode] ?? $this->config['default_error_page'];
    }

    /**
     * Render error page
     */
    private function renderErrorPage(string $template, array $data): string
    {
        try {
            // Try to render with Twig if available
            if (class_exists('\Twig\Environment')) {
                return $this->renderWithTwig($template, $data);
            }
            
            // Fallback to simple template rendering
            return $this->renderSimpleTemplate($template, $data);
            
        } catch (\Throwable $e) {
            // If template rendering fails, return fallback content
            $this->logger->error('Failed to render error page: ' . $e->getMessage(), [
                'template' => $template,
                'error' => $e->getMessage(),
            ]);
            
            return $this->getFallbackErrorContent($data['error_code'], null, $data);
        }
    }

    /**
     * Render with Twig
     */
    private function renderWithTwig(string $template, array $data): string
    {
        // This would need Twig environment to implement
        // For now, return simple template
        return $this->renderSimpleTemplate($template, $data);
    }

    /**
     * Render simple template
     */
    private function renderSimpleTemplate(string $template, array $data): string
    {
        $templatePath = __DIR__ . '/../../../resources/views/' . $template;
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Error template not found: {$templatePath}");
        }
        
        $content = file_get_contents($templatePath);
        
        // Simple variable replacement
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Convert arrays to JSON for display
                $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            } elseif (is_object($value)) {
                // Convert objects to string representation
                $value = (string) $value;
            } elseif (is_bool($value)) {
                // Convert booleans to string
                $value = $value ? 'true' : 'false';
            }
            
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Get fallback error content
     */
    private function getFallbackErrorContent(int $errorCode, ?\Throwable $exception = null, array $context = []): string
    {
        $errorTitle = $this->getErrorTitle($errorCode);
        $errorMessage = $this->getErrorMessage($errorCode);
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $errorCode . ' - ' . $errorTitle . ' | IslamWiki</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .error-container { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .error-code { font-size: 4rem; color: #dc2626; margin-bottom: 20px; }
        .error-title { font-size: 1.5rem; color: #374151; margin-bottom: 20px; }
        .error-message { color: #6b7280; margin-bottom: 30px; }
        .error-details { background: #f9fafb; padding: 20px; border-radius: 8px; text-align: left; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background: #1e3a8a; color: white; text-decoration: none; border-radius: 6px; margin: 0 10px; }
        .btn:hover { background: #1e40af; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">' . $errorCode . '</div>
        <h1 class="error-title">' . $errorTitle . '</h1>
        <p class="error-message">' . $errorMessage . '</p>';
        
        if ($exception && $this->config['display_errors']) {
            $html .= '
        <div class="error-details">
            <h3>Error Details</h3>
            <p><strong>Message:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>
            <p><strong>File:</strong> ' . htmlspecialchars($exception->getFile()) . '</p>
            <p><strong>Line:</strong> ' . htmlspecialchars($exception->getLine()) . '</p>
        </div>';
        }
        
        if (!empty($context)) {
            $html .= '
        <div class="error-details">
            <h3>Request Information</h3>
            <p><strong>URL:</strong> ' . htmlspecialchars($context['request_url'] ?? 'Unknown') . '</p>
            <p><strong>Method:</strong> ' . htmlspecialchars($context['request_method'] ?? 'Unknown') . '</p>
            <p><strong>Time:</strong> ' . htmlspecialchars($context['timestamp'] ?? 'Unknown') . '</p>
        </div>';
        }
        
        $html .= '
        <div>
            <a href="/" class="btn">Go to Homepage</a>
            <button onclick="history.back()" class="btn" style="background: #6b7280;">Go Back</button>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }

    /**
     * Get error title
     */
    private function getErrorTitle(int $errorCode): string
    {
        return match ($errorCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => 'Error',
        };
    }

    /**
     * Get error message
     */
    private function getErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            400 => 'The request could not be understood by the server due to malformed syntax.',
            401 => 'Authentication is required to access this resource.',
            403 => 'You do not have permission to access this resource.',
            404 => 'The requested resource could not be found on this server.',
            500 => 'We\'re experiencing technical difficulties. Our team has been notified and is working to resolve this issue.',
            503 => 'The service is temporarily unavailable. Please try again later.',
            default => 'An unexpected error occurred.',
        };
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
} 