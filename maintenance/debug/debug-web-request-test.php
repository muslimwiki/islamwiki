<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Http\Request;

echo "🔍 Debug Web Request Test\n";
echo "=========================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');
    
    echo "✅ Application loaded successfully\n\n";
    
    // Simulate a web request
    echo "🌐 Simulating Web Request:\n";
    
    // Create a mock request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['HTTP_HOST'] = 'local.islam.wiki';
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
    
    // Capture the request
    $request = Request::capture();
    echo "- Request Method: " . $request->getMethod() . "\n";
    echo "- Request URI: " . $request->getUri()->getPath() . "\n";
    echo "- Request Host: " . $request->getUri()->getHost() . "\n";
    
    // Handle the request
    echo "\n🔄 Handling Request:\n";
    $response = $app->handleRequest($request);
    
    echo "- Response Status: " . $response->getStatusCode() . "\n";
    echo "- Response Headers: " . count($response->getHeaders()) . " headers\n";
    
    // Check if skin data is in the response
    $body = $response->getBody();
    if (strpos($body, 'skin_css') !== false) {
        echo "- Skin CSS Found: Yes\n";
    } else {
        echo "- Skin CSS Found: No\n";
    }
    
    if (strpos($body, 'skin_js') !== false) {
        echo "- Skin JS Found: Yes\n";
    } else {
        echo "- Skin JS Found: No\n";
    }
    
    // Check for specific skin content
    if (strpos($body, 'Bismillah Skin') !== false) {
        echo "- Bismillah Skin Content: Found\n";
    } else {
        echo "- Bismillah Skin Content: Not found\n";
    }
    
    if (strpos($body, 'Muslim Skin') !== false) {
        echo "- Muslim Skin Content: Found\n";
    } else {
        echo "- Muslim Skin Content: Not found\n";
    }
    
    echo "\n✅ Web request test completed successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 