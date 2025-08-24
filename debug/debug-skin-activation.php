<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Container;\Container
use Application;\Application
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

    echo "🔍 Debug Skin Activation\n";
    echo "========================\n\n";

    // Get skin manager
    $skinManager = $container->get('skin.manager');

    echo "✅ Skin Service Provider registered\n";
    echo "✅ Skin Manager retrieved: " . get_class($skinManager) . "\n";

    // Get active skin
    $activeSkin = $skinManager->getActiveSkin();
    $activeSkinName = $skinManager->getActiveSkinName();

    echo "\n📊 Skin Information:\n";
    echo "- Active Skin Name: " . $activeSkinName . "\n";
    echo "- Active Skin Class: " . get_class($activeSkin) . "\n";
    echo "- Skin Name: " . $activeSkin->getName() . "\n";
    echo "- Skin Version: " . $activeSkin->getVersion() . "\n";

    // Check if skin has layout path
    if (method_exists($activeSkin, 'getLayoutPath')) {
        $layoutPath = $activeSkin->getLayoutPath();
        echo "- Layout Path: " . ($layoutPath ?: 'Not set') . "\n";

        if ($layoutPath) {
            $temp_88047b41 = (file_exists($layoutPath) ? 'Yes' : 'No') . "\n";
            echo "- Layout Path Exists: " . $temp_88047b41;
            $temp_973c1a89 = (is_dir(dirname($layoutPath)) ? 'Yes' : 'No') . "\n";
            echo "- Layout Directory: " . $temp_973c1a89;
        }
    }

    // Get skin CSS and JS
    $cssContent = $activeSkin->getCssContent();
    $jsContent = $activeSkin->getJsContent();

    echo "\n📄 Skin Assets:\n";
    echo "- CSS Length: " . strlen($cssContent) . " characters\n";
    echo "- JS Length: " . strlen($jsContent) . " characters\n";
        $temp_9ea014c8 = (strpos($cssContent, 'muslim-') !== false ? 'Yes' : 'No') . "\n";
        echo "- CSS Contains 'muslim-': " . $temp_9ea014c8;

    // Test skin layout path logic
    $skinLayoutPath = null;
    if (method_exists($activeSkin, 'getLayoutPath')) {
        $layoutPath = $activeSkin->getLayoutPath();
        if ($layoutPath && file_exists($layoutPath)) {
            $skinLayoutPath = $activeSkinName . '/templates/layout.twig';
            echo "- Computed Skin Layout Path: " . $skinLayoutPath . "\n";
        }
    }

    echo "\n🔧 Test Conditions:\n";
        $temp_19eb8d03 = (isset($skinLayoutPath) ? 'Yes' : 'No') . "\n";
        echo "- skin_layout_path is defined: " . $temp_19eb8d03;
    echo "- skin_layout_path is truthy: " . ($skinLayoutPath ? 'Yes' : 'No') . "\n";
        $temp_b9c8d03d = ($activeSkinName === 'Muslim' ? 'Yes' : 'No') . "\n";
        echo "- active_skin == 'Muslim': " . $temp_b9c8d03d;

    $condition = isset($skinLayoutPath) && $skinLayoutPath && $activeSkinName === 'Muslim';
        $temp_8e9db530 = ($condition ? 'True (Muslim skin should be used)' : 'False (Default skin will be used)') . "\n";
        echo "- Full condition result: " . $temp_8e9db530;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
