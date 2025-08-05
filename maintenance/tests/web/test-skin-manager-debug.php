<?php
// Detailed test to debug SkinManager loading
echo "<h1>SkinManager Debug Test</h1>";

// Test the exact logic from SkinManager
$skinsPath = __DIR__ . '/../skins';
echo "<h2>1. Skins Path</h2>";
echo "Skins path: $skinsPath<br>";
echo "Directory exists: " . (is_dir($skinsPath) ? 'Yes' : 'No') . "<br>";

// Load LocalSettings.php manually
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
if (file_exists($localSettingsPath)) {
    $content = file_get_contents($localSettingsPath);
    
    // Extract $wgValidSkins manually
    if (preg_match('/\$wgValidSkins\s*=\s*\[(.*?)\];/s', $content, $matches)) {
        echo "<h2>2. LocalSettings Configuration</h2>";
        echo "Found \$wgValidSkins configuration<br>";
        echo "Raw content: " . htmlspecialchars($matches[1]) . "<br>";
        
        // Parse the array manually
        $validSkins = [];
        if (preg_match_all('/\'(.*?)\'\s*=>\s*\'(.*?)\'/', $matches[1], $skinMatches)) {
            for ($i = 0; $i < count($skinMatches[1]); $i++) {
                $key = $skinMatches[1][$i];
                $value = $skinMatches[2][$i];
                $validSkins[$key] = $value;
            }
        }
        
        echo "Parsed valid skins: ";
        foreach ($validSkins as $key => $value) {
            echo "$key => $value, ";
        }
        echo "<br>";
        
        echo "<h2>3. Skin Directory Check</h2>";
        $skinDirs = [];
        foreach ($validSkins as $skinKey => $skinName) {
            $skinDir = $skinsPath . '/' . $skinName;
            echo "Checking '$skinName' at path: $skinDir<br>";
            echo "Directory exists: " . (is_dir($skinDir) ? 'Yes' : 'No') . "<br>";
            
            if (is_dir($skinDir)) {
                $skinDirs[] = $skinDir;
            }
        }
        
        echo "<h2>4. Skin Loading Test</h2>";
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $skinConfigFile = $skinDir . '/skin.json';
            
            echo "Processing skin: $skinName<br>";
            echo "Config file: $skinConfigFile<br>";
            echo "Config file exists: " . (file_exists($skinConfigFile) ? 'Yes' : 'No') . "<br>";
            
            if (file_exists($skinConfigFile)) {
                try {
                    $config = json_decode(file_get_contents($skinConfigFile), true);
                    echo "JSON valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "<br>";
                    
                    if ($config && isset($config['name'])) {
                        echo "Skin name: " . $config['name'] . "<br>";
                        echo "Skin version: " . ($config['version'] ?? 'Not set') . "<br>";
                        
                        // Test validation
                        $name = $config['name'] ?? '';
                        $version = $config['version'] ?? '';
                        $valid = !empty($name) && !empty($version);
                        echo "Validation result: " . ($valid ? '✅ Valid' : '❌ Invalid') . "<br>";
                        
                        if ($valid) {
                            echo "✅ Would be loaded as: " . strtolower($skinName) . "<br>";
                        } else {
                            echo "❌ Would be skipped due to validation failure<br>";
                        }
                    } else {
                        echo "❌ Invalid config - missing name<br>";
                    }
                } catch (Exception $e) {
                    echo "❌ Error loading skin: " . $e->getMessage() . "<br>";
                }
            }
            echo "<br>";
        }
    } else {
        echo "Could not find \$wgValidSkins in LocalSettings.php<br>";
    }
} else {
    echo "LocalSettings.php not found<br>";
}
?> 