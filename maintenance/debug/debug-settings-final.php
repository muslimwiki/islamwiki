<?php

/**
 * Final debug script to check settings page
 */

// Simulate a logged-in user session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['logged_in'] = true;

echo "=== Final Settings Debug ===\n\n";

try {
    // Load LocalSettings.php
    $localSettingsPath = __DIR__ . '/../LocalSettings.php';
    require_once $localSettingsPath;

    // Check global variables
    global $wgValidSkins;
    echo "1. \$wgValidSkins from LocalSettings:\n";
    if (isset($wgValidSkins)) {
        echo "   ✓ Set with " . count($wgValidSkins) . " skins\n";
        foreach ($wgValidSkins as $key => $value) {
            echo "   - $key => $value\n";
        }
    } else {
        echo "   ✗ NOT set\n";
    }

    // Initialize application
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get skin manager
    $skinManager = $container->get('skin.manager');
    $loadedSkins = $skinManager->getSkins();

    echo "\n2. Loaded skins from SkinManager:\n";
    foreach ($loadedSkins as $name => $skin) {
        echo "   - $name: {$skin->getName()} v{$skin->getVersion()}\n";
    }

    echo "\n3. Testing skin matching:\n";
    $skinOptions = [];
    foreach ($wgValidSkins as $skinKey => $skinName) {
        $lowerSkinName = strtolower($skinName);
        if (isset($loadedSkins[$lowerSkinName])) {
            $skin = $loadedSkins[$lowerSkinName];
            $skinOptions[$skinName] = [
                'name' => $skin->getName(),
                'version' => $skin->getVersion(),
                'author' => $skin->getAuthor(),
                'description' => $skin->getDescription(),
                'active' => false,
                'css_key' => $lowerSkinName
            ];
            echo "   ✓ Matched $skinName -> $lowerSkinName\n";
        } else {
            echo "   ✗ No match for $skinName -> $lowerSkinName\n";
        }
    }

    echo "\n4. Final skin options:\n";
    echo "   - Count: " . count($skinOptions) . "\n";
    foreach ($skinOptions as $name => $options) {
        echo "   - $name: {$options['name']} v{$options['version']} by {$options['author']}\n";
    }

    echo "\n5. Template should receive " . count($skinOptions) . " skin(s)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
