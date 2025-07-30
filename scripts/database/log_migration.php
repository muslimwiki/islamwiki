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
 * Log Migration as Completed
 * 
 * This script manually logs a migration as completed when the tables
 * already exist but the migration wasn't properly logged.
 * Usage: php scripts/log_migration.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;

echo "IslamWiki Migration Logger\n";
echo "=========================\n\n";

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

    // Check if migrations table exists
    $sql = "SHOW TABLES LIKE 'migrations'";
    $result = $connection->select($sql);
    
    if (empty($result)) {
        echo "❌ Migrations table does not exist. Creating it...\n";
        
        $createSql = "CREATE TABLE migrations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )";
        
        $connection->statement($createSql);
        echo "✅ Migrations table created\n";
    } else {
        echo "✅ Migrations table exists\n";
    }

    // Check if migration is already logged
    $sql = "SELECT * FROM migrations WHERE migration = ?";
    $result = $connection->select($sql, ['0001_initial_schema']);
    
    if (!empty($result)) {
        echo "⚠️  Migration 0001_initial_schema is already logged\n";
        echo "Batch: " . $result[0]['batch'] . "\n";
        echo "Created: " . $result[0]['created_at'] . "\n";
    } else {
        echo "📝 Logging migration 0001_initial_schema as completed...\n";
        
        $insertSql = "INSERT INTO migrations (migration, batch, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
        $connection->statement($insertSql, ['0001_initial_schema', 1]);
        
        echo "✅ Migration logged successfully\n";
    }
    
    // Show current migration status
    echo "\nCurrent Migration Status:\n";
    $sql = "SELECT * FROM migrations ORDER BY batch, id";
    $migrations = $connection->select($sql);
    
    foreach ($migrations as $migration) {
        echo "  {$migration['migration']}: ✓ Ran (Batch {$migration['batch']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n"; 