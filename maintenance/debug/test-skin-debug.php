<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Debug Skin Variables ===\n";

try {
    // Create application instance
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get skin manager
    $skinManager = $container->get('skin.manager');
    echo "✅ SkinManager found\n";

    // Get active skin name
    $activeSkinName = $skinManager->getActiveSkinName();
    echo "📄 Active skin name: " . $activeSkinName . "\n";

    // Get active skin
    $activeSkin = $skinManager->getActiveSkin();
    if ($activeSkin) {
        echo "📄 Active skin object: " . get_class($activeSkin) . "\n";
        echo "📄 Active skin name: " . $activeSkin->getName() . "\n";
        echo "📄 Active skin version: " . $activeSkin->getVersion() . "\n";

        // Check if skin has layout
        if (method_exists($activeSkin, 'getLayoutPath')) {
            $layoutPath = $activeSkin->getLayoutPath();
            echo "📄 Layout path: " . $layoutPath . "\n";
            $temp_88047b41 = (file_exists($layoutPath) ? 'Yes' : 'No') . "\n";
            echo "📄 Layout exists: " . $temp_88047b41;
        }

        // Get CSS content
        $cssContent = $activeSkin->getCssContent();
        echo "📄 CSS content length: " . strlen($cssContent) . "\n";

        // Check if CSS contains Muslim skin content
        if (strpos($cssContent, 'citizen-header') !== false) {
            echo "✅ CSS contains citizen-header (Muslim skin)\n";
        } else {
            echo "❌ CSS does not contain citizen-header\n";
        }
    } else {
        echo "❌ No active skin found\n";
    }

    // Get all available skins
    $skins = $skinManager->getAvailableSkins();
    echo "📄 Available skins: " . implode(', ', array_keys($skins)) . "\n";

    // Check if Muslim skin exists
    if (isset($skins['Muslim'])) {
        echo "✅ Muslim skin is available\n";
        $muslimSkin = $skins['Muslim'];
        echo "📄 Muslim skin name: " . $muslimSkin->getName() . "\n";
        echo "📄 Muslim skin version: " . $muslimSkin->getVersion() . "\n";
    } else {
        echo "❌ Muslim skin not available\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
