<?php
/**
 * Debug script for Muslim skin testing
 * 
 * This script tests the Muslim skin functionality and displays
 * information about the skin system.
 */

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load core files
require_once __DIR__ . '/../src/Core/Application.php';
require_once __DIR__ . '/../src/Skins/SkinManager.php';
require_once __DIR__ . '/../src/Skins/Skin.php';

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;

// Initialize application
$app = new Application(__DIR__ . '/..');

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Muslim Skin Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .warning { color: orange; }
        .card { border: 1px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>
    <h1>🕌 Muslim Skin Debug</h1>
    <p>Testing the Muslim skin functionality and integration.</p>
";

try {
    // Get container and skin manager
    $container = $app->getContainer();
    $skinManager = $container->get('skin.manager');
    
    echo "<div class='card'>
        <h2>✅ Application Status</h2>
        <p>Application initialized successfully</p>
        <p>Container: " . get_class($container) . "</p>
        <p>Skin Manager: " . get_class($skinManager) . "</p>
    </div>";
    
    // Test skin discovery
    echo "<div class='grid'>";
    
    echo "<div class='card'>
        <h3>🎨 Available Skins</h3>";
    
    $availableSkins = $skinManager->getAvailableSkinNames();
    if (!empty($availableSkins)) {
        echo "<ul>";
        foreach ($availableSkins as $skinName) {
            $status = $skinManager->hasSkin($skinName) ? "✅" : "❌";
            echo "<li>$status $skinName</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>No skins found</p>";
    }
    echo "</div>";
    
    // Test Muslim skin specifically
    echo "<div class='card'>
        <h3>🕌 Muslim Skin Test</h3>";
    
    if ($skinManager->hasSkin('Muslim')) {
        echo "<p class='success'>✅ Muslim skin found</p>";
        
        $muslimSkin = $skinManager->getSkin('Muslim');
        if ($muslimSkin) {
            echo "<p class='success'>✅ Muslim skin loaded successfully</p>";
            echo "<p>Skin Name: " . $muslimSkin->getName() . "</p>";
            echo "<p>Version: " . $muslimSkin->getVersion() . "</p>";
            echo "<p>Author: " . $muslimSkin->getAuthor() . "</p>";
            echo "<p>Description: " . $muslimSkin->getDescription() . "</p>";
            
            // Test skin assets
            $cssPath = $muslimSkin->getCssPath();
            $jsPath = $muslimSkin->getJsPath();
            $layoutPath = $muslimSkin->getLayoutPath();
            
            echo "<h4>📁 Asset Paths:</h4>";
            echo "<ul>";
            echo "<li>CSS: " . ($cssPath && file_exists($cssPath) ? "✅" : "❌") . " $cssPath</li>";
            echo "<li>JS: " . ($jsPath && file_exists($jsPath) ? "✅" : "❌") . " $jsPath</li>";
            echo "<li>Layout: " . ($layoutPath && file_exists($layoutPath) ? "✅" : "❌") . " $layoutPath</li>";
            echo "</ul>";
            
            // Test skin configuration
            $config = $muslimSkin->getConfig();
            echo "<h4>⚙️ Configuration:</h4>";
            echo "<pre>" . json_encode($config, JSON_PRETTY_PRINT) . "</pre>";
            
        } else {
            echo "<p class='error'>❌ Failed to load Muslim skin</p>";
        }
    } else {
        echo "<p class='error'>❌ Muslim skin not found</p>";
    }
    echo "</div>";
    
    echo "</div>";
    
    // Test skin switching
    echo "<div class='card'>
        <h3>🔄 Skin Switching Test</h3>";
    
    $originalSkin = $skinManager->getActiveSkinName();
    echo "<p>Current active skin: <strong>$originalSkin</strong></p>";
    
    if ($skinManager->setActiveSkin('Muslim')) {
        echo "<p class='success'>✅ Successfully switched to Muslim skin</p>";
        echo "<p>New active skin: <strong>" . $skinManager->getActiveSkinName() . "</strong></p>";
        
        // Switch back
        $skinManager->setActiveSkin($originalSkin);
        echo "<p class='info'>🔄 Switched back to $originalSkin</p>";
    } else {
        echo "<p class='error'>❌ Failed to switch to Muslim skin</p>";
    }
    
    // Test file structure
    echo "<div class='card'>
        <h3>📁 File Structure Check</h3>";
    
    $muslimSkinDir = __DIR__ . '/../skins/Muslim';
    $requiredFiles = [
        'skin.json' => 'Skin configuration',
        'css/muslim.css' => 'CSS styles',
        'js/muslim.js' => 'JavaScript',
        'templates/layout.twig' => 'Layout template'
    ];
    
    foreach ($requiredFiles as $file => $description) {
        $fullPath = $muslimSkinDir . '/' . $file;
        $exists = file_exists($fullPath);
        $status = $exists ? "✅" : "❌";
        echo "<p>$status $description: $file</p>";
        
        if ($exists) {
            $size = filesize($fullPath);
            echo "<p style='margin-left: 20px; color: #666;'>Size: " . number_format($size) . " bytes</p>";
        }
    }
    
    echo "</div>";
    
    // Test LocalSettings integration
    echo "<div class='card'>
        <h3>⚙️ LocalSettings Integration</h3>";
    
    // Load LocalSettings
    require_once __DIR__ . '/../LocalSettings.php';
    global $wgValidSkins;
    
    if (isset($wgValidSkins) && is_array($wgValidSkins)) {
        echo "<p class='success'>✅ wgValidSkins is set</p>";
        echo "<p>Available skins in LocalSettings:</p>";
        echo "<ul>";
        foreach ($wgValidSkins as $key => $value) {
            $status = $key === 'Muslim' ? "🕌" : "📄";
            echo "<li>$status $key => $value</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ wgValidSkins is not set</p>";
    }
    
    echo "</div>";
    
    // Test force reload
    echo "<div class='card'>
        <h3>🔄 Force Reload Test</h3>";
    
    // Try to create a new skin manager instance
    try {
        $newSkinManager = new SkinManager($app);
        echo "<p class='info'>✅ Created new SkinManager instance</p>";
        
        $newAvailableSkins = $newSkinManager->getAvailableSkinNames();
        echo "<p>Available skins in new instance:</p>";
        echo "<ul>";
        foreach ($newAvailableSkins as $skinName) {
            $status = $newSkinManager->hasSkin($skinName) ? "✅" : "❌";
            echo "<li>$status $skinName</li>";
        }
        echo "</ul>";
        
        if ($newSkinManager->hasSkin('Muslim')) {
            echo "<p class='success'>✅ Muslim skin found in new instance!</p>";
        } else {
            echo "<p class='error'>❌ Muslim skin still not found in new instance</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Failed to create new SkinManager: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
    
    // Test reloadAllSkins method
    echo "<div class='card'>
        <h3>🔄 Reload All Skins Test</h3>";
    
    try {
        echo "<p class='info'>Calling reloadAllSkins() method...</p>";
        $skinManager->reloadAllSkins();
        echo "<p class='success'>✅ reloadAllSkins() completed</p>";
        
        $reloadedSkins = $skinManager->getAvailableSkinNames();
        echo "<p>Available skins after reload:</p>";
        echo "<ul>";
        foreach ($reloadedSkins as $skinName) {
            $status = $skinManager->hasSkin($skinName) ? "✅" : "❌";
            echo "<li>$status $skinName</li>";
        }
        echo "</ul>";
        
        if ($skinManager->hasSkin('Muslim')) {
            echo "<p class='success'>✅ Muslim skin found after reload!</p>";
            
            $muslimSkin = $skinManager->getSkin('Muslim');
            if ($muslimSkin) {
                echo "<p class='success'>✅ Muslim skin loaded successfully after reload</p>";
                echo "<p>Skin Name: " . $muslimSkin->getName() . "</p>";
                echo "<p>Version: " . $muslimSkin->getVersion() . "</p>";
            }
        } else {
            echo "<p class='error'>❌ Muslim skin still not found after reload</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Failed to reload skins: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='card'>
        <h2 class='error'>❌ Error</h2>
        <p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>
        <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
    </div>";
}

echo "</body></html>"; 