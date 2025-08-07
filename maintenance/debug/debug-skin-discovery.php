<?php

/**
 * Debug script for skin discovery
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "<h1>🔍 Skin Discovery Debug</h1>";

// Load LocalSettings
require_once __DIR__ . '/../LocalSettings.php';
global $wgValidSkins;

echo "<h2>1. LocalSettings Configuration</h2>";
echo "<p>wgValidSkins: " . var_export($wgValidSkins, true) . "</p>";

// Check skins directory
$skinsPath = __DIR__ . '/../skins';
echo "<h2>2. Skins Directory</h2>";
echo "<p>Skins path: $skinsPath</p>";
echo "<p>Directory exists: " . (is_dir($skinsPath) ? 'Yes' : 'No') . "</p>";

if (is_dir($skinsPath)) {
    $skinDirs = glob($skinsPath . '/*', GLOB_ONLYDIR);
    echo "<p>Found directories:</p>";
    echo "<ul>";
    foreach ($skinDirs as $dir) {
        $skinName = basename($dir);
        echo "<li>$skinName</li>";
    }
    echo "</ul>";
}

// Check Muslim skin specifically
echo "<h2>3. Muslim Skin Check</h2>";
$muslimDir = $skinsPath . '/Muslim';
echo "<p>Muslim directory: $muslimDir</p>";
echo "<p>Directory exists: " . (is_dir($muslimDir) ? 'Yes' : 'No') . "</p>";

if (is_dir($muslimDir)) {
    $configFile = $muslimDir . '/skin.json';
    echo "<p>Config file: $configFile</p>";
    echo "<p>Config exists: " . (file_exists($configFile) ? 'Yes' : 'No') . "</p>";

    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        echo "<p>Config content:</p>";
        echo "<pre>" . json_encode($config, JSON_PRETTY_PRINT) . "</pre>";
    }
}

// Test skin manager loading logic
echo "<h2>4. Skin Manager Logic Test</h2>";
$validSkins = $wgValidSkins ?? [];
echo "<p>Valid skins: " . var_export($validSkins, true) . "</p>";

if (!empty($validSkins)) {
    echo "<p>Processing skins from wgValidSkins:</p>";
    foreach ($validSkins as $skinKey => $skinName) {
        $skinDir = $skinsPath . '/' . $skinName;
        echo "<p>Key: $skinKey, Name: $skinName, Dir: $skinDir</p>";
        echo "<p>Directory exists: " . (is_dir($skinDir) ? 'Yes' : 'No') . "</p>";
    }
} else {
    echo "<p>No valid skins defined, would use dynamic discovery</p>";
}
