<?php
/**
 * Test Route List Debug
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing Route List...\n";

try {
    $app = new IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    $router = $container->get('router');
    
    echo "✅ Application, container, and router loaded\n";
    
    // Get all registered routes
    echo "\n--- Registered Routes ---\n";
    $routes = $router->getRoutes();
    
    if (is_array($routes)) {
        echo "Total routes: " . count($routes) . "\n";
        foreach ($routes as $method => $methodRoutes) {
            echo "\n{$method} routes:\n";
            foreach ($methodRoutes as $pattern => $handler) {
                echo "  - {$pattern} => " . (is_array($handler) ? implode('::', $handler) : $handler) . "\n";
            }
        }
    } else {
        echo "Routes format: " . gettype($routes) . "\n";
        var_dump($routes);
    }
    
    echo "\n✅ Route listing completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 