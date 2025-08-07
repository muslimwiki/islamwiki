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

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

echo "Testing Migration with Transaction\n";
echo "==================================\n\n";

try {
    // Create database connection
    $connection = new Connection([
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ]);

    echo "✅ Database connection successful\n";

    // Create migrator
    $migrationPath = __DIR__ . '/../database/migrations';
    $migrator = new Migrator($connection, $migrationPath);

    echo "✅ Migrator created\n";

    // Check if tables exist before
    echo "\nTables before migration:\n";
    $pdo = $connection->getPdo();
    $tables = ['users', 'pages', 'page_revisions', 'categories', 'page_categories', 'media_files', 'user_watchlist'];

    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
        $stmt->execute([$_ENV['DB_DATABASE'] ?? 'islamwiki', $table]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $exists = $result['count'] > 0 ? '✓ Exists' : '✗ Missing';
        echo "  {$table}: {$exists}\n";
    }

    // Try to run the migration with transaction handling like the migrator does
    echo "\nRunning migration with transaction...\n";

    $migration = $migrator->resolve('0001_initial_schema');
    echo "✅ Migration resolved\n";

    $connection->beginTransaction();

    try {
        echo "Executing migration...\n";
        $migration->up();
        echo "✅ Migration executed\n";

        // Check if tables exist after migration but before commit
        echo "\nTables after migration (before commit):\n";
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
            $stmt->execute([$_ENV['DB_DATABASE'] ?? 'islamwiki', $table]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $exists = $result['count'] > 0 ? '✓ Exists' : '✗ Missing';
            echo "  {$table}: {$exists}\n";
        }

        echo "Committing transaction...\n";
        $connection->commit();
        echo "✅ Transaction committed\n";

        // Check if tables exist after commit
        echo "\nTables after commit:\n";
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
            $stmt->execute([$_ENV['DB_DATABASE'] ?? 'islamwiki', $table]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $exists = $result['count'] > 0 ? '✓ Exists' : '✗ Missing';
            echo "  {$table}: {$exists}\n";
        }
    } catch (Exception $e) {
        echo "❌ Error during migration: " . $e->getMessage() . "\n";
        echo "Rolling back transaction...\n";
        $connection->rollBack();
        echo "✅ Transaction rolled back\n";
        throw $e;
    }

    echo "\n🎉 Migration with transaction test completed!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
