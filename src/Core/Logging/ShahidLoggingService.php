<?php

declare(strict_types=1);

namespace IslamWiki\Core\Logging;

/**
 * Shahid Logging Service (شاهد - Witness/Evidence)
 * 
 * Comprehensive logging and error handling system.
 * Part of the Infrastructure Layer in the Islamic core architecture.
 * 
 * @package IslamWiki\Core\Logging
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class ShahidLoggingService
{
    private array $config;
    private array $loggers = [];
    private string $defaultLogger;
    private array $errorCounts = [];
    private array $performanceMetrics = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'default_logger' => 'file',
            'log_level' => 'info',
            'log_path' => __DIR__ . '/../../storage/logs',
            'max_files' => 30,
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'loggers' => [
                'file' => [
                    'driver' => 'file',
                    'path' => __DIR__ . '/../../storage/logs',
                    'filename' => 'islamwiki.log',
                    'max_files' => 30,
                    'max_file_size' => 10 * 1024 * 1024,
                ],
                'error' => [
                    'driver' => 'file',
                    'path' => __DIR__ . '/../../storage/logs',
                    'filename' => 'errors.log',
                    'max_files' => 30,
                    'max_file_size' => 10 * 1024 * 1024,
                ],
                'security' => [
                    'driver' => 'file',
                    'path' => __DIR__ . '/../../storage/logs',
                    'filename' => 'security.log',
                    'max_files' => 30,
                    'max_file_size' => 10 * 1024 * 1024,
                ],
                'performance' => [
                    'driver' => 'file',
                    'path' => __DIR__ . '/../../storage/logs',
                    'filename' => 'performance.log',
                    'max_files' => 30,
                    'max_file_size' => 10 * 1024 * 1024,
                ],
            ],
            'error_reporting' => [
                'log_errors' => true,
                'display_errors' => false,
                'log_errors_max_len' => 1024,
                'ignore_repeated_errors' => true,
                'ignore_repeated_source' => true,
            ],
            'performance_monitoring' => [
                'enabled' => true,
                'slow_query_threshold' => 1000, // 1 second
                'memory_threshold' => 128 * 1024 * 1024, // 128MB
            ],
        ], $config);

        $this->defaultLogger = $this->config['default_logger'];
        $this->initializeLoggers();
        $this->setupErrorHandling();
    }

    /**
     * Initialize loggers
     */
    private function initializeLoggers(): void
    {
        foreach ($this->config['loggers'] as $name => $config) {
            $this->loggers[$name] = $this->createLogger($config);
        }
    }

    /**
     * Create logger instance
     */
    private function createLogger(array $config): ShahidLoggerInterface
    {
        return match ($config['driver']) {
            'file' => new ShahidFileLogger($config),
            'database' => new ShahidDatabaseLogger($config),
            'syslog' => new ShahidSyslogLogger($config),
            default => throw new \InvalidArgumentException("Unsupported logger driver: {$config['driver']}"),
        };
    }

    /**
     * Setup error handling
     */
    private function setupErrorHandling(): void
    {
        if ($this->config['error_reporting']['log_errors']) {
            error_reporting(E_ALL);
            ini_set('log_errors', '1');
            ini_set('display_errors', $this->config['error_reporting']['display_errors'] ? '1' : '0');
            ini_set('log_errors_max_len', $this->config['error_reporting']['log_errors_max_len']);
            ini_set('ignore_repeated_errors', $this->config['error_reporting']['ignore_repeated_errors'] ? '1' : '0');
            ini_set('ignore_repeated_source', $this->config['error_reporting']['ignore_repeated_source'] ? '1' : '0');
        }

        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Log a message
     */
    public function log(string $level, string $message, array $context = [], string $logger = null): void
    {
        $logger = $logger ?? $this->defaultLogger;
        
        if (!isset($this->loggers[$logger])) {
            throw new \InvalidArgumentException("Logger '{$logger}' not found");
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'request_id' => $this->getRequestId(),
            'user_id' => $this->getCurrentUserId(),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $this->getUserAgent(),
            'url' => $this->getCurrentUrl(),
        ];

        $this->loggers[$logger]->log($level, $logEntry);
        
        // Update error counts for monitoring
        if (in_array($level, ['error', 'critical', 'emergency'])) {
            $this->errorCounts[$level] = ($this->errorCounts[$level] ?? 0) + 1;
        }
    }

    /**
     * Log emergency message
     */
    public function emergency(string $message, array $context = [], string $logger = null): void
    {
        $this->log('emergency', $message, $context, $logger);
    }

    /**
     * Log alert message
     */
    public function alert(string $message, array $context = [], string $logger = null): void
    {
        $this->log('alert', $message, $context, $logger);
    }

    /**
     * Log critical message
     */
    public function critical(string $message, array $context = [], string $logger = null): void
    {
        $this->log('critical', $message, $context, $logger);
    }

    /**
     * Log error message
     */
    public function error(string $message, array $context = [], string $logger = null): void
    {
        $this->log('error', $message, $context, $logger);
    }

    /**
     * Log warning message
     */
    public function warning(string $message, array $context = [], string $logger = null): void
    {
        $this->log('warning', $message, $context, $logger);
    }

    /**
     * Log notice message
     */
    public function notice(string $message, array $context = [], string $logger = null): void
    {
        $this->log('notice', $message, $context, $logger);
    }

    /**
     * Log info message
     */
    public function info(string $message, array $context = [], string $logger = null): void
    {
        $this->log('info', $message, $context, $logger);
    }

    /**
     * Log debug message
     */
    public function debug(string $message, array $context = [], string $logger = null): void
    {
        $this->log('debug', $message, $context, $logger);
    }

    /**
     * Log security event
     */
    public function security(string $message, array $context = [], string $logger = 'security'): void
    {
        $this->log('warning', $message, $context, $logger);
    }

    /**
     * Log performance metric
     */
    public function performance(string $operation, float $duration, array $context = [], string $logger = 'performance'): void
    {
        $context['duration'] = $duration;
        $context['operation'] = $operation;
        
        $this->log('info', "Performance: {$operation} completed in " . number_format($duration, 3) . "s", $context, $logger);
        
        // Store performance metrics
        $this->performanceMetrics[$operation] = [
            'duration' => $duration,
            'timestamp' => time(),
            'context' => $context,
        ];
        
        // Check for slow operations
        if ($duration > ($this->config['performance_monitoring']['slow_query_threshold'] / 1000)) {
            $this->warning("Slow operation detected: {$operation} took " . number_format($duration, 3) . "s", $context, 'performance');
        }
    }

    /**
     * Handle PHP errors
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED',
        ];

        $errorType = $errorTypes[$errno] ?? 'UNKNOWN';
        $message = "PHP {$errorType}: {$errstr} in {$errfile} on line {$errline}";

        $context = [
            'errno' => $errno,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
        ];

        $this->error($message, $context, 'error');

        // Return false to allow PHP's internal error handler to run
        return false;
    }

    /**
     * Handle exceptions
     */
    public function handleException(\Throwable $exception): void
    {
        $message = "Uncaught Exception: " . $exception->getMessage();
        
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious() ? get_class($exception->getPrevious()) : null,
        ];

        $this->critical($message, $context, 'error');

        // Display error page in production
        if (!$this->config['error_reporting']['display_errors']) {
            $this->displayErrorPage($exception);
        }
    }

    /**
     * Handle shutdown errors
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";
            
            $context = [
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
            ];

            $this->emergency($message, $context, 'error');
        }
    }

    /**
     * Display error page
     */
    private function displayErrorPage(\Throwable $exception): void
    {
        $errorCode = $exception->getCode() ?: 500;
        $errorMessage = $exception->getMessage();
        
        http_response_code($errorCode);
        
        // Try to display error page
        $errorPage = $this->getErrorPage($errorCode);
        
        if ($errorPage && file_exists($errorPage)) {
            include $errorPage;
        } else {
            // Fallback error display
            echo "<h1>Error {$errorCode}</h1>";
            echo "<p>An error occurred while processing your request.</p>";
            if ($this->config['error_reporting']['display_errors']) {
                echo "<p><strong>Error:</strong> {$errorMessage}</p>";
            }
        }
        
        exit(1);
    }

    /**
     * Get error page path
     */
    private function getErrorPage(int $errorCode): ?string
    {
        $errorPages = [
            400 => __DIR__ . '/../../resources/views/errors/400.twig',
            401 => __DIR__ . '/../../resources/views/errors/401.twig',
            403 => __DIR__ . '/../../resources/views/errors/403.twig',
            404 => __DIR__ . '/../../resources/views/errors/404.twig',
            500 => __DIR__ . '/../../resources/views/errors/500.twig',
            503 => __DIR__ . '/../../resources/views/errors/503.twig',
        ];

        return $errorPages[$errorCode] ?? null;
    }

    /**
     * Get request ID
     */
    private function getRequestId(): string
    {
        if (!isset($_SESSION['request_id'])) {
            $_SESSION['request_id'] = uniqid('req_', true);
        }
        return $_SESSION['request_id'];
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get user agent
     */
    private function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }

    /**
     * Get current URL
     */
    private function getCurrentUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        return "{$protocol}://{$host}{$uri}";
    }

    /**
     * Get error statistics
     */
    public function getErrorStats(): array
    {
        return [
            'counts' => $this->errorCounts,
            'total_errors' => array_sum($this->errorCounts),
            'last_error' => end($this->errorCounts),
        ];
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return $this->performanceMetrics;
    }

    /**
     * Clear old log files
     */
    public function rotateLogs(): void
    {
        foreach ($this->loggers as $logger) {
            if (method_exists($logger, 'rotate')) {
                $logger->rotate();
            }
        }
    }

    /**
     * Get logger instance
     */
    public function getLogger(string $name): ?ShahidLoggerInterface
    {
        return $this->loggers[$name] ?? null;
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

/**
 * Logger Interface
 */
interface ShahidLoggerInterface
{
    public function log(string $level, array $data): void;
    public function rotate(): void;
}

/**
 * File Logger
 */
class ShahidFileLogger implements ShahidLoggerInterface
{
    private string $path;
    private string $filename;
    private int $maxFiles;
    private int $maxFileSize;

    public function __construct(array $config)
    {
        $this->path = $config['path'];
        $this->filename = $config['filename'];
        $this->maxFiles = $config['max_files'];
        $this->maxFileSize = $config['max_file_size'];
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function log(string $level, array $data): void
    {
        $logFile = $this->path . '/' . $this->filename;
        
        // Check if rotation is needed
        if (file_exists($logFile) && filesize($logFile) > $this->maxFileSize) {
            $this->rotate();
        }
        
        $logEntry = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function rotate(): void
    {
        $logFile = $this->path . '/' . $this->filename;
        
        if (!file_exists($logFile)) {
            return;
        }
        
        // Rotate existing files
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $oldFile = $logFile . '.' . $i;
            $newFile = $logFile . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i == $this->maxFiles - 1) {
                    unlink($oldFile);
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        // Move current log file
        rename($logFile, $logFile . '.1');
    }
}

/**
 * Database Logger
 */
class ShahidDatabaseLogger implements ShahidLoggerInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function log(string $level, array $data): void
    {
        // This would need database connection to implement
        // For now, just pass through to avoid errors
    }

    public function rotate(): void
    {
        // Database rotation logic would go here
    }
}

/**
 * Syslog Logger
 */
class ShahidSyslogLogger implements ShahidLoggerInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function log(string $level, array $data): void
    {
        $message = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        syslog($this->getSyslogPriority($level), $message);
    }

    public function rotate(): void
    {
        // Syslog doesn't need rotation
    }

    private function getSyslogPriority(string $level): int
    {
        return match (strtolower($level)) {
            'emergency' => LOG_EMERG,
            'alert' => LOG_ALERT,
            'critical' => LOG_CRIT,
            'error' => LOG_ERR,
            'warning' => LOG_WARNING,
            'notice' => LOG_NOTICE,
            'info' => LOG_INFO,
            'debug' => LOG_DEBUG,
            default => LOG_INFO,
        };
    }
} 