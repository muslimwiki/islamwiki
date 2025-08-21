<?php

// Test to check UserSkin loading
echo "<h1>UserSkin Loading Test</h1>";

// Test loading BlueSkin configuration
$blueSkinDir = __DIR__ . '/../skins/BlueSkin';
$skinConfigFile = $blueSkinDir . '/skin.json';

echo "<h2>1. Loading BlueSkin configuration</h2>";
if (file_exists($skinConfigFile)) {
    $config = json_decode(file_get_contents($skinConfigFile), true);
    echo "JSON loaded successfully<br>";
    echo "Config: " . print_r($config, true) . "<br>";

    // Test if we can create a UserSkin instance
    echo "<h2>2. Testing UserSkin instantiation</h2>";
    try {
        // Include the UserSkin class
        require_once __DIR__ . '/../src/Skins/UserSkin.php';
        require_once __DIR__ . '/../src/Skins/Skin.php';

        // Create a mock skin instance
        $skin = new \IslamWiki\Skins\UserSkin($config, $blueSkinDir);

        echo "✅ UserSkin created successfully<br>";
        echo "Name: " . $skin->getName() . "<br>";
        echo "Version: " . $skin->getName() . "<br>";
        echo "Author: " . $skin->getAuthor() . "<br>";
        echo "Description: " . $skin->getDescription() . "<br>";

        // Test validation
        echo "Validation: " . ($skin->validate() ? '✅ Valid' : '❌ Invalid') . "<br>";
    } catch (Exception $e) {
        echo "❌ Error creating UserSkin: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
} else {
    echo "❌ skin.json not found<br>";
}

echo "<h2>3. Testing CSS and JS files</h2>";
$cssPath = $blueSkinDir . '/css/bismillah.css';
$jsPath = $blueSkinDir . '/js/bismillah.js';

echo "CSS file: $cssPath<br>";
echo "CSS exists: " . (file_exists($cssPath) ? 'Yes' : 'No') . "<br>";

echo "JS file: $jsPath<br>";
echo "JS exists: " . (file_exists($jsPath) ? 'Yes' : 'No') . "<br>";
