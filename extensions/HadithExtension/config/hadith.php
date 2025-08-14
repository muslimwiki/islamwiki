<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hadith Extension Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the HadithExtension.
    | You can override these values by publishing the config file.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    
    // Default language for hadith display
    'default_language' => env('HADITH_DEFAULT_LANGUAGE', 'en'),
    
    // Available languages for hadith content
    'languages' => [
        'en' => 'English',
        'ar' => 'العربية',
        'ur' => 'اردو',
        'id' => 'Bahasa Indonesia',
        'tr' => 'Türkçe',
        'ms' => 'Bahasa Melayu',
    ],
    
    // Default number of items per page for pagination
    'per_page' => 20,
    
    // Maximum number of search results to return
    'max_search_results' => 100,
    
    // Enable/disable features
    'features' => [
        'search' => true,
        'advanced_search' => true,
        'narrator_chains' => true,
        'authenticity_grades' => true,
        'bookmarks' => true,
        'related_hadith' => true,
        'daily_hadith' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    */
    
    'search' => [
        // Minimum search query length
        'min_length' => 3,
        
        // Maximum search query length
        'max_length' => 255,
        
        // Enable fuzzy search
        'fuzzy' => true,
        
        // Enable keyword highlighting in search results
        'highlight' => true,
        
        // Search result snippet length (in characters)
        'snippet_length' => 300,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    
    'cache' => [
        // Enable caching of hadith data
        'enabled' => env('HADITH_CACHE_ENABLED', true),
        
        // Default cache lifetime in minutes
        'lifetime' => env('HADITH_CACHE_LIFETIME', 1440), // 24 hours
        
        // Cache prefix
        'prefix' => 'hadith_',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */
    
    'ui' => [
        // Show Arabic text by default
        'show_arabic' => true,
        
        // Show translation by default
        'show_translation' => true,
        
        // Show narrator chain by default
        'show_narrator_chain' => true,
        
        // Show authenticity grade by default
        'show_grade' => true,
        
        // Show reference information by default
        'show_reference' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    
    'api' => [
        // Enable/disable API endpoints
        'enabled' => true,
        
        // Enable/disable public API access
        'public_access' => true,
        
        // Rate limiting (requests per minute)
        'rate_limit' => 60,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Authentication & Permissions
    |--------------------------------------------------------------------------
    */
    
    'permissions' => [
        // Allow guests to view hadith
        'view_hadith' => true,
        
        // Allow guests to search hadith
        'search_hadith' => true,
        
        // Allow guests to view narrator profiles
        'view_narrators' => true,
        
        // Require authentication to bookmark hadith
        'bookmark_hadith' => true,
        
        // Require authentication to view bookmarked hadith
        'view_bookmarks' => true,
        
        // Admin permissions (require admin role)
        'admin' => [
            'import_hadith' => true,
            'edit_hadith' => true,
            'delete_hadith' => true,
            'manage_narrators' => true,
            'manage_collections' => true,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    */
    
    'integrations' => [
        // Enable/disable integration with other systems
        'quran' => true,
        'tafsir' => true,
        'fiqh' => true,
        'seerah' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | External Services
    |--------------------------------------------------------------------------
    */
    
    'services' => [
        // Sunnah.com API integration
        'sunnah' => [
            'enabled' => false,
            'api_key' => env('SUNNAH_COM_API_KEY'),
            'base_url' => 'https://api.sunnah.com/v1/',
            'version' => '1.0',
            'timeout' => 10,
        ],
        
        // Al-Maktaba Al-Shamela integration
        'shamela' => [
            'enabled' => false,
            'base_url' => 'https://shamela.ws/api/v1/',
            'api_key' => env('SHAMELA_API_KEY'),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    */
    
    'paths' => [
        // Storage path for hadith data files
        'storage' => storage_path('hadith'),
        
        // Path to hadith data files (relative to storage path)
        'data' => 'data',
        
        // Path to hadith import/export files
        'imports' => 'imports',
        'exports' => 'exports',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    */
    
    // Enable debug mode (shows additional information for debugging)
    'debug' => env('APP_DEBUG', false),
    
    // Enable query logging for hadith-related queries
    'query_logging' => env('HADITH_QUERY_LOGGING', false),
    
    // Enable performance profiling
    'profile_queries' => env('HADITH_PROFILE_QUERIES', false),
];
