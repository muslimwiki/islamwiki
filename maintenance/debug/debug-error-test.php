<?php
declare(strict_types=1);

/**
 * Debug Error Test
 * 
 * Tests with detailed error reporting to catch the actual error.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing with Detailed Error Reporting\n";
echo "=======================================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

echo "✅ Application initialized\n";

// Simulate login
$session->login(1, 'admin', true);
echo "✅ Login simulation completed\n";

// Test the SettingsController with error handling
echo "\n📊 Testing SettingsController with Error Handling\n";
echo "================================================\n";

try {
    $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
    
    // Use reflection to access the private index method
    $reflection = new ReflectionClass($settingsController);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    
    echo "✅ SettingsController created\n";
    echo "✅ Method made accessible\n";
    
    echo "🔄 Invoking index method...\n";
    $response = $method->invoke($settingsController);
    
    echo "✅ SettingsController executed successfully\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    
    // Get the response body - handle Stream object properly
    $body = $response->getBody();
    if (is_object($body) && method_exists($body, 'getContents')) {
        $bodyContent = $body->getContents();
    } else {
        $bodyContent = (string) $body;
    }
    
    echo "Response length: " . strlen($bodyContent) . " characters\n";
    
    // Check for specific content
    if (strpos($bodyContent, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
    } else {
        echo "❌ Response does not contain skin cards\n";
    }
    
    if (strpos($bodyContent, 'Bismillah') !== false) {
        echo "✅ Response contains Bismillah skin\n";
    } else {
        echo "❌ Response does not contain Bismillah skin\n";
    }
    
    if (strpos($bodyContent, 'Muslim') !== false) {
        echo "✅ Response contains Muslim skin\n";
    } else {
        echo "❌ Response does not contain Muslim skin\n";
    }
    
    // Show a snippet of the response
    echo "\n📋 Response Preview (first 500 chars):\n";
    echo "=====================================\n";
    echo substr($bodyContent, 0, 500) . "...\n";
    
} catch (\Throwable $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Error test completed!\n"; 