<?php

// Test LocalSettings.php path resolution
echo "<h1>LocalSettings.php Path Test</h1>";

// Test the path from SettingsController perspective
$controllerPath = __DIR__ . '/../src/Http/Controllers/SettingsController.php';
echo "<p>Controller path: $controllerPath</p>";

// Simulate the path resolution from SettingsController
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
echo "<p>LocalSettings path: $localSettingsPath</p>";
echo "<p>File exists: " . (file_exists($localSettingsPath) ? 'true' : 'false') . "</p>";

if (file_exists($localSettingsPath)) {
    require_once $localSettingsPath;
    echo "<p>✅ LocalSettings.php loaded successfully</p>";

    global $wgValidSkins;
    echo "<p>wgValidSkins: " . var_export($wgValidSkins, true) . "</p>";
} else {
    echo "<p>❌ LocalSettings.php not found</p>";
}
