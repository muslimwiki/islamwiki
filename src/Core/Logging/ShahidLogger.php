<?php
declare(strict_types=1);

namespace IslamWiki\Core\Logging;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Shahid (شاهد) - Witness System
 * 
 * Comprehensive logging system for IslamWiki.
 * Shahid means "witness" or "testimony" in Arabic, representing the
 * system that bears witness to all application events and activities.
 */
class ShahidLogger implements LoggerInterface
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
     * Create a new Shahid witness system instance.
     *
     * @param string $logDir The directory where log files are stored
     * @param string $minLevel The minimum log level to log
     * @param int $maxFileSize Maximum log file size in MB before rotation
     * @param int $maxFiles Number of log files to keep when rotating
     */
    public function __construct(
        string $logDir,
        string $minLevel = 'debug',
        int $maxFileSize = 10,
        int $maxFiles = 5
    ) {
        $this->logDir = rtrim($logDir, '/');
        $this->minLevel = $minLevel;
        $this->maxFileSize = $maxFileSize * 1024 * 1024; // Convert MB to bytes
        $this->maxFiles = $maxFiles;
        
        // Ensure log directory exists
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        
        $this->logFile = $this->getLogFilePath();
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
     * Log performance metrics.
     */
    public function performance(string $operation, float $duration, array $context = []): void
    {
        $context['duration_ms'] = round($duration * 1000, 2);
        $context['operation'] = $operation;
        $this->info("Performance: {$operation} completed in {$context['duration_ms']}ms", $context);
    }

    /**
     * Log database query with timing.
     */
    public function query(string $sql, float $duration, array $context = []): void
    {
        $context['sql'] = $sql;
        $context['duration_ms'] = round($duration * 1000, 2);
        $this->debug("Database query executed in {$context['duration_ms']}ms", $context);
    }

    /**
     * Log security event.
     */
    public function security(string $event, array $context = []): void
    {
        $context['security_event'] = $event;
        $this->warning("Security event: {$event}", $context);
    }

    /**
     * Log user action.
     */
    public function userAction(string $action, array $context = []): void
    {
        $context['user_action'] = $action;
        $this->info("User action: {$action}", $context);
    }

    /**
     * Log API request.
     */
    public function apiRequest(string $endpoint, array $context = []): void
    {
        $context['api_endpoint'] = $endpoint;
        $this->info("API request to {$endpoint}", $context);
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
