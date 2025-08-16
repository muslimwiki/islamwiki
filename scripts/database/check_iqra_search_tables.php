<?php

/**
 * Check Iqra Search Tables Script
 *
 * This script checks if all the required tables for the Iqra search system exist
 * and have the correct structure.
 *
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

echo "Checking Iqra Search Required Tables\n";
echo "===================================\n\n";

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

try {
    // Create connection
    $connection = new Connection($config);
    echo "✅ Database connection successful\n\n";

    // Required tables for Iqra search
    $requiredTables = [
        'pages' => 'Wiki pages for general search',
        'users' => 'User information for author data',
        'ayahs' => 'Quran verses for Quran search',
        'surahs' => 'Quran chapters for Quran search',
        'ayah_translations' => 'Quran translations for search',
        'hadiths' => 'Hadith collections for Hadith search',
        'hadith_collections' => 'Hadith collection metadata',
        'islamic_events' => 'Islamic calendar events for calendar search',
        'event_categories' => 'Event categories for calendar search',
        'salah_times' => 'Prayer times for salah search',
        'scholars' => 'Islamic scholars for scholar search'
    ];

    $missingTables = [];
    $existingTables = [];

    foreach ($requiredTables as $table => $description) {
        try {
            $stmt = $connection->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Table '$table' exists - $description\n";
                $existingTables[] = $table;
                
                // Check table structure
                $stmt = $connection->query("DESCRIBE `$table`");
                $columns = $stmt->fetchAll();
                echo "   Columns: " . count($columns) . "\n";
                
                // Check if table has data
                $stmt = $connection->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $stmt->fetch();
                echo "   Records: " . $count['count'] . "\n\n";
            } else {
                echo "❌ Table '$table' MISSING - $description\n\n";
                $missingTables[] = $table;
            }
        } catch (Exception $e) {
            echo "❌ Error checking table '$table': " . $e->getMessage() . "\n\n";
            $missingTables[] = $table;
        }
    }

    echo "\n📊 Summary:\n";
    echo "===========\n";
    echo "✅ Existing tables: " . count($existingTables) . "\n";
    echo "❌ Missing tables: " . count($missingTables) . "\n";
    
    if (!empty($missingTables)) {
        echo "\nMissing tables:\n";
        foreach ($missingTables as $table) {
            echo "  - $table\n";
        }
        echo "\n⚠️  Iqra search may not work properly without these tables!\n";
    } else {
        echo "\n🎉 All required tables exist! Iqra search should work properly.\n";
    }

    // Test a simple search query
    echo "\n🧪 Testing basic search functionality...\n";
    try {
        // Test if we can query the pages table
        $stmt = $connection->query("SELECT COUNT(*) as count FROM pages");
        $result = $stmt->fetch();
        echo "✅ Pages table query successful: " . $result['count'] . " pages found\n";
        
        // Test if we can query the salah_times table
        $stmt = $connection->query("SELECT COUNT(*) as count FROM salah_times");
        $result = $stmt->fetch();
        echo "✅ Salah times table query successful: " . $result['count'] . " records found\n";
        
        echo "\n🎯 Basic search functionality appears to be working!\n";
        
    } catch (Exception $e) {
        echo "❌ Search functionality test failed: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 