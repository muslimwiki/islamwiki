<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Container;\Container

echo "Testing container resolve method...\n";

try {
    // Create container
    $container = new ContainerContainer();

    // Bind a simple closure
    $container->bind('test', function ($container) {
        return 'test_value';
    });

    // Try to get the test value
    echo "Getting test from container...\n";
    $result = $container->get('test');

    echo "Result type: " . gettype($result) . "\n";
    echo "Result value: " . var_export($result, true) . "\n";

    // Test with a class
    $container->bind('test_class', function ($container) {
        return new stdClass();
    });

    echo "Getting test_class from container...\n";
    $result2 = $container->get('test_class');

    echo "Result2 type: " . gettype($result2) . "\n";
    echo "Result2 class: " . get_class($result2) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
