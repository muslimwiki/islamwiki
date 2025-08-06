<?php
declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\API\SirajAPI;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Session\WisalSession;

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
    public function register(AsasContainer $container): void
    {
        // Register Siraj as singleton
        $container->singleton(Siraj::class, function () use ($container) {
            $logger = $container->get(ShahidLogger::class);
            $session = $container->get(WisalSession::class);
            return new SirajAPI($container, $logger, $session);
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
    public function boot(AsasContainer $container): void
    {
        // Log that Siraj API system is ready
        try {
            $logger = $container->get(ShahidLogger::class);
            $logger->info('Siraj API management system initialized', [
                'system' => 'Siraj',
                'version' => '0.0.40',
                'features' => ['authentication', 'rate_limiting', 'response_formatting']
            ]);
        } catch (\Exception $e) {
            // If logger is not available, just continue without logging
            error_log('Siraj API management system initialized (logger not available)');
        }
    }
} 