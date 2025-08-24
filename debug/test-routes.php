<?php

/**
 * Test Routes Script
 * 
 * Simple script to test if routes can be loaded and if there are any errors.
 * 
 * Version: 0.0.3.0
 * Usage: php debug/test-routes.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🛣️  Testing Routes Loading...\n\n";

try {
    // Test 1: Check if routes file exists
    echo "1️⃣  Checking routes file...\n";
    $routesFile = __DIR__ . '/../config/routes.php';
    if (file_exists($routesFile)) {
        echo "   ✅ Routes file exists\n";
    } else {
        echo "   ❌ Routes file not found\n";
        exit(1);
    }
    
    // Test 2: Check routes file syntax
    echo "\n2️⃣  Checking routes file syntax...\n";
    $syntaxCheck = shell_exec("php -l {$routesFile} 2>&1");
    if (strpos($syntaxCheck, 'No syntax errors detected') !== false) {
        echo "   ✅ Routes file syntax is valid\n";
    } else {
        echo "   ❌ Routes file has syntax errors:\n";
        echo "   " . $syntaxCheck . "\n";
        exit(1);
    }
    
    // Test 3: Try to load routes
    echo "\n3️⃣  Loading routes...\n";
    try {
        $routes = require $routesFile;
        echo "   ✅ Routes loaded successfully\n";
        echo "   📊 Routes type: " . gettype($routes) . "\n";
        
        if (is_callable($routes)) {
            echo "   ✅ Routes is callable\n";
        } else {
            echo "   ❌ Routes is not callable\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Routes loading failed: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Test 4: Try to create application and call routes
    echo "\n4️⃣  Testing routes with application...\n";
    try {
        $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
        echo "   ✅ Application created\n";
        
        if (is_callable($routes)) {
            try {
                $routes($app);
                echo "   ✅ Routes function called successfully\n";
            } catch (\Exception $e) {
                echo "   ❌ Routes function failed: " . $e->getMessage() . "\n";
            }
        }
    } catch (\Exception $e) {
        echo "   ❌ Application creation failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Routes test completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 