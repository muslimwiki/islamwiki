<?php

/**
 * Simple Extension System Test
 *
 * This script tests if the extension system classes are available and can be loaded.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

echo "=== Simple Extension System Test ===\n\n";

// Test 1: Check if classes exist
echo "1. Checking if extension classes exist...\n";

$classes = [
    'IslamWiki\Core\Extensions\Extension',
    'IslamWiki\Core\Extensions\Hooks\HookManager',
    'IslamWiki\Core\Extensions\ExtensionManager',
    'IslamWiki\Providers\ExtensionServiceProvider',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ {$class} exists\n";
    } else {
        echo "❌ {$class} does NOT exist\n";
    }
}

echo "\n2. Testing HookManager instantiation...\n";
try {
    $hookManager = new \IslamWiki\Core\Extensions\Hooks\HookManager();
    echo "✅ HookManager created successfully\n";
} catch (Exception $e) {
    echo "❌ HookManager creation failed: " . $e->getMessage() . "\n";
}

echo "\n3. Testing ExtensionManager instantiation...\n";
try {
    $container = new \IslamWiki\Core\Container();
    $extensionManager = new \IslamWiki\Core\Extensions\ExtensionManager($container);
    echo "✅ ExtensionManager created successfully\n";
} catch (Exception $e) {
    echo "❌ ExtensionManager creation failed: " . $e->getMessage() . "\n";
}

echo "\n4. Testing ExtensionServiceProvider...\n";
try {
    $container = new \IslamWiki\Core\Container();
    $provider = new \IslamWiki\Providers\ExtensionServiceProvider();
    $provider->register($container);
    echo "✅ ExtensionServiceProvider registered successfully\n";
} catch (Exception $e) {
    echo "❌ ExtensionServiceProvider registration failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
