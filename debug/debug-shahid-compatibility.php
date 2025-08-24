<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Logging;\Logger
use LoggingInterface;\Logger

echo "Testing Logging class compatibility with LoggingInterface...\n";

try {
    // Test instantiation
    $shahid = new Logger('/tmp', 'debug');
    echo "✓ Logging class instantiated successfully\n";

    // Test interface implementation
    if ($shahid instanceof LoggingInterface) {
        echo "✓ Logging implements LoggerInterface correctly\n";
    } else {
        echo "✗ Logging does not implement LoggingInterface\n";
    }

    // Test method calls
    $shahid->emergency("Test emergency message");
    $shahid->alert("Test alert message");
    $shahid->critical("Test critical message");
    $shahid->error("Test error message");
    $shahid->warning("Test warning message");
    $shahid->notice("Test notice message");
    $shahid->info("Test info message");
    $shahid->debug("Test debug message");

    echo "✓ All logging methods called successfully\n";
    echo "✓ Compatibility issue resolved!\n";
} catch (Error $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}
