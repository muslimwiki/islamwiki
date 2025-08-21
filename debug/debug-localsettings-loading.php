<?php

/**
 * Debug script to check LocalSettings loading
 */

echo "<h1>🔍 LocalSettings Loading Debug</h1>";

// Test 1: Direct LocalSettings loading
echo "<h2>1. Direct LocalSettings Loading</h2>";
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
echo "<p>LocalSettings path: $localSettingsPath</p>";
echo "<p>File exists: " . (file_exists($localSettingsPath) ? 'Yes' : 'No') . "</p>";

if (file_exists($localSettingsPath)) {
    // Clear any existing globals
    unset($GLOBALS['wgValidSkins']);
    unset($GLOBALS['wgActiveSkin']);

    // Load LocalSettings
    require_once $localSettingsPath;

    global $wgValidSkins, $wgActiveSkin;
    echo "<p>wgValidSkins: " . var_export($wgValidSkins, true) . "</p>";
    echo "<p>wgActiveSkin: " . var_export($wgActiveSkin, true) . "</p>";
} else {
    echo "<p class='error'>LocalSettings.php not found!</p>";
}

// Test 2: Check if there are multiple LocalSettings files
echo "<h2>2. Multiple LocalSettings Check</h2>";
$possiblePaths = [
    __DIR__ . '/../LocalSettings.php',
    __DIR__ . '/../config/LocalSettings.php',
    __DIR__ . '/../src/LocalSettings.php',
    __DIR__ . '/../public/LocalSettings.php',
];

foreach ($possiblePaths as $path) {
    $exists = file_exists($path);
    echo "<p>$path: " . ($exists ? 'Exists' : 'Not found') . "</p>";
    if ($exists) {
        $content = file_get_contents($path);
        if (strpos($content, 'Muslim') !== false) {
            echo "<p style='color: green;'>✅ Contains 'Muslim'</p>";
        } else {
            echo "<p style='color: red;'>❌ Does not contain 'Muslim'</p>";
        }
    }
}

// Test 3: Check file modification time
echo "<h2>3. File Modification Time</h2>";
if (file_exists($localSettingsPath)) {
    $mtime = filemtime($localSettingsPath);
        $temp_599db532 = date('Y-m-d H:i:s', $mtime) . "</p>";
        echo "<p>LocalSettings.php last modified: " . $temp_599db532;
    echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
}

// Test 4: Check file content directly
echo "<h2>4. File Content Check</h2>";
if (file_exists($localSettingsPath)) {
    $content = file_get_contents($localSettingsPath);
    if (preg_match('/\$wgValidSkins\s*=\s*\[(.*?)\];/s', $content, $matches)) {
        echo "<p>Found wgValidSkins definition:</p>";
        echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";
    } else {
        echo "<p class='error'>Could not find wgValidSkins definition in file</p>";
    }
}
