<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

// Helper function to get environment variables with defaults
if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

// Helper function for database path
if (!function_exists('database_path')) {
    function database_path($path = '')
    {
        return __DIR__ . '/../database/' . $path;
    }
}

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work.
    |
    */
    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    */
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

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'islamwiki'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        /*
        |--------------------------------------------------------------------------
        | Islamic Database Connections
        |--------------------------------------------------------------------------
        |
        | Separate database connections for different Islamic content types
        | to ensure proper security isolation and performance optimization.
        |
        */
        'quran' => [
            'driver' => 'mysql',
            'host' => env('QURAN_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('QURAN_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('QURAN_DB_DATABASE', 'islamwiki_quran'),
            'username' => env('QURAN_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('QURAN_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'hadith' => [
            'driver' => 'mysql',
            'host' => env('HADITH_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('HADITH_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('HADITH_DB_DATABASE', 'islamwiki_hadith'),
            'username' => env('HADITH_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('HADITH_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'wiki' => [
            'driver' => 'mysql',
            'host' => env('WIKI_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('WIKI_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('WIKI_DB_DATABASE', 'islamwiki_wiki'),
            'username' => env('WIKI_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('WIKI_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'scholar' => [
            'driver' => 'mysql',
            'host' => env('SCHOLAR_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('SCHOLAR_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('SCHOLAR_DB_DATABASE', 'islamwiki_scholar'),
            'username' => env('SCHOLAR_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('SCHOLAR_DB_PASSWORD', env('DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */
    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached.
    |
    */
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],
        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],
        'quran' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_QURAN_DB', 2),
        ],
        'hadith' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_HADITH_DB', 2),
        ],
    ],
];
