<?php

// Test LocalSettings.php variable scope
echo "<h1>LocalSettings.php Variable Scope Test</h1>";

// Test the path that SettingsController uses
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
echo "<p>LocalSettings path: $localSettingsPath</p>";
echo "<p>File exists: " . (file_exists($localSettingsPath) ? 'true' : 'false') . "</p>";

if (file_exists($localSettingsPath)) {
    // Clear any existing variables
    unset($wgValidSkins);

    // Load the file
    try {
        require_once $localSettingsPath;
        echo "<p>✅ LocalSettings.php loaded successfully</p>";

        // Check if $wgValidSkins is set
        echo "<p>isset(\$wgValidSkins): " . (isset($wgValidSkins) ? 'true' : 'false') . "</p>";
        echo "<p>is_array(\$wgValidSkins): " . (is_array($wgValidSkins) ? 'true' : 'false') . "</p>";
        echo "<p>wgValidSkins: " . var_export($wgValidSkins, true) . "</p>";

        if (isset($wgValidSkins) && is_array($wgValidSkins)) {
            echo "<p>✅ wgValidSkins is set with " . count($wgValidSkins) . " skins:</p>";
            foreach ($wgValidSkins as $key => $value) {
                echo "<p>  - $key => $value</p>";
            }
        } else {
            echo "<p>❌ wgValidSkins is not set or not an array</p>";
        }

        // Check other variables
        echo "<p>wgActiveSkin: " . var_export($wgActiveSkin ?? 'not set', true) . "</p>";
        echo "<p>wgDefaultSkin: " . var_export($wgDefaultSkin ?? 'not set', true) . "</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error loading LocalSettings.php: " . $e->getMessage() . "</p>";
        echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
    }
} else {
    echo "<p>❌ LocalSettings.php not found</p>";
}
