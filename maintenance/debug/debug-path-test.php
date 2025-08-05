<?php
declare(strict_types=1);

/**
 * Debug Path Test
 * 
 * Tests the path calculations used by ViewServiceProvider.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Path Calculations\n";
echo "============================\n\n";

// Test the path calculation used by ViewServiceProvider
$basePath = dirname(__DIR__, 2); // This is what ViewServiceProvider uses
$templatePath = $basePath . '/resources/views';
$cachePath = $basePath . '/storage/framework/views';

echo "📊 Path Analysis:\n";
echo "=================\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Base path (dirname(__DIR__, 2)): $basePath\n";
echo "Template path: $templatePath\n";
echo "Cache path: $cachePath\n\n";

echo "📊 Path Existence:\n";
echo "==================\n";
echo "Base path exists: " . (is_dir($basePath) ? 'Yes' : 'No') . "\n";
echo "Template path exists: " . (is_dir($templatePath) ? 'Yes' : 'No') . "\n";
echo "Cache path exists: " . (is_dir($cachePath) ? 'Yes' : 'No') . "\n\n";

echo "📊 Template File Check:\n";
echo "=======================\n";
$settingsTemplate = $templatePath . '/settings/index.twig';
echo "Settings template path: $settingsTemplate\n";
echo "Settings template exists: " . (file_exists($settingsTemplate) ? 'Yes' : 'No') . "\n";

if (file_exists($settingsTemplate)) {
    echo "Settings template readable: " . (is_readable($settingsTemplate) ? 'Yes' : 'No') . "\n";
    echo "Settings template size: " . filesize($settingsTemplate) . " bytes\n";
} else {
    echo "❌ Settings template not found!\n";
}

echo "\n📊 Alternative Path Test:\n";
echo "=========================\n";

// Test with the correct path
$correctBasePath = __DIR__ . '/..';
$correctTemplatePath = $correctBasePath . '/resources/views';
$correctSettingsTemplate = $correctTemplatePath . '/settings/index.twig';

echo "Correct base path: $correctBasePath\n";
echo "Correct template path: $correctTemplatePath\n";
echo "Correct settings template: $correctSettingsTemplate\n";
echo "Correct settings template exists: " . (file_exists($correctSettingsTemplate) ? 'Yes' : 'No') . "\n";

echo "\n📊 Directory Listing:\n";
echo "====================\n";
if (is_dir($templatePath)) {
    echo "Template directory contents:\n";
    $files = scandir($templatePath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $fullPath = $templatePath . '/' . $file;
            $type = is_dir($fullPath) ? 'DIR' : 'FILE';
            echo "  - $file ($type)\n";
        }
    }
} else {
    echo "❌ Template directory not found!\n";
}

echo "\n✅ Path test completed!\n"; 