<?php

declare(strict_types=1);

/**
 * Extensions Configuration
 * 
 * Configuration file for activating and managing IslamWiki extensions.
 * 
 * @package IslamWiki\Config
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Active Extensions
    |--------------------------------------------------------------------------
    |
    | This array contains all active extensions for IslamWiki.
    | Extensions are loaded in the order they appear in this array.
    |
    */
    'active_extensions' => [
        // Core System Extensions
        'SafaSkinExtension' => [
            'enabled' => true,
            'priority' => 1,
            'config' => [
                'default_skin' => 'Bismillah',
                'allow_user_skin_selection' => true,
                'admin_only_skin_management' => true,
                'live_preview_enabled' => true,
                'customization_enabled' => true,
            ]
        ],
        
        'DashboardExtension' => [
            'enabled' => true,
            'priority' => 2,
            'config' => [
                'default_dashboard' => 'user',
                'widgets_enabled' => true,
                'customization_enabled' => true,
            ]
        ],
        
        'QuranExtension' => [
            'enabled' => true,
            'priority' => 3,
            'config' => [
                'default_translation' => 'en',
                'arabic_text_enabled' => true,
                'audio_enabled' => true,
                'search_enabled' => true,
            ]
        ],
        
        'HadithExtension' => [
            'enabled' => true,
            'priority' => 4,
            'config' => [
                'default_collection' => 'bukhari',
                'authenticity_grading' => true,
                'search_enabled' => true,
                'categories_enabled' => true,
            ]
        ],
        
        'SalahTime' => [
            'enabled' => true,
            'priority' => 5,
            'config' => [
                'calculation_method' => 'muslim_world_league',
                'location_detection' => true,
                'notifications_enabled' => true,
                'qibla_direction' => true,
            ]
        ],
        
        'HijriCalendar' => [
            'enabled' => true,
            'priority' => 6,
            'config' => [
                'default_view' => 'gregorian',
                'hijri_dates_enabled' => true,
                'events_enabled' => true,
                'conversion_tools' => true,
            ]
        ],
        
        'EnhancedMarkdown' => [
            'enabled' => true,
            'priority' => 7,
            'config' => [
                'arabic_support' => true,
                'islamic_symbols' => true,
                'math_equations' => true,
                'syntax_highlighting' => true,
            ]
        ],
        
        'MarkdownDocsViewer' => [
            'enabled' => true,
            'priority' => 8,
            'config' => [
                'file_types' => ['md', 'markdown', 'txt'],
                'search_enabled' => true,
                'toc_generation' => true,
                'print_friendly' => true,
            ]
        ],
        
        'GitIntegration' => [
            'enabled' => true,
            'priority' => 9,
            'config' => [
                'auto_commit' => false,
                'branch_protection' => true,
                'merge_requests' => true,
                'webhooks_enabled' => false,
            ]
        ],
        
        'TranslatorExtension' => [
            'enabled' => true,
            'priority' => 10,
            'config' => [
                'default_source_lang' => 'en',
                'default_target_lang' => 'ar',
                'auto_translation' => false,
                'translation_memory' => true,
            ]
        ],
        
        'WikiMarkupExtension' => [
            'enabled' => true,
            'priority' => 11,
            'config' => [
                'syntax_highlighting' => true,
                'auto_completion' => true,
                'preview_enabled' => true,
                'templates_enabled' => true,
            ]
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Extension Settings
    |--------------------------------------------------------------------------
    |
    | Global settings that apply to all extensions.
    |
    */
    'extension_settings' => [
        'auto_update' => false,
        'development_mode' => true,
        'cache_enabled' => true,
        'logging_enabled' => true,
        'performance_monitoring' => true,
        'security_scanning' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Extension Dependencies
    |--------------------------------------------------------------------------
    |
    | Define dependencies between extensions.
    |
    */
    'dependencies' => [
        'SafaSkinExtension' => [
            'required' => ['DashboardExtension'],
            'conflicts' => [],
        ],
        'DashboardExtension' => [
            'required' => [],
            'conflicts' => [],
        ],
        'QuranExtension' => [
            'required' => ['EnhancedMarkdown'],
            'conflicts' => [],
        ],
        'HadithExtension' => [
            'required' => ['EnhancedMarkdown'],
            'conflicts' => [],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Extension Permissions
    |--------------------------------------------------------------------------
    |
    | Define which user roles can access extension features.
    |
    */
    'permissions' => [
        'SafaSkinExtension' => [
            'admin' => ['*'], // All permissions
            'moderator' => ['view', 'preview'],
            'user' => ['view', 'select_personal_skin'],
            'guest' => ['view'],
        ],
        'DashboardExtension' => [
            'admin' => ['*'],
            'moderator' => ['*'],
            'user' => ['view', 'customize'],
            'guest' => ['view'],
        ],
        'QuranExtension' => [
            'admin' => ['*'],
            'moderator' => ['*'],
            'user' => ['*'],
            'guest' => ['view', 'search'],
        ],
        'HadithExtension' => [
            'admin' => ['*'],
            'moderator' => ['*'],
            'user' => ['*'],
            'guest' => ['view', 'search'],
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Extension Hooks
    |--------------------------------------------------------------------------
    |
    | Define hooks that extensions can use to integrate with the system.
    |
    */
    'hooks' => [
        'init' => [
            'SafaSkinExtension',
            'DashboardExtension',
            'QuranExtension',
            'HadithExtension',
        ],
        'template_render' => [
            'SafaSkinExtension',
        ],
        'asset_load' => [
            'SafaSkinExtension',
            'DashboardExtension',
        ],
        'user_login' => [
            'DashboardExtension',
            'SalahTime',
        ],
        'content_save' => [
            'EnhancedMarkdown',
            'WikiMarkupExtension',
        ],
        'search_query' => [
            'QuranExtension',
            'HadithExtension',
            'MarkdownDocsViewer',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Extension Configuration Files
    |--------------------------------------------------------------------------
    |
    | Paths to extension-specific configuration files.
    |
    */
    'config_files' => [
        'SafaSkinExtension' => 'extensions/SafaSkinExtension/config/skin-config.php',
        'DashboardExtension' => 'extensions/DashboardExtension/config/dashboard-config.php',
        'QuranExtension' => 'extensions/QuranExtension/config/quran-config.php',
        'HadithExtension' => 'extensions/HadithExtension/config/hadith-config.php',
        'SalahTime' => 'extensions/SalahTime/config/salah-config.php',
        'HijriCalendar' => 'extensions/HijriCalendar/config/hijri-config.php',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Extension Database Tables
    |--------------------------------------------------------------------------
    |
    | Database tables that extensions create and manage.
    |
    */
    'database_tables' => [
        'SafaSkinExtension' => [
            'mizan_skins',
            'mizan_skin_preferences',
            'mizan_skin_customizations',
        ],
        'DashboardExtension' => [
            'mizan_dashboard_widgets',
            'mizan_user_dashboards',
            'mizan_widget_preferences',
        ],
        'QuranExtension' => [
            'mizan_quran_surahs',
            'mizan_quran_ayahs',
            'mizan_quran_translations',
            'mizan_quran_recitations',
        ],
        'HadithExtension' => [
            'mizan_hadith_collections',
            'mizan_hadiths',
            'mizan_hadith_narrators',
            'mizan_hadith_grades',
        ],
        'SalahTime' => [
            'mizan_salah_times',
            'mizan_locations',
            'mizan_prayer_settings',
        ],
        'HijriCalendar' => [
            'mizan_hijri_dates',
            'mizan_islamic_events',
            'mizan_calendar_settings',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Extension Assets
    |--------------------------------------------------------------------------
    |
    | CSS, JavaScript, and other assets that extensions provide.
    |
    */
    'assets' => [
        'SafaSkinExtension' => [
            'css' => [
                'extensions/SafaSkinExtension/assets/css/skin-manager.css',
            ],
            'js' => [
                'extensions/SafaSkinExtension/assets/js/skin-manager.js',
            ],
            'images' => [
                'extensions/SafaSkinExtension/assets/images/',
            ],
        ],
        'DashboardExtension' => [
            'css' => [
                'extensions/DashboardExtension/assets/css/dashboard.css',
            ],
            'js' => [
                'extensions/DashboardExtension/assets/js/dashboard.js',
            ],
        ],
        'QuranExtension' => [
            'css' => [
                'extensions/QuranExtension/assets/css/quran.css',
            ],
            'js' => [
                'extensions/QuranExtension/assets/js/quran.js',
            ],
        ],
        'HadithExtension' => [
            'css' => [
                'extensions/HadithExtension/assets/css/hadith.css',
            ],
            'js' => [
                'extensions/HadithExtension/assets/js/hadith.js',
            ],
        ],
    ],
]; 