<?php
/**
 * Test Router Debug
 * 
 * This script tests if the router is being called properly.
 * 
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

// Define the application's base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('Autoload file not found. Please run `composer install` to install the project dependencies.');
}

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

use IslamWiki\Core\Application;
use IslamWiki\Core\Routing\IslamRouter;

echo "🧪 Testing Router Debug\n";
echo "=======================\n\n";

try {
    // Initialize the application
    $app = new Application(BASE_PATH);
    echo "✅ Application created successfully\n";
    
    // Create router
    $router = new IslamRouter($app->getContainer());
    echo "✅ Router created successfully\n";
    
    // Test if the router can handle a simple request
    $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
    echo "✅ PSR-7 request created\n";
    
    // Try to handle the request
    echo "🔄 Attempting to handle request...\n";
    $response = $router->handle($request);
    echo "✅ Router handled request successfully\n";
    echo "📊 Response status: " . $response->getStatusCode() . "\n";
    
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
} 