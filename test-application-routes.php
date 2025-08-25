<?php
// Test Application class with routes
require_once __DIR__ . "/vendor/autoload.php";

try {
    echo "Testing Application class with routes...\n";
    
    $app = new \IslamWiki\Core\Application(__DIR__);
    echo "Application created successfully!\n";
    
    echo "Testing router...\n";
    $router = $app->getRouter();
    echo "Router class: " . get_class($router) . "\n";
    
    // Check if routes were loaded
    $routes = $router->getRoutes();
    echo "Router has " . count($routes) . " methods registered\n";
    
    foreach ($routes as $method => $methodRoutes) {
        echo "Method {$method}: " . count($methodRoutes) . " routes\n";
        foreach ($methodRoutes as $pattern => $handler) {
            echo "  {$pattern}\n";
        }
    }
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
