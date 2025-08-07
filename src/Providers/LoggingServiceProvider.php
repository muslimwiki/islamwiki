<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Logging\ShahidLogger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Logging Service Provider
 *
 * This service provider sets up the logging system for the application.
 * It configures and registers the logger instance with the container.
 */
class LoggingServiceProvider
{
    /**
     * Register the logger service.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        // error_log('LoggingServiceProvider: register() called');
        $container->bind(LoggerInterface::class, function (ContainerInterface $c) {
            // error_log('LoggingServiceProvider: Creating Shahid logger');
            $config = [];
            try {
                $config = $c->get('settings')['logging'] ?? [];
            } catch (\Exception $e) {
                // If settings binding doesn't exist, use defaults
                // error_log('LoggingServiceProvider: No settings binding found, using defaults');
            }

            // Ensure logs directory exists
            $logDir = $config['log_path'] ?? __DIR__ . '/../../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Create Shahid witness system instance
            $logger = new ShahidLogger(
                $logDir,
                $config['level'] ?? \Psr\Log\LogLevel::DEBUG,
                $config['max_file_size'] ?? 10, // MB
                $config['max_files'] ?? 5
            );

            // error_log('LoggingServiceProvider: Shahid logger created successfully');
            return $logger;
        });

        // Alias for convenience
        $container->bind('logger', function (ContainerInterface $c) {
            return $c->get(LoggerInterface::class);
        });

        // error_log('LoggingServiceProvider: register() completed');
    }

    /**
     * Boot the logging service provider.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        // Register error handler
        $this->registerErrorHandler($container);

        // Register shutdown handler for fatal errors
        $this->registerShutdownHandler($container);

        // Register exception handler
        $this->registerExceptionHandler($container);
    }

    /**
     * Register the error handler.
     */
    protected function registerErrorHandler(ContainerInterface $container): void
    {
        set_error_handler(function (
            int $errno,
            string $errstr,
            string $errfile = '',
            int $errline = 0,
            array $errcontext = []
        ) use ($container) {
            $log = $container->get(LoggerInterface::class);

            // Skip if error reporting is disabled
            if (!(error_reporting() & $errno)) {
                return false;
            }

            // Map error level to PSR log level
            $level = match (true) {
                ($errno & E_USER_ERROR) === E_USER_ERROR => \Psr\Log\LogLevel::ERROR,
                ($errno & E_USER_WARNING) === E_USER_WARNING => \Psr\Log\LogLevel::WARNING,
                ($errno & E_USER_NOTICE) === E_USER_NOTICE => \Psr\Log\LogLevel::NOTICE,
                ($errno & E_USER_DEPRECATED) === E_USER_DEPRECATED => \Psr\Log\LogLevel::NOTICE,
                default => \Psr\Log\LogLevel::ERROR,
            };

            // Log the error
            $log->log($level, sprintf(
                '%s in %s on line %d',
                $errstr,
                $errfile,
                $errline
            ), [
                'type' => $errno,
                'file' => $errfile,
                'line' => $errline,
                'context' => $errcontext,
            ]);

            // Don't execute PHP's internal error handler
            return true;
        });
    }

    /**
     * Register the shutdown handler.
     */
    protected function registerShutdownHandler(ContainerInterface $container): void
    {
        register_shutdown_function(function () use ($container) {
            $error = error_get_last();

            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
                $log = $container->get(LoggerInterface::class);

                $log->critical(
                    sprintf(
                        'Fatal error: %s in %s on line %d',
                        $error['message'],
                        $error['file'],
                        $error['line']
                    ),
                    [
                        'type' => $error['type'],
                        'file' => $error['file'],
                        'line' => $error['line'],
                    ]
                );
            }
        });
    }

    /**
     * Register the exception handler.
     */
    protected function registerExceptionHandler(ContainerInterface $container): void
    {
        set_exception_handler(function (\Throwable $e) use ($container) {
            $log = $container->get(LoggerInterface::class);
            $log->error(
                sprintf(
                    'Uncaught Exception: %s in %s on line %d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                [
                    'exception' => get_class($e),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            // Display a generic error message in production
            // For now, always show errors in development
            if (false) {
                http_response_code(500);
                echo 'An error occurred. Please try again later.';
                exit(1);
            }

            // Re-throw the exception to let the default error handler handle it in development
            throw $e;
        });
    }
}
