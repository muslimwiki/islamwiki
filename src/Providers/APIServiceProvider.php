<?php

declare(strict_types=1);

namespace IslamWiki\Providers;

use Container;\Container
use API;\API
use Logger;\Logger
use Session;\Session

/**
 * API Service Provider
 *
 * Registers the API API management system with the application container.
 * API (سراج) means "lamp" or "light" in Arabic, representing the system
 * that illuminates and guides API interactions.
 */
class APIServiceProvider
{
    /**
     * Register API API services with the container.
     */
    public function register(Container $container): void
    {
        // Register API as singleton
        $container->set(API::class, function () use ($container) {
            $logger = $container->get(Logger::class);
            $session = $container->get(Session::class);
            return new APIAPI($container, $logger, $session);
        });

        // Register API with alias for easier access
        $container->alias('api', API::class);
        $container->alias('siraj', API::class);

        // Register API configuration
        $container->set('api.config', function () {
            return [
                'rate_limiting' => [
                    'default' => ['requests' => 60, 'window' => 60],
                    'strict' => ['requests' => 10, 'window' => 60],
                    'relaxed' => ['requests' => 100, 'window' => 60],
                ],
                'authentication' => [
                    'methods' => ['session', 'token', 'api_key'],
                    'default_method' => 'session',
                ],
                'response_formats' => ['json', 'xml', 'html'],
                'default_format' => 'json',
            ];
        });
    }

    /**
     * Boot the API service provider.
     */
    public function boot(Container $container): void
    {
        // Log that API API system is ready
        try {
            $logger = $container->get(Logger::class);
            $logger->info('API API management system initialized', [
                'system' => 'API',
                'version' => '0.0.40',
                'features' => ['authentication', 'rate_limiting', 'response_formatting']
            ]);
        } catch (\Exception $e) {
            // If logger is not available, just continue without logging
            error_log('API API management system initialized (logger not available)');
        }
    }
}
