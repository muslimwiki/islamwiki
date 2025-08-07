<?php

/**
 * Simple Test Skin System
 *
 * Tests the skin system components directly.
 *
 * @package IslamWiki\Scripts
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Skins\SkinManager;
use IslamWiki\Skins\Bismillah\BismillahSkin;

echo "🧪 Testing IslamWiki Skin System (Simple)\n";
echo "==========================================\n\n";

try {
    // Test Bismillah Skin directly
    echo "Testing Bismillah Skin...\n";
    $bismillahSkin = new BismillahSkin();
    echo "✅ Bismillah Skin created successfully\n";

    // Test skin properties
    echo "📝 Name: " . $bismillahSkin->getName() . "\n";
    echo "📦 Version: " . $bismillahSkin->getVersion() . "\n";
    echo "👤 Author: " . $bismillahSkin->getAuthor() . "\n";
    echo "📄 Description: " . $bismillahSkin->getDescription() . "\n";

    // Test skin validation
    if ($bismillahSkin->validate()) {
        echo "✅ Skin validation passed\n";
    } else {
        echo "❌ Skin validation failed\n";
    }

    // Test skin configuration
    $config = $bismillahSkin->getConfig();
    echo "⚙️  Skin configuration: " . count($config) . " options\n";
    foreach ($config as $key => $value) {
        echo "  - {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
    }

    // Test CSS content
    $cssContent = $bismillahSkin->getCssContent();
    if (!empty($cssContent)) {
        echo "✅ CSS content loaded (" . strlen($cssContent) . " characters)\n";
        echo "📊 CSS contains " . substr_count($cssContent, '{') . " CSS rules\n";
    } else {
        echo "❌ No CSS content found\n";
    }

    // Test JavaScript content
    $jsContent = $bismillahSkin->getJsContent();
    if (!empty($jsContent)) {
        echo "✅ JavaScript content loaded (" . strlen($jsContent) . " characters)\n";
        echo "📊 JS contains " . substr_count($jsContent, 'function') . " functions\n";
    } else {
        echo "❌ No JavaScript content found\n";
    }

    // Test skin metadata
    $metadata = $bismillahSkin->getMetadata();
    echo "📊 Skin metadata: " . count($metadata) . " fields\n";
    foreach ($metadata as $key => $value) {
        if ($key === 'config') {
            echo "  - {$key}: " . count($value) . " configuration options\n";
        } else {
            echo "  - {$key}: {$value}\n";
        }
    }

    // Test skin file paths
    $bismillahSkin->setSkinPath(__DIR__ . '/../src/Skins/Bismillah');

    $cssPath = $bismillahSkin->getCssPath();
    $jsPath = $bismillahSkin->getJsPath();
    $layoutPath = $bismillahSkin->getLayoutPath();

    echo "📁 Skin paths:\n";
    echo "  - CSS: {$cssPath}\n";
    echo "  - JS: {$jsPath}\n";
    echo "  - Layout: {$layoutPath}\n";

    // Test file existence
    echo "📂 File existence:\n";
    echo "  - CSS file exists: " . (file_exists($cssPath) ? '✅' : '❌') . "\n";
    echo "  - JS file exists: " . (file_exists($jsPath) ? '✅' : '❌') . "\n";
    echo "  - Layout file exists: " . (file_exists($layoutPath) ? '✅' : '❌') . "\n";

    // Test custom file detection
    echo "🔍 Custom file detection:\n";
    echo "  - Has custom CSS: " . ($bismillahSkin->hasCustomCss() ? '✅' : '❌') . "\n";
    echo "  - Has custom JS: " . ($bismillahSkin->hasCustomJs() ? '✅' : '❌') . "\n";
    echo "  - Has custom layout: " . ($bismillahSkin->hasCustomLayout() ? '✅' : '❌') . "\n";

    echo "\n🎉 Simple skin system test completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
}
