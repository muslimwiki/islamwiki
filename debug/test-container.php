<?php

/**
 * Test Container Script
 * 
 * Simple script to test the Container directly and debug service registration.
 * 
 * Version: 0.0.3.0
 * Usage: php debug/test-container.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔧 Testing Container Directly...\n\n";

try {
    // Test 1: Create container
    echo "1️⃣  Creating Container...\n";
    $container = new \IslamWiki\Core\Container\Container();
    echo "   ✅ Container created\n";
    
    // Test 2: Check initial state
    echo "\n2️⃣  Checking initial state...\n";
    echo "   📊 Container created successfully\n";
    
    // Test 3: Register a simple service
    echo "\n3️⃣  Registering test service...\n";
    $container->set('test', 'Hello World');
    echo "   ✅ Test service registered\n";
    
    // Test 4: Check if service exists
    echo "\n4️⃣  Checking service existence...\n";
    echo "   🔍 Has 'test': " . ($container->has('test') ? 'YES' : 'NO') . "\n";
    echo "   🔍 Has 'logger': " . ($container->has('logger') ? 'YES' : 'NO') . "\n";
    echo "   🔍 Has 'router': " . ($container->has('router') ? 'YES' : 'NO') . "\n";
    
    // Test 5: Get the test service
    echo "\n5️⃣  Getting test service...\n";
    $testValue = $container->get('test');
    echo "   📦 Test value: " . $testValue . "\n";
    
    // Test 6: Try to register logger service
    echo "\n6️⃣  Registering logger service...\n";
    try {
        $logger = new \IslamWiki\Core\Logging\Logger(__DIR__ . '/../storage/logs');
        $container->set('logger', $logger);
        $container->set(\IslamWiki\Core\Logging\Logger::class, $logger);
        echo "   ✅ Logger service registered\n";
        
        // Check if it's available
        echo "   🔍 Has 'logger': " . ($container->has('logger') ? 'YES' : 'NO') . "\n";
        echo "   🔍 Has Logger::class: " . ($container->has(\IslamWiki\Core\Logging\Logger::class) ? 'YES' : 'NO') . "\n";
        
        // Try to get it
        $retrievedLogger = $container->get('logger');
        echo "   📦 Retrieved logger type: " . get_class($retrievedLogger) . "\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Logger registration failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Container test completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 