<?php
declare(strict_types=1);

/**
 * Skin Service Provider
 * 
 * Registers and manages skin-related services and view helpers.
 * 
 * @package IslamWiki\Providers
 * @version 0.0.29
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
     * Register skin services
     */
    public function register(): void
    {
        // Register the skin manager as a factory to avoid caching issues
        $this->app->getContainer()->bind('skin.manager', function () {
            return new \IslamWiki\Skins\SkinManager($this->app);
        });
        
        // Register the active skin (will be resolved dynamically)
        $this->app->getContainer()->singleton('skin.active', function () {
            return $this->getActiveSkinForCurrentUser();
        });
        
        // Add skin data to the view context (will be resolved dynamically)
        $this->app->getContainer()->singleton('skin.data', function () {
            return $this->getSkinDataForCurrentUser();
        });
    }
    
    /**
     * Get the active skin for the current user
     */
    private function getActiveSkinForCurrentUser()
    {
        try {
            $session = $this->app->getContainer()->get('session');
            
            if ($session->isLoggedIn()) {
                $userId = $session->getUserId();
                return $this->skinManager->getActiveSkinForUser($userId);
            }
            
            // Fallback to global skin for non-logged-in users
            return $this->skinManager->getActiveSkin();
        } catch (\Throwable $e) {
            error_log("SkinServiceProvider::getActiveSkinForCurrentUser - Error: " . $e->getMessage());
            return $this->skinManager->getActiveSkin();
        }
    }
    
    /**
     * Get skin data for the current user
     */
    private function getSkinDataForCurrentUser(): array
    {
        try {
            $activeSkin = $this->getActiveSkinForCurrentUser();
            
            if ($activeSkin === null) {
                return [
                    'css' => '',
                    'js' => '',
                    'name' => 'default',
                    'version' => '0.0.29',
                ];
            }
            
            return [
                'css' => $activeSkin->getCssContent(),
                'js' => $activeSkin->getJsContent(),
                'name' => $activeSkin->getName(),
                'version' => $activeSkin->getVersion(),
                'config' => $activeSkin->getConfig() ?? [],
            ];
        } catch (\Throwable $e) {
            error_log("SkinServiceProvider::getSkinDataForCurrentUser - Error: " . $e->getMessage());
            return [
                'css' => '',
                'js' => '',
                'name' => 'default',
                'version' => '0.0.29',
            ];
        }
    }
    
    /**
     * Boot the skin service provider
     */
    public function boot(): void
    {
        // Get the view renderer
        $viewRenderer = $this->app->getContainer()->get('view');
        
        // Add empty skin variables - will be populated by SkinMiddleware
        $viewRenderer->addGlobals([
            'skin_css' => '',
            'skin_js' => '',
            'skin_name' => 'default',
            'skin_version' => '0.0.29',
            'skin_config' => [],
            'active_skin' => 'bismillah',
        ]);
        
        // Register view helpers for skin functionality
        $this->registerViewHelpers();
    }
    
    /**
     * Register view helpers for skin functionality
     */
    private function registerViewHelpers(): void
    {
        // TODO: Fix helper functions to use proper application instance
        // For now, we'll skip the helper functions to get SkinMiddleware working
        /*
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
        */
    }
    
    /**
     * Get the skin manager instance
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
        try {
            $session = $this->app->getContainer()->get('session');
            
            if ($session->isLoggedIn()) {
                $userId = $session->getUserId();
                return $this->skinManager->getActiveSkinNameForUser($userId);
            }
            
            return $this->skinManager->getActiveSkinName();
        } catch (\Throwable $e) {
            return $this->skinManager->getActiveSkinName();
        }
    }
    
    /**
     * Get available skins
     */
    public function getAvailableSkins(): array
    {
        return $this->skinManager->getAvailableSkinNames();
    }
} 