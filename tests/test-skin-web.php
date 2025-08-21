<?php

/**
 * Test script to simulate a real web request and test skin middleware
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Load LocalSettings.php
require_once BASE_PATH . '/LocalSettings.php';

echo "=== Web Request Skin Test ===\n";

// Create application
$app = new \IslamWiki\Core\Application(BASE_PATH);

// Get container
$container = $app->getContainer();

// Test router
$router = new \IslamWiki\Core\Routing\IslamRouter($container);

// Load the web routes
$webRoutesPath = BASE_PATH . '/routes/web.php';
if (file_exists($webRoutesPath)) {
    // Store the router in a local variable for use in the routes file
    $router = $router;

    // Load routes
    require $webRoutesPath;
    echo "Routes loaded successfully\n";
} else {
    echo "ERROR: Web routes file not found at: " . $webRoutesPath . "\n";
    exit(1);
}

// Create a PSR-7 request that simulates a real web request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'local.islam.wiki';
$_SERVER['HTTPS'] = 'on';

$psrRequest = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

// Call handle method
try {
    $response = $router->handle($psrRequest);
    echo "Router handle executed successfully\n";
    echo "Response status: " . $response->getStatusCode() . "\n";

    // Get the response body
    $body = $response->getBody()->getContents();

    echo "Response body length: " . strlen($body) . "\n";
    echo "Response body preview: " . substr($body, 0, 500) . "...\n";

    // Check if skin_css is in the response
    if (strpos($body, 'skin_css') !== false) {
        echo "skin_css variable found in response\n";
    } else {
        echo "skin_css variable NOT found in response\n";
    }

    // Check for GreenSkin CSS
    if (strpos($body, 'GreenSkin') !== false) {
        echo "GreenSkin CSS found in response\n";
    } else {
        echo "GreenSkin CSS NOT found in response\n";
    }

    // Check for Bismillah CSS
    if (strpos($body, 'Bismillah') !== false) {
        echo "Bismillah CSS found in response\n";
    } else {
        echo "Bismillah CSS NOT found in response\n";
    }

    // Show a snippet of the CSS content
    $cssStart = strpos($body, '/*');
    if ($cssStart !== false) {
        $cssEnd = strpos($body, '*/', $cssStart);
        if ($cssEnd !== false) {
            $cssComment = substr($body, $cssStart, $cssEnd - $cssStart + 2);
            echo "CSS Comment: " . $cssComment . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Router handle error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "=== Test Complete ===\n";
