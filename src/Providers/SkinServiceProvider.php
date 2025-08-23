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
        $container->set('skin.data', function () {
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

        // Load skin data from the filesystem since middleware isn't working
        $skinData = $this->loadSkinData();
        
        // Add skin variables to the view renderer
        $viewRenderer->addGlobals([
            'skin_css' => $skinData['css'],
            'skin_js' => $skinData['js'],
            'skin_css_url' => $skinData['css_url'],
            'skin_js_url' => $skinData['js_url'],
            'skin_name' => $skinData['name'],
            'skin_version' => $skinData['version'],
            'skin_config' => $skinData['config'],
            'active_skin' => $skinData['name'],
        ]);
    }
    
    /**
     * Load skin data from the filesystem
     */
    private function loadSkinData(): array
    {
        $basePath = dirname(dirname(dirname(__DIR__)));
        $skinName = 'Bismillah';
        $skinPath = $basePath . '/skins/' . $skinName;
        
        // Check if skin exists
        if (!is_dir($skinPath)) {
            return $this->getDefaultSkinData();
        }
        
        // Load skin configuration
        $configFile = $skinPath . '/skin.json';
        $config = [];
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true) ?: [];
        }
        
        // Load CSS content
        $cssFile = $skinPath . '/css/' . strtolower($skinName) . '.css';
        $css = '';
        if (file_exists($cssFile)) {
            $css = file_get_contents($cssFile);
        }
        
        // Load JS content
        $jsFile = $skinPath . '/js/' . strtolower($skinName) . '.js';
        $js = '';
        if (file_exists($jsFile)) {
            $js = file_get_contents($jsFile);
        }
        
        return [
            'name' => $skinName,
            'version' => $config['version'] ?? '0.0.29',
            'css' => $css,
            'js' => $js,
            'css_url' => '/skins/' . $skinName . '/css/' . strtolower($skinName) . '.css',
            'js_url' => '/skins/' . $skinName . '/js/' . strtolower($skinName) . '.js',
            'config' => $config,
        ];
    }
    
    /**
     * Get default skin data
     */
    private function getDefaultSkinData(): array
    {
        return [
            'name' => 'Bismillah',
            'version' => '0.0.29',
            'css' => '',
            'js' => '',
            'css_url' => '/skins/Bismillah/css/bismillah.css',
            'js_url' => '/skins/Bismillah/js/bismillah.js',
            'config' => [],
        ];
    }
}
