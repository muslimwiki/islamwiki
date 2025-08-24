<?php

/**
 * AuthServiceProvider
 *
 * Registers authentication services with the container.
 *
 * @package IslamWiki\Providers
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Auth\Security;
use IslamWiki\Core\Session\Session;
use IslamWiki\Core\Logging\Logger;

class AuthServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(Container $container): void
    {
        // Register the enhanced core security manager as a lazy service
        $container->set(Security::class, function (Container $container) {
            // Only resolve dependencies when the service is actually requested
            $session = $container->get(Session::class);
            $db = $container->get('db');
            $logger = $container->get(Logger::class);
            
            // Get security configuration from settings
            $config = [];
            try {
                $config = $container->get('settings')['security'] ?? [];
            } catch (\Exception $e) {
                // Use default configuration if settings not available
            }
            
            return new Security($session, $db, $logger, $config);
        });

        // Register 'auth' alias to point to Security
        $container->alias('auth', Security::class);
        
        // Register 'security' alias for backward compatibility
        $container->alias('security', Security::class);
    }

    /**
     * Boot the service provider.
     */
    public function boot(Container $container): void
    {
        // Any boot-time initialization can go here
        // For now, we don't need any boot-time setup
    }
}
