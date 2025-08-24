<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use Logger;\Logger
use Psr\Container\ContainerInterface;
use Container;\Container
use Psr\Log\LoggerInterface;

/**
 * Logging Service Provider
 *
 * This service provider sets up the core logging system for the application.
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
        // Cast to Container to access set/alias methods
        if (!$container instanceof \IslamWiki\Core\Container\Container {
            throw new \InvalidArgumentException('Container must be an instance of Container');
        }
        
        $container->set(LoggerInterface::class, function (ContainerInterface $c) {
            $config = [];
            try {
                $config = $c->get('settings')['logging'] ?? [];
            } catch (\Exception $e) {
                // If settings binding doesn't exist, use defaults
            }

            // Ensure logs directory exists
            $logDir = $config['log_path'] ?? __DIR__ . '/../../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Create core logging system instance
            $logger = new LoggerLogger(
                $logDir,
                $config['level'] ?? \Psr\Log\LogLevel::DEBUG,
                $config['max_file_size'] ?? 10, // MB
                $config['max_files'] ?? 5
            );

            return $logger;
        });

        // Alias for convenience
        $container->set('logger', function (ContainerInterface $c) {
            return $c->get(LoggerInterface::class);
        });

        // Logger is already registered as LoggerInterface above
        // No need to register it again to avoid circular dependency

        // Register template management extension
        try {
            $extension = new \IslamWiki\Extensions\TemplateManagementExtension\TemplateManagementExtension($container);
            $container->set('template.management', $extension);
            $container->alias('template.management', \IslamWiki\Extensions\TemplateManagementExtension\TemplateManagementExtension::class);
            
            $container->get('logger')->info('Template Management Extension registered successfully');
        } catch (\Exception $e) {
            error_log('Failed to register Template Management Extension: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize Logging error handler
     */
    private function initializeLoggingErrorHandler(Container $container): void
    {
        try {
            $logger = $container->get(LoggerInterface::class);
            $debug = $container->get('settings')['debug'] ?? false;
            
            \IslamWiki\Core\Error\Logger $debug);
            
            $container->get('shahid.logger')->info('Logging Error Handler initialized successfully');
        } catch (\Exception $e) {
            error_log('Failed to initialize Logging Error Handler: ' . $e->getMessage());
        }
    }

    /**
     * Register Template Management Extension
     */
    private function registerTemplateManagementExtension(Container $container): void
    {
        try {
            $extension = new \IslamWiki\Extensions\TemplateManagementExtension\TemplateManagementExtension($container);
            $container->set('template.management', $extension);
            $container->alias('template.management', \IslamWiki\Extensions\TemplateManagementExtension\TemplateManagementExtension::class);
            
            $container->get('shahid.logger')->info('Template Management Extension registered successfully');
        } catch (\Exception $e) {
            error_log('Failed to register Template Management Extension: ' . $e->getMessage());
        }
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
