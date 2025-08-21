<?php

/**
 * Debug script to test router handle method
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

echo "=== Router Handle Debug Test ===\n";

// Create application
$app = new \IslamWiki\Core\Application(BASE_PATH);

// Get container
$container = $app->getContainer();

// Test router
$router = new \IslamWiki\Core\Routing\IslamRouter($container);

// Check if middleware stack is initialized before handle
$reflection = new \ReflectionClass($router);
$middlewareStackProperty = $reflection->getProperty('middlewareStack');
$middlewareStackProperty->setAccessible(true);
$middlewareStack = $middlewareStackProperty->getValue($router);

        $temp_4157577c = ($middlewareStack ? get_class($middlewareStack) : 'null') . "\n";
        echo "Middleware Stack before handle: " . $temp_4157577c;

// Create a PSR-7 request
$psrRequest = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

// Call handle method
try {
    $response = $router->handle($psrRequest);
    echo "Router handle executed successfully\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
} catch (\Exception $e) {
    echo "Router handle error: " . $e->getMessage() . "\n";
}

// Check if middleware stack is initialized after handle
$middlewareStack = $middlewareStackProperty->getValue($router);
        $temp_4157577c = ($middlewareStack ? get_class($middlewareStack) : 'null') . "\n";
        echo "Middleware Stack after handle: " . $temp_4157577c;

if ($middlewareStack) {
    echo "Middleware Count: " . $middlewareStack->count() . "\n";
    $allMiddleware = $middlewareStack->getAll();
    echo "Middleware Classes:\n";
    foreach ($allMiddleware as $mw) {
        echo "  - " . get_class($mw) . "\n";
    }
}

echo "=== Test Complete ===\n";
