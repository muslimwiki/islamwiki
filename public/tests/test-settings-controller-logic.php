<?php
// Test that simulates the exact SettingsController logic
echo "<h1>SettingsController Logic Test</h1>";

// Simulate the SettingsController logic
$localSettingsPath = __DIR__ . '/../LocalSettings.php';
if (file_exists($localSettingsPath)) {
    $content = file_get_contents($localSettingsPath);
    
    // Extract $wgValidSkins manually
    if (preg_match('/\$wgValidSkins\s*=\s*\[(.*?)\];/s', $content, $matches)) {
        // Parse the array manually
        $availableSkins = [];
        if (preg_match_all('/\'(.*?)\'\s*=>\s*\'(.*?)\'/', $matches[1], $skinMatches)) {
            for ($i = 0; $i < count($skinMatches[1]); $i++) {
                $key = $skinMatches[1][$i];
                $value = $skinMatches[2][$i];
                $availableSkins[$key] = $value;
            }
        }
        
        echo "<h2>1. Available skins from LocalSettings</h2>";
        echo "Available skins: ";
        foreach ($availableSkins as $key => $value) {
            echo "$key => $value, ";
        }
        echo "<br>";
        
        // Simulate the SkinManager loaded skins
        $loadedSkins = [
            'bismillah' => 'Bismillah Skin Object',
            'blueskin' => 'BlueSkin Skin Object',
            'greenskin' => 'GreenSkin Skin Object'
        ];
        
        echo "<h2>2. Simulated SkinManager loaded skins</h2>";
        echo "Loaded skins: ";
        foreach ($loadedSkins as $key => $value) {
            echo "$key => $value, ";
        }
        echo "<br>";
        
        echo "<h2>3. SettingsController logic simulation</h2>";
        $skinOptions = [];
        
        // Only show skins that are defined in $wgValidSkins
        foreach ($availableSkins as $skinKey => $skinName) {
            // Check if the skin is loaded by the skin manager (case-insensitive)
            $lowerSkinName = strtolower($skinName);
            echo "Checking '$skinName' (lowercase: '$lowerSkinName'): ";
            
            if (isset($loadedSkins[$lowerSkinName])) {
                echo "✅ Found in SkinManager<br>";
                
                // Simulate skin object properties
                $skinOptions[$skinName] = [
                    'name' => $skinName,
                    'version' => '1.0.0',
                    'author' => 'IslamWiki Team',
                    'description' => 'A beautiful skin for IslamWiki',
                    'active' => false,
                    'css_key' => $lowerSkinName
                ];
            } else {
                echo "❌ Not found in SkinManager<br>";
            }
        }
        
        echo "<h2>4. Final skinOptions array</h2>";
        echo "skinOptions count: " . count($skinOptions) . "<br>";
        echo "skinOptions keys: " . implode(', ', array_keys($skinOptions)) . "<br>";
        
        foreach ($skinOptions as $skinName => $skinData) {
            echo "- $skinName: " . $skinData['name'] . " v" . $skinData['version'] . "<br>";
        }
    } else {
        echo "Could not find \$wgValidSkins in LocalSettings.php<br>";
    }
} else {
    echo "LocalSettings.php not found<br>";
}
?> 