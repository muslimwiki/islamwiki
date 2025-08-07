<?php

/**
 * Debug script to test LoggerInterface registration
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Load LocalSettings.php
require_once BASE_PATH . '/LocalSettings.php';

echo "=== Logger Debug Test ===\n";

// Create application
$app = new \IslamWiki\Core\Application(BASE_PATH);

// Get container
$container = $app->getContainer();

// Test if LoggerInterface is available
        $temp_e832ca29 = ($container->has(\Psr\Log\LoggerInterface::class) ? 'Yes' : 'No') . "\n";
        echo "LoggerInterface available: " . $temp_e832ca29;

if ($container->has(\Psr\Log\LoggerInterface::class)) {
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    echo "Logger class: " . get_class($logger) . "\n";
        $temp_0cf29ce5 = (($logger instanceof \Psr\Log\LoggerInterface) ? 'Yes' : 'No') . "\n";
        echo "Logger implements LoggerInterface: " . $temp_0cf29ce5;
} else {
    echo "LoggerInterface not found in container\n";
}

// Test router
$router = new \IslamWiki\Core\Routing\IslamRouter($container);

// Check if middleware stack is initialized
$reflection = new \ReflectionClass($router);
$middlewareStackProperty = $reflection->getProperty('middlewareStack');
$middlewareStackProperty->setAccessible(true);
$middlewareStack = $middlewareStackProperty->getValue($router);

        $temp_4157577c = ($middlewareStack ? get_class($middlewareStack) : 'null') . "\n";
        echo "Middleware Stack: " . $temp_4157577c;

if ($middlewareStack) {
    echo "Middleware Count: " . $middlewareStack->count() . "\n";
    $allMiddleware = $middlewareStack->getAll();
    echo "Middleware Classes:\n";
    foreach ($allMiddleware as $mw) {
        echo "  - " . get_class($mw) . "\n";
    }
}

echo "=== Test Complete ===\n";
