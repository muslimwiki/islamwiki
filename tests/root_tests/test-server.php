<?php
// Enable error reporting
error_reporting(-1);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/php_errors.log');

// Simple debug function
function debug($message, $data = null) {
    $output = "[DEBUG] " . $message . "\n";
    if ($data !== null) {
        ob_start();
        var_dump($data);
        $output .= "Data: " . ob_get_clean() . "\n";
    }
    file_put_contents('php://stderr', $output);
    echo $output;
}

// Require Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

// Set up database
try {
    $db = new \Illuminate\Database\Capsule\Manager;
    $db->addConnection([
        'driver' => 'sqlite',
        'database' => __DIR__ . '/test.sqlite',
        'prefix' => '',
    ]);
    $db->setAsGlobal();
    $db->bootEloquent();
    
    // Enable query logging
    $db->connection()->enableQueryLog();
    
    debug("Database connection established");
    
} catch (\Exception $e) {
    die("Database error: " . $e->getMessage() . "\n");
}

// Set up router
$router = new \App\Http\SimpleRouter();
$handler = new \App\Http\RequestHandler($router);

// Add route
$router->addRoute('GET', '/pages/{slug}', [\App\Http\Controllers\PageController::class, 'show']);

debug("Test server started. Try accessing: http://localhost:8000/pages/test-page");

// Start the server
$server = new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter();
$response = $handler->handle(\Nyholm\Psr7\ServerRequest::fromGlobals());
$server->emit($response);
