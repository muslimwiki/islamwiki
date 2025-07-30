<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Application;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Uri;

echo "Testing Page Route\n";
echo "==================\n\n";

try {
    // Create application
    $app = new Application(__DIR__ . '/..');
    echo "✅ Application created\n";
    
    // Create a mock request for /welcome
    $uri = new Uri('http://local.islam.wiki/welcome');
    $request = new Request('GET', $uri);
    echo "✅ Request created\n";
    
    // Get the router
    $router = $app->getRouter();
    echo "✅ Router obtained\n";
    
    // Try to match the route
    $route = $router->match($request);
    echo "✅ Route matched: " . ($route ? 'Yes' : 'No') . "\n";
    
    if ($route) {
        echo "  - Handler: " . $route->getHandler() . "\n";
        echo "  - Parameters: " . json_encode($route->getParameters()) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ Page route test completed successfully!\n";
echo "\nDone!\n"; 