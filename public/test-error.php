<?php
// Set error reporting to maximum
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Set a custom error log
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Test error logging
error_log("=== Starting error test ===");

try {
    // Test 1: Simple error
    error_log("Test 1: Writing to error log");
    
    // Test 2: Trigger a warning
    $test = $undefined_variable;
    
    // Test 3: Trigger a notice
    $array = [];
    $value = $array['nonexistent'];
    
    // Test 4: Trigger an exception
    throw new Exception("This is a test exception");
    
} catch (Throwable $e) {
    error_log("Caught exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Output error details
    header('Content-Type: text/plain');
    echo "=== ERROR DETAILS ===\n";
    echo "Message: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n\n";
    
    // Show environment
    echo "=== ENVIRONMENT ===\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Error Reporting: " . error_reporting() . "\n";
    echo "Display Errors: " . ini_get('display_errors') . "\n";
    echo "Error Log: " . ini_get('error_log') . "\n";
    
    // Show some environment variables
    echo "\n=== ENV VARIABLES ===\n";
    foreach (['APP_ENV', 'APP_DEBUG', 'DB_CONNECTION'] as $var) {
        echo "$var: " . (getenv($var) ?: 'not set') . "\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
?>
