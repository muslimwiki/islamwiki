<?php

declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Simple routing test
echo "<h1>Direct Routing Test</h1>";

try {
    // Include the autoloader
    require __DIR__ . '/../vendor/autoload.php';

    // Create a simple request
    $request = new IslamWiki\Core\Http\Request(
        method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
        uri: $_SERVER['REQUEST_URI'] ?? '/',
        headers: [],
        body: null,
        version: '1.1',
        serverParams: []
    );

    // Create a simple response
    $response = new IslamWiki\Core\Http\Response(
        status: 200,
        headers: ['Content-Type' => 'text/html'],
        body: '<h2>Direct Response Test</h2><p>If you see this, the response system is working!</p>'
    );

    // Output the response
    $response->send();
} catch (Throwable $e) {
    echo "<div style='color:red; padding:10px; border:1px solid #f00;'>";
    echo "<h2>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
