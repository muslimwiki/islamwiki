<?php

/**
 * Test Container Aliases
 * 
 * Debug script to test if the container aliases are working properly.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "=== Testing Container Aliases ===\n\n";

try {
    // Test 1: Create container
    echo "1. Creating container:\n";
    $container = new \IslamWiki\Core\Container\AsasContainer();
    echo "   ✅ Container created successfully\n";
    
    // Test 2: Create mock logger
    echo "\n2. Creating mock logger:\n";
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
    
    // Test 3: Register logger
    echo "\n3. Registering logger:\n";
    $container->instance(\Psr\Log\LoggerInterface::class, $mockLogger);
    echo "   ✅ Logger registered successfully\n";
    
    // Test 4: Check if logger is available
    echo "\n4. Checking logger availability:\n";
    if ($container->has(\Psr\Log\LoggerInterface::class)) {
        echo "   ✅ Container has LoggerInterface\n";
        $logger = $container->get(\Psr\Log\LoggerInterface::class);
        echo "   ✅ Successfully retrieved LoggerInterface\n";
    } else {
        echo "   ❌ Container does not have LoggerInterface\n";
    }
    
    // Test 5: Register TranslationServiceProvider
    echo "\n5. Registering TranslationServiceProvider:\n";
    $provider = new \IslamWiki\Providers\TranslationServiceProvider();
    $provider->register($container);
    echo "   ✅ TranslationServiceProvider registered successfully\n";
    
    // Test 6: Check if TranslationService is available
    echo "\n6. Checking TranslationService availability:\n";
    if ($container->has(\IslamWiki\Services\TranslationService::class)) {
        echo "   ✅ Container has TranslationService\n";
        $translationService = $container->get(\IslamWiki\Services\TranslationService::class);
        echo "   ✅ Successfully retrieved TranslationService\n";
    } else {
        echo "   ❌ Container does not have TranslationService\n";
    }
    
    // Test 7: Check if SubdomainLanguageMiddleware is available
    echo "\n7. Checking SubdomainLanguageMiddleware availability:\n";
    if ($container->has(\IslamWiki\Http\Middleware\SubdomainLanguageMiddleware::class)) {
        echo "   ✅ Container has SubdomainLanguageMiddleware\n";
        $middleware = $container->get(\IslamWiki\Http\Middleware\SubdomainLanguageMiddleware::class);
        echo "   ✅ Successfully retrieved SubdomainLanguageMiddleware\n";
    } else {
        echo "   ❌ Container does not have SubdomainLanguageMiddleware\n";
    }
    
    // Test 8: Check if alias is working
    echo "\n8. Checking alias resolution:\n";
    if ($container->has('language.middleware')) {
        echo "   ✅ Container has 'language.middleware' alias\n";
        try {
            $middleware = $container->get('language.middleware');
            echo "   ✅ Successfully retrieved via alias\n";
        } catch (Exception $e) {
            echo "   ❌ Failed to get via alias: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Container does not have 'language.middleware' alias\n";
    }
    
    // Test 9: Check if translation alias is working
    echo "\n9. Checking translation alias:\n";
    if ($container->has('translation')) {
        echo "   ✅ Container has 'translation' alias\n";
        try {
            $translation = $container->get('translation');
            echo "   ✅ Successfully retrieved via alias\n";
        } catch (Exception $e) {
            echo "   ❌ Failed to get via alias: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Container does not have 'translation' alias\n";
    }
    
    // Test 10: List all available bindings
    echo "\n10. Available bindings:\n";
    $bindings = [];
    $instances = [];
    $aliases = [];
    
    // Use reflection to access private properties
    $reflection = new ReflectionClass($container);
    
    $bindingsProp = $reflection->getProperty('bindings');
    $bindingsProp->setAccessible(true);
    $bindings = $bindingsProp->getValue($container);
    
    $instancesProp = $reflection->getProperty('instances');
    $instancesProp->setAccessible(true);
    $instances = $instancesProp->getValue($container);
    
    $aliasesProp = $reflection->getProperty('aliases');
    $aliasesProp->setAccessible(true);
    $aliases = $aliasesProp->getValue($container);
    
    echo "   Bindings: " . count($bindings) . "\n";
    foreach ($bindings as $key => $value) {
        echo "     - {$key}\n";
    }
    
    echo "   Instances: " . count($instances) . "\n";
    foreach ($instances as $key => $value) {
        echo "     - {$key}\n";
    }
    
    echo "   Aliases: " . count($aliases) . "\n";
    foreach ($aliases as $key => $value) {
        echo "     - {$key} -> {$value}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 