<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Logging\Shahid;
use IslamWiki\Core\Logging\ShahidInterface;

echo "Testing Shahid class compatibility with ShahidInterface...\n";

try {
    // Test instantiation
    $shahid = new Shahid('/tmp', 'debug');
    echo "✓ Shahid class instantiated successfully\n";
    
    // Test interface implementation
    if ($shahid instanceof ShahidInterface) {
        echo "✓ Shahid implements ShahidInterface correctly\n";
    } else {
        echo "✗ Shahid does not implement ShahidInterface\n";
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