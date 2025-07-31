<?php
declare(strict_types=1);

/**
 * Skin Service Provider
 * 
 * Registers and manages the skin system for IslamWiki.
 * 
 * @package IslamWiki\Providers
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Providers;

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;

class SkinServiceProvider
{
    /**
     * @var Application The application instance
     */
    private Application $app;
    
    /**
     * @var SkinManager The skin manager instance
     */
    private SkinManager $skinManager;
    
    /**
     * Constructor
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->skinManager = new SkinManager($app);
    }
    
    /**
     * Register the skin service provider
     */
    public function register(): void
    {
        // Register the skin manager as a singleton
        $this->app->getContainer()->singleton('skin.manager', function () {
            return new SkinManager($this->app);
        });
        
        // Register the active skin
        $this->app->getContainer()->singleton('skin.active', function () {
            $skinManager = $this->app->getContainer()->get('skin.manager');
            return $skinManager->getActiveSkin();
        });
        
        // Add skin data to the view context
        $this->app->getContainer()->singleton('skin.data', function () {
            $skinManager = $this->app->getContainer()->get('skin.manager');
            $activeSkin = $skinManager->getActiveSkin();
            
            if ($activeSkin === null) {
                return [
                    'css' => '',
                    'js' => '',
                    'name' => 'default',
                    'version' => '0.0.28',
                ];
            }
            
            return [
                'css' => $activeSkin->getCssContent(),
                'js' => $activeSkin->getJsContent(),
                'name' => $activeSkin->getName(),
                'version' => $activeSkin->getVersion(),
                'config' => $activeSkin->getConfig() ?? [],
            ];
        });
    }
    
    /**
     * Boot the skin service provider
     */
    public function boot(): void
    {
        // Get the view renderer and add skin globals
        $viewRenderer = $this->app->getContainer()->get('view');
        $skinData = $this->app->getContainer()->get('skin.data');
        
        // Add skin variables to all views
        $viewRenderer->addGlobals([
            'skin_css' => $skinData['css'],
            'skin_js' => $skinData['js'],
            'skin_name' => $skinData['name'],
            'skin_version' => $skinData['version'],
            'skin_config' => $skinData['config'],
            'active_skin' => $this->skinManager->getActiveSkinName(),
        ]);
        
        // Register view helpers for skin functionality
        $this->registerViewHelpers();
    }
    
    /**
     * Register view helpers for skin functionality
     */
    private function registerViewHelpers(): void
    {
        // Helper function to get skin asset URL
        if (!function_exists('skin_asset')) {
            function skin_asset(string $path): string
            {
                $app = Application::getInstance();
                $activeSkin = $app->getContainer()->get('skin.active');
                
                if ($activeSkin) {
                    $skinPath = $activeSkin->getSkinPath();
                    return '/skins/' . basename($skinPath) . '/' . ltrim($path, '/');
                }
                
                return $path;
            }
        }
        
        // Helper function to check if skin has custom layout
        if (!function_exists('skin_has_custom_layout')) {
            function skin_has_custom_layout(): bool
            {
                $app = Application::getInstance();
                $skinManager = $app->getContainer()->get('skin.manager');
                return $skinManager->hasActiveSkinCustomLayout();
            }
        }
        
        // Helper function to get skin layout path
        if (!function_exists('skin_layout_path')) {
            function skin_layout_path(): string
            {
                $app = Application::getInstance();
                $skinManager = $app->getContainer()->get('skin.manager');
                return $skinManager->getActiveSkinLayoutPath();
            }
        }
        
        // Helper function to get available skins
        if (!function_exists('available_skins')) {
            function available_skins(): array
            {
                $app = Application::getInstance();
                $skinManager = $app->getContainer()->get('skin.manager');
                return $skinManager->getAvailableSkinNames();
            }
        }
        
        // Helper function to get skin metadata
        if (!function_exists('skin_metadata')) {
            function skin_metadata(): array
            {
                $app = Application::getInstance();
                $skinManager = $app->getContainer()->get('skin.manager');
                return $skinManager->getAllSkinMetadata();
            }
        }
    }
    
    /**
     * Get the skin manager
     */
    public function getSkinManager(): SkinManager
    {
        return $this->skinManager;
    }
    
    /**
     * Set the active skin
     */
    public function setActiveSkin(string $name): bool
    {
        return $this->skinManager->setActiveSkin($name);
    }
    
    /**
     * Get the active skin name
     */
    public function getActiveSkinName(): string
    {
        return $this->skinManager->getActiveSkinName();
    }
    
    /**
     * Get all available skins
     */
    public function getAvailableSkins(): array
    {
        return $this->skinManager->getSkins();
    }
} 