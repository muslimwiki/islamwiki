<?php

/**
 * Test Web Access Script
 * 
 * Simple script to test web application access and identify issues.
 * 
 * Version: 0.0.3.0
 * Usage: php debug/test-web-access.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🌐 Testing Web Application Access...\n\n";

try {
    // Test 1: Check if we can create a simple request
    echo "1️⃣  Testing HTTP Request creation...\n";
    
    if (class_exists('IslamWiki\Core\Http\Request')) {
        echo "   ✅ Request class exists\n";
        
        // Try to create a simple request
        try {
            $request = new \IslamWiki\Core\Http\Request('GET', '/wiki/Home');
            echo "   ✅ Request instance created\n";
            echo "   📍 Method: " . $request->getMethod() . "\n";
            echo "   📍 Path: " . $request->getUri()->getPath() . "\n";
        } catch (\Exception $e) {
            echo "   ❌ Request creation failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Request class not found\n";
    }
    
    echo "\n2️⃣  Testing Application creation...\n";
    
    // Test 2: Try to create the application
    if (class_exists('IslamWiki\Core\Application')) {
        echo "   ✅ Application class exists\n";
        
        try {
            $app = new \IslamWiki\Core\Application();
            echo "   ✅ Application instance created\n";
            
            // Test bootstrap
            try {
                $app->bootstrap();
                echo "   ✅ Application bootstrapped\n";
            } catch (\Exception $e) {
                echo "   ❌ Application bootstrap failed: " . $e->getMessage() . "\n";
            }
        } catch (\Exception $e) {
            echo "   ❌ Application creation failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Application class not found\n";
    }
    
    echo "\n3️⃣  Testing Container services...\n";
    
    // Test 3: Check if container services are working
    echo "   📊 Using Application's container...\n";
    
    try {
        $appContainer = $app->getContainer();
        echo "   ✅ Got Application's container\n";
        
        // Check if logger service exists
        if ($appContainer->has('logger')) {
            echo "   ✅ Logger service available\n";
        } else {
            echo "   ❌ Logger service not available\n";
        }
        
        // Check if router service exists
        if ($appContainer->has('router')) {
            echo "   ✅ Router service available\n";
        } else {
            echo "   ❌ Router service not available\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ Container access failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n4️⃣  Testing file structure...\n";
    
    // Test 4: Check critical files
    $criticalFiles = [
        'public/index.php',
        'config/routes.php',
        'src/Core/Application.php',
        'src/Core/Container/Container.php',
        'src/Core/Logging/Logger.php'
    ];
    
    foreach ($criticalFiles as $file) {
        if (file_exists($file)) {
            echo "   ✅ {$file} - exists\n";
        } else {
            echo "   ❌ {$file} - missing\n";
        }
    }
    
    echo "\n✅ Web application test completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 