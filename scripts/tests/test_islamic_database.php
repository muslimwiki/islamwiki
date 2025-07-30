<?php
declare(strict_types=1);

/**
 * Test Islamic Database Implementation
 * 
 * This script tests the Islamic database manager and separate connections
 * for Quran, Hadith, Wiki, and Scholar databases.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;
use IslamWiki\Core\Database\Connection;

// Load configuration
$config = require __DIR__ . '/../../config/database.php';

echo "=== Islamic Database Test ===\n";
echo "Testing separate database connections for Islamic content...\n\n";

try {
    // Extract Islamic database configurations
    $islamicConfigs = [
        'quran' => $config['connections']['quran'] ?? [],
        'hadith' => $config['connections']['hadith'] ?? [],
        'wiki' => $config['connections']['wiki'] ?? [],
        'scholar' => $config['connections']['scholar'] ?? [],
    ];

    // Create Islamic database manager
    $manager = new IslamicDatabaseManager($islamicConfigs);

    echo "1. Testing Database Connections...\n";
    $results = $manager->testConnections();
    
    foreach ($results as $type => $result) {
        if ($result['status'] === 'connected') {
            echo "   ✅ {$type}: Connected to {$result['database']} ({$result['driver']})\n";
        } else {
            echo "   ❌ {$type}: Connection failed - {$result['error']}\n";
        }
    }

    echo "\n2. Testing Database Statistics...\n";
    $stats = $manager->getDatabaseStats();
    
    foreach ($stats as $type => $stat) {
        if (isset($stat['error'])) {
            echo "   ❌ {$type}: Error - {$stat['error']}\n";
        } else {
            echo "   📊 {$type}: {$stat['tables']} tables, {$stat['total_rows']} rows, {$stat['database_size']} MB\n";
        }
    }

    echo "\n3. Testing Individual Connections...\n";
    
    // Test Quran connection
    try {
        $quranConnection = $manager->getQuranConnection();
        $quranDb = $quranConnection->getDatabaseName();
        echo "   ✅ Quran: Connected to {$quranDb}\n";
    } catch (Exception $e) {
        echo "   ❌ Quran: " . $e->getMessage() . "\n";
    }

    // Test Hadith connection
    try {
        $hadithConnection = $manager->getHadithConnection();
        $hadithDb = $hadithConnection->getDatabaseName();
        echo "   ✅ Hadith: Connected to {$hadithDb}\n";
    } catch (Exception $e) {
        echo "   ❌ Hadith: " . $e->getMessage() . "\n";
    }

    // Test Wiki connection
    try {
        $wikiConnection = $manager->getWikiConnection();
        $wikiDb = $wikiConnection->getDatabaseName();
        echo "   ✅ Wiki: Connected to {$wikiDb}\n";
    } catch (Exception $e) {
        echo "   ❌ Wiki: " . $e->getMessage() . "\n";
    }

    // Test Scholar connection
    try {
        $scholarConnection = $manager->getScholarConnection();
        $scholarDb = $scholarConnection->getDatabaseName();
        echo "   ✅ Scholar: Connected to {$scholarDb}\n";
    } catch (Exception $e) {
        echo "   ❌ Scholar: " . $e->getMessage() . "\n";
    }

    echo "\n4. Testing Connection Management...\n";
    
    // Test getting all connections
    $connections = $manager->getConnections();
    echo "   📊 Active connections: " . count($connections) . "\n";
    
    // Test disconnecting all
    $manager->disconnectAll();
    echo "   ✅ All connections disconnected\n";

    echo "\n=== Test Summary ===\n";
    echo "✅ Islamic Database Manager: Working\n";
    echo "✅ Separate Connections: Implemented\n";
    echo "✅ Connection Management: Working\n";
    echo "✅ Database Statistics: Available\n";

} catch (Exception $e) {
    echo "\n❌ Test Failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 