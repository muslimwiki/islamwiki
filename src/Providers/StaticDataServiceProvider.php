<?php

/**
 * Static Data Service Provider
 *
 * Registers the StaticDataManager with the container and provides
 * global static data management services.
 *
 * @category  IslamWiki
 * @package   Providers
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.44
 */

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Skin\StaticDataManager;

/**
 * StaticDataServiceProvider - Service provider for static data management
 *
 * @category  IslamWiki
 * @package   Providers
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.44
 */
class StaticDataServiceProvider
{
    /**
     * Register static data services
     *
     * @param AsasContainer $container The dependency injection container
     *
     * @return void
     */
    public function register(AsasContainer $container): void
    {
        // Register the static data manager as a singleton
        $container->set('static.data', function (AsasContainer $container) {
            // For now, return a simple mock implementation
            return new class {
                public function getStaticData(string $key = null): array
                {
                    $data = [
                        'site' => [
                            'name' => 'IslamWiki',
                            'tagline' => 'Your comprehensive source for Islamic knowledge and community',
                            'version' => '0.0.44',
                            'url' => 'https://local.islam.wiki',
                            'email' => 'contact@islam.wiki',
                        ],
                        'navigation' => [
                            'main' => [
                                ['url' => '/', 'label' => 'Home', 'icon' => '🏠'],
                                ['url' => '/quran', 'label' => 'Quran', 'icon' => '📖'],
                                ['url' => '/hadith', 'label' => 'Hadith', 'icon' => '📜'],
                                ['url' => '/community', 'label' => 'Community', 'icon' => '👥'],
                            ],
                        ],
                        'footer' => [
                            'sections' => [
                                'main' => [
                                    'title' => 'IslamWiki',
                                    'description' => 'Your comprehensive source for Islamic knowledge and community.',
                                ],
                            ],
                        ],
                        'features' => [],
                        'social' => [],
                        'components' => [
                            'login' => [
                                'title' => 'Login',
                                'description' => 'Sign in to your account',
                            ],
                            'register' => [
                                'title' => 'Create Account',
                                'description' => 'Join the IslamWiki community',
                            ],
                        ],
                    ];
                    
                    if ($key === null) {
                        return $data;
                    }
                    
                    return $data[$key] ?? [];
                }
            };
        });

        // Register the static data manager as a factory for dynamic updates
        $container->set('static.data.manager', function (AsasContainer $container) {
            return $container->get('static.data');
        });

        // Register global static data as a singleton
        $container->set('static.data.global', function (AsasContainer $container) {
            $manager = $container->get('static.data');
            return $manager->getStaticData();
        });
    }

    /**
     * Boot the static data service provider
     *
     * @param AsasContainer $container The dependency injection container
     *
     * @return void
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
     *
     * @return void
     */
    private function registerHelperFunctions(): void
    {
        // Helper function to get static data
        if (!function_exists('static_data')) {
            /**
             * Get static data by key
             *
             * @param string|null $key The key to retrieve
             * @param mixed $default Default value if key not found
             *
             * @return mixed
             */
            function static_data(string $key = null, $default = null)
            {
                global $static_data;

                if ($key === null) {
                    return $static_data ?? [];
                }

                return $static_data[$key] ?? $default;
            }
        }

        // Helper function to get navigation data
        if (!function_exists('get_navigation')) {
            /**
             * Get navigation data by type
             *
             * @param string $type The navigation type
             *
             * @return array
             */
            function get_navigation(string $type = 'main'): array
            {
                return static_data('navigation')[$type] ?? [];
            }
        }

        // Helper function to get site information
        if (!function_exists('get_site_info')) {
            /**
             * Get site information
             *
             * @param string|null $key The specific key to retrieve
             *
             * @return mixed
             */
            function get_site_info(string $key = null)
            {
                $siteInfo = static_data('site_info');
                if ($key === null) {
                    return $siteInfo;
                }
                return $siteInfo[$key] ?? null;
            }
        }

        // Helper function to get footer data
        if (!function_exists('get_footer')) {
            /**
             * Get footer data
             *
             * @return array
             */
            function get_footer(): array
            {
                return static_data('footer') ?? [];
            }
        }

        // Helper function to check if a feature is enabled
        if (!function_exists('is_feature_enabled')) {
            /**
             * Check if a feature is enabled
             *
             * @param string $feature The feature name
             *
             * @return bool
             */
            function is_feature_enabled(string $feature): bool
            {
                $features = static_data('features') ?? [];
                return isset($features[$feature]) && $features[$feature];
            }
        }

        // Helper function to get component data
        if (!function_exists('get_component')) {
            /**
             * Get component data by name
             *
             * @param string $componentName The component name
             *
             * @return array|null
             */
            function get_component(string $componentName): ?array
            {
                $components = static_data('components') ?? [];
                return $components[$componentName] ?? null;
            }
        }

        // Helper function to get social links
        if (!function_exists('get_social_links')) {
            /**
             * Get social media links
             *
             * @return array
             */
            function get_social_links(): array
            {
                return static_data('social') ?? [];
            }
        }
    }
}
