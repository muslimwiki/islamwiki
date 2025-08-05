<?php
/**
 * Test script to debug SettingsController specifically
 */

// Load the application
require_once __DIR__ . '/../vendor/autoload.php';

echo "<h1>🔍 SettingsController Debug Test</h1>";

try {
    // Initialize the application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    echo "<h2>1. Application initialized</h2>";
    echo "<p>✓ Application created successfully</p>";
    
    // Get the container
    $container = $app->getContainer();
    echo "<h2>2. Container obtained</h2>";
    echo "<p>✓ Container retrieved successfully</p>";
    
    // Get the database connection
    $db = $container->get('db');
    echo "<h2>3. Database connection obtained</h2>";
    echo "<p>✓ Database connection retrieved successfully</p>";
    
    // Mock session data
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['is_admin'] = false;
    $_SESSION['logged_in_at'] = time();
    
    echo "<h2>4. Session data set</h2>";
    echo "<p>✓ Session data set to simulate logged in user</p>";
    
    // Test the exact logic from SettingsController
    echo "<h2>5. Testing SettingsController Logic</h2>";
    
    // Load LocalSettings.php to get available skins
    $localSettingsPath = __DIR__ . '/../LocalSettings.php';
    echo "<p>LocalSettings path: $localSettingsPath</p>";
    
    if (file_exists($localSettingsPath)) {
        echo "<p>✓ LocalSettings.php exists</p>";
        require_once $localSettingsPath;
        echo "<p>✓ LocalSettings.php loaded</p>";
    } else {
        echo "<p>❌ LocalSettings.php not found</p>";
    }
    
    // Get available skins from LocalSettings.php
    global $wgValidSkins;
    echo "<p>After global statement - wgValidSkins: " . var_export($wgValidSkins, true) . "</p>";
    
    $availableSkins = $wgValidSkins ?? [];
    echo "<p>availableSkins: " . var_export($availableSkins, true) . "</p>";
    
    if (isset($wgValidSkins) && is_array($wgValidSkins)) {
        echo "<p>✓ wgValidSkins is set with " . count($wgValidSkins) . " skins</p>";
        foreach ($wgValidSkins as $key => $value) {
            echo "<p>  - $key => $value</p>";
        }
    } else {
        echo "<p>❌ wgValidSkins is not set or not an array</p>";
    }
    
    // Get skin manager to load skin details
    $skinManager = $container->get('skin.manager');
    echo "<p>✓ Skin Manager retrieved</p>";
    
    $loadedSkins = $skinManager->getSkins();
    echo "<p>Loaded skins count: " . count($loadedSkins) . "</p>";
    
    if (!empty($loadedSkins)) {
        echo "<p>✓ Loaded skins:</p>";
        foreach ($loadedSkins as $key => $skin) {
            echo "<p>  - $key: " . $skin->getName() . " v" . $skin->getVersion() . "</p>";
        }
    }
    
    // Test the skin loading logic
    echo "<h2>6. Testing Skin Loading Logic</h2>";
    
    $skinOptions = [];
    
    // Only show skins that are defined in $wgValidSkins
    foreach ($availableSkins as $skinKey => $skinName) {
        echo "<p>Processing skin: $skinKey => $skinName</p>";
        
        // Check if the skin is loaded by the skin manager (case-insensitive)
        $lowerSkinName = strtolower($skinName);
        echo "<p>  Looking for: $lowerSkinName</p>";
        
        if (isset($loadedSkins[$lowerSkinName])) {
            $skin = $loadedSkins[$lowerSkinName];
            echo "<p>  ✓ Found skin: " . $skin->getName() . "</p>";
            
            $skinOptions[$skinName] = [
                'name' => $skin->getName(),
                'version' => $skin->getVersion(),
                'author' => $skin->getAuthor(),
                'description' => $skin->getDescription(),
                'active' => false, // We'll set this later
                'css_key' => $lowerSkinName
            ];
            
            echo "<p>  ✓ Added to skinOptions</p>";
        } else {
            echo "<p>  ❌ Skin not found in loadedSkins</p>";
        }
    }
    
    echo "<h2>7. Final Results</h2>";
    echo "<p>skinOptions count: " . count($skinOptions) . "</p>";
    
    if (!empty($skinOptions)) {
        echo "<p>✓ Skin options:</p>";
        foreach ($skinOptions as $name => $skin) {
            echo "<p>  - $name: " . $skin['name'] . " v" . $skin['version'] . "</p>";
        }
    } else {
        echo "<p>❌ No skin options generated</p>";
    }
    
} catch (\Throwable $e) {
    echo "<h2>❌ Error occurred</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 