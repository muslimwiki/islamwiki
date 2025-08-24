<?php

declare(strict_types=1);

namespace IslamWiki\Core\Error;

use Throwable;
use Psr\Log\LoggerInterface;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Request;

/**
 * Logging Error Handler - Centralized error handling through Logging system
 * 
 * This handler consolidates all error handling to use the Logging logging system
 * and provides consistent error pages across the application.
 */
class LoggerErrorHandler
{
    private static ?LoggerInterface $logger = null;
    private static bool $debug = false;
    private static string $templatePath;

    /**
     * Initialize the Logging error handler
     */
    public static function initialize(LoggerInterface $logger, bool $debug = false): void
    {
        self::$logger = $logger;
        self::$debug = $debug;
        self::$templatePath = dirname(dirname(dirname(__DIR__))) . '/resources/views/errors';
        
        // Set error handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        // Log initialization
        self::$logger->info('Logging Error Handler initialized', [
            'debug_mode' => $debug,
            'template_path' => self::$templatePath
        ]);
    }

    /**
     * Handle PHP errors
     */
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile = '',
        int $errline = 0,
        array $errcontext = []
    ): bool {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorType = self::getErrorType($errno);
        $message = sprintf(
            '%s: %s in %s on line %d',
            $errorType,
            $errstr,
            $errfile,
            $errline
        );

        // Log through Logging
        self::$logger->error($message, [
            'error_type' => $errorType,
            'error_code' => $errno,
            'file' => $errfile,
            'line' => $errline,
            'context' => $errcontext
        ]);

        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(Throwable $e): void
    {
        $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
        
        // Log through Logging
        self::$logger->error('Exception: ' . $e->getMessage(), [
            'status_code' => $statusCode,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'exception_class' => get_class($e),
            'exception_file' => $e->getFile(),
            'exception_line' => $e->getLine(),
            'exception_trace' => $e->getTraceAsString()
        ]);

        // Create error response
        $response = self::createErrorResponse($e, $statusCode);
        $response->send();
        exit(1);
    }

    /**
     * Handle fatal errors
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            $message = sprintf(
                'FATAL %s: %s in %s on line %d',
                self::getErrorType($error['type']),
                $error['message'],
                $error['file'],
                $error['line']
            );

            // Log through Logging
            self::$logger->critical($message, [
                'error_type' => 'FATAL',
                'error_code' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);

            // Create error response
            $exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            $response = self::createErrorResponse($exception, 500);
            $response->send();
        }
    }

    /**
     * Create a 404 error response
     */
    public static function create404Response(Request $request, string $message = 'Page not found'): Response
    {
        // Log 404 through Logging
        self::$logger->warning('404 Page Not Found', [
            'request_uri' => $request->getUri()->getPath(),
            'request_method' => $request->getMethod(),
            'remote_addr' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'referer' => $request->getHeaderLine('Referer')
        ]);

        return self::createErrorResponse(
            new \Exception($message, 404),
            404,
            'errors/404.twig'
        );
    }

    /**
     * Create a 500 error response
     */
    public static function create500Response(Throwable $e): Response
    {
        return self::createErrorResponse($e, 500, 'errors/500.twig');
    }

    /**
     * Create error response with template
     */
    private static function createErrorResponse(Throwable $e, int $statusCode, string $template = null): Response
    {
        // Prepare template data
        $templateData = self::prepareTemplateData($e, $statusCode);
        
        // Try to render template
        $html = self::renderErrorTemplate($template, $templateData);
        
        return new Response(
            $statusCode,
            ['Content-Type' => 'text/html; charset=utf-8'],
            $html
        );
    }

    /**
     * Prepare template data for error pages
     */
    private static function prepareTemplateData(Throwable $e, int $statusCode): array
    {
        $data = [
            'status_code' => $statusCode,
            'error' => $e->getMessage(),
            'request_id' => self::generateRequestId(),
            'timestamp' => date('Y-m-d H:i:s T'),
            'exception' => $e,
            'debug_info' => self::getDebugInfo($e),
            'app' => [
                'request' => [
                    'uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                    'method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown'
                ]
            ]
        ];

        return $data;
    }

    /**
     * Get comprehensive debug information
     */
    private static function getDebugInfo(Throwable $e): array
    {
        return [
            'timestamp' => date('Y-m-d H:i:s T'),
            'context' => 'Error Handler',
            'error_type' => get_class($e),
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_info' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                'http_host' => $_SERVER['HTTP_HOST'] ?? 'N/A'
            ],
            'session_info' => [
                'session_id' => session_id() ?: 'N/A',
                'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive',
                'session_data' => isset($_SESSION) ? $_SESSION : [],
                'session_name' => session_name()
            ],
            'memory_usage' => [
                'memory_usage' => memory_get_usage(),
                'memory_peak' => memory_get_peak_usage(),
                'memory_limit' => ini_get('memory_limit')
            ],
            'php_info' => [
                'php_version' => PHP_VERSION,
                'extensions' => get_loaded_extensions(),
                'error_reporting' => error_reporting(),
                'display_errors' => ini_get('display_errors'),
                'log_errors' => ini_get('log_errors')
            ],
            'stack_trace' => $e->getTraceAsString()
        ];
    }

    /**
     * Render error template
     */
    private static function renderErrorTemplate(string $template, array $data): string
    {
        try {
            // Try to use Twig renderer
            return self::renderTwigTemplate($template, $data);
        } catch (Throwable $e) {
            // Fallback to basic HTML
            self::$logger->error('Failed to render error template', [
                'template' => $template,
                'error' => $e->getMessage()
            ]);
            
            return self::renderBasicErrorPage($data);
        }
    }

    /**
     * Render Twig template
     */
    private static function renderTwigTemplate(string $template, array $data): string
    {
        // Simple Twig-like rendering for error pages
        $templatePath = self::$templatePath . '/' . $template;
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template}");
        }

        $content = file_get_contents($templatePath);
        
        // Basic variable replacement for error pages
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $content = str_replace('{{ ' . $key . ' }}', $value, $content);
                $content = str_replace('{{' . $key . '}}', $value, $content);
            }
        }

        return $content;
    }

    /**
     * Render basic error page as fallback
     */
    private static function renderBasicErrorPage(array $data): string
    {
        $statusCode = $data['status_code'];
        $message = $data['error'];
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>{$statusCode} - Error</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #d32f2f; text-align: center; }
                .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .info { background: #e3f2fd; color: #1565c0; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>{$statusCode} - Error</h1>
                <div class='error'>
                    <strong>Error:</strong> {$message}
                </div>
                <div class='info'>
                    <p><strong>Time:</strong> {$data['timestamp']}</p>
                    <p><strong>Request ID:</strong> {$data['request_id']}</p>
                    <p><strong>Status Code:</strong> {$statusCode}</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Generate unique request ID
     */
    private static function generateRequestId(): string
    {
        return 'req_' . uniqid() . '.' . mt_rand(10000000, 99999999);
    }

    /**
     * Convert error level to string
     */
    private static function getErrorType(int $type): string
    {
        $types = [
            E_ERROR => 'FATAL ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSING ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED'
        ];

        return $types[$type] ?? 'UNKNOWN ERROR';
    }
} 