<?php
/**
 * Test Request URI
 * 
 * This script tests what URI is being passed to the router.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

echo "<h1>🔍 Test Request URI</h1>";

try {
    // Initialize Application
    $app = new \IslamWiki\Core\Application(BASE_PATH);
    $container = $app->getContainer();
    $container->instance('app', $app);

    // Initialize router
    $router = new \IslamWiki\Core\Routing\IslamRouter($container);
    
    // Load routes
    require_once BASE_PATH . '/routes/web.php';
    
    echo "<h2>Server Variables</h2>";
    echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "</p>";
    echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "</p>";
    echo "<p><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "</p>";
    echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "</p>";
    echo "<p><strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'NOT SET') . "</p>";
    
    echo "<h2>Request Capture Test</h2>";
    
    // Test Request::capture()
    $request = \IslamWiki\Core\Http\Request::capture();
    echo "<p><strong>Captured URI:</strong> " . $request->getUri() . "</p>";
    echo "<p><strong>Captured Path:</strong> " . $request->getUri()->getPath() . "</p>";
    echo "<p><strong>Captured Method:</strong> " . $request->getMethod() . "</p>";
    
    echo "<h2>Router Test</h2>";
    
    // Test router with captured request
    try {
        $response = $router->handle($request);
        echo "<p>✅ Router handled request successfully</p>";
        echo "<p><strong>Response Status:</strong> " . $response->getStatusCode() . "</p>";
        echo "<p><strong>Response Body Length:</strong> " . strlen($response->getBody()) . " characters</p>";
    } catch (\Exception $e) {
        echo "<p>❌ Router failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>Direct Route Test</h2>";
    
    // Test with a simple request
    $simpleRequest = new \IslamWiki\Core\Http\Request('GET', new \IslamWiki\Core\Http\Uri('/'));
    try {
        $response = $router->handle($simpleRequest);
        echo "<p>✅ Direct route test successful</p>";
        echo "<p><strong>Response Status:</strong> " . $response->getStatusCode() . "</p>";
    } catch (\Exception $e) {
        echo "<p>❌ Direct route test failed: " . $e->getMessage() . "</p>";
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