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

echo "Testing Database Connection\n";
echo "==========================\n\n";

try {
    // Test basic connection
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
    
    // Test creating a simple table
    echo "Creating test table...\n";
    $pdo = $connection->getPdo();
    
    // Drop test table if it exists
    $pdo->exec("DROP TABLE IF EXISTS test_table");
    
    // Create a simple test table
    $sql = "CREATE TABLE test_table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NULL,
        PRIMARY KEY (id)
    )";
    
    $pdo->exec($sql);
    echo "✅ Test table created successfully\n";
    
    // Insert test data
    $stmt = $pdo->prepare("INSERT INTO test_table (name, created_at) VALUES (?, NOW())");
    $stmt->execute(['Test Entry']);
    echo "✅ Test data inserted successfully\n";
    
    // Query test data
    $stmt = $pdo->prepare("SELECT * FROM test_table");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Test query successful: " . $result['name'] . "\n";
    
    // Clean up
    $pdo->exec("DROP TABLE test_table");
    echo "✅ Test table cleaned up\n";
    
    echo "\n🎉 Database test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 