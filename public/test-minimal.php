<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Include necessary files
require_once BASE_PATH . '/src/Core/Container/AsasContainer.php';
require_once BASE_PATH . '/src/Core/Routing/SabilRouting.php';
require_once BASE_PATH . '/src/Core/Http/Request.php';
require_once BASE_PATH . '/src/Core/Http/Response.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Routing\SabilRouting;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

echo "Starting minimal test...<br>";

try {
    // Initialize container
    echo "Initializing container...<br>";
    $container = new AsasContainer();
    echo "Container initialized successfully<br>";

    // Initialize router
    echo "Initializing router...<br>";
    $router = new SabilRouting($container);
    echo "Router initialized successfully<br>";

    // Add a test route
    echo "Adding test route...<br>";
    $router->get('/test-minimal', function ($request) {
        return new Response(200, ['Content-Type' => 'text/html'], '<h1>Minimal test route works!</h1>');
    });
    echo "Test route added successfully<br>";

    // Test route handling
    echo "Testing route handling...<br>";
    $request = Request::capture();
    echo "Request captured: " . $request->getUri()->getPath() . "<br>";
    
    $response = $router->handle($request);
    echo "Response generated: " . $response->getStatusCode() . "<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?> 