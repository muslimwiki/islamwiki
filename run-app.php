<?php

// Enable error reporting
error_reporting(-1);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/php_errors.log');

// Log script start
error_log('\n=== Script started at ' . date('Y-m-d H:i:s') . ' ===');
error_log('PHP Version: ' . phpversion());

// Log current working directory
error_log('Current working directory: ' . getcwd());

// Log environment
error_log('Environment: ' . print_r([
    'SERVER' => array_diff_key($_SERVER, array_flip(['PATH', 'PWD', 'SHELL', 'USER', 'HOME'])),
    'GET' => $_GET,
    'POST' => $_POST,
    'FILES' => $_FILES,
], true));

// Log included files
error_log('Included files: ' . print_r(get_included_files(), true));

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log(sprintf(
        'Error [%d] %s in %s on line %d',
        $errno,
        $errstr,
        $errfile,
        $errline
    ));
    return true;
});

// Set exception handler
set_exception_handler(function($e) {
    error_log(sprintf(
        'Uncaught %s: %s in %s on line %d\nStack trace:\n%s',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
    
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain');
    }
    
    echo 'An error occurred. Please check the error log for details.';
});

// Require Composer's autoloader
try {
    $autoloadPath = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        throw new \RuntimeException('Composer autoload file not found. Please run "composer install" first.');
    }
    
    error_log('Including autoloader: ' . $autoloadPath);
    $start = microtime(true);
    require $autoloadPath;
    error_log(sprintf('Autoloader included in %.4f seconds', microtime(true) - $start));
    
    // Check if required classes exist
    $requiredClasses = [
        'App\Http\SimpleRouter',
        'App\Http\RequestHandler',
        'Nyholm\Psr7\Factory\Psr17Factory',
        'Nyholm\Psr7Server\ServerRequestCreator',
        'Laminas\HttpHandlerRunner\Emitter\SapiEmitter'
    ];
    
    foreach ($requiredClasses as $class) {
        if (!class_exists($class)) {
            throw new \RuntimeException("Required class not found: $class");
        }
    }
    
} catch (\Throwable $e) {
    error_log('FATAL ERROR: ' . $e->getMessage());
    error_log($e->getTraceAsString());
    
    if (!headers_sent()) {
        header('Content-Type: text/plain', true, 500);
    }
    
    die('Application initialization failed. Check the error log for details.');
}

// Set up database
try {
    $db = new \Illuminate\Database\Capsule\Manager;
    $db->addConnection([
        'driver' => 'sqlite',
        'database' => __DIR__ . '/database/database.sqlite',
        'prefix' => '',
    ]);
    $db->setAsGlobal();
    $db->bootEloquent();
    
    // Create database file if it doesn't exist
    if (!file_exists(__DIR__ . '/database/database.sqlite')) {
        touch(__DIR__ . '/database/database.sqlite');
    }
    
    // Run migrations
    if (!file_exists(__DIR__ . '/database/migrations')) {
        mkdir(__DIR__ . '/database/migrations', 0755, true);
    }
    
} catch (\Exception $e) {
    die("Database error: " . $e->getMessage() . "\n");
}

// Set up router and request handler
$router = new \App\Http\SimpleRouter();
$handler = new \App\Http\RequestHandler($router);

// Create request from globals
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

// Clean any existing output buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Start output buffering
ob_start();

// Handle the current request
try {
    error_log('Creating request from globals...');
    $request = $creator->fromGlobals();
    error_log(sprintf('Request: %s %s', 
        $request->getMethod(),
        $request->getUri()->getPath()
    ));
    
    $response = $handler->handle($request);
    error_log(sprintf('Response: %d %s', 
        $response->getStatusCode(),
        $response->getReasonPhrase()
    ));
} catch (\Throwable $e) {
    error_log('Error handling request: ' . $e->getMessage());
    error_log($e->getTraceAsString());
    
    $response = new \Nyholm\Psr7\Response(
        500,
        ['Content-Type' => 'text/plain'],
        'Internal Server Error: ' . $e->getMessage()
    );
}

// Clean the output buffer
ob_clean();

// Send the response
(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
