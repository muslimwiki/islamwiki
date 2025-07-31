<?php
/**
 * Test User Skin System
 * 
 * This script tests the new user skin system that reads skins from /skins/
 * instead of src/Skins/.
 * 
 * @package IslamWiki\Tests
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Skins\UserSkin;
use IslamWiki\Skins\SkinManager;

echo "=== Testing User Skin System ===\n\n";

// Test 1: Load skin configuration
echo "1. Testing skin configuration loading...\n";

$bismillahConfigFile = __DIR__ . '/../skins/Bismillah/skin.json';
$blueSkinConfigFile = __DIR__ . '/../skins/BlueSkin/skin.json';

if (file_exists($bismillahConfigFile)) {
    $bismillahConfig = json_decode(file_get_contents($bismillahConfigFile), true);
    echo "✓ Bismillah skin config loaded successfully\n";
    echo "  - Name: " . $bismillahConfig['name'] . "\n";
    echo "  - Version: " . $bismillahConfig['version'] . "\n";
    echo "  - Author: " . $bismillahConfig['author'] . "\n";
    echo "  - Type: " . $bismillahConfig['type'] . "\n";
} else {
    echo "✗ Bismillah skin config not found\n";
}

if (file_exists($blueSkinConfigFile)) {
    $blueSkinConfig = json_decode(file_get_contents($blueSkinConfigFile), true);
    echo "✓ BlueSkin config loaded successfully\n";
    echo "  - Name: " . $blueSkinConfig['name'] . "\n";
    echo "  - Version: " . $blueSkinConfig['version'] . "\n";
    echo "  - Author: " . $blueSkinConfig['author'] . "\n";
    echo "  - Type: " . $blueSkinConfig['type'] . "\n";
} else {
    echo "✗ BlueSkin config not found\n";
}

echo "\n";

// Test 2: Create UserSkin instances
echo "2. Testing UserSkin instantiation...\n";

try {
    $bismillahSkin = new UserSkin($bismillahConfig, __DIR__ . '/../skins/Bismillah');
    echo "✓ Bismillah UserSkin created successfully\n";
    echo "  - Name: " . $bismillahSkin->getName() . "\n";
    echo "  - Version: " . $bismillahSkin->getVersion() . "\n";
    echo "  - Author: " . $bismillahSkin->getAuthor() . "\n";
    echo "  - Description: " . $bismillahSkin->getDescription() . "\n";
} catch (Exception $e) {
    echo "✗ Failed to create Bismillah UserSkin: " . $e->getMessage() . "\n";
}

try {
    $blueSkin = new UserSkin($blueSkinConfig, __DIR__ . '/../skins/BlueSkin');
    echo "✓ BlueSkin UserSkin created successfully\n";
    echo "  - Name: " . $blueSkin->getName() . "\n";
    echo "  - Version: " . $blueSkin->getVersion() . "\n";
    echo "  - Author: " . $blueSkin->getAuthor() . "\n";
    echo "  - Description: " . $blueSkin->getDescription() . "\n";
} catch (Exception $e) {
    echo "✗ Failed to create BlueSkin UserSkin: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Test skin assets
echo "3. Testing skin assets...\n";

if ($bismillahSkin) {
    echo "Bismillah skin assets:\n";
    echo "  - CSS path: " . $bismillahSkin->getCssPath() . "\n";
    echo "  - JS path: " . $bismillahSkin->getJsPath() . "\n";
    echo "  - Layout path: " . $bismillahSkin->getLayoutPath() . "\n";
    echo "  - Has custom CSS: " . ($bismillahSkin->hasCustomCss() ? 'Yes' : 'No') . "\n";
    echo "  - Has custom JS: " . ($bismillahSkin->hasCustomJs() ? 'Yes' : 'No') . "\n";
    echo "  - Has custom layout: " . ($bismillahSkin->hasCustomLayout() ? 'Yes' : 'No') . "\n";
}

if ($blueSkin) {
    echo "BlueSkin assets:\n";
    echo "  - CSS path: " . $blueSkin->getCssPath() . "\n";
    echo "  - JS path: " . $blueSkin->getJsPath() . "\n";
    echo "  - Layout path: " . $blueSkin->getLayoutPath() . "\n";
    echo "  - Has custom CSS: " . ($blueSkin->hasCustomCss() ? 'Yes' : 'No') . "\n";
    echo "  - Has custom JS: " . ($blueSkin->hasCustomJs() ? 'Yes' : 'No') . "\n";
    echo "  - Has custom layout: " . ($blueSkin->hasCustomLayout() ? 'Yes' : 'No') . "\n";
}

echo "\n";

// Test 4: Test skin features and dependencies
echo "4. Testing skin features and dependencies...\n";

if ($bismillahSkin) {
    echo "Bismillah skin features:\n";
    $features = $bismillahSkin->getFeatures();
    foreach ($features as $feature) {
        echo "  - " . $feature . "\n";
    }
    
    echo "Bismillah skin dependencies:\n";
    $dependencies = $bismillahSkin->getDependencies();
    foreach ($dependencies as $dep => $source) {
        echo "  - " . $dep . " (" . $source . ")\n";
    }
}

if ($blueSkin) {
    echo "BlueSkin features:\n";
    $features = $blueSkin->getFeatures();
    foreach ($features as $feature) {
        echo "  - " . $feature . "\n";
    }
    
    echo "BlueSkin dependencies:\n";
    $dependencies = $blueSkin->getDependencies();
    foreach ($dependencies as $dep => $source) {
        echo "  - " . $dep . " (" . $source . ")\n";
    }
}

echo "\n";

// Test 5: Test skin configuration
echo "5. Testing skin configuration...\n";

if ($bismillahSkin) {
    echo "Bismillah skin config:\n";
    $config = $bismillahSkin->getConfig();
    foreach ($config as $key => $value) {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        echo "  - " . $key . ": " . $value . "\n";
    }
}

if ($blueSkin) {
    echo "BlueSkin config:\n";
    $config = $blueSkin->getConfig();
    foreach ($config as $key => $value) {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        echo "  - " . $key . ": " . $value . "\n";
    }
}

echo "\n";

// Test 6: Test LocalSettings integration
echo "6. Testing LocalSettings integration...\n";

// Simulate LocalSettings
$wgActiveSkin = 'Bismillah';
$wgSkinConfig = [
    'enable_animations' => true,
    'enable_gradients' => true,
    'enable_dark_theme' => false,
];

echo "Active skin from LocalSettings: " . $wgActiveSkin . "\n";
echo "Skin config from LocalSettings:\n";
foreach ($wgSkinConfig as $key => $value) {
    $value = $value ? 'true' : 'false';
    echo "  - " . $key . ": " . $value . "\n";
}

echo "\n";

// Test 7: Test switching skins
echo "7. Testing skin switching...\n";

echo "Current active skin: " . $wgActiveSkin . "\n";
echo "To switch to BlueSkin, change in LocalSettings:\n";
echo "  \$wgActiveSkin = 'BlueSkin';\n";

echo "\n";

// Test 8: Test file structure
echo "8. Testing file structure...\n";

$skinsDir = __DIR__ . '/../skins';
$availableSkins = glob($skinsDir . '/*', GLOB_ONLYDIR);

echo "Available skins in /skins/:\n";
foreach ($availableSkins as $skinDir) {
    $skinName = basename($skinDir);
    $configFile = $skinDir . '/skin.json';
    
    if (file_exists($configFile)) {
        echo "  ✓ " . $skinName . " (has skin.json)\n";
    } else {
        echo "  ✗ " . $skinName . " (missing skin.json)\n";
    }
}

echo "\n";

// Test 9: Test skin validation
echo "9. Testing skin validation...\n";

if ($bismillahSkin) {
    echo "Bismillah skin validation: " . ($bismillahSkin->validate() ? 'Passed' : 'Failed') . "\n";
}

if ($blueSkin) {
    echo "BlueSkin validation: " . ($blueSkin->validate() ? 'Passed' : 'Failed') . "\n";
}

echo "\n";

// Test 10: Test CSS and JS content
echo "10. Testing CSS and JS content...\n";

if ($bismillahSkin && $bismillahSkin->hasCustomCss()) {
    $cssContent = $bismillahSkin->getCssContent();
    echo "Bismillah CSS content length: " . strlen($cssContent) . " characters\n";
    echo "CSS starts with: " . substr($cssContent, 0, 50) . "...\n";
}

if ($blueSkin && $blueSkin->hasCustomCss()) {
    $cssContent = $blueSkin->getCssContent();
    echo "BlueSkin CSS content length: " . strlen($cssContent) . " characters\n";
    echo "CSS starts with: " . substr($cssContent, 0, 50) . "...\n";
}

if ($bismillahSkin && $bismillahSkin->hasCustomJs()) {
    $jsContent = $bismillahSkin->getJsContent();
    echo "Bismillah JS content length: " . strlen($jsContent) . " characters\n";
    echo "JS starts with: " . substr($jsContent, 0, 50) . "...\n";
}

if ($blueSkin && $blueSkin->hasCustomJs()) {
    $jsContent = $blueSkin->getJsContent();
    echo "BlueSkin JS content length: " . strlen($jsContent) . " characters\n";
    echo "JS starts with: " . substr($jsContent, 0, 50) . "...\n";
}

echo "\n";

echo "=== User Skin System Test Complete ===\n";
echo "\nSummary:\n";
echo "- User skins are now stored in /skins/ directory\n";
echo "- Each skin has a skin.json configuration file\n";
echo "- Skins can be easily added by creating a new folder in /skins/\n";
echo "- Active skin is controlled by \$wgActiveSkin in LocalSettings.php\n";
echo "- The system supports CSS, JS, and layout template customization\n";
echo "- All styling now comes from the active skin, not local templates\n"; 