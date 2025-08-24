<?php

/**
 * Debug Skin CSS Loading
 *
 * Tests if the skin CSS is being loaded properly by the SkinServiceProvider
 *
 * @category  Debug
 * @package   IslamWiki
 * @author    IslamWiki Development Team
 * @license   MIT
 * @link      https://islam.wiki
 * @since     0.0.1
 */

// Load the autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load LocalSettings
require_once __DIR__ . '/../../LocalSettings.php';

// Load helpers
require_once __DIR__ . '/../../src/helpers.php';

// Create application
$app = new \IslamWiki\Core\Application . '/../..');

// Get container
$container = $app->getContainer();

echo "<h1>Debug Skin CSS Loading</h1>";

// Test 1: Check if SkinServiceProvider is loaded
echo "<h2>1. Checking SkinServiceProvider</h2>";
try {
    $skinManager = $container->get('skin.manager');
    echo "<p>✅ SkinManager loaded successfully</p>";
    echo "<p>SkinManager class: " . get_class($skinManager) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Failed to load SkinManager: " . $e->getMessage() . "</p>";
}

// Test 2: Check available skins
echo "<h2>2. Checking Available Skins</h2>";
try {
    $availableSkins = $skinManager->getAvailableSkinNames();
    echo "<p>Available skins: " . implode(', ', $availableSkins) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Failed to get available skins: " . $e->getMessage() . "</p>";
}

// Test 3: Check active skin
echo "<h2>3. Checking Active Skin</h2>";
try {
    $activeSkinName = $skinManager->getActiveSkinName();
    echo "<p>Active skin name: " . $activeSkinName . "</p>";

    $activeSkin = $skinManager->getActiveSkin();
    if ($activeSkin) {
        echo "<p>✅ Active skin loaded: " . $activeSkin->getName() . "</p>";
    } else {
        echo "<p>❌ Active skin is null</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Failed to get active skin: " . $e->getMessage() . "</p>";
}

// Test 4: Check skin CSS
echo "<h2>4. Checking Skin CSS</h2>";
try {
    $skinCss = $skinManager->getActiveSkinCss();
    echo "<p>Skin CSS length: " . strlen($skinCss) . " characters</p>";
    if (strlen($skinCss) > 0) {
        echo "<p>✅ Skin CSS loaded successfully</p>";
        $firstChars = htmlspecialchars(substr($skinCss, 0, 200));
        echo "<p>First 200 characters: " . $firstChars . "...</p>";
    } else {
        echo "<p>❌ Skin CSS is empty</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Failed to get skin CSS: " . $e->getMessage() . "</p>";
}

// Test 5: Check skin data from container
echo "<h2>5. Checking Skin Data from Container</h2>";
try {
    $skinData = $container->get('skin.data');
    echo "<p>Skin data: " . print_r($skinData, true) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Failed to get skin data: " . $e->getMessage() . "</p>";
}

// Test 6: Check view globals
echo "<h2>6. Checking View Globals</h2>";
try {
    $viewRenderer = $container->get('view');
    $globals = $viewRenderer->getGlobals();
    $globalKeys = implode(', ', array_keys($globals));
    echo "<p>View globals keys: " . $globalKeys . "</p>";

    if (isset($globals['skin_css'])) {
        echo "<p>✅ skin_css found in view globals</p>";
        $temp_f5092071 = strlen($globals['skin_css']) . " characters</p>";
        echo "<p>skin_css length: " . $temp_f5092071;
    } else {
        echo "<p>❌ skin_css not found in view globals</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Failed to get view globals: " . $e->getMessage() . "</p>";
}

echo "<h2>Debug Complete</h2>";
