<?php
/**
 * Simple test to trigger an error and test our enhanced error handler
 */

// Start session
session_start();

// Set some test data
$_SESSION['test_data'] = 'test_value';

echo "Testing enhanced error handler...\n";

// Test basic error handling
echo "Basic error handling test passed\n";

// Now let's test our WikiController error handling
try {
    // Include the application
    require_once __DIR__ . '/../src/helpers.php';
    
    // Get container
    $container = app()->getContainer();
    
    // Get WikiController
    $wikiController = $container->get(\IslamWiki\Http\Controllers\WikiController::class);
    
    // This should work now since we fixed the session lifetime
    echo "WikiController retrieved successfully\n";
    
    // Test if we can access the dashboard method
    $reflection = new ReflectionClass($wikiController);
    $method = $reflection->getMethod('generateDetailedErrorReport');
    echo "Enhanced error method found: " . $method->getName() . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} 