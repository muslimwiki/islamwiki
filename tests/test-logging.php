<?php

// Set error reporting to maximum
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Test different log locations
$testLogs = [
    '/var/www/html/local.islam.wiki/logs/error.log',
    '/tmp/php-error.log',
    ini_get('error_log') ?: '/tmp/php-default-error.log'
];

// Test logging to each location
foreach ($testLogs as $logFile) {
    // Ensure directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Set error log
    ini_set('log_errors', '1');
    ini_set('error_log', $logFile);

    // Test logging
    $testMessage = "[TEST] Testing error logging to: $logFile - " . date('Y-m-d H:i:s') . "\n";
    error_log($testMessage);

    // Check if log was written
    $logExists = file_exists($logFile);
    $logWritable = is_writable($logFile);
    $logContent = $logExists ? file_get_contents($logFile) : '';

    echo "<h3>Test: $logFile</h3>";
    echo "<pre>";
    echo "File exists: " . ($logExists ? 'Yes' : 'No') . "\n";
    echo "File writable: " . ($logWritable ? 'Yes' : 'No') . "\n";
    echo "File content: " . htmlspecialchars($logContent) . "\n";
    echo "</pre><hr>";
}

// Test direct file writing
$directTestFile = '/tmp/php-direct-test.log';
$testMessage = "[DIRECT TEST] Testing direct file writing - " . date('Y-m-d H:i:s') . "\n";
$directWrite = file_put_contents($directTestFile, $testMessage, FILE_APPEND);

echo "<h3>Direct File Write Test</h3>";
echo "<pre>";
echo "Test file: $directTestFile\n";
echo "Write result: " . ($directWrite !== false ? 'Success' : 'Failed') . "\n";
if (file_exists($directTestFile)) {
    echo "File content: " . htmlspecialchars(file_get_contents($directTestFile)) . "\n";
}
echo "</pre>";

// Show PHP info
echo "<h3>PHP Configuration</h3>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Error Reporting: " . error_reporting() . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Log Errors: " . ini_get('log_errors') . "\n";
echo "Error Log: " . ini_get('error_log') . "\n";
echo "Open Base Dir: " . ini_get('open_basedir') . "\n";
echo "User/Group: " . get_current_user() . ':' . getmygid() . "\n";
echo "Script Owner: " . get_current_user() . "\n";
echo "Web Server User: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'N/A') . "\n";
echo "</pre>";

// Test writing to a file in the same directory as the script
$scriptDirTestFile = __DIR__ . '/test-write.log';
$scriptDirWrite = file_put_contents($scriptDirTestFile, "Test writing to script directory\n", FILE_APPEND);

echo "<h3>Script Directory Write Test</h3>";
echo "<pre>";
echo "Test file: $scriptDirTestFile\n";
echo "Write result: " . ($scriptDirWrite !== false ? 'Success' : 'Failed') . "\n";
if (file_exists($scriptDirTestFile)) {
    echo "File content: " . htmlspecialchars(file_get_contents($scriptDirTestFile)) . "\n";
}
echo "</pre>";
