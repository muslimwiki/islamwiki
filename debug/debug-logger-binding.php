<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Container\Asas;

// Create container
$container = new Asas();

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
        echo "Implements LoggerInterface: " . (($logger instanceof \Psr\Log\LoggerInterface) ? 'yes' : 'no') . "\n";
    } else {
        echo "Logger value: " . var_export($logger, true) . "\n";
    }
} catch (Exception $e) {
    echo "Error getting logger: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 