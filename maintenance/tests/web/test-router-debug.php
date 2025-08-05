<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use IslamWiki\Core\Routing\IslamRouter;
use IslamWiki\Core\Container;

// Create a simple container
$container = new Container();

// Create router
$router = new IslamRouter($container);

// Add some test routes
$router->get('/test-route', function($request) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Test route works!');
});

$router->get('/test-param/{id}', function($request, $id) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], "Test param route works! ID: $id");
});

$router->get('/test-404', function($request) {
    return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/plain'], '404 Test Page');
});

// Test route matching
echo "Testing route matching...\n";

// Test 1: Simple route
$request = new \IslamWiki\Core\Http\Request('GET', '/test-route');
try {
    $response = $router->handle($request);
    echo "Test 1 (simple route): " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Test 1 failed: " . $e->getMessage() . "\n";
}

// Test 2: Parameterized route
$request = new \IslamWiki\Core\Http\Request('GET', '/test-param/123');
try {
    $response = $router->handle($request);
    echo "Test 2 (param route): " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Test 2 failed: " . $e->getMessage() . "\n";
}

// Test 3: Non-existent route
$request = new \IslamWiki\Core\Http\Request('GET', '/nonexistent');
try {
    $response = $router->handle($request);
    echo "Test 3 (404 route): " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "Test 3 failed: " . $e->getMessage() . "\n";
}

echo "Router debug test completed.\n"; 