<?php

/**
 * Comprehensive Skin Debug Script
 *
 * This script tests all aspects of the skin loading system to identify issues.
 *
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

// Define the application's base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('Autoload file not found. Please run `composer install` to install the project dependencies.');
}

// Load environment variables from .env file
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

echo "<h1>🔍 Comprehensive Skin Debug</h1>\n";
echo "<h2>Testing Skin Loading System</h2>\n";

try {
    // Test 1: LocalSettings Loading (before Application creation)
    echo "<h3>1. LocalSettings Configuration</h3>\n";
    $localSettingsPath = BASE_PATH . '/LocalSettings.php';
    if (file_exists($localSettingsPath)) {
        require_once $localSettingsPath;
        echo "✅ LocalSettings.php loaded<br>\n";

        global $wgValidSkins, $wgActiveSkin;
        $temp_f98e4715 = (isset($wgValidSkins) ? implode(', ', array_keys($wgValidSkins)) : 'Not set') . "<br>\n";
        echo "wgValidSkins: " . $temp_f98e4715;
        echo "wgActiveSkin: " . ($wgActiveSkin ?? 'Not set') . "<br>\n";
    } else {
        echo "❌ LocalSettings.php not found<br>\n";
    }

    // Test 2: Application Creation
    echo "<h3>2. Application Creation</h3>\n";
    $app = new NizamApplication(BASE_PATH);
    echo "✅ Application created successfully<br>\n";

    // Test 3: Container Access
    echo "<h3>3. Container Access</h3>\n";
    $container = $app->getContainer();
    echo "✅ Container accessed successfully<br>\n";

    // Test 4: Skin Manager Creation
    echo "<h3>4. Skin Manager Creation</h3>\n";
    $skinManager = new SkinManager($app);
    echo "✅ SkinManager created successfully<br>\n";

    // Test 5: Available Skins
    echo "<h3>5. Available Skins</h3>\n";
    $skins = $skinManager->getSkins();
    echo "Loaded skins: " . implode(', ', array_keys($skins)) . "<br>\n";

    // Test 6: Active Skin
    echo "<h3>6. Active Skin</h3>\n";
    $activeSkin = $skinManager->getActiveSkin();
    if ($activeSkin) {
        $temp_2d93df22 = $activeSkin->getName() . " (v" . $activeSkin->getVersion() . ")<br>\n";
        echo "✅ Active skin: " . $temp_2d93df22;
    } else {
        echo "❌ No active skin found<br>\n";
    }

    // Test 7: Skin Files
    echo "<h3>7. Skin Files Check</h3>\n";
    $skinsPath = BASE_PATH . '/skins';
    if (is_dir($skinsPath)) {
        echo "✅ Skins directory exists<br>\n";
        $skinDirs = glob($skinsPath . '/*', GLOB_ONLYDIR);
        $temp_a702ca64 = implode(', ', array_map('basename', $skinDirs)) . "<br>\n";
        echo "Skin directories found: " . $temp_a702ca64;

        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $configFile = $skinDir . '/skin.json';
            $cssFile = $skinDir . '/css/bismillah.css';
            $jsFile = $skinDir . '/js/bismillah.js';
            $layoutFile = $skinDir . '/templates/layout.twig';

            echo "Checking $skinName:<br>\n";
            $temp_5101729a = (file_exists($configFile) ? '✅' : '❌') . " $configFile<br>\n";
            echo "  - Config: " . $temp_5101729a;
            $temp_a9c1c367 = (file_exists($cssFile) ? '✅' : '❌') . " $cssFile<br>\n";
            echo "  - CSS: " . $temp_a9c1c367;
            $temp_78a5b8fa = (file_exists($jsFile) ? '✅' : '❌') . " $jsFile<br>\n";
            echo "  - JS: " . $temp_78a5b8fa;
            $temp_2d60582d = (file_exists($layoutFile) ? '✅' : '❌') . " $layoutFile<br>\n";
            echo "  - Layout: " . $temp_2d60582d;
        }
    } else {
        echo "❌ Skins directory not found<br>\n";
    }

    // Test 8: Container Services
    echo "<h3>8. Container Services</h3>\n";
    $services = ['skin.manager', 'session', 'db', 'view'];
    foreach ($services as $service) {
        if ($container->has($service)) {
            echo "✅ $service available<br>\n";
        } else {
            echo "❌ $service not available<br>\n";
        }
    }

    // Test 9: User Skin Settings (if logged in)
    echo "<h3>9. User Skin Settings</h3>\n";
    if ($container->has('session')) {
        $session = $container->get('session');
        if ($session->isLoggedIn()) {
            $userId = $session->getUserId();
            $userSkin = $skinManager->getActiveSkinForUser($userId);
            $userSkinName = $skinManager->getActiveSkinNameForUser($userId);

            echo "✅ User logged in (ID: $userId)<br>\n";
            echo "User's active skin: " . ($userSkinName ?: 'default') . "<br>\n";
        } else {
            echo "ℹ️ User not logged in<br>\n";
        }
    } else {
        echo "❌ Session service not available<br>\n";
    }

    // Test 10: Skin Validation
    echo "<h3>10. Skin Validation</h3>\n";
    foreach ($skins as $name => $skin) {
        $isValid = $skin->validate();
        echo "$name: " . ($isValid ? '✅ Valid' : '❌ Invalid') . "<br>\n";

        if (!$isValid) {
            echo "  - Name: " . $skin->getName() . "<br>\n";
            echo "  - Version: " . $skin->getVersion() . "<br>\n";
            echo "  - Path: " . $skin->getSkinPath() . "<br>\n";
        }
    }

    echo "<h2>🎉 Debug Complete</h2>\n";
} catch (\Throwable $e) {
    echo "<h2>❌ Error Occurred</h2>\n";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>\n";
        $temp_9b288771 = $e->getFile() . ":" . $e->getLine() . "</p>\n";
        echo "<p><strong>File:</strong> " . $temp_9b288771;
    echo "<p><strong>Stack Trace:</strong></p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}
