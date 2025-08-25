<?php

declare(strict_types=1);

/**
 * Internationalization Configuration
 * 
 * This file defines the supported languages and their configuration
 * for the IslamWiki platform.
 */

return [
    // Default language (fallback)
    'default' => 'en',
    
    // Supported languages
    'languages' => [
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'flag' => '🇺🇸',
            'locale' => 'en_US',
            'fallback' => null, // No fallback needed for default
            'enabled' => true,
            'default_skin' => 'Bismillah',
        ],
        'ar' => [
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'code' => 'ar',
            'direction' => 'rtl',
            'flag' => '🇸🇦',
            'locale' => 'ar_SA',
            'fallback' => 'en', // Fallback to English
            'enabled' => true,
            'default_skin' => 'Bismillah',
        ],
    ],
    
    // Language detection settings
    'detection' => [
        'methods' => [
            'url',      // First: Check URL path (/en/, /ar/)
            'session',  // Second: Check user's saved preference
            'browser',  // Third: Check browser Accept-Language header
            'default',  // Last: Use default language
        ],
        'persist' => true, // Save language preference in session
    ],
    
    // URL structure settings
    'url_structure' => [
        'prefix_required' => true, // Always require language prefix
        'root_redirect' => true,   // Redirect / to /en/
        'fallback_redirect' => true, // Redirect invalid languages to default
    ],
    
    // Localization settings
    'localization' => [
        'date_format' => [
            'en' => 'Y-m-d',
            'ar' => 'Y-m-d',
        ],
        'time_format' => [
            'en' => 'H:i',
            'ar' => 'H:i',
        ],
        'number_format' => [
            'en' => [
                'decimal_separator' => '.',
                'thousands_separator' => ',',
            ],
            'ar' => [
                'decimal_separator' => '.',
                'thousands_separator' => ',',
            ],
        ],
    ],
]; 