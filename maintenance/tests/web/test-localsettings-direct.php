<?php

// Direct test of LocalSettings.php loading
echo "<h1>Direct LocalSettings.php Test</h1>";

// Test the path that SettingsController uses
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
echo "<p>LocalSettings path: $localSettingsPath</p>";
echo "<p>File exists: " . (file_exists($localSettingsPath) ? 'true' : 'false') . "</p>";

if (file_exists($localSettingsPath)) {
    // Try to load it
    try {
        require_once $localSettingsPath;
        echo "<p>✅ LocalSettings.php loaded successfully</p>";

        // Check if $wgValidSkins is set
        global $wgValidSkins;
        echo "<p>wgValidSkins: " . var_export($wgValidSkins, true) . "</p>";
        echo "<p>isset(wgValidSkins): " . (isset($wgValidSkins) ? 'true' : 'false') . "</p>";
        echo "<p>is_array(wgValidSkins): " . (is_array($wgValidSkins) ? 'true' : 'false') . "</p>";

        if (isset($wgValidSkins) && is_array($wgValidSkins)) {
            echo "<p>✅ wgValidSkins is set with " . count($wgValidSkins) . " skins:</p>";
            foreach ($wgValidSkins as $key => $value) {
                echo "<p>  - $key => $value</p>";
            }
        } else {
            echo "<p>❌ wgValidSkins is not set or not an array</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error loading LocalSettings.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ LocalSettings.php not found</p>";
}
