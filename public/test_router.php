<?php
declare(strict_types=1);

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load the autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Routing\Router;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Ensure required environment variables are set
$dotenv->required(['APP_ENV', 'APP_DEBUG']);

// Set up error handling first
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Create and bootstrap the application instance
$app = new Application(dirname(__DIR__));

// Initialize the Router with the application instance
$router = Router::getInstance($app);

// Set the application instance for static access
Router::setApplication($app);

// Create a test request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';

// Get the router instance
$router = Router::getInstance($app);

// Handle the request
try {
    $request = \IslamWiki\Core\Http\Request::capture();
    error_log('Request captured: ' . $request->getMethod() . ' ' . $request->getUri()->getPath());
    
    // Debug: Log the router class and method being called
    error_log('Calling router->handle() with request');
    
    $response = $router->handle($request);
    error_log('Response received: ' . get_class($response));
    
    // Debug: Log the response details
    if ($response instanceof \Psr\Http\Message\ResponseInterface) {
        error_log('Response status: ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
        error_log('Response headers: ' . json_encode($response->getHeaders(), JSON_PRETTY_PRINT));
        error_log('Response body length: ' . $response->getBody()->getSize());
        
        // Log the first 200 characters of the body for debugging
        $body = (string) $response->getBody();
        error_log('Response body (first 200 chars): ' . substr($body, 0, 200));
        
        // Rewind the body stream for further processing
        $response->getBody()->rewind();
    }
    
    // Log response details
    if ($response instanceof \Psr\Http\Message\ResponseInterface) {
        error_log('Status: ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
        error_log('Headers: ' . json_encode($response->getHeaders(), JSON_PRETTY_PRINT));
        error_log('Body length: ' . $response->getBody()->getSize());
        
        // Send status code
        http_response_code($response->getStatusCode());
        
        // Send headers
        foreach ($response->getHeaders() as $name => $values) {
            $headerName = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                header(sprintf('%s: %s', $headerName, $value), true); // Changed to true to replace existing headers
                error_log(sprintf('Sending header: %s: %s', $headerName, $value));
            }
        }
        
        // Send body
        $body = (string) $response->getBody();
        error_log('Body content: ' . substr($body, 0, 200) . (strlen($body) > 200 ? '...' : ''));
        echo $body;
    } else {
        $message = "Unexpected response type: " . gettype($response);
        error_log($message);
        echo $message;
    }
} catch (\Throwable $e) {
    // Log the error
    error_log(sprintf(
        'Error in test_router.php: %s in %s:%d',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));
    
    // Send error response
    http_response_code(500);
    header('Content-Type: text/plain');
    echo '500 Internal Server Error';
    
    if (ini_get('display_errors')) {
        echo "\n\n" . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }
}
