<?php

// Test skin loading process
require_once __DIR__ . '/../src/Core/NizamApplication.php';
require_once __DIR__ . '/../src/Skins/SkinManager.php';
require_once __DIR__ . '/../src/Skins/UserSkin.php';

echo "<h1>Skin Loading Debug Test</h1>";

try {
    // Create application
    $app = new \IslamWiki\Core\Application();
    $container = $app->getContainer();

    // Get SkinManager
    $skinManager = $container->get('skin.manager');

    echo "✅ SkinManager loaded<br>";

    // Load skins
    $skins = $skinManager->loadSkins();

    echo "✅ loadSkins() called<br>";
    echo "Total skins loaded: " . count($skins) . "<br>";

    foreach ($skins as $name => $skin) {
        echo "Skin: $name<br>";
        echo "- Name: " . $skin->getName() . "<br>";
        echo "- Version: " . $skin->getVersion() . "<br>";
        echo "- Valid: " . ($skin->validate() ? 'Yes' : 'No') . "<br>";
        echo "<br>";
    }

    // Check LocalSettings.php
    echo "<h2>LocalSettings.php Check</h2>";
    global $wgValidSkins;
    echo "wgValidSkins: ";
    var_dump($wgValidSkins);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}
