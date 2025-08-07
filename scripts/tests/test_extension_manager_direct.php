<?php

/**
 * Direct ExtensionManager Test
 *
 * This script tests the ExtensionManager class directly.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

echo "=== Direct ExtensionManager Test ===\n\n";

try {
    // Test 1: Check if ExtensionManager class exists
    echo "1. Checking ExtensionManager class...\n";
    if (class_exists('IslamWiki\Core\Extensions\ExtensionManager')) {
        echo "✅ ExtensionManager class exists\n";
    } else {
        echo "❌ ExtensionManager class does NOT exist\n";
        exit(1);
    }

    // Test 2: Create a simple container
    echo "2. Creating simple container...\n";
    $container = new \IslamWiki\Core\Container();
    echo "✅ Container created\n";

    // Test 3: Register HookManager
    echo "3. Registering HookManager...\n";
    $container->singleton('IslamWiki\Core\Extensions\Hooks\HookManager', function () {
        return new \IslamWiki\Core\Extensions\Hooks\HookManager();
    });
    echo "✅ HookManager registered\n";

    // Test 4: Register ExtensionManager
    echo "4. Registering ExtensionManager...\n";
    $container->singleton('IslamWiki\Core\Extensions\ExtensionManager', function (\IslamWiki\Core\Container $container) {
        return new \IslamWiki\Core\Extensions\ExtensionManager($container);
    });
    echo "✅ ExtensionManager registered\n";

    // Test 5: Get ExtensionManager from container
    echo "5. Getting ExtensionManager from container...\n";
    $extensionManager = $container->get('IslamWiki\Core\Extensions\ExtensionManager');
    echo "✅ ExtensionManager retrieved: " . get_class($extensionManager) . "\n";

    // Test 6: Check available extensions
    echo "6. Checking available extensions...\n";
    $availableExtensions = $extensionManager->getAvailableExtensions();
    echo "Available extensions: " . implode(', ', $availableExtensions) . "\n";

    // Test 7: Check if GitIntegration is available
    if (in_array('GitIntegration', $availableExtensions)) {
        echo "✅ GitIntegration extension found\n";
    } else {
        echo "❌ GitIntegration extension NOT found\n";
    }

    echo "\n=== All Tests Passed! ===\n";
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
