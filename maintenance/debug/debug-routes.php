<?php

/**
 * Debug Routes
 *
 * This script shows what routes are registered in the router.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "<h1>🔍 Debug Routes</h1>";

try {
    // Initialize Application
    $app = new \IslamWiki\Core\Application(BASE_PATH);
    $container = $app->getContainer();
    $container->instance('app', $app);

    // Initialize router
    $router = new \IslamWiki\Core\Routing\IslamRouter($container);

    // Load routes
    require_once BASE_PATH . '/routes/web.php';

    echo "<h2>Router Routes</h2>";

    // Check if router has a method to get routes
    if (method_exists($router, 'getRoutes')) {
        $routes = $router->getRoutes();
        echo "<p>Total routes: " . count($routes) . "</p>";

        foreach ($routes as $method => $methodRoutes) {
            echo "<h3>Method: $method</h3>";
            echo "<ul>";
            foreach ($methodRoutes as $pattern => $handler) {
                $temp_22c91c83 = (is_string($handler) ? $handler : 'Closure') . "</li>";
                echo "<li><strong>$pattern</strong> → " . $temp_22c91c83;
            }
            echo "</ul>";
        }
    } else {
        echo "<p>⚠️ Router doesn't have getRoutes method</p>";

        // Try to access routes through reflection
        $reflection = new ReflectionClass($router);
        $properties = $reflection->getProperties();

        echo "<h3>Router Properties:</h3>";
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($router);
            $temp_94e763a3 = $property->getName() . ":</strong> " . gettype($value) . "</p>";
            echo "<p><strong>" . $temp_94e763a3;
        }
    }

    // Test specific routes
    echo "<h2>Test Specific Routes</h2>";
    $testPaths = ['/', '/about', '/test-router-alive', '/index-debug.php'];

    foreach ($testPaths as $path) {
        echo "<h3>Testing: $path</h3>";
        try {
            $request = new \IslamWiki\Core\Http\Request('GET', new \IslamWiki\Core\Http\Uri($path));
            $response = $router->handle($request);
            $temp_b850c93a = $response->getStatusCode() . "</p>";
            echo "<p>✅ Route found - Status: " . $temp_b850c93a;
        } catch (\Exception $e) {
            echo "<p>❌ Route not found: " . $e->getMessage() . "</p>";
        }
    }
} catch (\Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
