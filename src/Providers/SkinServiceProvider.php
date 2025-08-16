<?php

/**
 * Skin Service Provider
 *
 * Registers and manages skin-related services and view helpers.
 *
 * @package IslamWiki\Providers
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Skins\SkinManager;

class SkinServiceProvider
{
    /**
     * Register skin services
     */
    public function register(AsasContainer $container): void
    {
        // For now, just register basic skin data without complex dependencies
        // This will be expanded once the basic authentication system is working
        $container->singleton('skin.data', function () {
            return [
                'css' => '',
                'js' => '',
                'name' => 'Bismillah',
                'version' => '0.0.29',
                'config' => [],
            ];
        });
    }

    /**
     * Boot the skin service provider
     */
    public function boot(AsasContainer $container): void
    {
        // Get the view renderer
        $viewRenderer = $container->get('view');

        // Add empty skin variables - will be populated by SkinMiddleware
        $viewRenderer->addGlobals([
            'skin_css' => '',
            'skin_js' => '',
            'skin_name' => 'Bismillah',
            'skin_version' => '0.0.29',
            'skin_config' => [],
            'active_skin' => 'Bismillah',
        ]);
    }
}
