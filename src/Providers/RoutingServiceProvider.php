<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use Container;\Container
use Caching;\Routing
use Logger;\Logger
use IslamWiki\Core\Database\Connection;

/**
 * Routing Service Provider
 *
 * Registers the Routing caching system with the application container.
 * Routing (رحلة) means "journey" in Arabic, representing the system that manages
 * the journey of data through various cache layers for optimal performance.
 */
class RoutingServiceProvider
{
    /**
     * Register Routing caching services with the container.
     */
    public function register(Container $container): void
    {
        // Register Routing as singleton
        $container->set(Routing::class, function () use ($container) {
            // Only resolve dependencies when the service is actually requested
            $logger = $container->get(Logger::class);
            $db = $container->get(Connection::class);
            return new RoutingCaching($container, $logger, $db);
        });

        // Register Routing with alias for easier access
        $container->alias('cache', Routing::class);
        $container->alias('rihlah', Routing::class);

        // Register cache configuration
        $container->set('cache.config', function () {
            return [
                'default_driver' => 'memory',
                'drivers' => [
                    'memory' => [
                        'enabled' => extension_loaded('apcu'),
                        'ttl' => 3600,
                    ],
                    'redis' => [
                        'enabled' => extension_loaded('redis'),
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'database' => 0,
                        'prefix' => 'rihlah:',
                        'ttl' => 3600,
                    ],
                    'file' => [
                        'enabled' => true,
                        'ttl' => 7200,
                        'path' => 'cache/files',
                    ],
                    'database' => [
                        'enabled' => true,
                        'ttl' => 1800,
                        'table' => 'rihlah_cache',
                    ],
                    'session' => [
                        'enabled' => true,
                        'ttl' => 900,
                    ],
                ],
                'warm_up' => [
                    'enabled' => true,
                    'queries' => true,
                    'api_responses' => true,
                    'templates' => true,
                ],
            ];
        });
    }

    /**
     * Boot the Routing service provider.
     */
    public function boot(Container $container): void
    {
        // Log that Routing caching system is ready
        try {
            $logger = $container->get(Logger::class);
            $logger->info('Routing caching system initialized', [
                'system' => 'Routing',
                'version' => '0.0.40',
                'features' => [
                    'memory_caching' => extension_loaded('apcu'),
                    'redis_caching' => extension_loaded('redis'),
                    'file_caching' => true,
                    'database_caching' => true,
                    'session_caching' => true,
                    'cache_warmup' => true,
                ]
            ]);
        } catch (\Exception $e) {
            // If logger is not available, just continue without logging
            error_log('Routing caching system initialized (logger not available)');
        }
    }
}
