<?php
declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container;
use IslamWiki\Core\Extensions\ExtensionManager;
use IslamWiki\Core\Extensions\Hooks\HookManager;

/**
 * Extension Service Provider
 * 
 * Registers and bootstraps the extension system.
 */
class ExtensionServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(Container $container): void
    {
        // Register the HookManager
        $container->singleton(HookManager::class, function () {
            return new HookManager();
        });

        // Register the ExtensionManager
        $container->singleton(ExtensionManager::class, function (Container $container) {
            return new ExtensionManager($container);
        });
    }

    /**
     * Bootstrap the service provider.
     */
    public function boot(Container $container): void
    {
        // Don't load extensions during boot to avoid circular dependencies
        // Extensions will be loaded when the ExtensionManager is first accessed
        error_log('ExtensionServiceProvider: Service provider booted successfully');
    }
} 