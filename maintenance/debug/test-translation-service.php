<?php

/**
 * Test Translation Service Instantiation
 * 
 * Test if the TranslationService can be instantiated with the simplified constructor.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "=== Testing Translation Service Instantiation ===\n\n";

try {
    // Test 1: Check if classes exist
    echo "1. Checking if required classes exist:\n";
    
    $requiredClasses = [
        'IslamWiki\Services\TranslationService',
        'Psr\Log\LoggerInterface'
    ];
    
    foreach ($requiredClasses as $class) {
        if (class_exists($class)) {
            echo "   ✅ {$class} - EXISTS\n";
        } else {
            echo "   ❌ {$class} - MISSING\n";
        }
    }
    
    echo "\n";
    
    // Test 2: Create mock logger
    echo "2. Creating mock logger:\n";
    $mockLogger = new class implements \Psr\Log\LoggerInterface {
        public function emergency($message, array $context = array()): void {}
        public function alert($message, array $context = array()): void {}
        public function critical($message, array $context = array()): void {}
        public function error($message, array $context = array()): void {}
        public function warning($message, array $context = array()): void {}
        public function notice($message, array $context = array()): void {}
        public function info($message, array $context = array()): void {}
        public function debug($message, array $context = array()): void {}
        public function log($level, $message, array $context = array()): void {}
    };
    echo "   ✅ Mock logger created successfully\n";
    
    // Test 3: Try to instantiate TranslationService directly
    echo "\n3. Testing direct instantiation:\n";
    try {
        $translationService = new \IslamWiki\Services\TranslationService($mockLogger);
        echo "   ✅ TranslationService instantiated successfully\n";
        
        // Test 4: Check if it has required methods
        echo "\n4. Checking required methods:\n";
        $reflection = new ReflectionClass($translationService);
        $methods = ['translate', 'translateBatch', 'getSupportedLanguages', 'isRTL'];
        
        foreach ($methods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "   ✅ Method {$method} exists\n";
            } else {
                echo "   ❌ Method {$method} missing\n";
            }
        }
        
        // Test 5: Test basic functionality
        echo "\n5. Testing basic functionality:\n";
        try {
            $languages = $translationService->getSupportedLanguages();
            echo "   ✅ getSupportedLanguages() returned " . count($languages) . " languages\n";
            
            $isRTL = $translationService->isRTL('ar');
            echo "   ✅ isRTL('ar') returned: " . ($isRTL ? 'true' : 'false') . "\n";
            
            $direction = $translationService->getLanguageDirection('en');
            echo "   ✅ getLanguageDirection('en') returned: {$direction}\n";
            
        } catch (Exception $e) {
            echo "   ❌ Basic functionality test failed: " . $e->getMessage() . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Failed to instantiate: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 