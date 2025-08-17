<?php
/**
 * Simple Routing Test
 * 
 * This file tests basic routing functionality to isolate any issues.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Create a simple container
$container = new \IslamWiki\Core\Container\AsasContainer();

// Create router
$router = new \IslamWiki\Core\Routing\SabilRouting($container);

// Test basic routes
$router->get('/test', function ($request) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Basic test route works!');
});

$router->get('/quran', function ($request) {
    return new \IslamWiki\Core\Http\Response(302, ['Location' => '/en/quran'], '');
});

$router->get('/en/quran', function ($request) {
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'English Quran route works!');
});

// Get current request
$request = \IslamWiki\Core\Http\Request::capture();

// Handle the request
try {
    $response = $router->handle($request);
    
    // Send response
    http_response_code($response->getStatusCode());
    
    // Set headers
    foreach ($response->getHeaders() as $name => $values) {
        if (is_array($values)) {
            foreach ($values as $value) {
                header("$name: $value");
            }
        } else {
            header("$name: $values");
        }
    }
    
    // Output content
    echo $response->getBody();
} catch (\Exception $e) {
    // Handle errors
    http_response_code(500);
    echo '<h1>Routing Test Error</h1>';
    echo '<p>An error occurred: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
} 