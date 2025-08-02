<?php
// Test path comparison
echo "<h1>Path Comparison Test</h1>";

// Path used by SettingsController
$controllerPath = __DIR__ . '/../src/Http/Controllers/../../../LocalSettings.php';
echo "<p>Controller path: $controllerPath</p>";
echo "<p>Controller path exists: " . (file_exists($controllerPath) ? 'true' : 'false') . "</p>";

// Path used by test script
$testPath = __DIR__ . '/../LocalSettings.php';
echo "<p>Test path: $testPath</p>";
echo "<p>Test path exists: " . (file_exists($testPath) ? 'true' : 'false') . "</p>";

// Check if they're the same
echo "<p>Paths are same: " . ($controllerPath === $testPath ? 'true' : 'false') . "</p>";

// Test loading from controller path
if (file_exists($controllerPath)) {
    try {
        require_once $controllerPath;
        global $wgValidSkins;
        echo "<p>✅ Controller path loaded successfully</p>";
        echo "<p>wgValidSkins: " . var_export($wgValidSkins, true) . "</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error loading from controller path: " . $e->getMessage() . "</p>";
    }
}
?> 