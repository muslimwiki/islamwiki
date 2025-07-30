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

/**
 * Simple Database Migration Runner
 * 
 * Run this script to execute database migrations without the Application class.
 * Usage: php scripts/simple_migrate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

echo "IslamWiki Simple Database Migration Runner\n";
echo "=========================================\n\n";

try {
    // Create database connection directly
    $dbConfig = [
        'driver' => getenv('DB_CONNECTION') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];

    $connection = new Connection($dbConfig);
    echo "✅ Database connection created\n";

    // Create migrator directly
    $migrationPath = __DIR__ . '/../database/migrations';
    $migrator = new Migrator($connection, $migrationPath);
    echo "✅ Migrator created\n";

    // Check migration status
    echo "\nMigration Status:\n";
    $migrations = $migrator->getMigrationFiles();
    $ran = $migrator->getRanMigrations();
    
    foreach ($migrations as $migration) {
        $status = in_array($migration, $ran) ? '✓ Ran' : '✗ Pending';
        echo "  {$migration}: {$status}\n";
    }

    // Run pending migrations
    if (!empty($migrator->getPendingMigrations())) {
        echo "\nRunning pending migrations...\n";
        $migrator->run();
        echo "✅ Migrations completed successfully.\n";
    } else {
        echo "\n✅ No pending migrations to run.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n"; 