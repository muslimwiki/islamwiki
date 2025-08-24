<?php

/**
 * Simple Routes Test Script
 * 
 * Basic test to see where the routes loading fails.
 * 
 * Version: 0.0.3.0
 * Usage: php debug/test-simple-routes.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🛣️  Simple Routes Test...\n\n";

try {
    echo "1️⃣  Creating Application...\n";
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    echo "   ✅ Application created\n";
    
    echo "\n2️⃣  Getting Container...\n";
    $container = $app->getContainer();
    echo "   ✅ Container obtained\n";
    
    echo "\n3️⃣  Checking services...\n";
    echo "   🔍 Has 'logger': " . ($container->has('logger') ? 'YES' : 'NO') . "\n";
    echo "   🔍 Has 'router': " . ($container->has('router') ? 'YES' : 'NO') . "\n";
    echo "   🔍 Has 'db': " . ($container->has('db') ? 'YES' : 'NO') . "\n";
    echo "   🔍 Has Connection::class: " . ($container->has(\IslamWiki\Core\Database\Connection::class) ? 'YES' : 'NO') . "\n";
    
    echo "\n4️⃣  Loading routes file...\n";
    $routes = require __DIR__ . '/../config/routes.php';
    echo "   ✅ Routes loaded\n";
    
    echo "\n5️⃣  Calling routes function...\n";
    $routes($app);
    echo "   ✅ Routes function completed\n";
    
    echo "\n✅ Simple routes test completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (\Error $e) {
    echo "\n❌ Test failed with fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 