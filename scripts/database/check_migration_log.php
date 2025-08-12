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

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;

echo "Checking Migration Log\n";
echo "=====================\n\n";

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

    // Check if migrations table exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
    $stmt->execute([$_ENV['DB_DATABASE'] ?? 'islamwiki', 'migrations']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        echo "✅ Migrations table exists\n";

        // Check migration records
        $stmt = $pdo->prepare("SELECT * FROM migrations ORDER BY batch, migration");
        $stmt->execute();
        $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Migration records found: " . count($migrations) . "\n";

        foreach ($migrations as $migration) {
            echo "  - {$migration['migration']} (batch {$migration['batch']}) at {$migration['created_at']}\n";
        }
    } else {
        echo "❌ Migrations table does not exist\n";
    }

    echo "\n🎉 Migration log check completed!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
