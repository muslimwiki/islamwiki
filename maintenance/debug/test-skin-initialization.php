<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;
use IslamWiki\Providers\SkinServiceProvider;

try {
    // Create application with base path
    $app = new NizamApplication(__DIR__ . '/..');

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
        $temp_cae87065 = ($skinManager->hasSkin('Muslim') ? 'Yes' : 'No') . "\n";
        echo "- hasSkin('Muslim'): " . $temp_cae87065;
        $temp_63940f13 = ($skinManager->hasSkin('Bismillah') ? 'Yes' : 'No') . "\n";
        echo "- hasSkin('Bismillah'): " . $temp_63940f13;

    // Check current active skin
    echo "\n🎯 Current Active Skin:\n";
    echo "- Active skin name: " . $skinManager->getActiveSkinName() . "\n";

    // Check if we can get the Muslim skin directly
    $muslimSkin = $skinManager->getSkin('Muslim');
        $temp_202b1bdc = ($muslimSkin ? $muslimSkin->getName() : 'null') . "\n";
        echo "- Muslim skin object: " . $temp_202b1bdc;

    // Check if we can get the Bismillah skin directly
    $bismillahSkin = $skinManager->getSkin('Bismillah');
        $temp_337f2536 = ($bismillahSkin ? $bismillahSkin->getName() : 'null') . "\n";
        echo "- Bismillah skin object: " . $temp_337f2536;

    // Test setting the active skin
    echo "\n🔄 Testing Skin Switching:\n";
    $originalSkin = $skinManager->getActiveSkinName();
    echo "- Original skin: " . $originalSkin . "\n";

    $skinManager->setActiveSkin('Muslim');
        $temp_913b40c5 = $skinManager->getActiveSkinName() . "\n";
        echo "- After setActiveSkin('Muslim'): " . $temp_913b40c5;

    $skinManager->setActiveSkin('Bismillah');
        $temp_913b40c5 = $skinManager->getActiveSkinName() . "\n";
        echo "- After setActiveSkin('Bismillah'): " . $temp_913b40c5;

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
        $temp_b9c8d03d = ($activeSkinName === 'Muslim' ? 'Yes' : 'No') . "\n";
        echo "- active_skin == 'Muslim': " . $temp_b9c8d03d;
    echo "- skin_layout_path is truthy: " . ($skinLayoutPath ? 'Yes' : 'No') . "\n";

    $condition = isset($skinLayoutPath) && $skinLayoutPath && $activeSkinName === 'Muslim';
        $temp_88c04874 = ($condition ? 'True (Muslim layout should be used)' : 'False (Default layout will be used)') . "\n";
        echo "- Full condition result: " . $temp_88c04874;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
