<?php
declare(strict_types=1);

/**
 * Test Skin System
 * 
 * Tests the skin system functionality.
 * 
 * @package IslamWiki\Scripts
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;
use IslamWiki\Skins\Bismillah\BismillahSkin;
use IslamWiki\Providers\SkinServiceProvider;

echo "🧪 Testing IslamWiki Skin System\n";
echo "================================\n\n";

try {
    // Initialize application
    $app = new Application(__DIR__ . '/..');
    echo "✅ Application initialized successfully\n";
    
    // Test Skin Manager
    $skinManager = new SkinManager($app);
    echo "✅ Skin Manager created successfully\n";
    
    // Test available skins
    $availableSkins = $skinManager->getAvailableSkinNames();
    echo "📋 Available skins: " . implode(', ', $availableSkins) . "\n";
    
    // Test active skin
    $activeSkinName = $skinManager->getActiveSkinName();
    echo "🎨 Active skin: {$activeSkinName}\n";
    
    // Test getting active skin
    $activeSkin = $skinManager->getActiveSkin();
    if ($activeSkin) {
        echo "✅ Active skin loaded: {$activeSkin->getName()} v{$activeSkin->getVersion()}\n";
        echo "📝 Description: {$activeSkin->getDescription()}\n";
        echo "👤 Author: {$activeSkin->getAuthor()}\n";
    } else {
        echo "❌ No active skin found\n";
    }
    
    // Test skin CSS
    $cssContent = $skinManager->getActiveSkinCss();
    if (!empty($cssContent)) {
        echo "✅ Skin CSS loaded (" . strlen($cssContent) . " characters)\n";
    } else {
        echo "❌ No CSS content found\n";
    }
    
    // Test skin JavaScript
    $jsContent = $skinManager->getActiveSkinJs();
    if (!empty($jsContent)) {
        echo "✅ Skin JavaScript loaded (" . strlen($jsContent) . " characters)\n";
    } else {
        echo "❌ No JavaScript content found\n";
    }
    
    // Test skin layout
    $layoutPath = $skinManager->getActiveSkinLayoutPath();
    if (file_exists($layoutPath)) {
        echo "✅ Skin layout found: {$layoutPath}\n";
    } else {
        echo "⚠️  Skin layout not found: {$layoutPath}\n";
    }
    
    // Test skin validation
    if ($activeSkin && $activeSkin->validate()) {
        echo "✅ Skin validation passed\n";
    } else {
        echo "❌ Skin validation failed\n";
    }
    
    // Test skin metadata
    $metadata = $skinManager->getAllSkinMetadata();
    echo "📊 Skin metadata: " . count($metadata) . " skins found\n";
    
    foreach ($metadata as $name => $data) {
        echo "  - {$name}: {$data['name']} v{$data['version']}\n";
    }
    
    // Test Skin Service Provider
    $skinProvider = new SkinServiceProvider($app);
    echo "✅ Skin Service Provider created successfully\n";
    
    // Test setting active skin
    if ($skinProvider->setActiveSkin('bismillah')) {
        echo "✅ Successfully set active skin to 'bismillah'\n";
    } else {
        echo "❌ Failed to set active skin to 'bismillah'\n";
    }
    
    // Test getting available skins
    $availableSkins = $skinProvider->getAvailableSkins();
    echo "📋 Service Provider available skins: " . count($availableSkins) . " found\n";
    
    echo "\n🎉 Skin system test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 