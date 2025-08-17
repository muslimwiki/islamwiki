<?php
/**
 * Test Autoloader
 * 
 * This script tests if the autoloader is working properly.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Autoloader</h1>";

// Test 1: Check if vendor autoloader exists
echo "<h2>Test 1: Vendor Autoloader</h2>";
$vendorAutoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoloader)) {
    echo "✅ Vendor autoloader exists: $vendorAutoloader<br>";
} else {
    echo "❌ Vendor autoloader missing<br>";
}

// Test 2: Try to include vendor autoloader
echo "<h2>Test 2: Include Vendor Autoloader</h2>";
try {
    require_once $vendorAutoloader;
    echo "✅ Vendor autoloader included successfully<br>";
} catch (Exception $e) {
    echo "❌ Error including vendor autoloader: " . $e->getMessage() . "<br>";
}

// Test 3: Check if Twig classes are available
echo "<h2>Test 3: Twig Classes</h2>";
if (class_exists('\Twig\Environment')) {
    echo "✅ Twig\\Environment class found<br>";
} else {
    echo "❌ Twig\\Environment class not found<br>";
}

// Test 4: Check if our classes are available
echo "<h2>Test 4: Our Classes</h2>";
if (class_exists('\IslamWiki\Core\Language\TranslationService')) {
    echo "✅ TranslationService class found<br>";
} else {
    echo "❌ TranslationService class not found<br>";
}

if (class_exists('\IslamWiki\Core\View\TwigTranslationExtension')) {
    echo "✅ TwigTranslationExtension class found<br>";
} else {
    echo "❌ TwigTranslationExtension class not found<br>";
}

// Test 5: Try to create instances
echo "<h2>Test 5: Create Instances</h2>";
try {
    $translationService = new \IslamWiki\Core\Language\TranslationService('en');
    echo "✅ TranslationService instance created<br>";
} catch (Exception $e) {
    echo "❌ Error creating TranslationService: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><a href='/settings'>Back to Settings</a></p>";
?> 