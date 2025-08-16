<?php

/**
 * Test Web Request
 * 
 * Simple test to simulate a web request and see what's happening.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "=== Testing Web Request ===\n\n";

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
    
    // Test 4: Register TranslationServiceProvider
    echo "\n4. Registering TranslationServiceProvider:\n";
    $provider = new \IslamWiki\Providers\TranslationServiceProvider();
    $provider->register($container);
    echo "   ✅ TranslationServiceProvider registered successfully\n";
    
    // Test 5: Create router
    echo "5. Creating router:\n";
    $router = new \IslamWiki\Core\Routing\SabilRouting($container);
    echo "   ✅ Router created successfully\n";
    
    // Test 5.5: Initialize global middleware
    echo "\n5.5. Initializing global middleware:\n";
    $router->initializeGlobalMiddleware();
    echo "   ✅ Global middleware initialized\n";
    
    // Test 6: Check if middleware was added
    echo "\n6. Checking middleware stack:\n";
    $reflection = new ReflectionClass($router);
    $middlewareStackProperty = $reflection->getProperty('_middlewareStack');
    $middlewareStackProperty->setAccessible(true);
    $middlewareStack = $middlewareStackProperty->getValue($router);
    
    if (empty($middlewareStack)) {
        echo "   ❌ Middleware stack is empty\n";
    } else {
        echo "   ✅ Middleware stack has " . count($middlewareStack) . " items\n";
        foreach ($middlewareStack as $i => $middleware) {
            echo "      - Item {$i}: " . get_class($middleware) . "\n";
        }
    }
    
    echo "\n🎉 Test completed!\n";
    
} catch (Exception $e) {
    echo "\n❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 