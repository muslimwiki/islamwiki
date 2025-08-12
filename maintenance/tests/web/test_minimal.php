<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing minimal application...\n";

try {
    // Test 1: Basic container
    echo "1. Testing container...\n";
    $container = new \IslamWiki\Core\Container\AsasContainer();
    echo "✓ Container created\n";

    // Test 2: Basic logger
    echo "2. Testing logger...\n";
    $logger = new \IslamWiki\Core\Logging\ShahidLogger(__DIR__ . '/logs');
    echo "✓ Logger created\n";

    // Test 3: Basic session
    echo "3. Testing session...\n";
    $session = new \IslamWiki\Core\Session\WisalSession([]);
    echo "✓ Session created\n";

    // Test 4: Basic connection
    echo "4. Testing connection...\n";
    $connection = new \IslamWiki\Core\Database\Connection([]);
    echo "✓ Connection created\n";

    // Test 5: Basic auth
    echo "5. Testing auth...\n";
    $auth = new \IslamWiki\Core\Auth\AmanSecurity($session, $connection);
    echo "✓ Auth created\n";

    echo "✓ All basic components work!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "Minimal test completed.\n";
