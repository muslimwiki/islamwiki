<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Container\AsasContainer;
use Psr\Log\LoggerInterface;

echo "Testing logger binding in full app context...\n";

try {
    // Create application (this should set up the container properly)
    $app = new NizamApplication(__DIR__ . '/..');
    $app->bootstrap();

    // Get the container from the application
    $container = $app->getContainer();

    echo "Container type: " . gettype($container) . "\n";
    echo "Container class: " . get_class($container) . "\n";

    // Check if LoggerInterface is bound
        $temp_ea10d4dd = ($container->has(LoggerInterface::class) ? 'yes' : 'no') . "\n";
        echo "Has LoggerInterface: " . $temp_ea10d4dd;

    // Try to get logger
    echo "Getting logger from container...\n";
    $logger = $container->get(LoggerInterface::class);

    echo "Logger type: " . gettype($logger) . "\n";
    if (is_object($logger)) {
        echo "Logger class: " . get_class($logger) . "\n";
        $temp_6727c5f8 = (($logger instanceof LoggerInterface) ? 'yes' : 'no') . "\n";
        echo "Implements LoggerInterface: " . $temp_6727c5f8;
    } else {
        echo "Logger value: " . var_export($logger, true) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
