<?php
declare(strict_types=1);

namespace IslamWiki\Core\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use DateTimeImmutable;

/**
 * PSR-3 compatible logger implementation for IslamWiki.
 * 
 * This logger handles different log levels and writes to specified log files
 * with proper formatting and rotation support.
 */
class Logger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * The log levels with their severity.
     */
    private const LEVELS = [
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::NOTICE => 2,
        LogLevel::WARNING => 3,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 5,
        LogLevel::ALERT => 6,
        LogLevel::EMERGENCY => 7,
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
     * Create a new logger instance.
     *
     * @param string $logDir The directory where log files are stored
     * @param string $minLevel The minimum log level to log
     * @param int $maxFileSize Maximum log file size in MB before rotation
     * @param int $maxFiles Number of log files to keep when rotating
     */
    public function __construct(
        string $logDir,
        string $minLevel = LogLevel::DEBUG,
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

        // Interpolate context into message
        $message = $this->interpolate((string) $message, $context);
        
        // Format the log entry
        $timestamp = (new DateTimeImmutable())->format('Y-m-d H:i:s.u');
        $level = strtoupper($level);
        $logEntry = sprintf(
            "[%s] %s: %s %s" . PHP_EOL,
            $timestamp,
            $level,
            $message,
            !empty($context) ? json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ''
        );

        // Check if we need to rotate the log file
        if (file_exists($this->logFile) && filesize($this->logFile) >= $this->maxFileSize) {
            $this->rotateLogs();
        }

        // Write to the log file
        error_log($logEntry, 3, $this->logFile);
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
    
    /**
     * Create a context array with common fields for request logging.
     */
    public function createRequestContext(Request $request): array
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $userAgent = $request->getHeaderLine('User-Agent');
        $userId = $request->getAttribute('user')['id'] ?? 'guest';
        
        return [
            'ip' => $ip,
            'method' => $method,
            'uri' => $uri,
            'user_agent' => $userAgent,
            'user_id' => $userId,
        ];
    }
    
    /**
     * Log an exception with stack trace.
     */
    public function exception(\Throwable $e, array $context = [], string $level = LogLevel::ERROR): void
    {
        $this->log($level, sprintf(
            '%s in %s:%d',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ), array_merge([
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString(),
        ], $context));
    }
}
