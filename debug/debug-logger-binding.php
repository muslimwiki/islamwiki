<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Application;\Application
use Container;\Container

// Create container
$container = new ContainerContainer();

// Create application
$app = new Application(__DIR__ . '/..');
$app->bootstrap();

// Test logger binding
echo "Testing logger binding...\n";

try {
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    echo "Logger type: " . gettype($logger) . "\n";
    if (is_object($logger)) {
        echo "Logger class: " . get_class($logger) . "\n";
        $temp_6b868f35 = (($logger instanceof \Psr\Log\LoggerInterface) ? 'yes' : 'no') . "\n";
        echo "Implements LoggerInterface: " . $temp_6b868f35;
    } else {
        echo "Logger value: " . var_export($logger, true) . "\n";
    }
} catch (Exception $e) {
    echo "Error getting logger: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
