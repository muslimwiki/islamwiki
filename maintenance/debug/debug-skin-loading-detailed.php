<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

echo "🔍 Detailed Skin Loading Debug\n";
echo "==============================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get skin manager
    $skinManager = $container->get('skin.manager');

    echo "✅ Application and SkinManager loaded successfully\n\n";

    // Check LocalSettings configuration
    echo "📋 LocalSettings Configuration:\n";
    global $wgValidSkins, $wgActiveSkin;
    echo "- \$wgValidSkins: " . print_r($wgValidSkins, true) . "\n";
    echo "- \$wgActiveSkin: {$wgActiveSkin}\n\n";

    // Check skins directory
    echo "📁 Skins Directory Check:\n";
    $skinsPath = $app->basePath('skins');
    echo "- Skins path: {$skinsPath}\n";
    echo "- Directory exists: " . (is_dir($skinsPath) ? 'Yes' : 'No') . "\n";

    if (is_dir($skinsPath)) {
        $skinDirs = glob($skinsPath . '/*', GLOB_ONLYDIR);
        $temp_a416630b = implode(', ', array_map('basename', $skinDirs)) . "\n";
        echo "- Found skin directories: " . $temp_a416630b;

        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $skinConfigFile = $skinDir . '/skin.json';

            echo "\n- Checking skin: {$skinName}\n";
            echo "  - Directory: {$skinDir}\n";
            echo "  - Config file: {$skinConfigFile}\n";
            $temp_d137d8c0 = (file_exists($skinConfigFile) ? 'Yes' : 'No') . "\n";
            echo "  - Config exists: " . $temp_d137d8c0;

            if (file_exists($skinConfigFile)) {
                try {
                    $config = json_decode(file_get_contents($skinConfigFile), true);
                    $temp_a47d39e3 = (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
                    echo "  - Config valid: " . $temp_a47d39e3;

                    if ($config && isset($config['name'])) {
                        echo "  - Skin name in config: {$config['name']}\n";
                        $temp_78569d1e = ($config['version'] ?? 'Unknown') . "\n";
                        echo "  - Version: " . $temp_78569d1e;
                        $temp_52a25d15 = ($config['author'] ?? 'Unknown') . "\n";
                        echo "  - Author: " . $temp_52a25d15;

                        // Check if skin is in wgValidSkins
                        $inValidSkins = false;
                        foreach ($wgValidSkins as $key => $value) {
                            if (
                                strtolower($key) === strtolower($skinName) ||
                                strtolower($value) === strtolower($skinName)
                            ) {
                                $inValidSkins = true;
                                break;
                            }
                        }
                        $temp_e7c39ed5 = ($inValidSkins ? 'Yes' : 'No') . "\n";
                        echo "  - In \$wgValidSkins: " . $temp_e7c39ed5;
                    } else {
                        echo "  - Invalid config: missing name\n";
                    }
                } catch (\Exception $e) {
                    echo "  - Error reading config: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    // Check loaded skins
    echo "\n📦 Loaded Skins:\n";
    $loadedSkins = $skinManager->getSkins();
    echo "- Total loaded: " . count($loadedSkins) . "\n";

    foreach ($loadedSkins as $name => $skin) {
        echo "- Loaded skin: {$name}\n";
        echo "  - Class: " . get_class($skin) . "\n";
        echo "  - Name: " . $skin->getName() . "\n";
        echo "  - Version: " . $skin->getVersion() . "\n";
    }

    // Check available skin names
    echo "\n🎯 Available Skin Names:\n";
    $availableNames = $skinManager->getAvailableSkinNames();
    echo "- Available names: " . implode(', ', $availableNames) . "\n";

    // Test loading each skin individually
    echo "\n🧪 Individual Skin Loading Test:\n";
    foreach ($skinDirs as $skinDir) {
        $skinName = basename($skinDir);
        echo "- Testing skin: {$skinName}\n";

        $hasSkin = $skinManager->hasSkin($skinName);
        echo "  - Has skin: " . ($hasSkin ? 'Yes' : 'No') . "\n";

        if ($hasSkin) {
            $skin = $skinManager->getSkin($skinName);
            echo "  - Retrieved skin: " . ($skin ? $skin->getName() : 'None') . "\n";
        }
    }

    // Check debug information
    echo "\n🐛 SkinManager Debug Info:\n";
    $debugInfo = $skinManager->debugSkins();
    echo "- Debug info: " . print_r($debugInfo, true) . "\n";

    echo "\n✅ Detailed skin loading debug completed\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
