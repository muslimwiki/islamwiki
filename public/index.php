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

    // Load application configuration and bootstrap
    $app = new \IslamWiki\Core\NizamApplication(__DIR__ . '/..');

    // Load and register routes
    $routes = require __DIR__ . '/../config/routes.php';
    $routes($app);

    // Create request from globals
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $request = new \IslamWiki\Core\Http\Request($method, $path, getallheaders(), file_get_contents('php://input'), '1.1', $_SERVER);

    // Handle the request
    $response = $app->handleRequest($request);

    // Send the response
    $app->sendResponse($response);

} catch (\IslamWiki\Core\Http\HttpException $e) {
    // Handle HTTP exceptions (404, 500, etc.)
    $app = $app ?? new \IslamWiki\Core\NizamApplication(__DIR__ . '/..');
    $errorResponse = new \IslamWiki\Core\Http\Response($e->getStatusCode(), [
        'Content-Type' => 'text/html; charset=UTF-8',
        'X-Error-Type' => 'http_exception'
    ], $app->renderErrorPage($e->getStatusCode(), $e));
    
    $app->sendResponse($errorResponse);

} catch (\Throwable $e) {
    // Handle critical application errors
    $app = $app ?? new \IslamWiki\Core\NizamApplication(__DIR__ . '/..');
    $errorResponse = new \IslamWiki\Core\Http\Response(500, [
        'Content-Type' => 'text/html; charset=UTF-8',
        'X-Error-Type' => 'critical_error'
    ], $app->renderErrorPage(500, $e));
    
    $app->sendResponse($errorResponse);
}

// End output buffering and send content
ob_end_flush();
