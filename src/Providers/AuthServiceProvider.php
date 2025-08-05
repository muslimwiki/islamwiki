<?php
declare(strict_types=1);

/**
 * AuthServiceProvider
 * 
 * Registers authentication services with the container.
 * 
 * @package IslamWiki\Providers
 * @version 0.0.45
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Auth\Aman;

class AuthServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(AsasContainer $container): void
    {
        // Register the Aman authentication manager
        $container->singleton('auth', function (AsasContainer $container) {
            $session = $container->get('session');
            $db = $container->get('db');
            return new Aman($session, $db);
        });
        
        // Register Aman as a singleton with its class name
        $container->singleton(Aman::class, function (AsasContainer $container) {
            return $container->get('auth');
        });
    }
    
    /**
     * Boot the service provider.
     */
    public function boot(AsasContainer $container): void
    {
        // Any boot-time initialization can go here
        // For now, we don't need any boot-time setup
    }
} 