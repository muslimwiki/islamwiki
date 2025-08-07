<?php

/**
 * Test Migration System
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

// Database configuration
$config = [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
];

echo "Testing Migration System\n";
echo "======================\n\n";

try {
    // Create connection
    $connection = new Connection($config);
    echo "✅ Database connection successful\n";

    // Manually create migrations table
    echo "Creating migrations table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $connection->statement($sql);
    echo "✅ Migrations table created\n";

    // Test if table exists
    $result = $connection->select("SHOW TABLES LIKE 'migrations'");
    if (count($result) > 0) {
        echo "✅ Migrations table exists\n";
    } else {
        echo "❌ Migrations table does not exist\n";
    }

    // Test max query
    $result = $connection->select("SELECT MAX(batch) as max_batch FROM migrations");
    $maxBatch = $result[0]['max_batch'] ?? 0;
    echo "✅ Max batch: $maxBatch\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
