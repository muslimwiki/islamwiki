<?php
// Simple test to check BlueSkin loading
echo "<h1>BlueSkin Test</h1>";

// Check if BlueSkin directory exists
$blueSkinDir = __DIR__ . '/../skins/BlueSkin';
echo "<h2>1. Directory Check</h2>";
echo "BlueSkin directory: $blueSkinDir<br>";
echo "Directory exists: " . (is_dir($blueSkinDir) ? 'Yes' : 'No') . "<br>";

// Check skin.json
$skinJsonFile = $blueSkinDir . '/skin.json';
echo "<h2>2. Skin.json Check</h2>";
echo "skin.json file: $skinJsonFile<br>";
echo "File exists: " . (file_exists($skinJsonFile) ? 'Yes' : 'No') . "<br>";

if (file_exists($skinJsonFile)) {
    $config = json_decode(file_get_contents($skinJsonFile), true);
    echo "JSON valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "<br>";
    if ($config) {
        echo "Skin name: " . ($config['name'] ?? 'Not set') . "<br>";
        echo "Skin version: " . ($config['version'] ?? 'Not set') . "<br>";
    }
}

// Check LocalSettings.php
echo "<h2>3. LocalSettings.php Check</h2>";
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
if (file_exists($localSettingsPath)) {
    // Try to load without Dotenv
    $content = file_get_contents($localSettingsPath);
    
    // Extract $wgValidSkins manually
    if (preg_match('/\$wgValidSkins\s*=\s*\[(.*?)\];/s', $content, $matches)) {
        echo "Found \$wgValidSkins in LocalSettings.php<br>";
        echo "Content: " . htmlspecialchars($matches[1]) . "<br>";
    } else {
        echo "Could not find \$wgValidSkins in LocalSettings.php<br>";
    }
} else {
    echo "LocalSettings.php not found<br>";
}

echo "<h2>4. Manual Directory Scan</h2>";
$skinsDir = __DIR__ . '/../skins';
if (is_dir($skinsDir)) {
    $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
    echo "All skin directories:<br>";
    foreach ($skinDirs as $skinDir) {
        $skinName = basename($skinDir);
        echo "- $skinName<br>";
    }
}
?> 