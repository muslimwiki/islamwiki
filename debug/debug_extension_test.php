<?php

// Debug script to test extension loading
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Extension Debug Test ===\n\n";

// Set base path
define('BASE_PATH', __DIR__);

// Load Composer autoloader first
require_once BASE_PATH . '/vendor/autoload.php';

// Load required files
require_once BASE_PATH . '/src/Core/Container/Container.php';
require_once BASE_PATH . '/src/Core/Extensions/ExtensionManager.php';
require_once BASE_PATH . '/src/Core/Extensions/Extension.php';

echo "1. Core classes loaded successfully\n";

// Create container
$container = new \IslamWiki\Core\Container\Container

echo "2. Container created successfully\n";

// Create ExtensionManager
$extMgr = new \IslamWiki\Core\Extensions\ExtensionManager($container);

echo "3. ExtensionManager created successfully\n";

// Check available extensions
$available = $extMgr->getAvailableExtensions();
echo "4. Available extensions: " . implode(', ', $available) . "\n";

// Try to load QuranExtension specifically
echo "5. Attempting to load QuranExtension...\n";
try {
    $loaded = $extMgr->loadExtension('QuranExtension');
    if ($loaded) {
        echo "✅ QuranExtension loaded successfully\n";
        
        // Check if it's loaded
        if ($extMgr->isExtensionLoaded('QuranExtension')) {
            echo "✅ QuranExtension is marked as loaded\n";
            
            // Get the extension instance
            $extension = $extMgr->getExtension('QuranExtension');
            if ($extension) {
                echo "✅ QuranExtension instance retrieved: " . get_class($extension) . "\n";
            } else {
                echo "❌ Failed to get QuranExtension instance\n";
            }
        } else {
            echo "❌ QuranExtension is NOT marked as loaded\n";
        }
    } else {
        echo "❌ Failed to load QuranExtension\n";
    }
} catch (Exception $e) {
    echo "❌ Error loading QuranExtension: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
