<?php

/**
 * Test Middleware Registration
 * 
 * Debug script to test if the SubdomainLanguageMiddleware is being properly
 * registered and can be instantiated.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "=== Testing Middleware Registration ===\n\n";

try {
    // Test 1: Check if classes exist
    echo "1. Checking if required classes exist:\n";
    
    $requiredClasses = [
        'IslamWiki\Core\Container\AsasContainer',
        'IslamWiki\Services\TranslationService',
        'IslamWiki\Http\Middleware\SubdomainLanguageMiddleware',
        'IslamWiki\Core\Caching\RihlahCaching',
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
    
    // Test 2: Check if TranslationServiceProvider exists
    echo "2. Checking TranslationServiceProvider:\n";
    if (class_exists('IslamWiki\Providers\TranslationServiceProvider')) {
        echo "   ✅ TranslationServiceProvider - EXISTS\n";
    } else {
        echo "   ❌ TranslationServiceProvider - MISSING\n";
    }
    
    echo "\n";
    
    // Test 3: Try to create a simple container
    echo "3. Testing container creation:\n";
    try {
        $container = new \IslamWiki\Core\Container\AsasContainer();
        echo "   ✅ Container created successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Failed to create container: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Test 4: Try to register the TranslationServiceProvider
    echo "4. Testing TranslationServiceProvider registration:\n";
    try {
        $container = new \IslamWiki\Core\Container\AsasContainer();
        
        // Create a simple mock logger
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
        
        // Register the mock logger
        $container->instance(\Psr\Log\LoggerInterface::class, $mockLogger);
        
        // Create a simple mock cache
        $mockCache = new class {
            public function get($key) { return null; }
            public function set($key, $value, $ttl = 0): bool { return true; }
            public function delete($key): bool { return true; }
            public function has($key): bool { return false; }
            public function clear($driver = 'memory'): bool { return true; }
            public function getStats(): array { return []; }
            public function remember($key, callable $callback, $ttl = 3600, $driver = 'memory') { return $callback(); }
            public function rememberQuery($key, callable $query, $ttl = 3600): array { return []; }
            public function rememberApiResponse($key, callable $apiCall, $ttl = 1800): array { return []; }
            public function rememberTemplate($key, callable $template, $ttl = 7200): string { return ''; }
            public function getDriver($driver) { return null; }
            public function getDrivers(): array { return []; }
            public function warmUp(): void {}
        };
        
        // Register the mock cache
        $container->instance(\IslamWiki\Core\Caching\RihlahCaching::class, $mockCache);
        
        // Now try to register the TranslationServiceProvider
        $provider = new \IslamWiki\Providers\TranslationServiceProvider();
        $provider->register($container);
        
        echo "   ✅ TranslationServiceProvider registered successfully\n";
        
        // Test 5: Try to get the middleware from container
        echo "\n5. Testing middleware retrieval from container:\n";
        try {
            if ($container->has(\IslamWiki\Http\Middleware\SubdomainLanguageMiddleware::class)) {
                echo "   ✅ Container has SubdomainLanguageMiddleware\n";
                
                $middleware = $container->get(\IslamWiki\Http\Middleware\SubdomainLanguageMiddleware::class);
                echo "   ✅ Successfully retrieved SubdomainLanguageMiddleware\n";
                
                // Test if it has the required methods
                $reflection = new ReflectionClass($middleware);
                $methods = ['process', 'handle', 'getCurrentLanguage', 'isCurrentLanguageRTL'];
                
                foreach ($methods as $method) {
                    if ($reflection->hasMethod($method)) {
                        echo "   ✅ Method {$method} exists\n";
                    } else {
                        echo "   ❌ Method {$method} missing\n";
                    }
                }
                
            } else {
                echo "   ❌ Container does not have SubdomainLanguageMiddleware\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Failed to get middleware: " . $e->getMessage() . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Failed to register provider: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 