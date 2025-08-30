<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Simple debug function
function debug($message, $data = null) {
    echo "[DEBUG] " . $message . "\n";
    if ($data !== null) {
        echo "Data: " . print_r($data, true) . "\n";
    }
}

try {
    debug("Starting test runner");
    
    // Test database connection
    debug("Testing database connection");
    $db = new \Illuminate\Database\Capsule\Manager;
    $db->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);
    $db->setAsGlobal();
    $db->bootEloquent();
    
    debug("Database connection successful");
    
    // Test model
    debug("Testing Page model");
    $page = new \App\Models\Page([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => 'Test content',
        'is_published' => true
    ]);
    
    debug("Page model created", [
        'title' => $page->title,
        'slug' => $page->slug
    ]);
    
    // Test controller
    debug("Testing PageController");
    
    // Create a test page in the database
    $page = new \App\Models\Page([
        'title' => 'Test Page',
        'slug' => 'test-page',
        'content' => 'Test content',
        'is_published' => true
    ]);
    $page->save();
    
    debug("Test page saved to database", ['id' => $page->id]);
    
    // Create a request
    $request = new \Nyholm\Psr7\ServerRequest(
        'GET',
        '/pages/test-page',
        ['Accept' => 'application/json']
    );
    
    // Create router and handler
    $router = new \App\Http\SimpleRouter();
    $handler = new \App\Http\RequestHandler($router);
    
    // Add route
    $router->addRoute('GET', '/pages/{slug}', [\App\Http\Controllers\PageController::class, 'show']);
    
    // Handle the request
    $response = $handler->handle($request);
    
    debug("Response status: " . $response->getStatusCode());
    debug("Response body: " . (string) $response->getBody());
    
    if ($response->getStatusCode() !== 200) {
        throw new \RuntimeException("Unexpected status code: " . $response->getStatusCode());
    }
    
    $data = json_decode((string) $response->getBody(), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException("Invalid JSON response: " . json_last_error_msg());
    }
    
    if ($data['slug'] !== 'test-page') {
        throw new \RuntimeException("Unexpected page slug: " . $data['slug']);
    }
    
    debug("PageController test passed");
    
    echo "\nAll tests passed successfully!\n";
    
} catch (\Throwable $e) {
    echo "\nTest failed:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (" . $e->getLine() . ")\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
