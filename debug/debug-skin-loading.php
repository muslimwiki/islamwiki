<?php

// Debug script to check skin loading
require_once __DIR__ . '/../src/Core/Application.php';
require_once __DIR__ . '/../src/Core/Container.php';
require_once __DIR__ . '/../src/Skins/SkinManager.php';

echo "<h1>Skin Loading Debug</h1>";

// Load LocalSettings.php
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
if (file_exists($localSettingsPath)) {
    require_once $localSettingsPath;
}

global $wgValidSkins;
echo "<h2>1. LocalSettings.php Configuration</h2>";
echo "wgValidSkins: ";
if (isset($wgValidSkins) && is_array($wgValidSkins)) {
    echo implode(', ', array_keys($wgValidSkins));
} else {
    echo "Not set or not an array";
}
echo "<br>";

// Test skin manager
echo "<h2>2. Skin Manager Test</h2>";
try {
    $app = new \IslamWiki\Core\Application();
    $container = $app->getContainer();

    if ($container->has('skin.manager')) {
        $skinManager = $container->get('skin.manager');
        $loadedSkins = $skinManager->getSkins();

        echo "Loaded skins from SkinManager: ";
        if (!empty($loadedSkins)) {
            echo implode(', ', array_keys($loadedSkins));
        } else {
            echo "No skins loaded";
        }
        echo "<br>";

        // Check each skin from LocalSettings
        echo "<h3>3. Checking each skin from LocalSettings</h3>";
        foreach ($wgValidSkins as $skinKey => $skinName) {
            $lowerSkinName = strtolower($skinName);
            echo "Checking '$skinName' (lowercase: '$lowerSkinName'): ";

            if (isset($loadedSkins[$lowerSkinName])) {
                echo "✅ Found in SkinManager<br>";
            } else {
                echo "❌ Not found in SkinManager<br>";
            }
        }
    } else {
        echo "❌ Skin manager not available in container<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>4. SettingsController Logic Test</h2>";
$availableSkins = $wgValidSkins ?? [];
echo "Available skins from SettingsController logic: ";
if (!empty($availableSkins)) {
    echo implode(', ', array_keys($availableSkins));
} else {
    echo "Empty array";
}
echo "<br>";

// Test the fallback logic
if (!isset($wgValidSkins)) {
    echo "wgValidSkins is not set, would use dynamic discovery<br>";
} else {
    echo "wgValidSkins is set, using LocalSettings configuration<br>";
}
