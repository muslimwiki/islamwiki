<?php
declare(strict_types=1);

/**
 * Sabr Service Provider
 *
 * Registers the Sabr queue system with the application container.
 *
 * @package IslamWiki\Providers
 * @version 0.0.42
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\Asas;
use IslamWiki\Core\Queue\Sabr;
use IslamWiki\Core\Logging\Shahid;
use IslamWiki\Core\Database\Connection;

/**
 * Sabr Service Provider
 *
 * Registers the Sabr queue system with the application container.
 */
class SabrServiceProvider
{
    /**
     * Register Sabr queue services with the container.
     *
     * @param Asas $container The dependency injection container
     */
    public function register(Asas $container): void
    {
        // Register Sabr queue system
        $container->singleton(Sabr::class, function () use ($container) {
            $logger = $container->get(Shahid::class);
            $db = $container->get(Connection::class);
            return new Sabr($container, $logger, $db);
        });

        // Register queue aliases
        $container->alias('queue', Sabr::class);
        $container->alias('sabr', Sabr::class);

        // Register queue configuration
        $container->singleton('queue.config', function () {
            return [
                'default_driver' => 'database',
                'drivers' => [
                    'database' => [
                        'connection' => 'default',
                        'table' => 'queue_jobs',
                    ],
                    'file' => [
                        'path' => storage_path('queue'),
                    ],
                    'memory' => [
                        'max_jobs' => 1000,
                    ],
                    'redis' => [
                        'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                        'port' => $_ENV['REDIS_PORT'] ?? 6379,
                        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
                        'database' => $_ENV['REDIS_DATABASE'] ?? 0,
                    ],
                ],
                'queues' => [
                    'default' => [
                        'driver' => 'database',
                        'max_attempts' => 3,
                        'timeout' => 60,
                    ],
                    'emails' => [
                        'driver' => 'database',
                        'max_attempts' => 5,
                        'timeout' => 30,
                    ],
                    'notifications' => [
                        'driver' => 'database',
                        'max_attempts' => 3,
                        'timeout' => 15,
                    ],
                    'reports' => [
                        'driver' => 'database',
                        'max_attempts' => 2,
                        'timeout' => 300,
                    ],
                    'cleanup' => [
                        'driver' => 'database',
                        'max_attempts' => 1,
                        'timeout' => 600,
                    ],
                ],
            ];
        });

        // Queue services registered successfully
    }

    /**
     * Boot the queue system.
     *
     * @param Asas $container The dependency injection container
     */
    public function boot(Asas $container): void
    {
        try {
            $logger = $container->get(Shahid::class);
            $queue = $container->get(Sabr::class);

            // Log queue system initialization
            $logger->info('Sabr queue system booted successfully', [
                'drivers' => $queue->getDrivers(),
                'config' => $container->get('queue.config')
            ]);

            // Schedule cleanup jobs if needed
            $this->scheduleCleanupJobs($container);

        } catch (\Exception $e) {
            $logger->error('Failed to boot Sabr queue system', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Schedule cleanup jobs.
     */
    private function scheduleCleanupJobs(Asas $container): void
    {
        try {
            $queue = $container->get(Sabr::class);
            $logger = $container->get(Shahid::class);

            // Schedule daily cleanup jobs
            $queue->cleanup('temp_files', ['max_age' => 86400]); // 24 hours
            $queue->cleanup('old_logs', ['max_age' => 2592000]); // 30 days
            $queue->cleanup('expired_sessions', ['max_age' => 86400]); // 24 hours
            $queue->cleanup('failed_jobs', ['max_age' => 604800]); // 7 days

            $logger->info('Cleanup jobs scheduled successfully');

        } catch (\Exception $e) {
            $logger->error('Failed to schedule cleanup jobs', [
                'error' => $e->getMessage()
            ]);
        }
    }
} 