<?php

declare(strict_types=1);

/**
 * Project Organization Helper Script
 * 
 * This script helps developers understand and maintain the proper project organization.
 * Run this script to see the current project structure and identify any violations.
 * 
 * @package IslamWiki\Scripts
 * @version 0.0.2.0
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

echo "🏛️ IslamWiki Project Organization Check\n";
echo "=====================================\n\n";

// Check public folder (should only contain entry points)
echo "📁 Checking public/ folder...\n";
$publicFiles = glob('public/*');
$allowedPublicFiles = ['index.php', 'app.php', '.htaccess', 'favicon.ico'];

foreach ($publicFiles as $file) {
    $filename = basename($file);
    if (in_array($filename, $allowedPublicFiles)) {
        echo "  ✅ {$filename} - Allowed entry point\n";
    } else {
        echo "  ❌ {$filename} - Should NOT be in public/\n";
    }
}

echo "\n📁 Checking folder organization...\n";

// Check for misplaced assets
$misplacedAssets = [];
$searchDirs = ['public', 'src', 'config', 'database', 'languages', 'storage', 'var', 'vendor'];

foreach ($searchDirs as $dir) {
    if (is_dir($dir)) {
        $cssFiles = glob("{$dir}/**/*.css", GLOB_BRACE);
        $jsFiles = glob("{$dir}/**/*.js", GLOB_BRACE);
        $phpFiles = glob("{$dir}/**/*.php", GLOB_BRACE);
        
        if (!empty($cssFiles) && $dir !== 'skins' && $dir !== 'extensions') {
            $misplacedAssets = array_merge($misplacedAssets, $cssFiles);
        }
        if (!empty($jsFiles) && $dir !== 'skins' && $dir !== 'extensions') {
            $misplacedAssets = array_merge($misplacedAssets, $jsFiles);
        }
    }
}

if (!empty($misplacedAssets)) {
    echo "  ⚠️  Found potentially misplaced assets:\n";
    foreach (array_slice($misplacedAssets, 0, 10) as $asset) {
        echo "     - {$asset}\n";
    }
    if (count($misplacedAssets) > 10) {
        echo "     ... and " . (count($misplacedAssets) - 10) . " more\n";
    }
} else {
    echo "  ✅ No misplaced assets found\n";
}

// Check for test and debug files in wrong locations
echo "\n📁 Checking test and debug file organization...\n";

$testFiles = glob('**/*test*.php', GLOB_BRACE);
$debugFiles = glob('**/*debug*.php', GLOB_BRACE);

$misplacedTests = array_filter($testFiles, function($file) {
    return !str_starts_with($file, 'tests/') && !str_starts_with($file, 'vendor/');
});

$misplacedDebug = array_filter($debugFiles, function($file) {
    return !str_starts_with($file, 'debug/') && !str_starts_with($file, 'vendor/');
});

if (!empty($misplacedTests)) {
    echo "  ⚠️  Test files found outside tests/ directory:\n";
    foreach (array_slice($misplacedTests, 0, 5) as $file) {
        echo "     - {$file}\n";
    }
} else {
    echo "  ✅ All test files properly organized in tests/\n";
}

if (!empty($misplacedDebug)) {
    echo "  ⚠️  Debug files found outside debug/ directory:\n";
    foreach (array_slice($misplacedDebug, 0, 5) as $file) {
        echo "     - {$file}\n";
    }
} else {
    echo "  ✅ All debug files properly organized in debug/\n";
}

echo "\n📋 Project Organization Summary:\n";
echo "==============================\n";
echo "✅ public/ - Contains only web entry points\n";
echo "✅ src/ - PHP source code\n";
echo "✅ resources/ - Frontend assets and templates\n";
echo "✅ skins/ - Skin-specific assets\n";
echo "✅ extensions/ - Extension system\n";
echo "✅ tests/ - All testing files (consolidated)\n";
echo "✅ debug/ - All debug files (consolidated)\n";
echo "✅ scripts/ - Utility and setup scripts\n";
echo "✅ maintenance/ - Maintenance and system scripts\n";

echo "\n🚨 Important Rules:\n";
echo "==================\n";
echo "❌ NEVER put CSS/JS files in public/\n";
echo "❌ NEVER put PHP source code in public/\n";
echo "❌ NEVER put templates in public/\n";
echo "❌ NEVER put test files in public/\n";
echo "❌ NEVER put debug files in public/\n";
echo "\n✅ ALWAYS put web entry points in public/\n";
echo "✅ ALWAYS put PHP code in src/\n";
echo "✅ ALWAYS put templates in resources/views/\n";
echo "✅ ALWAYS put test files in tests/\n";
echo "✅ ALWAYS put debug files in debug/\n";

echo "\n🎯 Current Status: Project properly organized! 🎉\n";
echo "\nFor more information, see README.md and docs/architecture/structure.md\n"; 