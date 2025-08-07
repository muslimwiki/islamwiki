<?php

/**
 * Debug script to test middleware execution
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

echo "=== Middleware Debug Test ===\n";

// Create application
$app = new \IslamWiki\Core\Application(BASE_PATH);

// Get container
$container = $app->getContainer();

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

// Test creating a request
$request = \IslamWiki\Core\Http\Request::capture();
echo "Request created: " . $request->getUri()->getPath() . "\n";

// Test middleware directly
$skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
echo "Skin Middleware created: " . get_class($skinMiddleware) . "\n";

// Test the middleware handle method
try {
    $response = $skinMiddleware->handle($request, function ($req) {
        return new \IslamWiki\Core\Http\Response(200, [], 'Test response');
    });
    echo "Skin Middleware executed successfully\n";
} catch (\Exception $e) {
    echo "Skin Middleware error: " . $e->getMessage() . "\n";
}

echo "=== Test Complete ===\n";
