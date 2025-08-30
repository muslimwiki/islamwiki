<?php

return [
    'name' => env('APP_NAME', 'IslamWiki'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    
    'providers' => [
        // Service providers will be registered here
    ],
    
    'aliases' => [
        // Class aliases will be registered here
    ],
];
