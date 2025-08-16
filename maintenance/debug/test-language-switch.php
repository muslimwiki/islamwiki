<?php

/**
 * Test page for the Enhanced Language Switch
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__, 2));

// Load Composer's autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Language Switch Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
    </style>
</head>
<body>
    <h1>Enhanced Language Switch Test</h1>";

// Test 1: Check if files exist
echo "<div class='test-section info'>
    <h2>1. File Existence Check</h2>";

$files = [
    'extensions/LanguageSwitch/templates/language-switch.twig',
    'extensions/LanguageSwitch/LanguageSwitch.php',
    'extensions/LanguageSwitch/extension.json'
];

foreach ($files as $file) {
    $fullPath = BASE_PATH . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<p>✅ {$file} - EXISTS</p>";
    } else {
        echo "<p>❌ {$file} - MISSING</p>";
    }
}

echo "</div>";

// Test 2: Check extension configuration
echo "<div class='test-section info'>
    <h2>2. Extension Configuration</h2>";

$extensionJson = BASE_PATH . '/extensions/LanguageSwitch/extension.json';
if (file_exists($extensionJson)) {
    $config = json_decode(file_get_contents($extensionJson), true);
    if ($config) {
        echo "<p>✅ Extension JSON parsed successfully</p>";
        echo "<p>Name: " . ($config['name'] ?? 'N/A') . "</p>";
        echo "<p>Version: " . ($config['version'] ?? 'N/A') . "</p>";
        echo "<p>Supported Languages: " . implode(', ', $config['config']['supportedLanguages'] ?? []) . "</p>";
    } else {
        echo "<p>❌ Extension JSON parsing failed</p>";
    }
} else {
    echo "<p>❌ Extension JSON not found</p>";
}

echo "</div>";

// Test 3: Check if extension is loaded
echo "<div class='test-section info'>
    <h2>3. Extension Loading Check</h2>";

// Check if the extension directory is accessible
$extensionDir = BASE_PATH . '/extensions/LanguageSwitch';
if (is_dir($extensionDir)) {
    echo "<p>✅ Extension directory exists</p>";
    
    // Check if main extension file is loadable
    $mainFile = $extensionDir . '/LanguageSwitch.php';
    if (file_exists($mainFile)) {
        echo "<p>✅ Main extension file exists</p>";
        
        // Try to include the file
        try {
            require_once $mainFile;
            echo "<p>✅ Extension file included successfully</p>";
            
            // Check if class exists
            if (class_exists('IslamWiki\Extensions\LanguageSwitch\LanguageSwitch')) {
                echo "<p>✅ Extension class exists</p>";
            } else {
                echo "<p>❌ Extension class not found</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Failed to include extension: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Main extension file not found</p>";
    }
} else {
    echo "<p>❌ Extension directory not found</p>";
}

echo "</div>";

// Test 4: Check template loading
echo "<div class='test-section info'>
    <h2>4. Template Loading Check</h2>";

$templateFile = BASE_PATH . '/extensions/LanguageSwitch/templates/language-switch.twig';
if (file_exists($templateFile)) {
    echo "<p>✅ Template file exists</p>";
    
    $templateContent = file_get_contents($templateFile);
    if (strpos($templateContent, 'enhanced-language-switch') !== false) {
        echo "<p>✅ Template contains enhanced language switch component</p>";
    } else {
        echo "<p>❌ Template does not contain enhanced language switch component</p>";
    }
    
    if (strpos($templateContent, 'switchLanguage') !== false) {
        echo "<p>✅ Template contains JavaScript functions</p>";
    } else {
        echo "<p>❌ Template missing JavaScript functions</p>";
    }
} else {
    echo "<p>❌ Template file not found</p>";
}

echo "</div>";

// Test 5: Check environment variables
echo "<div class='test-section info'>
    <h2>5. Environment Variables</h2>";

$envVars = [
    'GOOGLE_TRANSLATE_API_KEY',
    'DEFAULT_LANGUAGE',
    'BASE_DOMAIN'
];

foreach ($envVars as $var) {
    $value = $_ENV[$var] ?? null;
    if ($value) {
        echo "<p>✅ {$var} = " . (strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value) . "</p>";
    } else {
        echo "<p>⚠️  {$var} not set</p>";
    }
}

echo "</div>";

// Test 6: Check subdomain functionality
echo "<div class='test-section info'>
    <h2>6. Subdomain Functionality Test</h2>";

$currentHost = $_SERVER['HTTP_HOST'] ?? 'unknown';
echo "<p>Current Host: {$currentHost}</p>";

// Simulate subdomain extraction
$language = 'en'; // default
$supportedLanguages = ['en', 'ar', 'ur', 'tr', 'id', 'ms', 'fa', 'he'];

foreach ($supportedLanguages as $code) {
    if (strpos($currentHost, $code . '.') === 0) {
        $language = $code;
        break;
    }
}

echo "<p>Detected Language: {$language}</p>";

// Generate test URLs
echo "<p>Test URLs:</p>";
echo "<ul>";
foreach ($supportedLanguages as $code) {
    if ($code === 'en') {
        echo "<li><a href='http://{$currentHost}/test'>English: {$currentHost}/test</a></li>";
    } else {
        $baseDomain = $currentHost;
        if (strpos($baseDomain, 'en.') === 0) {
            $baseDomain = substr($baseDomain, 3);
        }
        foreach ($supportedLanguages as $langCode) {
            if (strpos($baseDomain, $langCode . '.') === 0) {
                $baseDomain = substr($baseDomain, strlen($langCode) + 1);
                break;
            }
        }
        echo "<li><a href='http://{$code}.{$baseDomain}/test'>{$code}: {$code}.{$baseDomain}/test</a></li>";
    }
}
echo "</ul>";

echo "</div>";

// Test 7: Display the actual template
echo "<div class='test-section info'>
    <h2>7. Template Preview</h2>";

if (file_exists($templateFile)) {
    echo "<p>Template content (first 500 characters):</p>";
    echo "<pre>" . htmlspecialchars(substr(file_get_contents($templateFile), 0, 500)) . "...</pre>";
} else {
    echo "<p>❌ Template file not found</p>";
}

echo "</div>";

echo "<div class='test-section success'>
    <h2>Test Complete</h2>
    <p>If you see any ❌ errors above, those need to be fixed for the language switch to work properly.</p>
    <p>To test the language switch:</p>
    <ol>
        <li>Make sure all files exist and are accessible</li>
        <li>Check that the LanguageSwitch extension is properly loaded</li>
        <li>Verify that the template is being included in your main layout</li>
        <li>Test by visiting different language subdomains (ar.local.islam.wiki, ur.local.islam.wiki, etc.)</li>
    </ol>
</div>";

echo "</body>
</html>"; 