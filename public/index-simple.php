<?php

/**
 * Simple IslamWiki Application Entry Point
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Create a simple working application
$container = new \IslamWiki\Core\Container\AsasContainer();

// Create basic services
$logger = new \IslamWiki\Core\Logging\ShahidLogger(BASE_PATH . '/storage/logs');
$db = new \IslamWiki\Core\Database\Connection([]);
$session = new \IslamWiki\Core\Session\WisalSession([]);

// Bind services to container
$container->instance('logger', $logger);
$container->instance('db', $db);
$container->instance('session', $session);
$container->instance(\Psr\Log\LoggerInterface::class, $logger);
$container->instance(\IslamWiki\Core\Database\Connection::class, $db);
$container->instance(\IslamWiki\Core\Session\WisalSession::class, $session);

// Create router
$router = new \IslamWiki\Core\Routing\SabilRouting($container);

// Load routes
require_once BASE_PATH . '/routes/web.php';

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
    echo '<h1>Application Error</h1>';
    echo '<p>An error occurred while processing your request.</p>';
    if (ini_get('display_errors')) {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
}
