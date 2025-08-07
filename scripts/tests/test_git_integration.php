<?php

/**
 * Test GitIntegration Extension
 *
 * This script tests the GitIntegration extension to ensure it's properly set up
 * and functioning correctly.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Extensions\ExtensionManager;
use IslamWiki\Extensions\GitIntegration\GitIntegration;

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== GitIntegration Extension Test ===\n\n";

try {
    // Create application instance
    echo "1. Creating application instance...\n";
    $app = new Application(__DIR__ . '/../..');
    echo "✅ Application created successfully\n\n";

    // Get container
    echo "2. Getting container...\n";
    $container = $app->getContainer();
    echo "✅ Container retrieved successfully\n\n";

    // Check if ExtensionManager is available
    echo "3. Checking ExtensionManager...\n";
    if ($container->has(ExtensionManager::class)) {
        echo "✅ ExtensionManager is registered in container\n";
    } else {
        echo "❌ ExtensionManager is NOT registered in container\n";
        exit(1);
    }

    // Get ExtensionManager
    echo "4. Getting ExtensionManager...\n";
    try {
        $extensionManager = $container->get(ExtensionManager::class);
        echo "✅ ExtensionManager retrieved successfully\n\n";
    } catch (Exception $e) {
        echo "❌ Failed to get ExtensionManager: " . $e->getMessage() . "\n";
        exit(1);
    }

    // Check available extensions
    echo "5. Checking available extensions...\n";
    $availableExtensions = $extensionManager->getAvailableExtensions();
    echo "Available extensions: " . implode(', ', $availableExtensions) . "\n";

    if (in_array('GitIntegration', $availableExtensions)) {
        echo "✅ GitIntegration extension found\n";
    } else {
        echo "❌ GitIntegration extension NOT found\n";
        exit(1);
    }

    // Check enabled extensions
    echo "6. Checking enabled extensions...\n";
    $enabledExtensions = $extensionManager->getEnabledExtensions();
    echo "Enabled extensions: " . implode(', ', $enabledExtensions) . "\n";

    // Try to load GitIntegration extension
    echo "7. Loading GitIntegration extension...\n";
    $loaded = $extensionManager->loadExtension('GitIntegration');
    if ($loaded) {
        echo "✅ GitIntegration extension loaded successfully\n";
    } else {
        echo "❌ Failed to load GitIntegration extension\n";
        exit(1);
    }

    // Check if extension is loaded
    echo "8. Checking if extension is loaded...\n";
    if ($extensionManager->isExtensionLoaded('GitIntegration')) {
        echo "✅ GitIntegration extension is loaded\n";
    } else {
        echo "❌ GitIntegration extension is NOT loaded\n";
        exit(1);
    }

    // Get extension instance
    echo "9. Getting extension instance...\n";
    $extension = $extensionManager->getExtension('GitIntegration');
    if ($extension instanceof GitIntegration) {
        echo "✅ GitIntegration extension instance retrieved\n";
    } else {
        echo "❌ Failed to get GitIntegration extension instance\n";
        exit(1);
    }

    // Check extension configuration
    echo "10. Checking extension configuration...\n";
    $config = $extension->getConfig();
    echo "Extension config: " . json_encode($config, JSON_PRETTY_PRINT) . "\n";

    // Check if extension is enabled
    echo "11. Checking if extension is enabled...\n";
    if ($extension->isEnabled()) {
        echo "✅ GitIntegration extension is enabled\n";
    } else {
        echo "⚠️  GitIntegration extension is disabled (this is normal for testing)\n";
    }

    // Test repository status
    echo "12. Testing repository status...\n";
    $status = $extension->getRepositoryStatus();
    echo "Repository status: " . json_encode($status, JSON_PRETTY_PRINT) . "\n";

    // Test hook system
    echo "13. Testing hook system...\n";
    $hookManager = $container->get(\IslamWiki\Core\Extensions\Hooks\HookManager::class);
    $hooks = $hookManager->getHooks();
    echo "Registered hooks: " . implode(', ', $hooks) . "\n";

    // Test extension statistics
    echo "14. Testing extension statistics...\n";
    $stats = $extensionManager->getStatistics();
    echo "Extension statistics: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";

    echo "\n=== All Tests Passed! ===\n";
    echo "✅ GitIntegration extension is properly set up and working\n";
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
