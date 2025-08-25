<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

echo "🔍 Debug Skin Management System\n";
echo "===============================\n\n";

try {
    // Create application instance
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get skin manager
    $skinManager = $container->get('skin.manager');

    echo "✅ Application and SkinManager loaded successfully\n";
    echo "✅ SkinManager class: " . get_class($skinManager) . "\n\n";

    // Test skin discovery
    echo "📁 Skin Discovery:\n";
    $availableSkins = $skinManager->getAvailableSkins();
    echo "- Total skins loaded: " . count($availableSkins) . "\n";

    foreach ($availableSkins as $name => $skin) {
        echo "- Skin: {$name}\n";
        echo "  - Name: " . ($skin['name'] ?? 'Unknown') . "\n";
        echo "  - Version: " . ($skin['version'] ?? 'Unknown') . "\n";
        echo "  - Author: " . ($skin['author'] ?? 'Unknown') . "\n";
        echo "  - Description: " . ($skin['description'] ?? 'No description') . "\n";
        echo "  - Path: " . ($skin['path'] ?? 'Unknown') . "\n";
    }

    echo "\n🎯 Active Skin:\n";
    $activeSkin = $skinManager->getActiveSkin();
    if ($activeSkin) {
        echo "- Active Skin: " . ($activeSkin['name'] ?? 'Unknown') . "\n";
        echo "- Active Skin Name: " . $skinManager->getActiveSkinName() . "\n";
    } else {
        echo "- No active skin found\n";
    }

    // Test skin switching
    echo "\n🔄 Skin Switching Test:\n";
    $availableSkinNames = array_keys($availableSkins);
    foreach ($availableSkinNames as $skinName) {
        echo "- Testing switch to: {$skinName}\n";
        $result = $skinManager->setActiveSkin($skinName);
        echo "  - Result: " . ($result ? 'Success' : 'Failed') . "\n";

        if ($result) {
            $newActiveSkin = $skinManager->getActiveSkin();
            echo "  - New active skin: " . ($newActiveSkin ? ($newActiveSkin['name'] ?? 'Unknown') : 'None') . "\n";
        }
    }

    // Test skin validation
    echo "\n✅ Skin Validation:\n";
    foreach ($availableSkinNames as $skinName) {
        $hasSkin = $skinManager->hasSkin($skinName);
        echo "- Skin '{$skinName}': " . ($hasSkin ? 'Valid' : 'Invalid') . "\n";
    }

    // Test skin metadata
    echo "\n📋 Skin Metadata:\n";
    foreach ($availableSkins as $name => $skin) {
        $metadata = $skinManager->getSkinMetadata($name);
        if ($metadata) {
            echo "- {$name}:\n";
            echo "  - Version: " . ($metadata['version'] ?? 'Unknown') . "\n";
            echo "  - Author: " . ($metadata['author'] ?? 'Unknown') . "\n";
            echo "  - Description: " . ($metadata['description'] ?? 'No description') . "\n";
        }
    }

    // Test skin assets
    echo "\n🎨 Skin Assets:\n";
    $activeSkin = $skinManager->getActiveSkin();
    if ($activeSkin) {
        $assets = $skinManager->getSkinAssets();
        echo "- CSS files: " . (isset($assets['css']) ? count($assets['css']) : 0) . "\n";
        echo "- JS files: " . (isset($assets['js']) ? count($assets['js']) : 0) . "\n";
        
        if (isset($assets['css'])) {
            foreach ($assets['css'] as $cssFile) {
                echo "  - CSS: {$cssFile}\n";
            }
        }
        
        if (isset($assets['js'])) {
            foreach ($assets['js'] as $jsFile) {
                echo "  - JS: {$jsFile}\n";
            }
        }
    }

    echo "\n✅ Skin management test completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
