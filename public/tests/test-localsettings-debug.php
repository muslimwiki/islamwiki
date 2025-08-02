<?php
// Test LocalSettings.php loading
echo "<h1>LocalSettings.php Debug Test</h1>";

// Load LocalSettings.php
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
echo "Loading LocalSettings.php from: $localSettingsPath<br>";

if (file_exists($localSettingsPath)) {
    echo "✅ LocalSettings.php exists<br>";
    require_once $localSettingsPath;
    
    // Check variables
    global $wgValidSkins, $wgActiveSkin, $wgDefaultSkin;
    
    echo "<h2>Variables Check</h2>";
    echo "wgValidSkins: ";
    var_dump($wgValidSkins);
    echo "<br><br>";
    
    echo "wgActiveSkin: ";
    var_dump($wgActiveSkin);
    echo "<br><br>";
    
    echo "wgDefaultSkin: ";
    var_dump($wgDefaultSkin);
    echo "<br><br>";
    
    // Check if BlueSkin directory exists
    $blueSkinDir = __DIR__ . '/../skins/BlueSkin';
    echo "BlueSkin directory exists: " . (is_dir($blueSkinDir) ? 'Yes' : 'No') . "<br>";
    
    if (is_dir($blueSkinDir)) {
        $configFile = $blueSkinDir . '/skin.json';
        echo "BlueSkin config file exists: " . (file_exists($configFile) ? 'Yes' : 'No') . "<br>";
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            echo "BlueSkin config valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "<br>";
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "JSON error: " . json_last_error_msg() . "<br>";
            }
        }
    }
    
} else {
    echo "❌ LocalSettings.php not found<br>";
}
?> 