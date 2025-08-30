<?php

return [
    'default' => env('DB_CONNECTION', 'sqlite'),
    
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', __DIR__ . '/../database/database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
        
        'sqlite_testing' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE_TEST', __DIR__ . '/../database/testing.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
        
        // Add other database configurations as needed
    ],
    
    'migrations' => 'migrations',
];
