<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;

echo "🔍 Debug Skin Web Test\n";
echo "======================\n\n";

try {
    // Create application instance
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get skin manager
    $skinManager = $container->get('skin.manager');
    
    echo "✅ Application and SkinManager loaded successfully\n\n";
    
    // Test current state
    echo "🎯 Current State:\n";
    $activeSkin = $skinManager->getActiveSkin();
    echo "- Active Skin: " . ($activeSkin ? $activeSkin->getName() : 'None') . "\n";
    echo "- Active Skin Name: " . $skinManager->getActiveSkinName() . "\n";
    
    // Test switching to Muslim
    echo "\n🔄 Switching to Muslim Skin:\n";
    $result = $skinManager->setActiveSkin('Muslim');
    echo "- Switch Result: " . ($result ? 'Success' : 'Failed') . "\n";
    
    if ($result) {
        $newActiveSkin = $skinManager->getActiveSkin();
        echo "- New Active Skin: " . ($newActiveSkin ? $newActiveSkin->getName() : 'None') . "\n";
        echo "- New Active Skin Name: " . $skinManager->getActiveSkinName() . "\n";
        
        // Test container data
        if ($container->has('skin.data')) {
            $skinData = $container->get('skin.data');
            echo "\n📦 Container Skin Data:\n";
            echo "- Name: " . ($skinData['name'] ?? 'Unknown') . "\n";
            echo "- CSS Length: " . strlen($skinData['css'] ?? '') . " characters\n";
            echo "- JS Length: " . strlen($skinData['js'] ?? '') . " characters\n";
        }
        
        // Test view globals
        if ($container->has('view')) {
            $view = $container->get('view');
            echo "\n📋 View Globals:\n";
            // Note: We can't directly access view globals, but we can test if the view is working
            echo "- View Renderer: " . get_class($view) . "\n";
        }
    }
    
    // Test switching back to Bismillah
    echo "\n🔄 Switching Back to Bismillah:\n";
    $result = $skinManager->setActiveSkin('Bismillah');
    echo "- Switch Result: " . ($result ? 'Success' : 'Failed') . "\n";
    
    if ($result) {
        $newActiveSkin = $skinManager->getActiveSkin();
        echo "- New Active Skin: " . ($newActiveSkin ? $newActiveSkin->getName() : 'None') . "\n";
        echo "- New Active Skin Name: " . $skinManager->getActiveSkinName() . "\n";
    }
    
    echo "\n✅ Skin web test completed successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 