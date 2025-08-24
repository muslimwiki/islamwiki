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

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

use IslamWiki\Core\Database\Connection;

echo "Resetting Migration State\n";
echo "========================\n\n";

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

    $pdo = $connection->getPdo();

    // Drop all existing tables
    echo "🗑️  Dropping existing tables...\n";

    // Get all tables in the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "  No tables found to drop\n";
    } else {
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
            echo "  Dropped table: {$table}\n";
        }
    }

    echo "✅ All tables dropped\n";

    echo "\n🎉 Migration state reset completed!\n";
    echo "You can now run 'php scripts/setup_database.php' to set up the database properly.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
