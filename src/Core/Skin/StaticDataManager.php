<?php
declare(strict_types=1);

/**
 * Static Data Manager
 * 
 * Manages global static data and skin-specific components.
 * Provides a centralized way to handle navigation, content areas,
 * footers, and other static elements across different skins.
 * 
 * @package IslamWiki\Core\Skin
 * @version 0.0.44
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Core\Skin;

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

class StaticDataManager
{
    /**
     * @var NizamApplication The application instance
     */
    private NizamApplication $app;
    
    /**
     * @var SkinManager The skin manager instance
     */
    private SkinManager $skinManager;
    
    /**
     * @var array Global static data
     */
    private array $globalData = [];
    
    /**
     * @var array Skin-specific component data
     */
    private array $skinComponents = [];
    
    /**
     * Constructor
     */
    public function __construct(NizamApplication $app)
    {
        $this->app = $app;
        $this->skinManager = new SkinManager($app);
        $this->loadGlobalData();
        $this->loadSkinComponents();
    }
    
    /**
     * Load global static data that applies to all skins
     */
    private function loadGlobalData(): void
    {
        $this->globalData = [
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
                    ['url' => '/pages', 'label' => 'Browse', 'icon' => '📚'],
                    ['url' => '/quran', 'label' => 'Quran', 'icon' => '📖'],
                    ['url' => '/hadith', 'label' => 'Hadith', 'icon' => '📜'],
                    ['url' => '/islamic-sciences', 'label' => 'Islamic Sciences', 'icon' => '🔬'],
                    ['url' => '/prayer-times', 'label' => 'Prayer Times', 'icon' => '🕌'],
                    ['url' => '/calendar', 'label' => 'Islamic Calendar', 'icon' => '📅'],
                    ['url' => '/community', 'label' => 'Community', 'icon' => '👥'],
                    ['url' => '/about', 'label' => 'About', 'icon' => 'ℹ️'],
                ],
                'secondary' => [
                    ['url' => '/search', 'label' => 'Search', 'icon' => '🔍'],
                    ['url' => '/iqra-search', 'label' => 'Iqra Search', 'icon' => '📖'],
                    ['url' => '/help', 'label' => 'Help', 'icon' => '❓'],
                ],
                'user' => [
                    ['url' => '/dashboard', 'label' => 'Dashboard', 'icon' => '📊'],
                    ['url' => '/profile', 'label' => 'Profile', 'icon' => '👤'],
                    ['url' => '/settings', 'label' => 'Settings', 'icon' => '⚙️'],
                    ['url' => '/logout', 'label' => 'Logout', 'icon' => '🚪'],
                ],
            ],
            'footer' => [
                'sections' => [
                    'main' => [
                        'title' => 'IslamWiki',
                        'description' => 'Your comprehensive source for Islamic knowledge and community.',
                        'links' => [
                            ['url' => '/about', 'label' => 'About Us'],
                            ['url' => '/contact', 'label' => 'Contact'],
                            ['url' => '/privacy', 'label' => 'Privacy Policy'],
                            ['url' => '/terms', 'label' => 'Terms of Service'],
                        ],
                    ],
                    'quick_links' => [
                        'title' => 'Quick Links',
                        'links' => [
                            ['url' => '/pages', 'label' => 'Browse Pages'],
                            ['url' => '/quran', 'label' => 'Quran'],
                            ['url' => '/hadith', 'label' => 'Hadith'],
                            ['url' => '/prayer-times', 'label' => 'Prayer Times'],
                        ],
                    ],
                    'community' => [
                        'title' => 'Community',
                        'links' => [
                            ['url' => '/community', 'label' => 'Community Hub'],
                            ['url' => '/discussions', 'label' => 'Discussions'],
                            ['url' => '/events', 'label' => 'Events'],
                            ['url' => '/volunteer', 'label' => 'Volunteer'],
                        ],
                    ],
                    'resources' => [
                        'title' => 'Resources',
                        'links' => [
                            ['url' => '/help', 'label' => 'Help Center'],
                            ['url' => '/api', 'label' => 'API Documentation'],
                            ['url' => '/developers', 'label' => 'Developer Guide'],
                            ['url' => '/extensions', 'label' => 'Extensions'],
                        ],
                    ],
                ],
                'bottom' => [
                    'copyright' => '© 2025 IslamWiki. All rights reserved.',
                    'license' => 'Licensed under AGPL-3.0-only',
                    'links' => [
                        ['url' => '/sitemap', 'label' => 'Sitemap'],
                        ['url' => '/rss', 'label' => 'RSS'],
                        ['url' => '/status', 'label' => 'Status'],
                    ],
                ],
            ],
            'features' => [
                'search' => [
                    'enabled' => true,
                    'placeholder' => 'Search Islamic knowledge...',
                    'action' => '/iqra-search',
                ],
                'user_menu' => [
                    'enabled' => true,
                    'dropdown' => true,
                ],
                'breadcrumbs' => [
                    'enabled' => true,
                ],
                'pagination' => [
                    'enabled' => true,
                    'per_page' => 20,
                ],
            ],
            'social' => [
                'twitter' => 'https://twitter.com/islamwiki',
                'facebook' => 'https://facebook.com/islamwiki',
                'instagram' => 'https://instagram.com/islamwiki',
                'youtube' => 'https://youtube.com/islamwiki',
                'github' => 'https://github.com/islamwiki',
            ],
        ];
    }
    
    /**
     * Load skin-specific component data
     */
    private function loadSkinComponents(): void
    {
        $activeSkin = $this->skinManager->getActiveSkin();
        $skinName = $activeSkin ? $activeSkin->getName() : 'Bismillah';
        
        // Check if skin has component templates
        $skinsPath = dirname(__DIR__, 3) . '/skins';
        $skinComponentsPath = "{$skinsPath}/{$skinName}/components";
        $hasComponents = is_dir($skinComponentsPath);
        
        $this->skinComponents = [];
        
        // Only add components if the skin has component templates
        if ($hasComponents) {
            $this->skinComponents = [
                'header' => [
                    'template' => "{$skinName}/components/header.twig",
                    'data' => [
                        'logo' => [
                            'icon' => $this->getSkinLogo($skinName),
                            'text' => 'IslamWiki',
                            'url' => '/',
                        ],
                        'search' => [
                            'enabled' => true,
                            'placeholder' => 'Search Islamic knowledge...',
                            'action' => '/iqra-search',
                        ],
                        'navigation' => $this->globalData['navigation']['main'],
                        'secondary_navigation' => $this->globalData['navigation']['secondary'],
                        'user_menu_items' => $this->globalData['navigation']['user'],
                        'user_menu' => [
                            'enabled' => true,
                            'dropdown' => true,
                        ],
                    ],
                ],
                'footer' => [
                    'template' => "{$skinName}/components/footer.twig",
                    'data' => $this->globalData['footer'],
                ],
                'sidebar' => [
                    'template' => "{$skinName}/components/sidebar.twig",
                    'data' => [
                        'navigation' => $this->globalData['navigation']['secondary'],
                        'quick_links' => [
                            ['url' => '/dashboard', 'label' => 'Dashboard', 'icon' => '📊'],
                            ['url' => '/recent', 'label' => 'Recent Pages', 'icon' => '🕒'],
                            ['url' => '/favorites', 'label' => 'Favorites', 'icon' => '⭐'],
                            ['url' => '/watchlist', 'label' => 'Watchlist', 'icon' => '👁️'],
                        ],
                    ],
                ],
                'breadcrumbs' => [
                    'template' => "{$skinName}/components/breadcrumbs.twig",
                    'data' => [
                        'enabled' => true,
                        'separator' => '>',
                    ],
                ],
                'pagination' => [
                    'template' => "{$skinName}/components/pagination.twig",
                    'data' => [
                        'enabled' => true,
                        'per_page' => 20,
                    ],
                ],
            ];
        } else {
            // For skins without components, provide empty components so the layout doesn't break
            $this->skinComponents = [
                'header' => null,
                'footer' => null,
                'sidebar' => null,
                'breadcrumbs' => null,
                'pagination' => null,
            ];
        }
    }
    
    /**
     * Get skin-specific logo
     */
    private function getSkinLogo(string $skinName): string
    {
        $logos = [
            'Muslim' => '🕌',
            'Muslim' => '📖',
            'default' => '🏠',
        ];
        
        return $logos[$skinName] ?? $logos['default'];
    }
    
    /**
     * Get all static data for a specific context
     */
    public function getStaticData(string $context = 'default'): array
    {
        $data = $this->globalData;
        
        // Add skin-specific components
        $data['components'] = $this->skinComponents;
        
        // Add context-specific data
        $data['context'] = $context;
        $data['active_skin'] = $this->skinManager->getActiveSkinName();
        
        return $data;
    }
    
    /**
     * Get a specific component data
     */
    public function getComponent(string $componentName): ?array
    {
        return $this->skinComponents[$componentName] ?? null;
    }
    
    /**
     * Get navigation data
     */
    public function getNavigation(string $type = 'main'): array
    {
        return $this->globalData['navigation'][$type] ?? [];
    }
    
    /**
     * Get footer data
     */
    public function getFooter(): array
    {
        return $this->globalData['footer'];
    }
    
    /**
     * Get site information
     */
    public function getSiteInfo(): array
    {
        return $this->globalData['site'];
    }
    
    /**
     * Get feature configuration
     */
    public function getFeatureConfig(string $feature): array
    {
        return $this->globalData['features'][$feature] ?? [];
    }
    
    /**
     * Check if a feature is enabled
     */
    public function isFeatureEnabled(string $feature): bool
    {
        $config = $this->getFeatureConfig($feature);
        return $config['enabled'] ?? false;
    }
    
    /**
     * Get social media links
     */
    public function getSocialLinks(): array
    {
        return $this->globalData['social'];
    }
    
    /**
     * Update global data (for dynamic content)
     */
    public function updateGlobalData(string $key, $value): void
    {
        $this->globalData[$key] = $value;
    }
    
    /**
     * Update component data
     */
    public function updateComponent(string $componentName, array $data): void
    {
        if (isset($this->skinComponents[$componentName])) {
            $this->skinComponents[$componentName]['data'] = array_merge(
                $this->skinComponents[$componentName]['data'],
                $data
            );
        }
    }
    
    /**
     * Reload skin components (when skin changes)
     */
    public function reloadSkinComponents(): void
    {
        $this->loadSkinComponents();
    }
} 