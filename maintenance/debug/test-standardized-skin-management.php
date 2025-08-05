<?php
/**
 * Test script for standardized skin management
 * 
 * This script tests the new standardized approach for managing skins.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/NizamApplication.php';
require_once __DIR__ . '/../src/Skins/SkinManager.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

// Initialize application
$app = new NizamApplication(__DIR__ . '/..');

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Standardized Skin Management Test</title>
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
    <h1>🔧 Standardized Skin Management Test</h1>
    <p>Testing the new standardized approach for managing skins.</p>
";

try {
    // Get container and skin manager
    $container = $app->getContainer();
    $skinManager = $container->get('skin.manager');
    
    // Force reload all skins to ensure Muslim is loaded
    $skinManager->reloadAllSkins();
    
    echo "<div class='card'>
        <h2>✅ Application Status</h2>
        <p>Application initialized successfully</p>
        <p>Container: " . get_class($container) . "</p>
        <p>Skin Manager: " . get_class($skinManager) . "</p>
    </div>";
    
    // Test 1: Static helper methods
    echo "<div class='card'>
        <h3>🧪 Test 1: Static Helper Methods</h3>";
    
    $activeSkinStatic = SkinManager::getActiveSkinNameStatic($app);
    echo "<p>Active skin (static): <strong>$activeSkinStatic</strong></p>";
    
    $activeSkinDirect = $skinManager->getActiveSkinName();
    echo "<p>Active skin (direct): <strong>$activeSkinDirect</strong></p>";
    
    if ($activeSkinStatic === $activeSkinDirect) {
        echo "<p class='success'>✅ Static and direct methods return same result</p>";
    } else {
        echo "<p class='error'>❌ Static and direct methods return different results</p>";
    }
    
    echo "</div>";
    
    // Test 2: Skin switching
    echo "<div class='card'>
        <h3>🔄 Test 2: Skin Switching</h3>";
    
    $originalSkin = $skinManager->getActiveSkinName();
    echo "<p>Original skin: <strong>$originalSkin</strong></p>";
    
    // Test switching to Muslim skin
    if ($skinManager->hasSkin('Muslim')) {
        $success = $skinManager->setActiveSkin('Muslim');
        if ($success) {
            echo "<p class='success'>✅ Successfully switched to Muslim skin</p>";
            echo "<p>New active skin: <strong>" . $skinManager->getActiveSkinName() . "</strong></p>";
            
            // Test static method
            $staticResult = SkinManager::getActiveSkinNameStatic($app);
            echo "<p>Static method result: <strong>$staticResult</strong></p>";
            
            // Switch back
            $skinManager->setActiveSkin($originalSkin);
            echo "<p class='info'>🔄 Switched back to $originalSkin</p>";
        } else {
            echo "<p class='error'>❌ Failed to switch to Muslim skin</p>";
        }
    } else {
        echo "<p class='error'>❌ Muslim skin not available</p>";
    }
    
    echo "</div>";
    
    // Test 3: Fallback mechanisms
    echo "<div class='card'>
        <h3>🛡️ Test 3: Fallback Mechanisms</h3>";
    
    // Test getActiveSkinNameWithFallback
    $fallbackResult = $skinManager->getActiveSkinNameWithFallback();
    echo "<p>Fallback result: <strong>$fallbackResult</strong></p>";
    
    // Test with invalid skin
    $invalidResult = $skinManager->setActiveSkin('NonExistentSkin');
    echo "<p>Setting invalid skin result: " . ($invalidResult ? 'true' : 'false') . "</p>";
    
    if (!$invalidResult) {
        echo "<p class='success'>✅ Properly rejected invalid skin</p>";
    } else {
        echo "<p class='error'>❌ Should have rejected invalid skin</p>";
    }
    
    echo "</div>";
    
    // Test 4: LocalSettings integration
    echo "<div class='card'>
        <h3>⚙️ Test 4: LocalSettings Integration</h3>";
    
    // Load LocalSettings
    require_once __DIR__ . '/../LocalSettings.php';
    global $wgActiveSkin, $wgValidSkins;
    
    echo "<p>wgActiveSkin from LocalSettings: <strong>" . ($wgActiveSkin ?? 'not set') . "</strong></p>";
    echo "<p>wgValidSkins from LocalSettings:</p>";
    echo "<ul>";
    if (isset($wgValidSkins) && is_array($wgValidSkins)) {
        foreach ($wgValidSkins as $key => $value) {
            echo "<li>$key => $value</li>";
        }
    } else {
        echo "<li>Not set</li>";
    }
    echo "</ul>";
    
    // Test initialization from LocalSettings
    $skinManager->initializeFromLocalSettings();
    $initializedSkin = $skinManager->getActiveSkinName();
    echo "<p>Skin after LocalSettings initialization: <strong>$initializedSkin</strong></p>";
    
    echo "</div>";
    
    // Test 5: Available skins
    echo "<div class='card'>
        <h3>🎨 Test 5: Available Skins</h3>";
    
    $availableSkins = $skinManager->getAvailableSkinNames();
    echo "<p>Available skins:</p>";
    echo "<ul>";
    foreach ($availableSkins as $skinName) {
        $status = $skinManager->hasSkin($skinName) ? "✅" : "❌";
        echo "<li>$status $skinName</li>";
    }
    echo "</ul>";
    
    // Test specific skins
    $testSkins = ['Bismillah', 'Muslim', 'NonExistent'];
    echo "<p>Testing specific skins:</p>";
    echo "<ul>";
    foreach ($testSkins as $skinName) {
        $hasSkin = $skinManager->hasSkin($skinName);
        $status = $hasSkin ? "✅" : "❌";
        echo "<li>$status $skinName: " . ($hasSkin ? 'Available' : 'Not available') . "</li>";
    }
    echo "</ul>";
    
    echo "</div>";
    
    // Test 6: Performance and caching
    echo "<div class='card'>
        <h3>⚡ Test 6: Performance and Caching</h3>";
    
    $startTime = microtime(true);
    for ($i = 0; $i < 100; $i++) {
        $skinManager->getActiveSkinName();
    }
    $endTime = microtime(true);
    $duration = ($endTime - $startTime) * 1000;
    
    echo "<p>100 getActiveSkinName() calls took: <strong>" . number_format($duration, 2) . "ms</strong></p>";
    
    $startTime = microtime(true);
    for ($i = 0; $i < 100; $i++) {
        SkinManager::getActiveSkinNameStatic($app);
    }
    $endTime = microtime(true);
    $duration = ($endTime - $startTime) * 1000;
    
    echo "<p>100 static getActiveSkinNameStatic() calls took: <strong>" . number_format($duration, 2) . "ms</strong></p>";
    
    echo "</div>";
    
    // Summary
    echo "<div class='card'>
        <h3>📊 Summary</h3>";
    
    $tests = [
        'Static helper methods work' => $activeSkinStatic === $activeSkinDirect,
        'Skin switching works' => $skinManager->hasSkin('Muslim') && $skinManager->setActiveSkin('Muslim'),
        'Fallback mechanisms work' => !$skinManager->setActiveSkin('NonExistentSkin'),
        'LocalSettings integration works' => isset($wgActiveSkin),
        'Available skins loaded' => count($availableSkins) > 0,
    ];
    
    $passed = 0;
    $total = count($tests);
    
    echo "<ul>";
    foreach ($tests as $test => $result) {
        $status = $result ? "✅" : "❌";
        $color = $result ? "green" : "red";
        echo "<li style='color: $color;'>$status $test</li>";
        if ($result) $passed++;
    }
    echo "</ul>";
    
    echo "<p><strong>Results: $passed/$total tests passed</strong></p>";
    
    if ($passed === $total) {
        echo "<p class='success'>🎉 All tests passed! The standardized skin management is working correctly.</p>";
    } else {
        echo "<p class='error'>⚠️ Some tests failed. Please check the implementation.</p>";
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