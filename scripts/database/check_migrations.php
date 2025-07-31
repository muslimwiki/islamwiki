<?php

/**
 * Check Migration Status
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

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

echo "Migration Status Check\n";
echo "====================\n\n";

try {
    // Create connection
    $connection = new Connection($config);
    echo "✅ Database connection successful\n";
    
    // Create migrator
    $migrationPath = __DIR__ . '/../database/migrations';
    $migrator = new Migrator($connection, $migrationPath);
    
    // Get migration files
    $files = $migrator->getMigrationFiles();
    echo "📁 Migration files found: " . count($files) . "\n";
    foreach ($files as $file) {
        echo "  - $file\n";
    }
    
    // Get ran migrations
    $ran = $migrator->getRanMigrations();
    echo "\n✅ Ran migrations: " . count($ran) . "\n";
    foreach ($ran as $migration) {
        echo "  - $migration\n";
    }
    
    // Get pending migrations
    $pending = $migrator->getPendingMigrations();
    echo "\n⏳ Pending migrations: " . count($pending) . "\n";
    foreach ($pending as $migration) {
        echo "  - $migration\n";
    }
    
    // Check tables
    echo "\n📊 Database tables:\n";
    $tables = $connection->select("SHOW TABLES");
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "  - $tableName\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 