<?php

/**
 * AuthServiceProvider
 *
 * Registers authentication services with the container.
 *
 * @package IslamWiki\Providers
 * @version 0.0.45
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Auth\AmanSecurity;

class AuthServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(AsasContainer $container): void
    {
        // Register the Aman authentication manager as a lazy service
        $container->set(AmanSecurity::class, function (AsasContainer $container) {
            // Only resolve dependencies when the service is actually requested
            $session = $container->get('session');
            $db = $container->get('db');
            return new AmanSecurity($session, $db);
        });

        // Register 'auth' alias to point to AmanSecurity
        $container->alias('auth', AmanSecurity::class);
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
