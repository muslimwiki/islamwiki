<?php
declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Asas;
use IslamWiki\Core\API\Siraj;
use IslamWiki\Core\Logging\Shahid;
use IslamWiki\Core\Session\Wisal;

/**
 * Siraj Service Provider
 * 
 * Registers the Siraj API management system with the application container.
 * Siraj (سراج) means "lamp" or "light" in Arabic, representing the system
 * that illuminates and guides API interactions.
 */
class SirajServiceProvider
{
    /**
     * Register Siraj API services with the container.
     */
    public function register(Asas $container): void
    {
        // Register Siraj as singleton
        $container->singleton(Siraj::class, function () use ($container) {
            $logger = $container->get(Shahid::class);
            $session = $container->get(Wisal::class);
            return new Siraj($container, $logger, $session);
        });

        // Register Siraj with alias for easier access
        $container->alias('api', Siraj::class);
        $container->alias('siraj', Siraj::class);

        // Register API configuration
        $container->singleton('api.config', function () {
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
     * Boot the Siraj service provider.
     */
    public function boot(Asas $container): void
    {
        // Log that Siraj API system is ready
        $logger = $container->get(Shahid::class);
        $logger->info('Siraj API management system initialized', [
            'system' => 'Siraj',
            'version' => '0.0.40',
            'features' => ['authentication', 'rate_limiting', 'response_formatting']
        ]);
    }
} 