<?php
/**
 * Simple Route Test
 * 
 * This script tests if the router is working correctly.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "<h1>🔍 Simple Route Test</h1>";

try {
    // Initialize Application
    $app = new \IslamWiki\Core\Application(BASE_PATH);
    $container = $app->getContainer();
    $container->instance('app', $app);

    // Initialize router
    $router = new \IslamWiki\Core\Routing\IslamRouter($container);
    
    // Add a simple test route
    $router->get('/test-simple-route', function($request) {
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], '<h1>✅ Simple Route Works!</h1>');
    });
    
    // Load routes
    require_once BASE_PATH . '/routes/web.php';
    
    echo "<h2>Testing Simple Route</h2>";
    
    // Test with a simple request
    $request = new \IslamWiki\Core\Http\Request('GET', new \IslamWiki\Core\Http\Uri('/test-simple-route'));
    
    try {
        $response = $router->handle($request);
        echo "<p>✅ Router handled request successfully</p>";
        echo "<p><strong>Response Status:</strong> " . $response->getStatusCode() . "</p>";
        echo "<p><strong>Response Body:</strong> " . $response->getBody() . "</p>";
    } catch (\Exception $e) {
        echo "<p>❌ Router failed: " . $e->getMessage() . "</p>";
        echo "<p>Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
    }
    
    echo "<h2>Testing Root Route</h2>";
    
    // Test with root request
    $rootRequest = new \IslamWiki\Core\Http\Request('GET', new \IslamWiki\Core\Http\Uri('/'));
    
    try {
        $response = $router->handle($rootRequest);
        echo "<p>✅ Root route handled successfully</p>";
        echo "<p><strong>Response Status:</strong> " . $response->getStatusCode() . "</p>";
        echo "<p><strong>Response Body Length:</strong> " . strlen($response->getBody()) . " characters</p>";
    } catch (\Exception $e) {
        echo "<p>❌ Root route failed: " . $e->getMessage() . "</p>";
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
?> 