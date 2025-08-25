<?php
// Test loading routes file directly
require_once __DIR__ . "/vendor/autoload.php";

try {
    echo "Testing routes file loading...\n";
    
    $routesFile = __DIR__ . "/config/routes.php";
    echo "Routes file: " . $routesFile . "\n";
    
    if (file_exists($routesFile)) {
        echo "Routes file exists\n";
        
        // Try to load the routes file
        echo "Loading routes file...\n";
        $routesCallback = require $routesFile;
        echo "Routes file loaded\n";
        
        if (is_callable($routesCallback)) {
            echo "Routes callback is callable\n";
            
            // Create a mock app to test the callback
            $app = new \IslamWiki\Core\Application(__DIR__);
            echo "Mock app created\n";
            
            // Call the routes callback
            echo "Calling routes callback...\n";
            $routesCallback($app);
            echo "Routes callback executed successfully\n";
            
            // Check if routes were registered
            $router = $app->getRouter();
            $routes = $router->getRoutes();
            echo "Router has " . count($routes) . " methods registered\n";
            
        } else {
            echo "Routes callback is NOT callable\n";
            echo "Type: " . gettype($routesCallback) . "\n";
            if (is_object($routesCallback)) {
                echo "Class: " . get_class($routesCallback) . "\n";
            }
        }
        
    } else {
        echo "Routes file does not exist\n";
    }
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
