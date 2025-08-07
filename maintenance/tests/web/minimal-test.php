<?php

// Test basic PHP functionality
echo "<h1>PHP Test Page</h1>";

// Test 1: Basic output
echo "<p>1. Basic output works</p>";

// Test 2: Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
echo "<p>2. Error reporting set to E_ALL</p>";

// Test 3: File writing
$testFile = '/tmp/php_test_' . time() . '.txt';
$writeResult = file_put_contents($testFile, 'test');
$fileExists = file_exists($testFile);
$content = $fileExists ? file_get_contents($testFile) : 'Could not read file';

if ($writeResult !== false && $fileExists) {
    echo "<p>3. File write test: SUCCESS (Wrote to $testFile)</p>";
    unlink($testFile); // Clean up
} else {
    echo "<p style='color:red'>3. File write test: FAILED (Could not write to $testFile)</p>";
}

// Test 4: Environment variables
echo "<h3>Environment Variables:</h3>";
echo "<pre>";
foreach (['APP_ENV', 'APP_DEBUG', 'DB_CONNECTION'] as $var) {
    echo "$var: " . (getenv($var) ?: 'not set') . "\n";
}
echo "</pre>";

// Test 5: PHP Info
if (isset($_GET['phpinfo'])) {
    phpinfo();
    exit;
}

// Test 6: Error logging
$logMessage = "Test log message at " . date('Y-m-d H:i:s');
error_log($logMessage);

// Display a link to phpinfo for more details
echo "<p><a href='?phpinfo=1'>View PHP Info</a> for more details.</p>";

// Show current directory and permissions
echo "<h3>Current Directory:</h3>";
echo "<pre>";
echo "Directory: " . __DIR__ . "\n";
echo "Writable: " . (is_writable(__DIR__) ? 'Yes' : 'No') . "\n";

echo "\n<h3>PHP Settings:</h3>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Error Log: " . ini_get('error_log') . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Error Reporting: " . error_reporting() . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "</pre>";
