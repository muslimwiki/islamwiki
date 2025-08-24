<?php

/**
 * Minimal Routes Test Script
 * 
 * Test with minimal routes to see if the basic web application works.
 * 
 * Version: 0.0.3.0
 * Usage: php debug/test-minimal-routes.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🛣️  Minimal Routes Test...\n\n";

try {
    echo "1️⃣  Creating Application...\n";
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    echo "   ✅ Application created\n";
    
    echo "\n2️⃣  Getting Router...\n";
    $router = $app->getRouter();
    echo "   ✅ Router obtained\n";
    
    echo "\n3️⃣  Adding minimal test route...\n";
    $router->get('/test', function() {
        return new \IslamWiki\Core\Http\Response(200, [], 'Test route working!');
    });
    echo "   ✅ Test route added\n";
    
    echo "\n4️⃣  Testing route dispatch...\n";
    $request = new \IslamWiki\Core\Http\Request('GET', '/test');
    $response = $router->dispatch($request);
    echo "   ✅ Route dispatched successfully\n";
    echo "   📊 Response status: " . $response->getStatusCode() . "\n";
    echo "   📊 Response body: " . $response->getBody() . "\n";
    
    echo "\n5️⃣  Testing web access...\n";
    $webRequest = new \IslamWiki\Core\Http\Request('GET', '/wiki/Home');
    $webResponse = $app->handleRequest($webRequest);
    echo "   ✅ Web request handled\n";
    echo "   📊 Web response status: " . $webResponse->getStatusCode() . "\n";
    
    echo "\n✅ Minimal routes test completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (\Error $e) {
    echo "\n❌ Test failed with fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 