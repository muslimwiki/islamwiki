<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Caching\RihlahCaching;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Database\Connection;

/**
 * Rihlah Service Provider
 *
 * Registers the Rihlah caching system with the application container.
 * Rihlah (رحلة) means "journey" in Arabic, representing the system that manages
 * the journey of data through various cache layers for optimal performance.
 */
class RihlahServiceProvider
{
    /**
     * Register Rihlah caching services with the container.
     */
    public function register(AsasContainer $container): void
    {
        // Register Rihlah as singleton
        $container->set(Rihlah::class, function () use ($container) {
            // Only resolve dependencies when the service is actually requested
            $logger = $container->get(ShahidLogger::class);
            $db = $container->get(Connection::class);
            return new RihlahCaching($container, $logger, $db);
        });

        // Register Rihlah with alias for easier access
        $container->alias('cache', Rihlah::class);
        $container->alias('rihlah', Rihlah::class);

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
     * Boot the Rihlah service provider.
     */
    public function boot(AsasContainer $container): void
    {
        // Log that Rihlah caching system is ready
        try {
            $logger = $container->get(ShahidLogger::class);
            $logger->info('Rihlah caching system initialized', [
                'system' => 'Rihlah',
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
            error_log('Rihlah caching system initialized (logger not available)');
        }
    }
}
