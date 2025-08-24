<?php
/**
 * Test script to trigger the enhanced error handler
 * This will help us verify that our debug information is being captured correctly
 */

// Start session to test session data capture
session_start();

// Set some test session data
$_SESSION['test_user_id'] = 'test123';
$_SESSION['test_username'] = 'testuser';

// Include the application bootstrap
require_once __DIR__ . '/../src/helpers.php';

try {
    // Get the container
    $container = app()->getContainer();
    
    // Get the WikiController
    $wikiController = $container->get(\IslamWiki\Http\Controllers\WikiController::class);
    
    // This should trigger our enhanced error handling
    echo "Testing enhanced error handler...\n";
    
    // Try to access a method that might fail
    $result = $wikiController->dashboard(new \IslamWiki\Core\Http\Request('GET', '/wiki'));
    
} catch (\Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
    echo "Check the error page for detailed debug information.\n";
    
    // Display the debug info if it's in the session
    if (isset($_SESSION['debug_error_info'])) {
        echo "\n=== DEBUG INFO CAPTURED ===\n";
        echo json_encode($_SESSION['debug_error_info'], JSON_PRETTY_PRINT);
        echo "\n=== END DEBUG INFO ===\n";
    } else {
        echo "No debug info captured in session.\n";
    }
} 