<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

echo "Testing application startup...\n";

try {
    // Create application
    $app = new Application();
    echo "✓ Application created successfully\n";
    
    // Get router
    $router = $app->getRouter();
    echo "✓ Router retrieved successfully\n";
    
    // Check if routes were loaded
    echo "\nChecking if routes were loaded...\n";
    
    // Try to access a route
    $request = new \IslamWiki\Core\Http\Request('GET', '/en/login');
    echo "✓ Test request created: GET /en/login\n";
    
    try {
        $response = $app->handleRequest($request);
        echo "✓ Request handled successfully\n";
        echo "Response status: " . $response->getStatusCode() . "\n";
        echo "Response body length: " . strlen($response->getBody()) . "\n";
    } catch (Exception $e) {
        echo "✗ Request handling failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Application error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 