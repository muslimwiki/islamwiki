<?php
declare(strict_types=1);

/**
 * Static Data Service Provider
 * 
 * Registers the StaticDataManager with the container and provides
 * global static data management services.
 * 
 * @package IslamWiki\Providers
 * @version 0.0.44
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Providers;

use IslamWiki\Core\Application;
use IslamWiki\Core\Skin\StaticDataManager;

class StaticDataServiceProvider
{
    /**
     * @var Application The application instance
     */
    private Application $app;
    
    /**
     * Constructor
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * Register static data services
     */
    public function register(): void
    {
        // Register the static data manager as a singleton
        $this->app->getContainer()->singleton('static.data', function () {
            return new StaticDataManager($this->app);
        });
        
        // Register the static data manager as a factory for dynamic updates
        $this->app->getContainer()->bind('static.data.manager', function () {
            return new StaticDataManager($this->app);
        });
        
        // Register global static data as a singleton
        $this->app->getContainer()->singleton('static.data.global', function () {
            $manager = $this->app->getContainer()->get('static.data');
            return $manager->getStaticData();
        });
    }
    
    /**
     * Boot the static data service provider
     */
    public function boot(): void
    {
        try {
            // Get the static data manager
            $staticDataManager = $this->app->getContainer()->get('static.data');
            
            // Add global static data to the view renderer if available
            if ($this->app->getContainer()->has('view')) {
                $viewRenderer = $this->app->getContainer()->get('view');
                $staticData = $staticDataManager->getStaticData();
                
                // Add static data as global variables
                $viewRenderer->addGlobals([
                    'static_data' => $staticData,
                    'site_info' => $staticData['site'],
                    'navigation' => $staticData['navigation'],
                    'footer' => $staticData['footer'],
                    'features' => $staticData['features'],
                    'social' => $staticData['social'],
                    'components' => $staticData['components'],
                ]);
            }
            
            // Add helper functions for static data access
            $this->registerHelperFunctions();
            
        } catch (\Exception $e) {
            error_log('StaticDataServiceProvider::boot - Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Register helper functions for static data access
     */
    private function registerHelperFunctions(): void
    {
        // Helper function to get static data
        if (!function_exists('static_data')) {
            function static_data(string $key = null, $default = null) {
                global $static_data;
                
                if ($key === null) {
                    return $static_data ?? [];
                }
                
                $keys = explode('.', $key);
                $value = $static_data ?? [];
                
                foreach ($keys as $k) {
                    if (isset($value[$k])) {
                        $value = $value[$k];
                    } else {
                        return $default;
                    }
                }
                
                return $value;
            }
        }
        
        // Helper function to get navigation
        if (!function_exists('get_navigation')) {
            function get_navigation(string $type = 'main'): array {
                return static_data("navigation.{$type}", []);
            }
        }
        
        // Helper function to get site info
        if (!function_exists('get_site_info')) {
            function get_site_info(string $key = null) {
                if ($key === null) {
                    return static_data('site', []);
                }
                return static_data("site.{$key}");
            }
        }
        
        // Helper function to get footer data
        if (!function_exists('get_footer')) {
            function get_footer(): array {
                return static_data('footer', []);
            }
        }
        
        // Helper function to check if feature is enabled
        if (!function_exists('is_feature_enabled')) {
            function is_feature_enabled(string $feature): bool {
                return static_data("features.{$feature}.enabled", false);
            }
        }
        
        // Helper function to get component data
        if (!function_exists('get_component')) {
            function get_component(string $componentName): ?array {
                return static_data("components.{$componentName}");
            }
        }
        
        // Helper function to get social links
        if (!function_exists('get_social_links')) {
            function get_social_links(): array {
                return static_data('social', []);
            }
        }
    }
} 