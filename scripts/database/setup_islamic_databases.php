<?php
declare(strict_types=1);

/**
 * Setup Islamic Databases
 * 
 * This script creates the separate databases for Islamic content:
 * - islamwiki_quran
 * - islamwiki_hadith
 * - islamwiki_wiki
 * - islamwiki_scholar
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

// Load configuration
$config = require __DIR__ . '/../../config/database.php';

echo "=== Islamic Database Setup ===\n";
echo "Creating separate databases for Islamic content...\n\n";

// Get base database configuration
$baseConfig = $config['connections']['mysql'];

// Database names to create
$databases = [
    'islamwiki_quran',
    'islamwiki_hadith', 
    'islamwiki_wiki',
    'islamwiki_scholar'
];

try {
    // Create connection to MySQL server (without specifying database)
    $serverConfig = $baseConfig;
    $serverConfig['database'] = null; // Connect to server, not specific database
    
    $connection = new Connection($serverConfig);
    $pdo = $connection->getPdo();
    
    echo "✅ Connected to MySQL server\n\n";
    
    // Create each database
    foreach ($databases as $database) {
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "✅ Created database: {$database}\n";
        } catch (Exception $e) {
            echo "❌ Failed to create database {$database}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Database Creation Complete ===\n";
    
    // Test connections to each database
    echo "\nTesting connections to new databases...\n";
    
    foreach ($databases as $database) {
        try {
            $dbConfig = $baseConfig;
            $dbConfig['database'] = $database;
            
            $testConnection = new Connection($dbConfig);
            $testPdo = $testConnection->getPdo();
            
            // Test a simple query
            $result = $testPdo->query("SELECT 1")->fetch();
            
            if ($result) {
                echo "✅ {$database}: Connection successful\n";
            } else {
                echo "❌ {$database}: Connection failed\n";
            }
            
            $testConnection->disconnect();
        } catch (Exception $e) {
            echo "❌ {$database}: " . $e->getMessage() . "\n";
        }
    }
    
    $connection->disconnect();
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Setup Complete ===\n";
echo "Next steps:\n";
echo "1. Run migrations for each database\n";
echo "2. Test the Islamic database manager\n";
echo "3. Import sample data\n"; 