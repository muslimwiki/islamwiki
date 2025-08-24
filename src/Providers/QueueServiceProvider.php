<?php

/**
 * Queue Service Provider
 *
 * Registers the Queue queue system with the application container.
 *
 * @package IslamWiki\Providers
 * @version 0.0.42
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Providers;

use Container;\Container
use Queue;\Queue
use Logger;\Logger
use IslamWiki\Core\Database\Connection;

/**
 * Queue Service Provider
 *
 * Registers the Queue queue system with the application container.
 */
class QueueServiceProvider
{
    /**
     * Register Queue queue services with the container.
     *
     * @param Container $container The dependency injection container
     */
    public function register(Container $container): void
    {
        // Register Queue queue system
        $container->set(Queue::class, function () use ($container) {
            // Only resolve dependencies when the service is actually requested
            $logger = $container->get(Logger::class);
            $db = $container->get(Connection::class);
            return new Queue($container, $logger, $db);
        });

        // Register queue aliases
        $container->alias('queue', Queue::class);
        $container->alias('sabr', Queue::class);

        // Register queue configuration
        $container->set('queue.config', function () {
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
     * @param Container $container The dependency injection container
     */
    public function boot(Container $container): void
    {
        try {
            $logger = $container->get(Logger::class);
            $queue = $container->get(Queue::class);

            // Log queue system initialization
            $logger->info('Queue queue system booted successfully', [
                'drivers' => $queue->getDrivers(),
                'config' => $container->get('queue.config')
            ]);

            // Schedule cleanup jobs if needed
            $this->scheduleCleanupJobs($container);
        } catch (\Exception $e) {
            if ($logger) {
                $logger->error('Failed to boot Queue queue system', [
                    'error' => $e->getMessage()
                ]);
            } else {
                error_log('Failed to boot Queue queue system: ' . $e->getMessage());
            }
        }
    }

    /**
     * Schedule cleanup jobs.
     */
    private function scheduleCleanupJobs(Container $container): void
    {
        try {
            $queue = $container->get(Queue::class);
            $logger = $container->get(Logger::class);

            // Schedule daily cleanup jobs
            $queue->cleanup('temp_files', ['max_age' => 86400]); // 24 hours
            $queue->cleanup('old_logs', ['max_age' => 2592000]); // 30 days
            $queue->cleanup('expired_sessions', ['max_age' => 86400]); // 24 hours
            $queue->cleanup('failed_jobs', ['max_age' => 604800]); // 7 days

            if ($logger) {
                $logger->info('Cleanup jobs scheduled successfully');
            }
        } catch (\Exception $e) {
            if ($logger) {
                $logger->error('Failed to schedule cleanup jobs', [
                    'error' => $e->getMessage()
                ]);
            } else {
                error_log('Failed to schedule cleanup jobs: ' . $e->getMessage());
            }
        }
    }
}
