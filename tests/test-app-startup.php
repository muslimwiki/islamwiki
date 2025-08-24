<?php

// Test application startup
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing application startup...\n";

// Test autoloader
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✓ Autoloader working\n";
} catch (Exception $e) {
    echo "✗ Autoloader failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test application creation
try {
    $app = new \IslamWiki\Core\Application . '/..');
    echo "✓ Application created\n";

    $container = $app->getContainer();
    echo "✓ Container created\n";

    echo "✓ Application startup successful!\n";
} catch (Exception $e) {
    echo "✗ Application startup failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✓ Application startup test completed!\n";
