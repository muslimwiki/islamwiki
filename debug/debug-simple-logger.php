<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Logging;\Logger

echo "Testing Logging logger creation...\n";

try {
    $logger = new Logger(__DIR__ . '/../storage/logs');
    echo "Logger created successfully\n";
    echo "Logger type: " . gettype($logger) . "\n";
    echo "Logger class: " . get_class($logger) . "\n";
        $temp_6b868f35 = (($logger instanceof \Psr\Log\LoggerInterface) ? 'yes' : 'no') . "\n";
        echo "Implements LoggerInterface: " . $temp_6b868f35;

    // Test logging
    $logger->info('Test log message');
    echo "Logging test successful\n";
} catch (Exception $e) {
    echo "Error creating logger: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
