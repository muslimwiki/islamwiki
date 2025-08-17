<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\DashboardExtension\Config;

/**
 * Dashboard Configuration
 * 
 * Role-based dashboard configurations for different user types
 */
class DashboardConfig
{
    /**
     * Get dashboard configuration for a specific user role
     *
     * @param string $role User role
     * @return array Dashboard configuration
     */
    public static function getRoleConfig(string $role): array
    {
        $configs = [
            'admin' => self::getAdminConfig(),
            'scholar' => self::getScholarConfig(),
            'contributor' => self::getContributorConfig(),
            'user' => self::getUserConfig(),
        ];

        return $configs[$role] ?? $configs['user'];
    }

    /**
     * Admin dashboard configuration
     *
     * @return array Admin dashboard config
     */
    private static function getAdminConfig(): array
    {
        return [
            'template' => 'admin_dashboard.twig',
            'layout' => 'modern',
            'widgets' => [
                'system-overview' => [
                    'name' => 'System Overview',
                    'size' => 'large',
                    'position' => 1,
                    'required' => true
                ],
                'user-management' => [
                    'name' => 'User Management',
                    'size' => 'medium',
                    'position' => 2,
                    'required' => true
                ],
                'content-moderation' => [
                    'name' => 'Content Moderation',
                    'size' => 'medium',
                    'position' => 3,
                    'required' => true
                ],
                'system-status' => [
                    'name' => 'System Status',
                    'size' => 'small',
                    'position' => 4,
                    'required' => true
                ],
                'analytics' => [
                    'name' => 'Analytics Dashboard',
                    'size' => 'large',
                    'position' => 5,
                    'required' => false
                ],
                'security' => [
                    'name' => 'Security Overview',
                    'size' => 'medium',
                    'position' => 6,
                    'required' => false
                ]
            ],
            'permissions' => [
                'view_all_users' => true,
                'moderate_content' => true,
                'system_admin' => true,
                'view_analytics' => true,
                'manage_extensions' => true
            ],
            'refresh_interval' => 60, // 1 minute for admin
            'theme' => 'professional'
        ];
    }

    /**
     * Scholar dashboard configuration
     *
     * @return array Scholar dashboard config
     */
    private static function getScholarConfig(): array
    {
        return [
            'template' => 'scholar_dashboard.twig',
            'layout' => 'islamic',
            'widgets' => [
                'scholar-overview' => [
                    'name' => 'Scholar Overview',
                    'size' => 'medium',
                    'position' => 1,
                    'required' => true
                ],
                'content-review' => [
                    'name' => 'Content Review Queue',
                    'size' => 'large',
                    'position' => 2,
                    'required' => true
                ],
                'islamic-calendar' => [
                    'name' => 'Islamic Calendar',
                    'size' => 'small',
                    'position' => 3,
                    'required' => true
                ],
                'prayer-times' => [
                    'name' => 'Prayer Times',
                    'size' => 'small',
                    'position' => 4,
                    'required' => false
                ],
                'quran-verse' => [
                    'name' => 'Quran Verse of the Day',
                    'size' => 'medium',
                    'position' => 5,
                    'required' => false
                ],
                'hadith-quote' => [
                    'name' => 'Hadith of the Day',
                    'size' => 'medium',
                    'position' => 6,
                    'required' => false
                ],
                'academic-resources' => [
                    'name' => 'Academic Resources',
                    'size' => 'medium',
                    'position' => 7,
                    'required' => false
                ]
            ],
            'permissions' => [
                'review_content' => true,
                'approve_articles' => true,
                'edit_scholarly_content' => true,
                'view_moderation_queue' => true,
                'access_academic_resources' => true
            ],
            'refresh_interval' => 300, // 5 minutes
            'theme' => 'islamic'
        ];
    }

    /**
     * Contributor dashboard configuration
     *
     * @return array Contributor dashboard config
     */
    private static function getContributorConfig(): array
    {
        return [
            'template' => 'contributor_dashboard.twig',
            'layout' => 'islamic',
            'widgets' => [
                'contributor-overview' => [
                    'name' => 'Contributor Overview',
                    'size' => 'medium',
                    'position' => 1,
                    'required' => true
                ],
                'my-articles' => [
                    'name' => 'My Articles',
                    'size' => 'large',
                    'position' => 2,
                    'required' => true
                ],
                'draft-manager' => [
                    'name' => 'Draft Manager',
                    'size' => 'medium',
                    'position' => 3,
                    'required' => true
                ],
                'content-stats' => [
                    'name' => 'Content Statistics',
                    'size' => 'medium',
                    'position' => 4,
                    'required' => false
                ],
                'recent-activity' => [
                    'name' => 'Recent Activity',
                    'size' => 'medium',
                    'position' => 5,
                    'required' => false
                ],
                'islamic-calendar' => [
                    'name' => 'Islamic Calendar',
                    'size' => 'small',
                    'position' => 6,
                    'required' => false
                ],
                'quick-actions' => [
                    'name' => 'Quick Actions',
                    'size' => 'small',
                    'position' => 7,
                    'required' => false
                ]
            ],
            'permissions' => [
                'create_articles' => true,
                'edit_own_articles' => true,
                'submit_for_review' => true,
                'view_own_stats' => true,
                'access_contributor_tools' => true
            ],
            'refresh_interval' => 600, // 10 minutes
            'theme' => 'islamic'
        ];
    }

