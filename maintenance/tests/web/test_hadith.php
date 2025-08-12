<?php

/**
 * Test script for Hadith System
 *
 * This script tests the basic components of the Hadith system
 * to identify any issues before testing through the web interface.
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing Hadith System Components\n";
echo "================================\n\n";

try {
    // Test 1: Check if classes can be loaded
    echo "1. Testing class loading...\n";
    
    if (class_exists('IslamWiki\Models\Hadith')) {
        echo "   ✅ Hadith model class loaded successfully\n";
    } else {
        echo "   ❌ Failed to load Hadith model class\n";
    }
    
    if (class_exists('IslamWiki\Http\Controllers\HadithController')) {
        echo "   ✅ HadithController class loaded successfully\n";
    } else {
        echo "   ❌ Failed to load HadithController class\n";
    }
    
    if (class_exists('IslamWiki\Core\Http\Response')) {
        echo "   ✅ Response class loaded successfully\n";
    } else {
        echo "   ❌ Failed to load Response class\n";
    }
    
    echo "\n2. Testing database connection...\n";
    
    // Test database connection
    $dbConfig = [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'islamwiki_hadith',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];
    
    try {
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
            $dbConfig['username'],
            $dbConfig['password']
        );
        echo "   ✅ Database connection successful\n";
        
        // Test table access
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "   ✅ Found " . count($tables) . " tables in hadith database\n";
        
    } catch (PDOException $e) {
        echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n3. Testing Hadith model methods...\n";
    
    try {
        $hadith = new \IslamWiki\Models\Hadith();
        echo "   ✅ Hadith model instantiated successfully\n";
        
        // Test basic methods
        $collections = $hadith->getCollections();
        echo "   ✅ getCollections() method executed (returned " . count($collections) . " collections)\n";
        
        $stats = $hadith->getStatistics();
        echo "   ✅ getStatistics() method executed\n";
        
    } catch (Exception $e) {
        echo "   ❌ Hadith model test failed: " . $e->getMessage() . "\n";
        echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    echo "\n4. Testing Response class...\n";
    
    try {
        $response = \IslamWiki\Core\Http\Response::json(['test' => 'data'], 200);
        echo "   ✅ Response::json() method works\n";
        
        $response2 = new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'application/json'], '{"test":"data"}');
        echo "   ✅ Response constructor works\n";
        
    } catch (Exception $e) {
        echo "   ❌ Response class test failed: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
