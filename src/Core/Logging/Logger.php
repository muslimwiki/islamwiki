<?php

declare(strict_types=1);

namespace IslamWiki\Core\Logging;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Core Logging System
 *
 * Comprehensive logging system for IslamWiki.
 * This is the unified logging service that handles all application
 * logging, error tracking, monitoring, and performance metrics.
 */
class Logger implements LoggerInterface
{
    /**
     * The log levels with their severity.
     */
    private const LEVELS = [
        'debug' => 0,
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5,
        'alert' => 6,
        'emergency' => 7,
    ];

    /**
     * The minimum log level to actually log.
     */
    private string $minLevel;

    /**
     * The directory where log files are stored.
     */
    private string $logDir;

    /**
     * The current log file path.
     */
    private string $logFile;

    /**
     * The maximum log file size in bytes before rotation.
     */
    private int $maxFileSize;

    /**
     * The number of log files to keep when rotating.
     */
    private int $maxFiles;

    /**
     * Configuration for different log types.
     */
    private array $config;

    /**
     * Performance metrics tracking.
     */
    private array $performanceMetrics = [];

    /**
     * Error counts for monitoring.
     */
    private array $errorCounts = [];

    /**
     * Create a new core logging system instance.
     *
     * @param string $logDir The directory where log files are stored
     * @param string $minLevel The minimum log level to log
     * @param int $maxFileSize Maximum log file size in MB before rotation
     * @param int $maxFiles Number of log files to keep when rotating
     * @param array $config Additional configuration options
     */
    public function __construct(
        string $logDir,
        string $minLevel = 'debug',
        int $maxFileSize = 10,
        int $maxFiles = 5,
        array $config = []
    ) {
        $this->logDir = rtrim($logDir, '/');
        $this->minLevel = $minLevel;
        $this->maxFileSize = $maxFileSize * 1024 * 1024; // Convert MB to bytes
        $this->maxFiles = $maxFiles;
        $this->config = array_merge([
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

        // Ensure log directory exists
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        $this->logFile = $this->getLogFilePath();
        $this->setupErrorHandling();
    }

    /**
     * Setup error handling integration.
     */
    private function setupErrorHandling(): void
    {
        if ($this->config['error_reporting']['log_errors']) {
            ini_set('log_errors', '1');
            ini_set('error_log', $this->logFile);
        }

        if (!$this->config['error_reporting']['display_errors']) {
            ini_set('display_errors', '0');
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = []): void
    {
        // Check if we should log this level
        if (!$this->shouldLog($level)) {
            return;
        }

        // Add timestamp and process info to context
        $context['timestamp'] = date('Y-m-d H:i:s');
        $context['pid'] = getmypid();
        $context['memory_usage'] = memory_get_usage();
        $context['peak_memory'] = memory_get_peak_usage();

        // Add request information if available
        if (isset($_SERVER['REQUEST_URI'])) {
            $context['request_uri'] = $_SERVER['REQUEST_URI'];
            $context['request_method'] = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
            $context['remote_addr'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        // Interpolate the message
        $interpolatedMessage = $this->interpolate($message, $context);

        // Format the log entry
        $logEntry = sprintf(
            "[%s] %s: %s %s\n",
            $context['timestamp'],
            strtoupper($level),
            $interpolatedMessage,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES) : ''
        );

        // Check if we need to rotate logs
        $this->rotateLogs();

        // Write to log file
        if (file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            // If we can't write to the log file, write to error log
            error_log("Failed to write to log file: {$this->logFile}");
        }
    }

    /**
     * Log a debug message with additional context.
     */
    public function debug($message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log an info message with additional context.
     */
    public function info($message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log a warning message with additional context.
     */
    public function warning($message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log an error message with additional context.
     */
    public function error($message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log a critical message with additional context.
     */
    public function critical($message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log an alert message with additional context.
     */
    public function alert($message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Log an emergency message with additional context.
     */
    public function emergency($message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Log a notice message with additional context.
     */
    public function notice($message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Log exception with full context.
     */
    public function exception(\Throwable $e, array $context = [], string $level = 'error'): void
    {
        $context['exception_class'] = get_class($e);
        $context['exception_message'] = $e->getMessage();
        $context['exception_file'] = $e->getFile();
        $context['exception_line'] = $e->getLine();
        $context['exception_trace'] = $e->getTraceAsString();

        $this->log($level, "Exception: {$e->getMessage()}", $context);
    }

    /**
     * Log performance metrics with tracking.
     */
    public function performance(string $operation, float $duration, array $context = []): void
    {
        $context['duration_ms'] = round($duration * 1000, 2);
        $context['operation'] = $operation;
        
        // Track performance metrics
        if ($this->config['performance_monitoring']['enabled']) {
            $this->performanceMetrics[$operation][] = $duration;
            
            // Check if operation is slow
            if ($duration > ($this->config['performance_monitoring']['slow_query_threshold'] / 1000)) {
                $this->warning("Slow operation detected: {$operation} took {$context['duration_ms']}ms", $context);
            }
        }
        
        $this->info("Performance: {$operation} completed in {$context['duration_ms']}ms", $context);
    }

    /**
     * Log database query with timing and performance tracking.
     */
    public function query(string $sql, float $duration, array $context = []): void
    {
        $context['sql'] = $sql;
        $context['duration_ms'] = round($duration * 1000, 2);
        
        // Track query performance
        if ($this->config['performance_monitoring']['enabled']) {
            $this->performanceMetrics['database_queries'][] = $duration;
        }
        
        $this->debug("Database query executed in {$context['duration_ms']}ms", $context);
    }

    /**
     * Log security event with enhanced tracking.
     */
    public function security(string $event, array $context = []): void
    {
        $context['security_event'] = $event;
        $context['timestamp'] = date('Y-m-d H:i:s');
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Track security events
        if (!isset($this->errorCounts['security_events'])) {
            $this->errorCounts['security_events'] = 0;
        }
        $this->errorCounts['security_events']++;
        
        $this->warning("Security event: {$event}", $context);
    }

    /**
     * Log user action with enhanced context.
     */
    public function userAction(string $action, array $context = []): void
    {
        $context['user_action'] = $action;
        $context['timestamp'] = date('Y-m-d H:i:s');
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $this->info("User action: {$action}", $context);
    }

    /**
     * Log API request with enhanced monitoring.
     */
    public function apiRequest(string $endpoint, array $context = []): void
    {
        $context['api_endpoint'] = $endpoint;
        $context['timestamp'] = date('Y-m-d H:i:s');
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['method'] = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        
        $this->info("API request to {$endpoint}", $context);
    }

    /**
     * Get performance metrics summary.
     */
    public function getPerformanceMetrics(): array
    {
        $metrics = [];
        
        foreach ($this->performanceMetrics as $operation => $durations) {
            if (!empty($durations)) {
                $metrics[$operation] = [
                    'count' => count($durations),
                    'average' => array_sum($durations) / count($durations),
                    'min' => min($durations),
                    'max' => max($durations),
                    'total' => array_sum($durations)
                ];
            }
        }
        
        return $metrics;
    }

    /**
     * Get error counts summary.
     */
    public function getErrorCounts(): array
    {
        return $this->errorCounts;
    }

    /**
     * Reset performance metrics.
     */
    public function resetPerformanceMetrics(): void
    {
        $this->performanceMetrics = [];
    }

    /**
     * Reset error counts.
     */
    public function resetErrorCounts(): void
    {
        $this->errorCounts = [];
    }

    /**
     * Log memory usage information.
     */
    public function memoryUsage(array $context = []): void
    {
        $context['memory_current'] = memory_get_usage(true);
        $context['memory_peak'] = memory_get_peak_usage(true);
        $context['memory_limit'] = ini_get('memory_limit');
        
        if ($this->config['performance_monitoring']['enabled']) {
            $memoryLimit = $this->parseMemoryLimit($context['memory_limit']);
            if ($context['memory_current'] > $memoryLimit * 0.8) {
                $this->warning("High memory usage detected: {$context['memory_current']} bytes", $context);
            }
        }
        
        $this->debug("Memory usage: {$context['memory_current']} bytes (peak: {$context['memory_peak']} bytes)", $context);
    }

    /**
     * Parse memory limit string to bytes.
     */
    private function parseMemoryLimit(string $limit): int
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        return match ($unit) {
            'k' => $value * 1024,
            'm' => $value * 1024 * 1024,
            'g' => $value * 1024 * 1024 * 1024,
            default => $value
        };
    }

    /**
     * Create a request context array.
     */
    public function createRequestContext(\IslamWiki\Core\Http\Request $request): array
    {
        return [
            'ip' => $this->getClientIp($request),
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->getPath(),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'referer' => $request->getHeaderLine('Referer'),
            'content_type' => $request->getHeaderLine('Content-Type'),
            'content_length' => $request->getHeaderLine('Content-Length'),
        ];
    }

    /**
     * Get client IP address from request.
     */
    private function getClientIp(\IslamWiki\Core\Http\Request $request): string
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
     * Check if the logger should log at the given level.
     */
    private function shouldLog(string $level): bool
    {
        return self::LEVELS[$level] >= self::LEVELS[$this->minLevel];
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    private function interpolate(string $message, array $context = []): string
    {
        if (empty($context)) {
            return $message;
        }

        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * Get the current log file path.
     */
    private function getLogFilePath(): string
    {
        return sprintf('%s/application-%s.log', $this->logDir, date('Y-m-d'));
    }

    /**
     * Rotate log files.
     */
    private function rotateLogs(): void
    {
        $logFile = $this->getLogFilePath();

        // If current log file doesn't exist, no need to rotate
        if (!file_exists($logFile)) {
            return;
        }

        $basePath = $this->logDir . '/application-';

        // Rotate existing files
        for ($i = $this->maxFiles - 1; $i >= 0; --$i) {
            $rotateFile = $basePath . date('Y-m-d') . '-' . $i . '.log';
            if (file_exists($rotateFile)) {
                if ($i === $this->maxFiles - 1) {
                    @unlink($rotateFile);
                } else {
                    $newFile = $basePath . date('Y-m-d') . '-' . ($i + 1) . '.log';
                    rename($rotateFile, $newFile);
                }
            }
        }

        // Rotate the current log file
        rename($logFile, $basePath . date('Y-m-d') . '-0.log');
    }
}