    /**
     * Basic user dashboard configuration
     *
     * @return array User dashboard config
     */
    private static function getUserConfig(): array
    {
        return [
            'template' => 'user_dashboard.twig',
            'layout' => 'compact',
            'widgets' => [
                'user-overview' => [
                    'name' => 'User Overview',
                    'size' => 'small',
                    'position' => 1,
                    'required' => true
                ],
                'recent-content' => [
                    'name' => 'Recent Content',
                    'size' => 'medium',
                    'position' => 2,
                    'required' => false
                ],
                'islamic-calendar' => [
                    'name' => 'Islamic Calendar',
                    'size' => 'small',
                    'position' => 3,
                    'required' => false
                ],
                'prayer-times' => [
                    'name' => 'Prayer Times',
                    'size' => 'small',
                    'position' => 4,
                    'required' => false
                ],
                'quick-actions' => [
                    'name' => 'Quick Actions',
                    'size' => 'small',
                    'position' => 5,
                    'required' => false
                ]
            ],
            'permissions' => [
                'view_own_profile' => true,
                'browse_content' => true,
                'view_islamic_info' => true,
                'access_basic_features' => true
            ],
            'refresh_interval' => 900, // 15 minutes
            'theme' => 'compact'
        ];
    }

    /**
     * Get available layouts
     *
     * @return array Available layouts
     */
    public static function getAvailableLayouts(): array
    {
        return [
            'islamic' => [
                'name' => 'Islamic Layout',
                'description' => 'Traditional Islamic design with geometric patterns',
                'columns' => 3,
                'max_widgets' => 12
            ],
            'modern' => [
                'name' => 'Modern Layout',
                'description' => 'Clean, modern design with cards and shadows',
                'columns' => 4,
                'max_widgets' => 16
            ],
            'compact' => [
                'name' => 'Compact Layout',
                'description' => 'Space-efficient layout for small screens',
                'columns' => 2,
                'max_widgets' => 8
            ]
        ];
    }

    /**
     * Get available themes
     *
     * @return array Available themes
     */
    public static function getAvailableThemes(): array
    {
        return [
            'islamic' => [
                'name' => 'Islamic Theme',
                'description' => 'Traditional Islamic colors and patterns',
                'primary_color' => '#2d5016',
                'secondary_color' => '#d4af37'
            ],
            'professional' => [
                'name' => 'Professional Theme',
                'description' => 'Clean, business-like appearance',
                'primary_color' => '#2c3e50',
                'secondary_color' => '#3498db'
            ],
            'compact' => [
                'name' => 'Compact Theme',
                'description' => 'Minimalist design for efficiency',
                'primary_color' => '#34495e',
                'secondary_color' => '#95a5a6'
            ]
        ];
    }

    /**
     * Get widget definitions
     *
     * @return array Widget definitions
     */
    public static function getWidgetDefinitions(): array
    {
        return [
            'system-overview' => [
                'name' => 'System Overview',
                'description' => 'Comprehensive system status and metrics',
                'category' => 'system',
                'admin_only' => true
            ],
            'user-management' => [
                'name' => 'User Management',
                'description' => 'Manage users and permissions',
                'category' => 'administration',
                'admin_only' => true
            ],
            'content-moderation' => [
                'name' => 'Content Moderation',
                'description' => 'Review and moderate content',
                'category' => 'moderation',
                'admin_only' => true
            ],
            'scholar-overview' => [
                'name' => 'Scholar Overview',
                'description' => 'Scholar-specific information and tools',
                'category' => 'scholar',
                'scholar_only' => true
            ],
            'content-review' => [
                'name' => 'Content Review Queue',
                'description' => 'Queue of content awaiting review',
                'category' => 'moderation',
                'scholar_only' => true
            ],
            'contributor-overview' => [
                'name' => 'Contributor Overview',
                'description' => 'Contributor statistics and tools',
                'category' => 'contributor',
                'contributor_only' => true
            ],
            'my-articles' => [
                'name' => 'My Articles',
                'description' => 'Manage your contributed articles',
                'category' => 'content',
                'contributor_only' => true
            ],
            'draft-manager' => [
                'name' => 'Draft Manager',
                'description' => 'Manage your article drafts',
                'category' => 'content',
                'contributor_only' => true
            ],
            'islamic-calendar' => [
                'name' => 'Islamic Calendar',
                'description' => 'Hijri calendar and Islamic dates',
                'category' => 'islamic',
                'available_to_all' => true
            ],
            'prayer-times' => [
                'name' => 'Prayer Times',
                'description' => 'Current prayer schedule',
                'category' => 'islamic',
                'available_to_all' => true
            ],
            'quran-verse' => [
                'name' => 'Quran Verse of the Day',
                'description' => 'Daily Quran verse',
                'category' => 'islamic',
                'available_to_all' => true
            ],
            'hadith-quote' => [
                'name' => 'Hadith of the Day',
                'description' => 'Daily hadith quote',
                'category' => 'islamic',
                'available_to_all' => true
            ],
            'quick-actions' => [
                'name' => 'Quick Actions',
                'description' => 'Common action shortcuts',
                'category' => 'navigation',
                'available_to_all' => true
            ]
        ];
    }
} 