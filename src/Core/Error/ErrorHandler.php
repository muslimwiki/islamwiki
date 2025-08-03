<?php
declare(strict_types=1);

namespace IslamWiki\Core\Error;

use Throwable;
use Psr\Log\LoggerInterface;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\Asas;

/**
 * Handles application errors and exceptions with detailed debugging information
 */
class ErrorHandler
{
    private static ?LoggerInterface $logger = null;
    private static bool $debug = false;

    /**
     * Initialize the error handler
     */
    public static function initialize(bool $debug = false): void
    {
        // Set debug mode
        self::$debug = $debug;
        
        // Ensure error reporting is set to maximum
        error_reporting(E_ALL);
        
        // Enable display errors in development
        ini_set('display_errors', '1');
        
        // Enable error logging
        ini_set('log_errors', '1');
        
        // Set a default error log location if not already set
        $logDir = __DIR__ . '/../../../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/error.log';
        if (!file_exists($logFile)) {
            @file_put_contents($logFile, '');
            @chmod($logFile, 0666);
        }
        
        ini_set('error_log', $logFile);
        
        // Set error handler
        set_error_handler([self::class, 'handleError']);
        
        // Set exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Set shutdown function to catch fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
        
        // Log initialization
        error_log('Error handler initialized. Debug mode: ' . ($debug ? 'ON' : 'OFF'));
    }

    /**
     * Set the logger instance
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
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
            return false; // Respect error_reporting settings
        }

        $errorType = self::getErrorType($errno);
        $message = sprintf(
            '%s: %s in %s on line %d',
            $errorType,
            $errstr,
            $errfile,
            $errline
        );

        self::logError($message, $errorType);

        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(Throwable $e): void
    {
        $message = sprintf(
            'Uncaught %s: %s in %s on line %d',
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        // Log the exception with full context
        self::logError($message, 'EXCEPTION', $e);

        // Log additional server state for 500 errors
        if ($e->getCode() >= 500) {
            self::logServerState($e);
        }

        $response = self::createErrorResponse($e);
        $response->send();
        exit(1);
    }
    
    /**
     * Log detailed server state for 500 errors
     */
    private static function logServerState(Throwable $e): void
    {
        $context = [
            'server' => $_SERVER,
            'session' => isset($_SESSION) ? $_SESSION : [],
            'cookies' => $_COOKIE,
            'post_data' => $_POST,
            'get_data' => $_GET,
            'files' => array_keys($_FILES),
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
            ],
            'included_files' => get_included_files(),
            'extensions' => get_loaded_extensions(),
            'php_ini' => [
                'display_errors' => ini_get('display_errors'),
                'error_reporting' => error_reporting(),
                'log_errors' => ini_get('log_errors'),
                'error_log' => ini_get('error_log'),
            ],
        ];

        $logMessage = sprintf(
            '[%s] SERVER_STATE: %s',
            date('Y-m-d H:i:s'),
            'Server state captured for 500 error: ' . $e->getMessage()
        );

        if (self::$logger !== null) {
            self::$logger->error($logMessage, $context);
        } else {
            error_log($logMessage . ' ' . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
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

            $exception = new \ErrorException(
                $error['message'], 
                0, 
                $error['type'], 
                $error['file'], 
                $error['line']
            );

            // Log the fatal error with full context
            self::logError($message, 'FATAL', $exception);
            
            // Log server state for fatal errors
            self::logServerState($exception);

            if (self::$debug) {
                $exception = new \ErrorException(
                    $error['message'], 
                    0, 
                    $error['type'], 
                    $error['file'], 
                    $error['line']
                );
                $response = self::createErrorResponse($exception);
                $response->send();
            } else {
                $response = new Response(
                    'A fatal error occurred. Please check the server logs for more information.',
                    500,
                    ['Content-Type' => 'text/plain']
                );
                $response->send();
            }
        }
    }

