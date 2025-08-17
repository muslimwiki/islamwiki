<?php
/**
 * Test Application Translation System
 * 
 * This script tests the translation system in the context of the actual application.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include vendor autoloader for Twig classes
require_once BASE_PATH . '/vendor/autoload.php';

// Include the TranslationService directly to bypass autoloader issues
require_once BASE_PATH . '/src/Core/Language/TranslationService.php';
require_once BASE_PATH . '/src/Core/View/TwigTranslationExtension.php';

// Test 1: Check if the JSON language files exist and are readable
echo "<h2>Test 1: Language Files</h2>";
$enFile = BASE_PATH . '/languages/locale/en.json';
$arFile = BASE_PATH . '/languages/locale/ar.json';

if (file_exists($enFile)) {
    echo "✅ English file exists: $enFile<br>";
    $enContent = json_decode(file_get_contents($enFile), true);
    if (isset($enContent['nav']['home'])) {
        echo "✅ English 'nav.home': " . $enContent['nav']['home'] . "<br>";
    }
} else {
    echo "❌ English file missing<br>";
}

if (file_exists($arFile)) {
    echo "✅ Arabic file exists: $arFile<br>";
    $arContent = json_decode(file_get_contents($arFile), true);
    if (isset($arContent['nav']['home'])) {
        echo "✅ Arabic 'nav.home': " . $arContent['nav']['home'] . "<br>";
    }
} else {
    echo "❌ Arabic file missing<br>";
}

// Test 2: Check if the TranslationService can load from JSON files
echo "<h2>Test 2: TranslationService with JSON</h2>";
try {
    $translationService = new \IslamWiki\Core\Language\TranslationService('en');
    echo "✅ TranslationService created<br>";
    echo "Current language: " . $translationService->getCurrentLanguage() . "<br>";
    
    $testKey = 'nav.home';
    $translation = $translationService->translate($testKey);
    echo "Translation '$testKey': '$translation'<br>";
    
    // Change to Arabic
    $translationService->setLanguage('ar');
    echo "Changed to Arabic<br>";
    $translation = $translationService->translate($testKey);
    echo "Arabic translation '$testKey': '$translation'<br>";
    
} catch (Exception $e) {
    echo "❌ TranslationService error: " . $e->getMessage() . "<br>";
}

// Test 3: Check if the TwigTranslationExtension works
echo "<h2>Test 3: TwigTranslationExtension</h2>";
try {
    $translationService = new \IslamWiki\Core\Language\TranslationService('en');
    $extension = new \IslamWiki\Core\View\TwigTranslationExtension($translationService);
    echo "✅ TwigTranslationExtension created<br>";
    
    // Test the translate method directly
    $result = $extension->translate('nav.home');
    echo "Direct translation 'nav.home': '$result'<br>";
    
} catch (Exception $e) {
    echo "❌ TwigTranslationExtension error: " . $e->getMessage() . "<br>";
}

// Test 4: Check if the issue is with the Twig environment
echo "<h2>Test 4: Twig Environment</h2>";
try {
    $loader = new \Twig\Loader\FilesystemLoader(BASE_PATH . '/resources/views');
    $twig = new \Twig\Environment($loader, ['debug' => true]);
    
    $translationService = new \IslamWiki\Core\Language\TranslationService('en');
    $extension = new \IslamWiki\Core\View\TwigTranslationExtension($translationService);
    $twig->addExtension($extension);
    
    echo "✅ Twig environment created with extension<br>";
    
    // Test a simple template
    $template = $twig->createTemplate("{{ __('nav.home') }}");
    $result = $template->render();
    echo "Twig template result: '$result'<br>";
    
} catch (Exception $e) {
    echo "❌ Twig environment error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><a href='/settings'>Back to Settings</a></p>";
?> 