<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Include helpers
require_once BASE_PATH . '/src/helpers.php';

// Load LocalSettings.php for configuration
require_once BASE_PATH . '/LocalSettings.php';

// Include necessary files
require_once BASE_PATH . '/src/Core/Container/AsasContainer.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';
require_once BASE_PATH . '/src/Core/Routing/IslamRouter.php';
require_once BASE_PATH . '/src/Core/Http/Request.php';
require_once BASE_PATH . '/src/Core/Http/Response.php';
require_once BASE_PATH . '/src/Http/Controllers/AssetController.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Routing\IslamRouter;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

// Initialize Application
$app = new \IslamWiki\Core\Application(BASE_PATH);
$container = $app->getContainer();

// Register the application in the container
$container->instance('app', $app);

// Initialize router
$router = new IslamRouter($container);

// Add asset routes directly
$router->get('/assets/css/{filename}', 'IslamWiki\Http\Controllers\AssetController@serveCss');
$router->get('/assets/js/{filename}', 'IslamWiki\Http\Controllers\AssetController@serveJs');

// Add a simple test route
$router->get('/test-simple', function($request) {
    return new Response(200, ['Content-Type' => 'text/html'], '<h1>Simple test route works!</h1>');
});

// Get current request
$request = Request::capture();

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
    echo '<h1>Application Error</h1>';
    echo '<p>An error occurred while processing your request.</p>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . htmlspecialchars($e->getLine()) . '</p>';
    if (ini_get('display_errors')) {
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
}
?> 