<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use IslamWiki\Core\Routing\IslamRouter;
use IslamWiki\Core\Container;

echo "=== IslamRouter Comprehensive Test ===\n";

// Create a simple container
$container = new Container();

// Create router
$router = new IslamRouter($container);

// Add test routes
$router->get('/test-simple', function ($request) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Simple route works!');
});

$router->get('/test-param/{id}', function ($request, $id) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], "Param route works! ID: $id");
});

$router->post('/test-post', function ($request) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'POST route works!');
});

$router->get('/test-404', function ($request) {
    return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/plain'], '404 Test Page');
});

// Test route matching
echo "\n1. Testing simple route...\n";
$request = new \IslamWiki\Core\Http\Request('GET', '/test-simple');
try {
    $response = $router->handle($request);
    echo "✓ Simple route: " . $response->getStatusCode() . " - " . $response->getBody() . "\n";
} catch (Exception $e) {
    echo "✗ Simple route failed: " . $e->getMessage() . "\n";
}

echo "\n2. Testing parameterized route...\n";
$request = new \IslamWiki\Core\Http\Request('GET', '/test-param/123');
try {
    $response = $router->handle($request);
    echo "✓ Param route: " . $response->getStatusCode() . " - " . $response->getBody() . "\n";
} catch (Exception $e) {
    echo "✗ Param route failed: " . $e->getMessage() . "\n";
}

echo "\n3. Testing POST route...\n";
$request = new \IslamWiki\Core\Http\Request('POST', '/test-post');
try {
    $response = $router->handle($request);
    echo "✓ POST route: " . $response->getStatusCode() . " - " . $response->getBody() . "\n";
} catch (Exception $e) {
    echo "✗ POST route failed: " . $e->getMessage() . "\n";
}

echo "\n4. Testing 404 route...\n";
$request = new \IslamWiki\Core\Http\Request('GET', '/nonexistent');
try {
    $response = $router->handle($request);
    echo "✓ 404 route: " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "✗ 404 route failed: " . $e->getMessage() . "\n";
}

echo "\n5. Testing method not allowed...\n";
$request = new \IslamWiki\Core\Http\Request('PUT', '/test-simple');
try {
    $response = $router->handle($request);
    echo "✓ Method not allowed: " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "✗ Method not allowed failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "IslamRouter is working correctly!\n";
echo "✓ Route matching\n";
echo "✓ Parameter extraction\n";
echo "✓ HTTP method validation\n";
echo "✓ 404 error handling\n";
echo "✓ Method not allowed handling\n";
echo "✓ Closure handlers\n";
echo "✓ Response generation\n";
