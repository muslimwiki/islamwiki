<?php
declare(strict_types=1);

/**
 * Debug Muslim Skin
 * 
 * Tests why the Muslim skin is not being loaded by the SkinManager.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Muslim Skin Loading\n";
echo "=============================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();

echo "✅ Application initialized\n";

// Test 1: Check if Muslim skin directory exists
echo "\n📊 Test 1: Muslim Skin Directory\n";
echo "==================================\n";

$muslimSkinDir = __DIR__ . '/../skins/Muslim';
$muslimSkinConfig = $muslimSkinDir . '/skin.json';

echo "Muslim skin directory: $muslimSkinDir\n";
echo "Muslim skin directory exists: " . (is_dir($muslimSkinDir) ? 'Yes' : 'No') . "\n";
echo "Muslim skin config: $muslimSkinConfig\n";
echo "Muslim skin config exists: " . (file_exists($muslimSkinConfig) ? 'Yes' : 'No') . "\n";

if (file_exists($muslimSkinConfig)) {
    $config = json_decode(file_get_contents($muslimSkinConfig), true);
    echo "Muslim skin config valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
    if ($config) {
        echo "Muslim skin name: {$config['name']}\n";
        echo "Muslim skin version: {$config['version']}\n";
        echo "Muslim skin author: {$config['author']}\n";
    }
}

// Test 2: Check SkinManager loading
echo "\n📊 Test 2: SkinManager Loading\n";
echo "==============================\n";

try {
    $skinManager = $container->get('skin.manager');
    $loadedSkins = $skinManager->getSkins();
    
    echo "✅ SkinManager loaded\n";
    echo "Loaded skins: " . count($loadedSkins) . "\n";
    
    foreach ($loadedSkins as $key => $skin) {
        echo "  - $key: {$skin->getName()} (v{$skin->getVersion()})\n";
    }
    
    // Check if Muslim skin is loaded
    if (isset($loadedSkins['muslim'])) {
        echo "✅ Muslim skin is loaded by SkinManager\n";
    } else {
        echo "❌ Muslim skin is NOT loaded by SkinManager\n";
    }
    
    if (isset($loadedSkins['Muslim'])) {
        echo "✅ Muslim skin (capitalized) is loaded by SkinManager\n";
    } else {
        echo "❌ Muslim skin (capitalized) is NOT loaded by SkinManager\n";
    }
    
} catch (\Exception $e) {
    echo "❌ SkinManager error: " . $e->getMessage() . "\n";
}

// Test 3: Check LocalSettings configuration
echo "\n📊 Test 3: LocalSettings Configuration\n";
echo "=====================================\n";

$localSettingsPath = __DIR__ . '/../LocalSettings.php';
if (file_exists($localSettingsPath)) {
    // Load LocalSettings to check $wgValidSkins
    require_once $localSettingsPath;
    
    global $wgValidSkins;
    echo "✅ LocalSettings loaded\n";
    echo "wgValidSkins: " . (isset($wgValidSkins) ? 'Yes' : 'No') . "\n";
    
    if (isset($wgValidSkins)) {
        echo "Valid skins:\n";
        foreach ($wgValidSkins as $key => $value) {
            echo "  - $key => $value\n";
        }
        
        if (isset($wgValidSkins['Muslim'])) {
            echo "✅ Muslim skin is in wgValidSkins\n";
        } else {
            echo "❌ Muslim skin is NOT in wgValidSkins\n";
        }
    }
} else {
    echo "❌ LocalSettings.php not found\n";
}

// Test 4: Check skin discovery in SettingsController
echo "\n📊 Test 4: SettingsController Skin Discovery\n";
echo "============================================\n";

try {
    $db = $container->get('db');
    $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
    
    // Use reflection to access the private discoverAvailableSkins method
    $reflection = new ReflectionClass($settingsController);
    $discoverMethod = $reflection->getMethod('discoverAvailableSkins');
    $discoverMethod->setAccessible(true);
    
    $availableSkins = $discoverMethod->invoke($settingsController);
    
    echo "✅ SettingsController skin discovery completed\n";
    echo "Available skins: " . count($availableSkins) . "\n";
    
    foreach ($availableSkins as $key => $skinData) {
        echo "  - $key: {$skinData['name']} (v{$skinData['version']}) by {$skinData['author']}\n";
    }
    
    if (isset($availableSkins['muslim'])) {
        echo "✅ Muslim skin is discovered by SettingsController\n";
    } else {
        echo "❌ Muslim skin is NOT discovered by SettingsController\n";
    }
    
} catch (\Exception $e) {
    echo "❌ SettingsController error: " . $e->getMessage() . "\n";
}

echo "\n✅ Muslim skin test completed!\n"; 