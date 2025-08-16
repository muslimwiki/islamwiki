<?php

/**
 * Test script to check if the LanguageSwitch extension is being loaded
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__, 2));

// Load Composer's autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "=== Testing LanguageSwitch Extension Loading ===\n\n";

// Test 1: Check if extension files exist
echo "1. Checking extension files:\n";
$extensionFiles = [
    'extensions/LanguageSwitch/LanguageSwitch.php',
    'extensions/LanguageSwitch/extension.json',
    'extensions/LanguageSwitch/templates/language-switch.twig'
];

foreach ($extensionFiles as $file) {
    $fullPath = BASE_PATH . '/' . $file;
    if (file_exists($fullPath)) {
        echo "   ✅ {$file} - EXISTS\n";
    } else {
        echo "   ❌ {$file} - MISSING\n";
    }
}

echo "\n";

// Test 2: Check extension configuration
echo "2. Checking extension configuration:\n";
$extensionJson = BASE_PATH . '/extensions/LanguageSwitch/extension.json';
if (file_exists($extensionJson)) {
    $config = json_decode(file_get_contents($extensionJson), true);
    if ($config) {
        echo "   ✅ Extension JSON parsed successfully\n";
        echo "   Name: " . ($config['name'] ?? 'N/A') . "\n";
        echo "   Version: " . ($config['version'] ?? 'N/A') . "\n";
        echo "   Class: " . ($config['class'] ?? 'N/A') . "\n";
        echo "   Main: " . ($config['main'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ Extension JSON parsing failed\n";
    }
} else {
    echo "   ❌ Extension JSON not found\n";
}

echo "\n";

// Test 3: Check if extension class can be loaded
echo "3. Testing extension class loading:\n";
try {
    require_once BASE_PATH . '/extensions/LanguageSwitch/LanguageSwitch.php';
    echo "   ✅ Extension file included successfully\n";
    
    if (class_exists('IslamWiki\Extensions\LanguageSwitch\LanguageSwitch')) {
        echo "   ✅ Extension class exists\n";
        
        // Try to create an instance
        try {
            $extension = new \IslamWiki\Extensions\LanguageSwitch\LanguageSwitch();
            echo "   ✅ Extension instance created successfully\n";
            echo "   Extension name: " . $extension->getName() . "\n";
            echo "   Extension version: " . $extension->getVersion() . "\n";
        } catch (Exception $e) {
            echo "   ❌ Failed to create extension instance: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Extension class not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Failed to include extension: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check extension manager
echo "4. Testing extension manager:\n";
try {
    // Create a mock container
    $mockContainer = new class {
        public function get($id) {
            if ($id === 'IslamWiki\Core\Extensions\Hooks\HookManager') {
                return new class {
                    public function register($hook, $callback) { return true; }
                    public function runLast($hook, $data) { return $data; }
                };
            }
            return null;
        }
    };
    
    $extensionManager = new \IslamWiki\Core\Extensions\ExtensionManager($mockContainer);
    echo "   ✅ Extension manager created successfully\n";
    
    // Check if extensions directory is accessible
    $extensionsPath = $extensionManager->getExtensionsPath();
    echo "   Extensions path: {$extensionsPath}\n";
    
    if (is_dir($extensionsPath)) {
        echo "   ✅ Extensions directory exists\n";
        
        // Check if LanguageSwitch directory exists
        $languageSwitchPath = $extensionsPath . '/LanguageSwitch';
        if (is_dir($languageSwitchPath)) {
            echo "   ✅ LanguageSwitch extension directory exists\n";
        } else {
            echo "   ❌ LanguageSwitch extension directory not found\n";
        }
    } else {
        echo "   ❌ Extensions directory not found\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Failed to create extension manager: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Check template rendering
echo "5. Testing template rendering:\n";
$templateFile = BASE_PATH . '/extensions/LanguageSwitch/templates/language-switch.twig';
if (file_exists($templateFile)) {
    $templateContent = file_get_contents($templateFile);
    
    // Check for key elements
    $checks = [
        'enhanced-language-switch' => 'Main component class',
        'switchLanguage' => 'JavaScript function',
        'language-option' => 'Language options',
        '🇺🇸' => 'English flag',
        '🇸🇦' => 'Arabic flag'
    ];
    
    foreach ($checks as $check => $description) {
        if (strpos($templateContent, $check) !== false) {
            echo "   ✅ {$description} found in template\n";
        } else {
            echo "   ❌ {$description} not found in template\n";
        }
    }
} else {
    echo "   ❌ Template file not found\n";
}

echo "\n";

// Test 6: Check if extension is being loaded in main app
echo "6. Checking main application integration:\n";

// Check if the main layout includes the extension
$mainLayoutFile = BASE_PATH . '/resources/views/layouts/app.twig';
if (file_exists($mainLayoutFile)) {
    $layoutContent = file_get_contents($mainLayoutFile);
    
    if (strpos($layoutContent, 'extensions/LanguageSwitch/language-switch.twig') !== false) {
        echo "   ✅ Main layout includes LanguageSwitch template\n";
    } else {
        echo "   ❌ Main layout does not include LanguageSwitch template\n";
    }
    
    if (strpos($layoutContent, 'bismillah-header-actions') !== false) {
        echo "   ✅ Header actions section found in layout\n";
    } else {
        echo "   ❌ Header actions section not found in layout\n";
    }
} else {
    echo "   ❌ Main layout file not found\n";
}

echo "\n";

// Test 7: Check for any PHP errors
echo "7. Checking for PHP errors:\n";
$errorLog = ini_get('error_log');
if ($errorLog) {
    echo "   Error log: {$errorLog}\n";
    
    // Check if there are recent errors
    if (file_exists($errorLog)) {
        $recentErrors = shell_exec("tail -n 20 {$errorLog} 2>/dev/null | grep -i 'language\\|extension' | head -5");
        if ($recentErrors) {
            echo "   Recent relevant errors:\n{$recentErrors}\n";
        } else {
            echo "   ✅ No recent relevant errors found\n";
        }
    }
} else {
    echo "   ⚠️  Error log not configured\n";
}

echo "\n=== Test Complete ===\n";

// Recommendations
echo "\n=== Recommendations ===\n";
echo "1. Make sure the LanguageSwitch extension is enabled in the extension system\n";
echo "2. Check that the extension manager is loading extensions properly\n";
echo "3. Verify that the template path is correct in the main layout\n";
echo "4. Check browser console for JavaScript errors\n";
echo "5. Test the language switch by visiting the main page\n";
echo "6. Check if the extension is being loaded in the extension manager\n"; 