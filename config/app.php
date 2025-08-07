<?php

/**
 * Application Configuration
 *
 * Basic application configuration settings.
 *
 * @package IslamWiki
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'IslamWiki'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', true),
    'url' => env('APP_URL', 'https://local.islam.wiki'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'key' => env('APP_KEY', 'base64:your-secret-key-here'),
    'cipher' => env('APP_CIPHER', 'AES-256-CBC'),

    // Database configuration
    'database' => [
        'default' => env('DB_CONNECTION', 'mysql'),
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'islamwiki'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ],
    ],

    // Logging configuration
    'logging' => [
        'default' => env('LOG_CHANNEL', 'stack'),
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                'channels' => ['single'],
                'ignore_exceptions' => false,
            ],
            'single' => [
                'driver' => 'single',
                'path' => storage_path('logs/islamwiki.log'),
                'level' => env('LOG_LEVEL', 'debug'),
            ],
        ],
    ],

    // Cache configuration
    'cache' => [
        'default' => env('CACHE_DRIVER', 'file'),
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/data'),
            ],
        ],
    ],

    // Session configuration
    'session' => [
        'driver' => env('SESSION_DRIVER', 'file'),
        'lifetime' => env('SESSION_LIFETIME', 120),
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => storage_path('framework/sessions'),
        'connection' => env('SESSION_CONNECTION'),
        'table' => 'sessions',
        'store' => env('SESSION_STORE'),
        'lottery' => [2, 100],
        'cookie' => env(
            'SESSION_COOKIE',
            'islamwiki_session'
        ),
        'path' => '/',
        'domain' => env('SESSION_DOMAIN'),
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => true,
        'same_site' => 'lax',
    ],

    // Security configuration
    'security' => [
        'csrf_protection' => true,
        'rate_limiting' => true,
        'session_lifetime' => 7200,
    ],

    // Islamic configuration
    'islamic' => [
        'default_prayer_method' => 'MWL',
        'enable_quran_integration' => true,
        'enable_hadith_integration' => true,
    ],

    // Extension configuration
    'extensions' => [
        'enable_enhanced_markdown' => true,
        'enable_git_integration' => false,
    ],

    // Performance configuration
    'performance' => [
        'enable_caching' => true,
        'cache_lifetime' => 3600,
    ],
];
