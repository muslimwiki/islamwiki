<?php

declare(strict_types=1);

/**
 * IslamWiki Beautiful Islamic Design Entry Point
 * 
 * Main application entry point for local.islam.wiki
 * 
 * @package IslamWiki\Public
 * @version 0.0.2.2
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

// Initialize error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to prevent premature output
ob_start();

try {
    // Load the autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    // Load environment variables from .env file
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($envLines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    // Load application configuration and bootstrap
    $app = new \IslamWiki\Core\NizamApplication(__DIR__ . '/..');

    // Load and register routes
    $routes = require __DIR__ . '/../config/routes.php';
    $routes($app);

    // Create request from globals
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    
    // Parse POST data for POST requests
    $parsedBody = null;
    if ($method === 'POST') {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            $parsedBody = $_POST;
        } else {
            // For other content types, read from php://input
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                if (strpos($contentType, 'application/json') !== false) {
                    $parsedBody = json_decode($input, true);
                } else {
                    parse_str($input, $parsedBody);
                }
            }
        }
    }
    
    $request = new \IslamWiki\Core\Http\Request($method, $path, getallheaders(), null, '1.1', $_SERVER);
    
    // Set the parsed body if we have POST data
    if ($parsedBody !== null) {
        $request = $request->withParsedBody($parsedBody);
    }

    // Handle the request
    $response = $app->handleRequest($request);

    // Send the response
    $app->sendResponse($response);

} catch (\Exception $e) {
    // Handle HTTP exceptions and other application errors
    ob_end_clean(); // Clear the output buffer
    
    // Try to determine the appropriate status code
    $statusCode = 500;
    if (method_exists($e, 'getStatusCode')) {
        $statusCode = $e->getStatusCode();
    }
    
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=UTF-8');
    
    // In development, show detailed error information
    if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
        echo "<h1>HTTP Exception ({$statusCode})</h1>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>{$statusCode} - Error</h1>";
        echo "<p>An error occurred while processing your request.</p>";
    }
    exit;

} catch (\Throwable $e) {
    // Handle critical application errors
    ob_end_clean(); // Clear the output buffer
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    
    // In development, show detailed error information
    if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>An error occurred while processing your request.</p>";
    }
    exit;
}

// End output buffering and send content
ob_end_flush();
