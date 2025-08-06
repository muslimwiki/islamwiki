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

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Skin\StaticDataManager;

class StaticDataServiceProvider
{
    /**
     * Register static data services
     */
    public function register(AsasContainer $container): void
    {
        // Register the static data manager as a singleton
        $container->singleton('static.data', function (AsasContainer $container) {
            return new StaticDataManager($container->get('app'));
        });
        
        // Register the static data manager as a factory for dynamic updates
        $container->bind('static.data.manager', function (AsasContainer $container) {
            return new StaticDataManager($container->get('app'));
        });
        
        // Register global static data as a singleton
        $container->singleton('static.data.global', function (AsasContainer $container) {
            $manager = $container->get('static.data');
            return $manager->getStaticData();
        });
    }
    
    /**
     * Boot the static data service provider
     */
    public function boot(AsasContainer $container): void
    {
        try {
            // Get the static data manager
            $staticDataManager = $container->get('static.data');
            
            // Add global static data to the view renderer if available
            if ($container->has('view')) {
                $viewRenderer = $container->get('view');
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
            // error_log('StaticDataServiceProvider::boot - Error: ' . $e->getMessage());
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
                
                return $static_data[$key] ?? $default;
            }
        }
        
        // Helper function to get navigation data
        if (!function_exists('get_navigation')) {
            function get_navigation(string $type = 'main'): array {
                $navigation = static_data('navigation', []);
                return $navigation[$type] ?? [];
            }
        }
        
        // Helper function to get site info
        if (!function_exists('get_site_info')) {
            function get_site_info(string $key = null) {
                $siteInfo = static_data('site', []);
                
                if ($key === null) {
                    return $siteInfo;
                }
                
                return $siteInfo[$key] ?? null;
            }
        }
        
        // Helper function to get footer data
        if (!function_exists('get_footer')) {
            function get_footer(): array {
                return static_data('footer', []);
            }
        }
        
        // Helper function to check if a feature is enabled
        if (!function_exists('is_feature_enabled')) {
            function is_feature_enabled(string $feature): bool {
                $features = static_data('features', []);
                return $features[$feature] ?? false;
            }
        }
        
        // Helper function to get component data
        if (!function_exists('get_component')) {
            function get_component(string $componentName): ?array {
                $components = static_data('components', []);
                return $components[$componentName] ?? null;
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