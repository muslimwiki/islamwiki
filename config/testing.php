<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Testing Environment Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options specific to the testing
    | environment for the IslamWiki application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Test Database
    |--------------------------------------------------------------------------
    |
    | Here we define the test database configuration. This database will be
    | used when running tests to ensure a clean state for each test case.
    |
    */
    'database' => [
        'connection' => getenv('DB_TEST_CONNECTION') ?: 'sqlite',
        'database' => getenv('DB_DATABASE_TEST') ?: __DIR__ . '/../database/database.sqlite',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Directories
    |--------------------------------------------------------------------------
    |
    | Define the directories where your test files are located.
    |
    */
    'directories' => [
        'unit' => 'tests/Unit',
        'feature' => 'tests/Feature',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Helpers
    |--------------------------------------------------------------------------
    |
    | Path to test helper files that should be autoloaded before running tests.
    |
    */
    'helpers' => [
        base_path('tests/TestHelper.php'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time (in seconds) a test can run before timing out.
    |
    */
    'timeout' => 60,

    /*
    |--------------------------------------------------------------------------
    | Test Coverage
    |--------------------------------------------------------------------------
    |
    | Configuration for code coverage reporting.
    |
    */
    'coverage' => [
        'enabled' => env('TEST_COVERAGE_ENABLED', false),
        'directory' => storage_path('coverage'),
        'format' => 'html', // html, clover, text, etc.
        'exclude' => [
            'bootstrap/*',
            'config/*',
            'database/*',
            'public/*',
            'resources/*',
            'routes/*',
            'storage/*',
            'tests/*',
            'vendor/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Environment Variables
    |--------------------------------------------------------------------------
    |
    | Environment variables that should be set when running tests.
    |
    */
    'env' => [
        'APP_ENV' => 'testing',
        'APP_DEBUG' => 'true',
        'CACHE_DRIVER' => 'array',
        'DB_CONNECTION' => 'sqlite',
        'SESSION_DRIVER' => 'array',
        'QUEUE_DRIVER' => 'sync',
    ],
];
