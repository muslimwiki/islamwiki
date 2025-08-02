<?php
// Test script to verify dynamic skin discovery
echo "<h1>Skin Discovery Test</h1>";

// Test the dynamic skin discovery logic
$skinsDir = __DIR__ . '/../skins';
$availableSkins = [];

echo "<h2>1. Checking skins directory</h2>";
echo "Skins directory: $skinsDir<br>";
echo "Directory exists: " . (is_dir($skinsDir) ? 'Yes' : 'No') . "<br>";

if (is_dir($skinsDir)) {
    $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
    echo "Found " . count($skinDirs) . " skin directories:<br>";
    
    foreach ($skinDirs as $skinDir) {
        $skinName = basename($skinDir);
        $availableSkins[$skinName] = $skinName;
        echo "- $skinName<br>";
    }
}

echo "<h2>2. Available skins array</h2>";
echo "Available skins: " . implode(', ', array_keys($availableSkins)) . "<br>";

echo "<h2>3. Testing with LocalSettings.php</h2>";
// Load LocalSettings.php
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
if (file_exists($localSettingsPath)) {
    require_once $localSettingsPath;
}

global $wgValidSkins;
echo "wgValidSkins from LocalSettings: ";
if (isset($wgValidSkins) && is_array($wgValidSkins)) {
    echo implode(', ', array_keys($wgValidSkins));
} else {
    echo "Not set or not an array";
}
echo "<br>";

echo "<h2>4. Final result</h2>";
if (empty($wgValidSkins)) {
    echo "Using dynamic discovery fallback<br>";
    echo "Skins available: " . implode(', ', array_keys($availableSkins));
} else {
    echo "Using LocalSettings.php configuration<br>";
    echo "Skins available: " . implode(', ', array_keys($wgValidSkins));
}
?> 