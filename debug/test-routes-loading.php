<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing routes file loading...\n";

try {
    // Test 1: Check if routes file exists
    $routesFile = __DIR__ . '/../config/routes.php';
    echo "Routes file path: $routesFile\n";
    
    if (file_exists($routesFile)) {
        echo "✓ Routes file exists\n";
    } else {
        echo "✗ Routes file not found\n";
        exit(1);
    }
    
    // Test 2: Check routes file syntax
    $syntaxCheck = shell_exec("php -l $routesFile 2>&1");
    if (strpos($syntaxCheck, 'No syntax errors detected') !== false) {
        echo "✓ Routes file syntax is valid\n";
    } else {
        echo "✗ Routes file has syntax errors:\n";
        echo $syntaxCheck . "\n";
        exit(1);
    }
    
    // Test 3: Try to load routes
    echo "\nLoading routes...\n";
    $routesCallback = require $routesFile;
    
    if (is_callable($routesCallback)) {
        echo "✓ Routes callback is callable\n";
        
        // Test 4: Try to execute routes callback
        echo "Executing routes callback...\n";
        
        // Create a mock application object
        $mockApp = new class {
            public function getRouter() {
                return new class {
                    public function get($path, $handler) {
                        echo "✓ Route registered: $path\n";
                    }
                };
            }
            public function getContainer() {
                return new class {
                    public function get($service) {
                        return new class {
                            public function __construct() {}
                        };
                    }
                };
            }
        };
        
        try {
            $routesCallback($mockApp);
            echo "✓ Routes callback executed successfully\n";
        } catch (Exception $e) {
            echo "✗ Routes callback execution failed: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
        
    } else {
        echo "✗ Routes file did not return a callable\n";
        echo "Type: " . gettype($routesCallback) . "\n";
        if (is_object($routesCallback)) {
            echo "Class: " . get_class($routesCallback) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error loading routes: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 