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

    echo "🔍 Test Muslim Skin Availability\n";
    echo "================================\n\n";

    // Check if Muslim skin exists
    $hasMuslimSkin = $skinManager->hasSkin('Muslim');
    echo "✅ Muslim skin exists: " . ($hasMuslimSkin ? 'Yes' : 'No') . "\n";

    // Check if Bismillah skin exists
    $hasBismillahSkin = $skinManager->hasSkin('Bismillah');
    echo "✅ Bismillah skin exists: " . ($hasBismillahSkin ? 'Yes' : 'No') . "\n";

    // Check if skins exist individually
    echo "\n📋 Skin availability:\n";
        $temp_cae87065 = ($skinManager->hasSkin('Muslim') ? 'Yes' : 'No') . "\n";
        echo "- Muslim skin exists: " . $temp_cae87065;
        $temp_63940f13 = ($skinManager->hasSkin('Bismillah') ? 'Yes' : 'No') . "\n";
        echo "- Bismillah skin exists: " . $temp_63940f13;

    // Check current active skin
    $activeSkinName = $skinManager->getActiveSkinName();
    echo "\n🎯 Current active skin: " . $activeSkinName . "\n";

    // Try to set Muslim skin
    $skinManager->setActiveSkin('Muslim');
    $newActiveSkinName = $skinManager->getActiveSkinName();
    echo "🎯 After setting Muslim skin: " . $newActiveSkinName . "\n";

    // Check if the skin was actually changed
    $activeSkin = $skinManager->getActiveSkin();
        $temp_6974c67f = ($activeSkin ? $activeSkin->getName() : 'null') . "\n";
        echo "🎯 Active skin object: " . $temp_6974c67f;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
