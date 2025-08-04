<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;
use IslamWiki\Providers\SkinServiceProvider;

try {
    // Create application with base path
    $app = new Application(__DIR__ . '/..');
    
    // Register skin service provider
    $skinProvider = new SkinServiceProvider($app);
    $skinProvider->register();
    
    // Get container from app
    $container = $app->getContainer();
    $skinManager = $container->get('skin.manager');
    
    echo "🔍 Test Skin Initialization\n";
    echo "===========================\n\n";
    
    // Check if skins exist
    echo "📋 Skin Availability Check:\n";
    echo "- hasSkin('Muslim'): " . ($skinManager->hasSkin('Muslim') ? 'Yes' : 'No') . "\n";
    echo "- hasSkin('Bismillah'): " . ($skinManager->hasSkin('Bismillah') ? 'Yes' : 'No') . "\n";
    
    // Check current active skin
    echo "\n🎯 Current Active Skin:\n";
    echo "- Active skin name: " . $skinManager->getActiveSkinName() . "\n";
    
    // Check if we can get the Muslim skin directly
    $muslimSkin = $skinManager->getSkin('Muslim');
    echo "- Muslim skin object: " . ($muslimSkin ? $muslimSkin->getName() : 'null') . "\n";
    
    // Check if we can get the Bismillah skin directly
    $bismillahSkin = $skinManager->getSkin('Bismillah');
    echo "- Bismillah skin object: " . ($bismillahSkin ? $bismillahSkin->getName() : 'null') . "\n";
    
    // Test setting the active skin
    echo "\n🔄 Testing Skin Switching:\n";
    $originalSkin = $skinManager->getActiveSkinName();
    echo "- Original skin: " . $originalSkin . "\n";
    
    $skinManager->setActiveSkin('Muslim');
    echo "- After setActiveSkin('Muslim'): " . $skinManager->getActiveSkinName() . "\n";
    
    $skinManager->setActiveSkin('Bismillah');
    echo "- After setActiveSkin('Bismillah'): " . $skinManager->getActiveSkinName() . "\n";
    
    // Test the condition that determines which layout to use
    $activeSkinName = $skinManager->getActiveSkinName();
    $activeSkin = $skinManager->getActiveSkin();
    $skinLayoutPath = null;
    
    if ($activeSkin && method_exists($activeSkin, 'getLayoutPath')) {
        $layoutPath = $activeSkin->getLayoutPath();
        if ($layoutPath && file_exists($layoutPath)) {
            $skinLayoutPath = $activeSkinName . '/templates/layout.twig';
        }
    }
    
    echo "\n🔧 Layout Condition Test:\n";
    echo "- active_skin: " . $activeSkinName . "\n";
    echo "- skin_layout_path: " . ($skinLayoutPath ?: 'null') . "\n";
    echo "- active_skin == 'Muslim': " . ($activeSkinName === 'Muslim' ? 'Yes' : 'No') . "\n";
    echo "- skin_layout_path is truthy: " . ($skinLayoutPath ? 'Yes' : 'No') . "\n";
    
    $condition = isset($skinLayoutPath) && $skinLayoutPath && $activeSkinName === 'Muslim';
    echo "- Full condition result: " . ($condition ? 'True (Muslim layout should be used)' : 'False (Default layout will be used)') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 