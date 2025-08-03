<?php
/**
 * Test Route Registration
 * 
 * This script tests if routes are being registered correctly.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "<h1>🔍 Test Route Registration</h1>";

try {
    // Initialize Application
    $app = new \IslamWiki\Core\Application(BASE_PATH);
    $container = $app->getContainer();
    $container->instance('app', $app);

    // Initialize router
    $router = new \IslamWiki\Core\Routing\IslamRouter($container);
    
    echo "<h2>Step 1: Check Router Before Routes</h2>";
    
    // Check routes before loading
    $reflection = new ReflectionClass($router);
    $routesProperty = $reflection->getProperty('routes');
    $routesProperty->setAccessible(true);
    $routes = $routesProperty->getValue($router);
    
    echo "<p>Routes before loading: " . count($routes) . "</p>";
    
    echo "<h2>Step 2: Load Routes</h2>";
    
    // Load routes
    require_once BASE_PATH . '/routes/web.php';
    
    echo "<p>✅ Routes file loaded</p>";
    
    echo "<h2>Step 3: Check Router After Routes</h2>";
    
    // Check routes after loading
    $routes = $routesProperty->getValue($router);
    echo "<p>Routes after loading: " . count($routes) . "</p>";
    
    if (count($routes) > 0) {
        echo "<h3>First 5 Routes:</h3>";
        echo "<ul>";
        foreach (array_slice($routes, 0, 5) as $route) {
            echo "<li><strong>" . implode('|', $route['methods']) . " " . $route['route'] . "</strong> → " . 
                 (is_string($route['handler']) ? $route['handler'] : 'Closure') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ No routes found!</p>";
    }
    
    echo "<h2>Step 4: Test Route Matching</h2>";
    
    // Test route matching
    $testPaths = ['/', '/about', '/test-router-alive'];
    
    foreach ($testPaths as $path) {
        echo "<h3>Testing: $path</h3>";
        
        // Use reflection to call findRoute
        $findRouteMethod = $reflection->getMethod('findRoute');
        $findRouteMethod->setAccessible(true);
        
        $routeMatch = $findRouteMethod->invoke($router, 'GET', $path);
        
        if ($routeMatch) {
            echo "<p>✅ Route found: " . (is_string($routeMatch['handler']) ? $routeMatch['handler'] : 'Closure') . "</p>";
        } else {
            echo "<p>❌ Route not found</p>";
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
?> 