<?php

/**
 * Test Core Systems Script
 * 
 * Simple script to test if core systems are working after consolidation.
 * 
 * Version: 0.0.3.0
 * Usage: php debug/test-core-systems.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🚀 Testing Core Systems...\n\n";

try {
    // Test 1: Check if core classes exist
    echo "1️⃣  Checking core classes...\n";
    
    $classes = [
        'IslamWiki\Core\Container\Container',
        'IslamWiki\Core\Auth\Security',
        'IslamWiki\Core\Session\Session',
        'IslamWiki\Core\Logging\Logger'
    ];
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "   ✅ {$class} - OK\n";
        } else {
            echo "   ❌ {$class} - NOT FOUND\n";
        }
    }
    
    echo "\n2️⃣  Testing basic functionality...\n";
    
    // Test 2: Try to create a simple container
    if (class_exists('IslamWiki\Core\Container\Container')) {
        echo "   ✅ Container class exists\n";
        
        // Try to create instance
        try {
            $container = new \IslamWiki\Core\Container\Container();
            echo "   ✅ Container instance created\n";
        } catch (\Exception $e) {
            echo "   ❌ Container creation failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n3️⃣  Testing file structure...\n";
    
    // Test 3: Check file structure
    $coreDir = __DIR__ . '/../src/Core';
    $directories = ['Container', 'Auth', 'Session', 'Logging', 'Routing', 'Search', 'Formatter'];
    
    foreach ($directories as $dir) {
        $path = $coreDir . '/' . $dir;
        if (is_dir($path)) {
            $files = scandir($path);
            $phpFiles = array_filter($files, fn($f) => str_ends_with($f, '.php'));
            echo "   📁 {$dir}: " . count($phpFiles) . " PHP files\n";
        } else {
            echo "   ❌ {$dir}: Directory not found\n";
        }
    }
    
    echo "\n✅ Core systems test completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 