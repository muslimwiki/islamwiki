<?php
// Test script to verify skin configuration logic
echo "<h1>Skin Configuration Test</h1>";

// Simulate the SettingsController logic
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
if (file_exists($localSettingsPath)) {
    require_once $localSettingsPath;
}

global $wgValidSkins;
echo "<h2>1. wgValidSkins from LocalSettings.php</h2>";
if (isset($wgValidSkins) && is_array($wgValidSkins)) {
    echo "Available skins: " . implode(', ', array_keys($wgValidSkins)) . "<br>";
} else {
    echo "wgValidSkins is not set or not an array<br>";
}

echo "<h2>2. Testing the logic</h2>";
$availableSkins = $wgValidSkins ?? [];

echo "Initial availableSkins: ";
if (empty($availableSkins)) {
    echo "Empty array<br>";
} else {
    echo implode(', ', array_keys($availableSkins)) . "<br>";
}

// Test the fallback logic
if (!isset($wgValidSkins)) {
    echo "wgValidSkins is not set, using dynamic discovery<br>";
    $skinsDir = __DIR__ . '/../skins';
    $availableSkins = [];
    
    if (is_dir($skinsDir)) {
        $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $availableSkins[$skinName] = $skinName;
        }
    }
    
    echo "Dynamic discovery found: " . implode(', ', array_keys($availableSkins)) . "<br>";
} else {
    echo "wgValidSkins is set, using LocalSettings configuration<br>";
}

echo "<h2>3. Final result</h2>";
echo "Final available skins: " . implode(', ', array_keys($availableSkins)) . "<br>";
?> 