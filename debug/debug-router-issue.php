<?php

/**
 * Debug Router Issue
 *
 * This script tests the router to identify why it's returning 404 for all requests.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "<h1>🔍 Debug Router Issue</h1>";

try {
    // Test 1: Check if classes can be loaded
    echo "<h2>Test 1: Class Loading</h2>";

    $classes = [
        'IslamWiki\Core\Application',
        'IslamWiki\Core\Container\Container
        'IslamWiki\Core\Routing\IslamRouter',
        'IslamWiki\Core\Http\Request',
        'IslamWiki\Core\Http\Response'
    ];

    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "✅ $class - OK<br>";
        } else {
            echo "❌ $class - NOT FOUND<br>";
        }
    }

    // Test 2: Initialize Application
    echo "<h2>Test 2: Application Initialization</h2>";
    $app = new \IslamWiki\Core\Application(BASE_PATH);
    echo "✅ Application created successfully<br>";

    $container = $app->getContainer();
    echo "✅ Container retrieved successfully<br>";

    // Test 3: Initialize Router
    echo "<h2>Test 3: Router Initialization</h2>";
    $router = new \IslamWiki\Core\Routing\IslamRouter($container);
    echo "✅ Router created successfully<br>";

    // Test 4: Load Routes
    echo "<h2>Test 4: Route Loading</h2>";
    require_once BASE_PATH . '/routes/web.php';
    echo "✅ Routes loaded successfully<br>";

    // Test 5: Create Request
    echo "<h2>Test 5: Request Creation</h2>";
    $request = \IslamWiki\Core\Http\Request::capture();
    echo "✅ Request captured successfully<br>";
    echo "Request URI: " . $request->getUri() . "<br>";
    echo "Request Method: " . $request->getMethod() . "<br>";

    // Test 6: Handle Request
    echo "<h2>Test 6: Request Handling</h2>";
    try {
        $response = $router->handle($request);
        echo "✅ Request handled successfully<br>";
        echo "Response Status: " . $response->getStatusCode() . "<br>";
        $temp_7f3bab53 = strlen($response->getBody()) . " characters<br>";
        echo "Response Body Length: " . $temp_7f3bab53;
    } catch (\Exception $e) {
        echo "❌ Request handling failed: " . $e->getMessage() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    }

    // Test 7: Check Router Routes
    echo "<h2>Test 7: Router Routes</h2>";
    if (method_exists($router, 'getRoutes')) {
        $routes = $router->getRoutes();
        echo "Total routes: " . count($routes) . "<br>";
        foreach ($routes as $method => $methodRoutes) {
            echo "Method $method: " . count($methodRoutes) . " routes<br>";
        }
    } else {
        echo "⚠️ Router doesn't have getRoutes method<br>";
    }
} catch (\Exception $e) {
    echo "<h2>❌ Fatal Error</h2>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
