<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
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
 * Database Migration Runner
 *
 * Run this script to execute database migrations.
 * Usage: php scripts/migrate.php [--fresh] [--rollback]
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use Application;\Application
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get database connection
$connection = $container->get('db');

// Get migrator
$migrator = $container->get('migrator');

// Parse command line arguments
$options = getopt('', ['fresh', 'rollback', 'status']);

echo "IslamWiki Database Migration Runner\n";
echo "==================================\n\n";

try {
    if (isset($options['status'])) {
        // Show migration status
        echo "Migration Status:\n";
        $migrations = $migrator->getMigrationFiles();
        $ran = $migrator->getRanMigrations();

        foreach ($migrations as $migration) {
            $status = in_array($migration, $ran) ? '✓ Ran' : '✗ Pending';
            echo "  {$migration}: {$status}\n";
        }
    } elseif (isset($options['rollback'])) {
        // Rollback last migration
        echo "Rolling back last migration...\n";
        $migrator->rollback();
        echo "✓ Migration rolled back successfully.\n";
    } elseif (isset($options['fresh'])) {
        // Fresh migration (drop all tables and re-run)
        echo "Running fresh migration...\n";
        $migrator->fresh();
        echo "✓ Fresh migration completed successfully.\n";
    } else {
        // Run pending migrations
        echo "Running pending migrations...\n";
        $migrator->run();
        echo "✓ Migrations completed successfully.\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";
