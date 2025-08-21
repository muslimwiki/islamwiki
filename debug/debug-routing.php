<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__ . '/../../public/app.php';

// Get the router
$router = $app->getContainer()->get('router');

echo "Router loaded successfully\n";
echo "Routes count: " . count($router->getRoutes()) . "\n";

// Test the test-simple route
$request = new \IslamWiki\Core\Http\Request('GET', '/test-simple');
$response = $router->handle($request);

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response body: " . $response->getBody() . "\n";
