<?php

/**
 * Test Container Resolution
 * 
 * Simple test to check if the container can properly resolve the SubdomainLanguageMiddleware.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "=== Testing Container Resolution ===\n\n";

try {
    // Test 1: Create container
    echo "1. Creating container:\n";
    $container = new \IslamWiki\Core\Container\AsasContainer();
    echo "   ✅ Container created successfully\n";
    
    // Test 2: Create mock logger
    echo "\n2. Creating mock logger:\n";
    $mockLogger = new class implements \Psr\Log\LoggerInterface {
        public function emergency($message, array $context = array()): void { echo "      EMERGENCY: {$message}\n"; }
        public function alert($message, array $context = array()): void { echo "      ALERT: {$message}\n"; }
        public function critical($message, array $context = array()): void { echo "      CRITICAL: {$message}\n"; }
        public function error($message, array $context = array()): void { echo "      ERROR: {$message}\n"; }
        public function warning($message, array $context = array()): void { echo "      WARNING: {$message}\n"; }
        public function notice($message, array $context = array()): void { echo "      NOTICE: {$message}\n"; }
        public function info($message, array $context = array()): void { echo "      INFO: {$message}\n"; }
        public function debug($message, array $context = array()): void { echo "      DEBUG: {$message}\n"; }
        public function log($level, $message, array $context = array()): void { echo "      LOG[{$level}]: {$message}\n"; }
    };
    echo "   ✅ Mock logger created successfully\n";
    
    // Test 3: Register logger in container
    echo "\n3. Registering logger in container:\n";
    $container->instance(\Psr\Log\LoggerInterface::class, $mockLogger);
    echo "   ✅ Logger registered successfully\n";
    
    // Test 4: Check if TranslationService can be created
    echo "\n4. Testing TranslationService creation:\n";
    try {
        $translationService = new \IslamWiki\Services\TranslationService($mockLogger);
        echo "   ✅ TranslationService created successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Failed to create TranslationService: " . $e->getMessage() . "\n";
        return;
    }
    
    // Test 5: Check if SubdomainLanguageMiddleware can be created
    echo "\n5. Testing SubdomainLanguageMiddleware creation:\n";
    try {
        $middleware = new \IslamWiki\Http\Middleware\SubdomainLanguageMiddleware($mockLogger, $translationService);
        echo "   ✅ SubdomainLanguageMiddleware created successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Failed to create SubdomainLanguageMiddleware: " . $e->getMessage() . "\n";
        return;
    }
    
    // Test 6: Register TranslationServiceProvider
    echo "\n6. Registering TranslationServiceProvider:\n";
    try {
        $provider = new \IslamWiki\Providers\TranslationServiceProvider();
        $provider->register($container);
        echo "   ✅ TranslationServiceProvider registered successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Failed to register TranslationServiceProvider: " . $e->getMessage() . "\n";
        return;
    }
    
    // Test 7: Check if container has the middleware
    echo "\n7. Checking if container has the middleware:\n";
    if ($container->has(\IslamWiki\Http\Middleware\SubdomainLanguageMiddleware::class)) {
        echo "   ✅ Container has SubdomainLanguageMiddleware\n";
    } else {
        echo "   ❌ Container does not have SubdomainLanguageMiddleware\n";
        return;
    }
    
    // Test 8: Try to get the middleware from container
    echo "\n8. Getting middleware from container:\n";
    try {
        $middlewareFromContainer = $container->get(\IslamWiki\Http\Middleware\SubdomainLanguageMiddleware::class);
        echo "   ✅ Successfully got middleware from container\n";
    } catch (Exception $e) {
        echo "   ❌ Failed to get middleware from container: " . $e->getMessage() . "\n";
        return;
    }
    
    echo "\n🎉 All tests passed! Container resolution is working correctly.\n";
    
} catch (Exception $e) {
    echo "\n❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 