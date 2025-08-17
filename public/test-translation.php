<?php
/**
 * Test Translation System
 * 
 * This script tests if the translation system is working properly.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes
require_once BASE_PATH . '/vendor/autoload.php';

// Create container
$container = new \IslamWiki\Core\Container\AsasContainer();

// Test 1: Check if TranslationService can be created
echo "<h2>Test 1: Creating TranslationService</h2>";
try {
    $translationService = new \IslamWiki\Core\Language\TranslationService('en');
    echo "✅ TranslationService created successfully<br>";
    echo "Current language: " . $translationService->getCurrentLanguage() . "<br>";
} catch (Exception $e) {
    echo "❌ Error creating TranslationService: " . $e->getMessage() . "<br>";
}

// Test 2: Check if translations can be loaded
echo "<h2>Test 2: Loading Translations</h2>";
try {
    if (isset($translationService)) {
        $testKey = 'nav.home';
        $translation = $translationService->translate($testKey);
        echo "✅ Translation loaded successfully<br>";
        echo "Key: '$testKey' -> '$translation'<br>";
    }
} catch (Exception $e) {
    echo "❌ Error loading translations: " . $e->getMessage() . "<br>";
}

// Test 3: Check if language can be changed
echo "<h2>Test 3: Changing Language</h2>";
try {
    if (isset($translationService)) {
        $translationService->setLanguage('ar');
        echo "✅ Language changed to Arabic<br>";
        echo "Current language: " . $translationService->getCurrentLanguage() . "<br>";
        
        $testKey = 'nav.home';
        $translation = $translationService->translate($testKey);
        echo "Key: '$testKey' -> '$translation'<br>";
    }
} catch (Exception $e) {
    echo "❌ Error changing language: " . $e->getMessage() . "<br>";
}

// Test 4: Check if JSON files exist
echo "<h2>Test 4: Checking Language Files</h2>";
$enFile = BASE_PATH . '/languages/locale/en.json';
$arFile = BASE_PATH . '/languages/locale/ar.json';

if (file_exists($enFile)) {
    echo "✅ English language file exists: $enFile<br>";
    $enContent = json_decode(file_get_contents($enFile), true);
    if (isset($enContent['nav']['home'])) {
        echo "✅ English translation for 'nav.home': " . $enContent['nav']['home'] . "<br>";
    }
} else {
    echo "❌ English language file not found: $enFile<br>";
}

if (file_exists($arFile)) {
    echo "✅ Arabic language file exists: $arFile<br>";
    $arContent = json_decode(file_get_contents($arFile), true);
    if (isset($arContent['nav']['home'])) {
        echo "✅ Arabic translation for 'nav.home': " . $arContent['nav']['home'] . "<br>";
    }
} else {
    echo "❌ Arabic language file not found: $arFile<br>";
}

echo "<hr>";
echo "<p><a href='/settings'>Back to Settings</a></p>";
?> 