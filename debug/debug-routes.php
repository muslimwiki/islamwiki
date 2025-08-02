<?php
/**
 * Debug script to check route loading
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

echo "=== Route Debug Test ===\n";

// Create application
$app = new \IslamWiki\Core\Application(BASE_PATH);

// Get container
$container = $app->getContainer();

// Test router
$router = new \IslamWiki\Core\Routing\IslamRouter($container);

// Check if routes are loaded
$reflection = new \ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "Routes loaded: " . count($routes) . "\n";

if (count($routes) > 0) {
    echo "First few routes:\n";
    foreach (array_slice($routes, 0, 5) as $route) {
        echo "  " . implode('|', $route['methods']) . " " . $route['route'] . " -> " . (is_string($route['handler']) ? $route['handler'] : gettype($route['handler'])) . "\n";
    }
} else {
    echo "No routes loaded!\n";
}

// Test finding a specific route
$routeMatch = $router->findRoute('GET', '/');
if ($routeMatch) {
    echo "Route '/' found: " . (is_string($routeMatch['handler']) ? $routeMatch['handler'] : gettype($routeMatch['handler'])) . "\n";
} else {
    echo "Route '/' NOT found\n";
}

echo "=== Test Complete ===\n"; 