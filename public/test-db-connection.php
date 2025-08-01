<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use IslamWiki\Core\Application;

// Load environment variables
Dotenv::createImmutable(__DIR__ . '/..')->load();

echo "<h1>Database Connection Test</h1>";

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

try {
    // Get database connection
    $db = $container->get('db');
    echo "<p>✅ Database connection created</p>";
    
    // Test basic query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p>✅ Basic query test: " . ($result ? 'success' : 'failed') . "</p>";
    
    // Check if user_settings table exists
    $stmt = $db->query("SHOW TABLES LIKE 'user_settings'");
    $tableExists = $stmt->fetch();
    echo "<p>✅ user_settings table exists: " . ($tableExists ? 'yes' : 'no') . "</p>";
    
    if ($tableExists) {
        // Check table structure
        $stmt = $db->query("DESCRIBE user_settings");
        $columns = $stmt->fetchAll();
        echo "<p>📋 Table structure:</p>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>{$column['Field']} - {$column['Type']}</li>";
        }
        echo "</ul>";
        
        // Check if there are any records
        $stmt = $db->query("SELECT COUNT(*) as count FROM user_settings");
        $count = $stmt->fetch();
        echo "<p>📊 Total records in user_settings: {$count['count']}</p>";
        
        // Show all records
        $stmt = $db->query("SELECT * FROM user_settings LIMIT 5");
        $records = $stmt->fetchAll();
        echo "<p>📋 Records:</p>";
        foreach ($records as $record) {
            echo "<p>User ID: {$record['user_id']}, Settings: {$record['settings']}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
?> 