<?php

/**
 * Test Direct Middleware Instantiation
 * 
 * Test if the SubdomainLanguageMiddleware can be instantiated directly.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "=== Testing Direct Middleware Instantiation ===\n\n";

try {
    // Test 1: Check if classes exist
    echo "1. Checking if required classes exist:\n";
    
    $requiredClasses = [
        'IslamWiki\Http\Middleware\SubdomainLanguageMiddleware',
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
    
    // Test 3: Create translation service
    echo "\n3. Creating translation service:\n";
    try {
        $translationService = new \IslamWiki\Services\TranslationService($mockLogger);
        echo "   ✅ TranslationService created successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Failed to create TranslationService: " . $e->getMessage() . "\n";
        return;
    }
    
    // Test 4: Try to instantiate SubdomainLanguageMiddleware directly
    echo "\n4. Testing direct instantiation:\n";
    try {
        $middleware = new \IslamWiki\Http\Middleware\SubdomainLanguageMiddleware($mockLogger, $translationService);
        echo "   ✅ SubdomainLanguageMiddleware instantiated successfully\n";
        
        // Test 5: Check if it has required methods
        echo "\n5. Checking required methods:\n";
        $reflection = new ReflectionClass($middleware);
        $methods = ['process', 'handle', 'getCurrentLanguage', 'isCurrentLanguageRTL'];
        
        foreach ($methods as $method) {
            if ($reflection->hasMethod($method)) {
                echo "   ✅ Method {$method} exists\n";
            } else {
                echo "   ❌ Method {$method} missing\n";
            }
        }
        
        // Test 6: Test basic functionality
        echo "\n6. Testing basic functionality:\n";
        try {
            $currentLang = $middleware->getCurrentLanguage();
            echo "   ✅ getCurrentLanguage() returned: {$currentLang}\n";
            
            $isRTL = $middleware->isCurrentLanguageRTL();
            echo "   ✅ isCurrentLanguageRTL() returned: " . ($isRTL ? 'true' : 'false') . "\n";
            
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