<?php
/**
 * Test script to verify skin loading
 */

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Core/Container.php';
require_once __DIR__ . '/../src/Core/NizamApplication.php';
require_once __DIR__ . '/../src/Skins/SkinManager.php';
require_once __DIR__ . '/../src/Skins/Skin.php';
require_once __DIR__ . '/../src/Skins/UserSkin.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

// Initialize application
$app = new NizamApplication(__DIR__ . '/..');

// Create skin manager
$skinManager = new SkinManager($app);

// Debug information
echo "<h1>Skin Loading Test</h1>\n";
echo "<pre>\n";

// Show debug information
$debug = $skinManager->debugSkins();
echo "Debug Information:\n";
echo "Loaded skins: " . implode(', ', $debug['loaded_skins']) . "\n";
echo "Valid skins from LocalSettings: " . implode(', ', $debug['valid_skins_from_localsettings']) . "\n";
echo "Active skin: " . $debug['active_skin'] . "\n\n";

// Get active skin
$activeSkin = $skinManager->getActiveSkin();
if ($activeSkin) {
    echo "Active skin found: " . $activeSkin->getName() . "\n";
    echo "Skin directory: " . $activeSkin->getDirectory() . "\n";
    echo "CSS path: " . $activeSkin->getCssPath() . "\n";
    echo "JS path: " . $activeSkin->getJsPath() . "\n";
    echo "Layout path: " . $activeSkin->getLayoutPath() . "\n";
} else {
    echo "ERROR: No active skin found!\n";
}

echo "</pre>\n"; 