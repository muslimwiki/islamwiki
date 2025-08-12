<?php

/**
 * Test Home Controller
 *
 * This script tests if the HomeController works without skin manager issues.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "<h1>🔍 Test Home Controller</h1>";

try {
    // Initialize Application
    $app = new \IslamWiki\Core\Application(BASE_PATH);
    $container = $app->getContainer();
    $container->instance('app', $app);

    // Initialize router
    $router = new \IslamWiki\Core\Routing\IslamRouter($container);

    // Load routes
    require_once BASE_PATH . '/routes/web.php';

    echo "<h2>Testing HomeController</h2>";

    // Test with root request
    $request = new \IslamWiki\Core\Http\Request('GET', new \IslamWiki\Core\Http\Uri('/'));

    try {
        $response = $router->handle($request);
        echo "<p>✅ HomeController handled request successfully</p>";
        echo "<p><strong>Response Status:</strong> " . $response->getStatusCode() . "</p>";
        echo "<p><strong>Response Body Length:</strong> " . strlen($response->getBody()) . " characters</p>";

        // Show first 500 characters of response
        $body = $response->getBody();
        echo "<h3>Response Preview:</h3>";
        echo "<pre>" . htmlspecialchars(substr($body, 0, 500)) . "...</pre>";
    } catch (\Exception $e) {
        echo "<p>❌ HomeController failed: " . $e->getMessage() . "</p>";
        echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
    }
} catch (\Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
