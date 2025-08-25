<?php
// Test Application class instantiation
require_once __DIR__ . "/vendor/autoload.php";

try {
    echo "Testing Application class instantiation...\n";
    
    $app = new \IslamWiki\Core\Application(__DIR__);
    echo "Application created successfully!\n";
    
    echo "Testing router...\n";
    $router = $app->getRouter();
    echo "Router class: " . get_class($router) . "\n";
    
    echo "Testing container...\n";
    $container = $app->getContainer();
    echo "Container class: " . get_class($container) . "\n";
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
