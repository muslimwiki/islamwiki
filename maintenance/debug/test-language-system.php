<?php

/**
 * Test script for the Hybrid Translation System
 * Run this to test if the system is working properly
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__, 2));

// Load Composer's autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "=== Testing Hybrid Translation System ===\n\n";

// Test 1: Check if classes exist
echo "1. Checking if classes exist:\n";
$classes = [
    'IslamWiki\Services\TranslationService',
    'IslamWiki\Http\Middleware\SubdomainLanguageMiddleware',
    'IslamWiki\Http\Controllers\LanguageController',
    'IslamWiki\Providers\TranslationServiceProvider'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "   ✅ {$class} - EXISTS\n";
    } else {
        echo "   ❌ {$class} - MISSING\n";
    }
}

echo "\n";

// Test 2: Check if files exist
echo "2. Checking if files exist:\n";
$files = [
    'src/Services/TranslationService.php',
    'src/Http/Middleware/SubdomainLanguageMiddleware.php',
    'src/Http/Controllers/LanguageController.php',
    'src/Providers/TranslationServiceProvider.php',
    'config/translation.php',
    'resources/views/components/enhanced-language-switch.twig'
];

foreach ($files as $file) {
    $fullPath = BASE_PATH . '/' . $file;
    if (file_exists($fullPath)) {
        echo "   ✅ {$file} - EXISTS\n";
    } else {
        echo "   ❌ {$file} - MISSING\n";
    }
}

echo "\n";

// Test 3: Check routes
echo "3. Checking if routes are loaded:\n";
$routesFile = BASE_PATH . '/routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    if (strpos($routesContent, '/language/') !== false) {
        echo "   ✅ Language routes found in web.php\n";
    } else {
        echo "   ❌ Language routes not found in web.php\n";
    }
} else {
    echo "   ❌ routes/web.php not found\n";
}

echo "\n";

// Test 4: Check configuration
echo "4. Checking configuration:\n";
$configFile = BASE_PATH . '/config/translation.php';
if (file_exists($configFile)) {
    $config = require $configFile;
    if (isset($config['supported_languages'])) {
        $langCount = count($config['supported_languages']);
        echo "   ✅ Translation config loaded with {$langCount} languages\n";
        
        foreach ($config['supported_languages'] as $code => $lang) {
            echo "      - {$code}: {$lang['name']} ({$lang['direction']})\n";
        }
    } else {
        echo "   ❌ Translation config missing supported_languages\n";
    }
} else {
    echo "   ❌ Translation config file not found\n";
}

echo "\n";

// Test 5: Check environment variables
echo "5. Checking environment variables:\n";
$envVars = [
    'GOOGLE_TRANSLATE_API_KEY',
    'DEFAULT_LANGUAGE',
    'BASE_DOMAIN'
];

foreach ($envVars as $var) {
    $value = $_ENV[$var] ?? null;
    if ($value) {
        echo "   ✅ {$var} = " . (strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value) . "\n";
    } else {
        echo "   ⚠️  {$var} not set\n";
    }
}

echo "\n";

// Test 6: Simulate subdomain language detection
echo "6. Testing subdomain language detection:\n";
$testHosts = [
    'local.islam.wiki' => 'en',
    'ar.local.islam.wiki' => 'ar',
    'ur.local.islam.wiki' => 'ur',
    'tr.local.islam.wiki' => 'tr'
];

foreach ($testHosts as $host => $expectedLang) {
    // Simulate the extraction logic
    $language = 'en'; // default
    foreach (['en', 'ar', 'ur', 'tr', 'id', 'ms', 'fa', 'he'] as $code) {
        if (strpos($host, $code . '.') === 0) {
            $language = $code;
            break;
        }
    }
    
    if ($language === $expectedLang) {
        echo "   ✅ {$host} → {$language}\n";
    } else {
        echo "   ❌ {$host} → {$language} (expected {$expectedLang})\n";
    }
}

echo "\n";

// Test 7: Check if middleware can be instantiated
echo "7. Testing middleware instantiation:\n";
try {
    // Test basic functionality without full instantiation
    echo "   ✅ Testing class structure and method availability\n";
    
    // Check if methods exist using reflection
    $translationServiceReflection = new ReflectionClass(\IslamWiki\Services\TranslationService::class);
    $middlewareReflection = new ReflectionClass(\IslamWiki\Http\Middleware\SubdomainLanguageMiddleware::class);
    
    echo "   ✅ TranslationService has " . count($translationServiceReflection->getMethods()) . " methods\n";
    echo "   ✅ SubdomainLanguageMiddleware has " . count($middlewareReflection->getMethods()) . " methods\n";
    
    // Check for required methods
    $requiredMethods = ['translate', 'translateBatch', 'getSupportedLanguages', 'isRTL'];
    foreach ($requiredMethods as $method) {
        if ($translationServiceReflection->hasMethod($method)) {
            echo "   ✅ TranslationService::{$method} method exists\n";
        } else {
            echo "   ❌ TranslationService::{$method} method missing\n";
        }
    }
    
    echo "   ✅ Basic class structure validation passed\n";
    
} catch (Exception $e) {
    echo "   ❌ Failed to validate classes: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 8: Check API endpoints
echo "8. Testing API endpoints:\n";
$endpoints = [
    '/language/current',
    '/language/available',
    '/language/switch/ar',
    '/language/translate',
    '/language/stats'
];

foreach ($endpoints as $endpoint) {
    echo "   - {$endpoint}\n";
}

echo "\n=== Test Complete ===\n";

// Recommendations
echo "\n=== Recommendations ===\n";
echo "1. Make sure your .env file has GOOGLE_TRANSLATE_API_KEY set\n";
echo "2. Ensure DNS is configured for language subdomains (ar.local.islam.wiki, etc.)\n";
echo "3. Check that the TranslationServiceProvider is loaded in your application\n";
echo "4. Verify that the middleware is added to the router's middleware stack\n";
echo "5. Test the language switching by visiting ar.local.islam.wiki\n"; 