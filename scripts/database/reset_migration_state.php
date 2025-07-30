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
 * Reset Migration State
 * 
 * This script clears the migration log to reset the migration state.
 * Usage: php scripts/reset_migration_state.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;

echo "IslamWiki Migration State Reset\n";
echo "==============================\n\n";

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
        echo "⚠️  Migrations table does not exist. Nothing to reset.\n";
    } else {
        echo "🗑️  Clearing migration log...\n";
        
        $deleteSql = "DELETE FROM migrations";
        $connection->statement($deleteSql);
        
        echo "✅ Migration log cleared\n";
    }
    
    // Show current migration status
    echo "\nCurrent Migration Status:\n";
    $sql = "SELECT * FROM migrations ORDER BY batch, id";
    $migrations = $connection->select($sql);
    
    if (empty($migrations)) {
        echo "  No migrations logged\n";
    } else {
        foreach ($migrations as $migration) {
            echo "  {$migration['migration']}: ✓ Ran (Batch {$migration['batch']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n"; 