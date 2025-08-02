<?php
/**
 * Simple test script to verify skin loading
 */

// Load LocalSettings.php
require_once __DIR__ . '/../LocalSettings.php';

echo "<h1>Simple Skin Loading Test</h1>\n";
echo "<pre>\n";

// Check LocalSettings variables
global $wgActiveSkin, $wgValidSkins;
echo "Active Skin from LocalSettings: " . $wgActiveSkin . "\n";
echo "Valid Skins: " . print_r($wgValidSkins, true) . "\n\n";

// Check if Bismillah skin directory exists
$skinPath = __DIR__ . '/../skins/Bismillah';
echo "Bismillah skin path: $skinPath\n";
echo "Directory exists: " . (is_dir($skinPath) ? 'YES' : 'NO') . "\n";

// Check skin.json
$skinConfigPath = $skinPath . '/skin.json';
echo "Skin config path: $skinConfigPath\n";
echo "Config file exists: " . (file_exists($skinConfigPath) ? 'YES' : 'NO') . "\n";

if (file_exists($skinConfigPath)) {
    $config = json_decode(file_get_contents($skinConfigPath), true);
    echo "Config loaded: " . ($config ? 'YES' : 'NO') . "\n";
    if ($config) {
        echo "Skin name: " . ($config['name'] ?? 'NOT SET') . "\n";
        echo "Skin version: " . ($config['version'] ?? 'NOT SET') . "\n";
    }
}

// Check CSS file
$cssPath = $skinPath . '/css/style.css';
echo "\nCSS path: $cssPath\n";
echo "CSS file exists: " . (file_exists($cssPath) ? 'YES' : 'NO') . "\n";

// Check JS file
$jsPath = $skinPath . '/js/script.js';
echo "JS path: $jsPath\n";
echo "JS file exists: " . (file_exists($jsPath) ? 'YES' : 'NO') . "\n";

// Check layout file
$layoutPath = $skinPath . '/templates/layout.twig';
echo "Layout path: $layoutPath\n";
echo "Layout file exists: " . (file_exists($layoutPath) ? 'YES' : 'NO') . "\n";

echo "</pre>\n"; 