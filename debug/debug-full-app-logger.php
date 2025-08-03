<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Container\Asas;
use Psr\Log\LoggerInterface;

echo "Testing logger binding in full app context...\n";

try {
    // Create application (this should set up the container properly)
    $app = new Application(__DIR__ . '/..');
    $app->bootstrap();
    
    // Get the container from the application
    $container = $app->getContainer();
    
    echo "Container type: " . gettype($container) . "\n";
    echo "Container class: " . get_class($container) . "\n";
    
    // Check if LoggerInterface is bound
    echo "Has LoggerInterface: " . ($container->has(LoggerInterface::class) ? 'yes' : 'no') . "\n";
    
    // Try to get logger
    echo "Getting logger from container...\n";
    $logger = $container->get(LoggerInterface::class);
    
    echo "Logger type: " . gettype($logger) . "\n";
    if (is_object($logger)) {
        echo "Logger class: " . get_class($logger) . "\n";
        echo "Implements LoggerInterface: " . (($logger instanceof LoggerInterface) ? 'yes' : 'no') . "\n";
    } else {
        echo "Logger value: " . var_export($logger, true) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 