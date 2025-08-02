<?php
/**
 * Check User Settings Table
 * 
 * Simple script to check if user_settings table exists.
 * 
 * @package IslamWiki\Tests
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get database connection
$db = $container->get('db');

echo "=== Check User Settings Table ===\n\n";

// Check if user_settings table exists
try {
    $stmt = $db->prepare("SHOW TABLES LIKE 'user_settings'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ user_settings table exists\n";
        
        // Check table structure
        $stmt = $db->prepare("DESCRIBE user_settings");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nTable structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']}: {$column['Type']}\n";
        }
        
        // Check if table has data
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_settings");
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nRecords in table: {$count['count']}\n";
        
        if ($count['count'] > 0) {
            $stmt = $db->prepare("SELECT * FROM user_settings");
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\nData in table:\n";
            foreach ($records as $record) {
                echo "  User ID: {$record['user_id']}\n";
                echo "  Settings: {$record['settings']}\n";
                echo "  Created: {$record['created_at']}\n";
                echo "  Updated: {$record['updated_at']}\n\n";
            }
        }
        
    } else {
        echo "❌ user_settings table does not exist\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n"; 