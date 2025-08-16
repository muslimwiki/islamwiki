<?php

declare(strict_types=1);

/**
 * Translation Configuration
 * 
 * Configuration for the hybrid translation system with Google Translate API
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language for the application. This will be used when
    | no language is specified in the URL or session.
    |
    */
    'default_language' => env('DEFAULT_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | List of supported languages with their configuration.
    |
    */
    'supported_languages' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇺🇸',
            'direction' => 'ltr',
            'is_rtl' => false,
            'subdomain' => null, // No subdomain for default language
            'locale' => 'en_US',
            'fallback' => true
        ],
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'flag' => '🇸🇦',
            'direction' => 'rtl',
            'is_rtl' => true,
            'subdomain' => 'ar',
            'locale' => 'ar_SA',
            'fallback' => false
        ],
        'ur' => [
            'name' => 'Urdu',
            'native' => 'اردو',
            'flag' => '🇵🇰',
            'direction' => 'rtl',
            'is_rtl' => true,
            'subdomain' => 'ur',
            'locale' => 'ur_PK',
            'fallback' => false
        ],
        'tr' => [
            'name' => 'Turkish',
            'native' => 'Türkçe',
            'flag' => '🇹🇷',
            'direction' => 'ltr',
            'is_rtl' => false,
            'subdomain' => 'tr',
            'locale' => 'tr_TR',
            'fallback' => false
        ],
        'id' => [
            'name' => 'Indonesian',
            'native' => 'Bahasa Indonesia',
            'flag' => '🇮🇩',
            'direction' => 'ltr',
            'is_rtl' => false,
            'subdomain' => 'id',
            'locale' => 'id_ID',
            'fallback' => false
        ],
        'ms' => [
            'name' => 'Malay',
            'native' => 'Bahasa Melayu',
            'flag' => '🇲🇾',
            'direction' => 'ltr',
            'is_rtl' => false,
            'subdomain' => 'ms',
            'locale' => 'ms_MY',
            'fallback' => false
        ],
        'fa' => [
            'name' => 'Persian',
            'native' => 'فارسی',
            'flag' => '🇮🇷',
            'direction' => 'rtl',
            'is_rtl' => true,
            'subdomain' => 'fa',
            'locale' => 'fa_IR',
            'fallback' => false
        ],
        'he' => [
            'name' => 'Hebrew',
            'native' => 'עברית',
            'flag' => '🇮🇱',
            'direction' => 'rtl',
            'is_rtl' => true,
            'subdomain' => 'he',
            'locale' => 'he_IL',
            'fallback' => false
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Translate API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Translate API integration.
    |
    */
    'google_translate' => [
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY', ''),
        'api_endpoint' => 'https://translation.googleapis.com/language/translate/v2',
        'batch_size' => 50,
        'timeout' => 30,
        'retry_attempts' => 3,
        'enable_auto_detection' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Memory Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for local translation memory and caching.
    |
    */
    'translation_memory' => [
        'enabled' => true,
        'max_size' => env('TRANSLATION_MEMORY_SIZE', 1000),
        'cache_ttl' => env('TRANSLATION_CACHE_TTL', 86400), // 24 hours
        'persistence' => true,
        'quality_threshold' => env('TRANSLATION_QUALITY_THRESHOLD', 0.7)
    ],

    /*
    |--------------------------------------------------------------------------
    | Subdomain Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for language-specific subdomains.
    |
    */
    'subdomains' => [
        'enabled' => env('ENABLE_LANGUAGE_SUBDOMAINS', true),
        'base_domain' => env('BASE_DOMAIN', 'local.islam.wiki'),
        'default_subdomain' => null, // No subdomain for default language
        'redirect_base_to_default' => true, // Redirect base domain to default language
        'preserve_paths' => true, // Preserve current page path when switching languages
        'ssl_enabled' => env('SESSION_SECURE', false)
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Detection Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic language detection.
    |
    */
    'detection' => [
        'enabled' => true,
        'methods' => ['subdomain', 'session', 'browser', 'ip'],
        'confidence_threshold' => 0.6,
        'fallback_language' => 'en',
        'browser_preference_weight' => 0.3,
        'session_preference_weight' => 0.4,
        'subdomain_weight' => 0.8
    ],

    /*
    |--------------------------------------------------------------------------
    | Quality Assurance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for translation quality monitoring and feedback.
    |
    */
    'quality_assurance' => [
        'enabled' => true,
        'user_feedback_enabled' => true,
        'auto_quality_scoring' => true,
        'quality_metrics' => [
            'length_ratio' => 0.3,
            'character_set_consistency' => 0.4,
            'html_entity_check' => 0.2,
            'punctuation_check' => 0.1
        ],
        'minimum_quality_score' => 0.5
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for translation performance optimization.
    |
    */
    'performance' => [
        'cache_enabled' => true,
        'memory_cache_enabled' => true,
        'batch_translation_enabled' => true,
        'preload_common_translations' => true,
        'lazy_loading_enabled' => true,
        'compression_enabled' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for translation fallback strategies.
    |
    */
    'fallbacks' => [
        'enabled' => true,
        'strategies' => [
            'exact_match' => 1.0,
            'fuzzy_match' => 0.8,
            'partial_match' => 0.6,
            'google_translate' => 0.4,
            'original_text' => 0.0
        ],
        'max_fallback_depth' => 3
    ]
]; 