    /**
     * Create an error response based on the exception
     */
    private static function createErrorResponse(Throwable $e): Response
    {
        // Ensure error logging is enabled
        ini_set('log_errors', '1');
        ini_set('error_log', __DIR__ . '/../../../logs/error.log');
        
        // Log the error for debugging
        error_log('===== START ERROR HANDLER =====');
        error_log('Error class: ' . get_class($e));
        
        // Safely get message with null check
        $message = $e->getMessage();
        $message = is_string($message) ? $message : 'No message';
        error_log('Error message: ' . $message);
        
        error_log('Error code: ' . $e->getCode());
        error_log('Error file: ' . $e->getFile() . ':' . $e->getLine());
        error_log('Error trace: ' . $e->getTraceAsString());
        
        // Check if we should show debug information
        // First check if we're in a development environment
                $isDev = (function_exists('getenv') && getenv('APP_ENV') === 'development') ||
                 (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') ||
                 (getenv('APP_ENV') === 'development') ||
                (php_sapi_name() === 'cli');
                
        // Then check if debug is explicitly enabled
        $debugEnabled = (function_exists('getenv') && getenv('APP_DEBUG') === 'true') ||
                       (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true');
        
        $showDebug = self::$debug || $isDev || $debugEnabled;
        
        // If we have a custom error template, use it
        $customTemplate = __DIR__ . '/../../../resources/views/errors/500.php';
        if (file_exists($customTemplate) && is_readable($customTemplate)) {
            ob_start();
            $error = $e;
            $debug = $showDebug;
            include $customTemplate;
            $html = ob_get_clean();
        } else {
            // Fallback to a simple error page
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>500 - Internal Server Error</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background: #f8f9fa; }
                    .container { max-width: 800px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                    h1 { color: #dc3545; margin-top: 0; }
                    .error { background: #fff5f5; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; }
                    pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>500 - Internal Server Error</h1>
                    <div class="error">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
            
            if ($showDebug) {
                $html .= '
                    <h2>Debug Information</h2>
                    <h3>Error Details</h3>
                    <p><strong>Type:</strong> ' . get_class($e) . '</p>
                    <p><strong>File:</strong> ' . htmlspecialchars($e->getFile() . ':' . $e->getLine(), ENT_QUOTES, 'UTF-8') . '</p>
                    <p><strong>Code:</strong> ' . $e->getCode() . '</p>
                    <h3>Stack Trace</h3>
                    <pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
            }
            
            $html .= '
                    <p>Please try again later or contact support if the problem persists.</p>
                </div>
            </body>
            </html>';
        }

        // Simple response with minimal dependencies

        return new Response(
            500,
            [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Content-Length' => strlen($html),
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache'
            ],
            $html
        );
    }

    /**
     * Render a debug page with detailed error information
     */
    private static function renderDebugPage(Throwable $e): string
    {
        // Initialize all template variables with safe defaults
        $errorType = get_class($e);
        $errorMessage = $e->getMessage() ?: 'Unknown error';
        $errorFile = $e->getFile() ?: 'Unknown file';
        $errorLine = $e->getLine() ?: 0;
        
        // Sanitize output
        $errorType = htmlspecialchars((string)$errorType, ENT_QUOTES, 'UTF-8');
        $errorMessage = htmlspecialchars((string)$errorMessage, ENT_QUOTES, 'UTF-8');
        $errorFile = htmlspecialchars((string)$errorFile, ENT_QUOTES, 'UTF-8');
        
        // Get stack trace as string and sanitize
        $errorTrace = '';
        try {
            $errorTrace = $e->getTraceAsString();
            $errorTrace = htmlspecialchars($errorTrace, ENT_QUOTES, 'UTF-8');
        } catch (\Throwable $traceError) {
            $errorTrace = 'Unable to get stack trace: ' . htmlspecialchars($traceError->getMessage(), ENT_QUOTES, 'UTF-8');
        }
        
        // Get the source code around the error
        $source = '';
        if (!empty($e->getFile()) && $e->getLine() > 0) {
            try {
                $source = self::getErrorSource($e->getFile(), $e->getLine());
            } catch (\Throwable $sourceError) {
                $source = 'Unable to load source code: ' . htmlspecialchars($sourceError->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }
        
        // Get server and request information
        $serverInfo = self::getServerInfo();
        $requestInfo = self::getRequestInfo();

        // Ensure all template variables are set
        $templateVars = [
            'errorType' => $errorType,
            'errorMessage' => $errorMessage,
            'errorFile' => $errorFile,
            'errorLine' => $errorLine,
            'errorTrace' => $errorTrace,
            'source' => $source,
            'serverInfo' => $serverInfo,
            'requestInfo' => $requestInfo,
        ];

        // Render the error page
        ob_start();
        extract($templateVars, EXTR_SKIP);
        include __DIR__ . '/Templates/debug_error_page.php';
        return ob_get_clean() ?: 'An error occurred while rendering the error page.';
    }

    /**
     * Get the source code around the error line
     */
    private static function getErrorSource(string $file, int $line, int $linesAround = 5): string
    {
        if (!file_exists($file) || !is_readable($file)) {
            return 'Source file not available';
        }

        $source = file($file);
        $start = max(0, $line - $linesAround - 1);
        $end = min(count($source), $line + $linesAround);
        
        $result = '';
        for ($i = $start; $i < $end; $i++) {
            $lineNumber = $i + 1;
            $lineContent = htmlspecialchars($source[$i], ENT_QUOTES, 'UTF-8');
            $highlight = ($lineNumber === $line) ? ' style="background-color: #ffcccc;"' : '';
            $result .= sprintf(
                '<div%s><span style="color:#999;">%d</span> %s</div>',
                $highlight,
                $lineNumber,
                $lineContent
            );
        }
        
        return $result;
    }

    /**
     * Get server information for debugging
     */
    private static function getServerInfo(): array
    {
        return [
            'PHP Version' => phpversion(),
            'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'Server Name' => $_SERVER['SERVER_NAME'] ?? 'N/A',
            'Server Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'N/A',
            'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            'Request Time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
            'Memory Usage' => self::formatBytes(memory_get_usage(true)),
            'Peak Memory Usage' => self::formatBytes(memory_get_peak_usage(true)),
            'Max Execution Time' => ini_get('max_execution_time') . 's',
            'Memory Limit' => ini_get('memory_limit'),
        ];
    }

    /**
     * Get request information for debugging
     */
    private static function getRequestInfo(): array
    {
        return [
            'Request Method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'Request URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'Query String' => $_SERVER['QUERY_STRING'] ?? 'N/A',
            'Content Type' => $_SERVER['CONTENT_TYPE'] ?? 'N/A',
            'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'Remote IP' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'HTTPS' => isset($_SERVER['HTTPS']) ? 'Yes' : 'No',
            'Request Time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
        ];
    }

    /**
     * Format bytes to a human-readable format
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
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
            E_STRICT => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED',
        ];

        return $types[$type] ?? "UNKNOWN ERROR ($type)";
    }

    /**
     * Log an error message with context
     */
    private static function logError(string $message, string $level = 'ERROR', ?Throwable $exception = null): void
    {
        $context = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
                'query' => $_SERVER['QUERY_STRING'] ?? '',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            ],
            'server' => [
                'name' => $_SERVER['SERVER_NAME'] ?? 'N/A',
                'port' => $_SERVER['SERVER_PORT'] ?? 'N/A',
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            ],
            'php' => [
                'version' => PHP_VERSION,
                'os' => PHP_OS,
                'sapi' => PHP_SAPI,
                'memory_usage' => memory_get_usage(true),
                'peak_memory_usage' => memory_get_peak_usage(true),
                'include_path' => get_include_path(),
            ],
        ];

        // Add exception details if available
        if ($exception !== null) {
            $context['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        // Format the log message
        $logMessage = sprintf(
            '[%s] %s: %s',
            $context['timestamp'],
            $level,
            $message
        );

        if (self::$logger !== null) {
            if ($exception !== null) {
                self::$logger->error($message, ['exception' => $exception] + $context);
            } else {
                self::$logger->error($message, $context);
            }
        } else {
            // Fallback to error_log with JSON-encoded context
            error_log($logMessage . ' ' . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
