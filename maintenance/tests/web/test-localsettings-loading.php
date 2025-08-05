<?php
/**
 * Test script to check LocalSettings.php loading
 */

echo "<h1>🔍 LocalSettings.php Loading Test</h1>";

try {
    // Check if LocalSettings.php exists
    $localSettingsPath = __DIR__ . '/../LocalSettings.php';
    echo "<h2>1. Checking LocalSettings.php</h2>";
    echo "<p>LocalSettings path: $localSettingsPath</p>";
    
    if (file_exists($localSettingsPath)) {
        echo "<p>✓ LocalSettings.php exists</p>";
        
        // Check file contents
        $contents = file_get_contents($localSettingsPath);
        if (strpos($contents, '$wgValidSkins') !== false) {
            echo "<p>✓ LocalSettings.php contains \$wgValidSkins</p>";
        } else {
            echo "<p>❌ LocalSettings.php does not contain \$wgValidSkins</p>";
        }
        
        // Load LocalSettings.php
        echo "<h2>2. Loading LocalSettings.php</h2>";
        require_once $localSettingsPath;
        echo "<p>✓ LocalSettings.php loaded</p>";
        
        // Check if $wgValidSkins is set
        echo "<h2>3. Checking \$wgValidSkins</h2>";
        global $wgValidSkins;
        
        if (isset($wgValidSkins)) {
            echo "<p>✓ \$wgValidSkins is set</p>";
            echo "<p>Type: " . gettype($wgValidSkins) . "</p>";
            
            if (is_array($wgValidSkins)) {
                echo "<p>✓ \$wgValidSkins is an array</p>";
                echo "<p>Count: " . count($wgValidSkins) . "</p>";
                
                foreach ($wgValidSkins as $key => $value) {
                    echo "<p>  - $key => $value</p>";
                }
            } else {
                echo "<p>❌ \$wgValidSkins is not an array</p>";
            }
        } else {
            echo "<p>❌ \$wgValidSkins is not set</p>";
        }
        
        // Check other global variables
        echo "<h2>4. Checking Other Global Variables</h2>";
        
        global $wgActiveSkin, $wgDefaultSkin;
        
        if (isset($wgActiveSkin)) {
            echo "<p>✓ \$wgActiveSkin is set: $wgActiveSkin</p>";
        } else {
            echo "<p>❌ \$wgActiveSkin is not set</p>";
        }
        
        if (isset($wgDefaultSkin)) {
            echo "<p>✓ \$wgDefaultSkin is set: $wgDefaultSkin</p>";
        } else {
            echo "<p>❌ \$wgDefaultSkin is not set</p>";
        }
        
    } else {
        echo "<p>❌ LocalSettings.php not found</p>";
    }
    
} catch (\Throwable $e) {
    echo "<h2>❌ Error occurred</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 