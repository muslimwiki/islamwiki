<?php
// Restore proper Application class usage
require_once __DIR__ . '/../vendor/autoload.php';

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the application
try {
    error_log('INDEX: Starting application bootstrap');
    
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    error_log('INDEX: Application created successfully');
    
    // Create a request object from the current HTTP request
    $request = new \IslamWiki\Core\Http\Request(
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        $_SERVER['REQUEST_URI'] ?? '/'
    );
    
    // Set POST data if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $request = $request->withParsedBody($_POST);
    }
    error_log('INDEX: Request object created');
    
    // Handle the request
    $response = $app->handleRequest($request);
    error_log('INDEX: Request handled successfully');
    
    // Send the response
    $app->sendResponse($response);
    error_log('INDEX: Response sent successfully');
    
} catch (Exception $e) {
    error_log('INDEX: Fatal error: ' . $e->getMessage());
    error_log('INDEX: Stack trace: ' . $e->getTraceAsString());
    
    // Display error for debugging
    echo '<h1>Application Error</h1>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
