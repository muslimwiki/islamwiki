<?php
declare(strict_types=1);

/**
 * Debug LocalSettings Loading
 * 
 * Tests if LocalSettings.php is being properly loaded and if $wgValidSkins is accessible.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing LocalSettings Loading\n";
echo "==============================\n\n";

// Test 1: Check LocalSettings.php directly
echo "\n📊 Test 1: Direct LocalSettings Check\n";
echo "=====================================\n";

$localSettingsPath = __DIR__ . '/../LocalSettings.php';
echo "LocalSettings path: $localSettingsPath\n";
echo "LocalSettings exists: " . (file_exists($localSettingsPath) ? 'Yes' : 'No') . "\n";

if (file_exists($localSettingsPath)) {
    // Load LocalSettings to check $wgValidSkins
    require_once $localSettingsPath;
    
    global $wgValidSkins;
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
    } else {
        echo "❌ wgValidSkins is not set\n";
    }
} else {
    echo "❌ LocalSettings.php not found\n";
}

// Test 2: Check Application loading
echo "\n📊 Test 2: Application Loading\n";
echo "==============================\n";

try {
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    echo "✅ Application created\n";
    
    // Check if LocalSettings was loaded by the application
    global $wgValidSkins;
    echo "wgValidSkins after app creation: " . (isset($wgValidSkins) ? 'Yes' : 'No') . "\n";
    
    if (isset($wgValidSkins)) {
        echo "Valid skins after app creation:\n";
        foreach ($wgValidSkins as $key => $value) {
            echo "  - $key => $value\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Application error: " . $e->getMessage() . "\n";
}

// Test 3: Check SkinManager creation
echo "\n📊 Test 3: SkinManager Creation\n";
echo "===============================\n";

try {
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "✅ Application and container created\n";
    
    $skinManager = $container->get('skin.manager');
    echo "✅ SkinManager created\n";
    
    // Check debug info
    $debugInfo = $skinManager->debugSkins();
    echo "Debug info:\n";
    echo "  - Loaded skins: " . implode(', ', $debugInfo['loaded_skins']) . "\n";
    echo "  - Valid skins from LocalSettings: " . implode(', ', $debugInfo['valid_skins_from_localsettings']) . "\n";
    echo "  - Active skin: {$debugInfo['active_skin']}\n";
    
    // Check if Muslim is in the loaded skins
    if (in_array('Muslim', $debugInfo['loaded_skins'])) {
        echo "✅ Muslim skin is loaded by SkinManager\n";
    } else {
        echo "❌ Muslim skin is NOT loaded by SkinManager\n";
    }
    
    if (in_array('muslim', $debugInfo['loaded_skins'])) {
        echo "✅ Muslim skin (lowercase) is loaded by SkinManager\n";
    } else {
        echo "❌ Muslim skin (lowercase) is NOT loaded by SkinManager\n";
    }
    
} catch (\Exception $e) {
    echo "❌ SkinManager error: " . $e->getMessage() . "\n";
}

// Test 4: Check skin directories
echo "\n📊 Test 4: Skin Directories\n";
echo "===========================\n";

$skinsPath = __DIR__ . '/../skins';
echo "Skins path: $skinsPath\n";
echo "Skins path exists: " . (is_dir($skinsPath) ? 'Yes' : 'No') . "\n";

if (is_dir($skinsPath)) {
    $skinDirs = glob($skinsPath . '/*', GLOB_ONLYDIR);
    echo "Skin directories found: " . count($skinDirs) . "\n";
    
    foreach ($skinDirs as $skinDir) {
        $skinName = basename($skinDir);
        $skinConfigFile = $skinDir . '/skin.json';
        
        echo "  - $skinName: " . (file_exists($skinConfigFile) ? 'Has config' : 'No config') . "\n";
    }
}

echo "\n✅ LocalSettings loading test completed!\n"; 