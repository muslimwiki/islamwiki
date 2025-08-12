<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing basic application components...\n";

// Test 1: Check if autoloader works
echo "1. Testing autoloader...\n";
try {
    $container = new \IslamWiki\Core\Container\AsasContainer();
    echo "✓ AsasContainer loaded successfully\n";
} catch (Exception $e) {
    echo "✗ AsasContainer failed: " . $e->getMessage() . "\n";
}

// Test 2: Check if logger works
echo "2. Testing logger...\n";
try {
    $logger = new \IslamWiki\Core\Logging\ShahidLogger(__DIR__ . '/logs');
    echo "✓ ShahidLogger loaded successfully\n";
} catch (Exception $e) {
    echo "✗ ShahidLogger failed: " . $e->getMessage() . "\n";
}

// Test 3: Check if session works
echo "3. Testing session...\n";
try {
    $session = new \IslamWiki\Core\Session\WisalSession([]);
    echo "✓ WisalSession loaded successfully\n";
} catch (Exception $e) {
    echo "✗ WisalSession failed: " . $e->getMessage() . "\n";
}

echo "Basic tests completed.\n";
