<?php

declare(strict_types=1);

// Enable all error reporting
error_reporting(-1);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/php_errors.log');

// Start output buffering
ob_start();

// Simple debug function
function debug($message, $data = null) {
    $output = "[DEBUG] " . $message . "\n";
    if ($data !== null) {
        ob_start();
        var_dump($data);
        $output .= "Data: " . ob_get_clean() . "\n";
    }
    // Output to both stdout and stderr to ensure we see it
    fwrite(STDERR, $output);
    fwrite(STDOUT, $output);
    error_log(trim($output));
}

// Flush output buffer
function flush_output() {
    $output = ob_get_clean();
    if (!empty($output)) {
        fwrite(STDERR, $output);
        fwrite(STDOUT, $output);
        @ob_flush();
        flush();
    }
}

// Make sure we have the database manager
if (!class_exists('Illuminate\Database\Capsule\Manager')) {
    if (file_exists(__DIR__ . '/vendor/illuminate/database/Capsule/Manager.php')) {
        require __DIR__ . '/vendor/illuminate/database/Capsule/Manager.php';
    } else {
        die("Error: Database manager not found. Please run 'composer require illuminate/database'\n");
    }
}

try {
    debug("Starting simple controller test");
    
    // Set up database
    try {
        $db = new \Illuminate\Database\Capsule\Manager;
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $db->setAsGlobal();
        $db->bootEloquent();
        debug("Database connection established");
        
        // Enable query logging
        $db->connection()->enableQueryLog();
        
    } catch (\Exception $e) {
        die("Failed to connect to database: " . $e->getMessage() . "\n");
    }
    
    // Create migrations table
    $db->connection()->getPdo()->exec('CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        is_published BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Create test page
    try {
        // First, check if the pages table exists
        $tables = $db->connection()->getPdo()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='pages'")->fetchAll();
        debug("Tables in database:", $tables);
        
        // Create the pages table if it doesn't exist
        $db->connection()->getPdo()->exec("
            CREATE TABLE IF NOT EXISTS pages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content TEXT NOT NULL,
                is_published BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $page = new \App\Models\Page([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'This is a test page',
            'is_published' => true
        ]);
        $page->save();
        
        debug("Test page created", $page->toArray());
        debug("Queries executed:", $db->connection()->getQueryLog());
        
    } catch (\Exception $e) {
        die("Failed to create test page: " . $e->getMessage() . "\n");
    }
    
    // Test retrieving the page
    $foundPage = \App\Models\Page::where('slug', 'test-page')->first();
    
    if (!$foundPage) {
        throw new \RuntimeException("Failed to retrieve test page from database");
    }
    
    debug("Test page retrieved", [
        'id' => $foundPage->id,
        'title' => $foundPage->title,
        'slug' => $foundPage->slug
    ]);
    
    // Test controller
    debug("Testing PageController");
    
    // Create a request
    $url = 'http://localhost/pages/test-page';
    $request = new \Nyholm\Psr7\ServerRequest(
        'GET',
        $url,
        ['Accept' => 'application/json']
    );
    
    debug("Created request for URL: " . $url);
    debug("Request path: " . $request->getUri()->getPath());
    
    // Create router and handler
    $router = new \App\Http\SimpleRouter();
    $handler = new \App\Http\RequestHandler($router);
    
    // Add route
    $router->addRoute('GET', '/pages/{slug}', [\App\Http\Controllers\PageController::class, 'show']);
    
    // Debug the router setup
    debug("Registered routes: ", getObjectProperty($router, 'routes'));
    
    // Handle the request
    try {
        echo "\n=== Processing Request ===\n";
        echo "URI: " . $request->getUri() . "\n";
        echo "Method: " . $request->getMethod() . "\n";
        echo "Headers: " . print_r($request->getHeaders(), true) . "\n\n";
        
        $response = $handler->handle($request);
        
        echo "\n=== Response ===\n";
        echo "Status: " . $response->getStatusCode() . "\n";
        echo "Headers: " . print_r($response->getHeaders(), true) . "\n";
        echo "Body: " . (string) $response->getBody() . "\n\n";
        
        debug("Response status: " . $response->getStatusCode());
        debug("Response headers: ", $response->getHeaders());
        debug("Response body: " . (string) $response->getBody());
        
        if ($response->getStatusCode() !== 200) {
        $error = "Unexpected status code: " . $response->getStatusCode();
        if ($response->getStatusCode() === 404) {
            $error .= " (Route not found)";
        }
            throw new \RuntimeException($error);
        }
    } catch (\Throwable $e) {
        echo "\n=== Error ===\n";
        echo get_class($e) . ": " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n\n";
        throw $e;
    }
    
    $data = json_decode((string) $response->getBody(), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException("Invalid JSON response: " . json_last_error_msg());
    }
    
    if ($data['slug'] !== 'test-page') {
        throw new \RuntimeException("Unexpected page slug: " . ($data['slug'] ?? 'null'));
    }
    
    debug("PageController test passed");
    
    // Show any error logs
    if (file_exists('/tmp/php_errors.log')) {
        $errors = file_get_contents('/tmp/php_errors.log');
        if (!empty($errors)) {
            echo "\n=== Error Logs ===\n$errors\n";
            // Clear the log
            file_put_contents('/tmp/php_errors.log', '');
        }
    }
    
    echo "\n=== All tests passed successfully! ===\n";
    
    // Flush any remaining output
    flush_output();
} catch (\Throwable $e) {
    // Show any error logs first
    if (file_exists('/tmp/php_errors.log')) {
        $errors = file_get_contents('/tmp/php_errors.log');
        if (!empty($errors)) {
            echo "\n=== Error Logs ===\n$errors\n";
        }
    }
    
    echo "\n=== Test Failed ===\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (" . $e->getLine() . ")\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n\n";
    
    // Output any previous exceptions
    $previous = $e->getPrevious();
    while ($previous) {
        echo "\nPrevious error: " . $previous->getMessage() . "\n";
        echo "File: " . $previous->getFile() . " (" . $previous->getLine() . ")\n";
        echo "Trace: " . $previous->getTraceAsString() . "\n";
        $previous = $previous->getPrevious();
    }
    
    // Flush any remaining output
    flush_output();
    exit(1);
}

/**
 * Helper function to get private/protected properties of an object for debugging
 */
function getObjectProperty($object, $property) {
    $reflection = new \ReflectionClass($object);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);
    return $property->getValue($object);
}
