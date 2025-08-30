<?php
// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Start the application
try {
    $app = new Application(__DIR__ . '/..');
    $app->run();
} catch (\Exception $e) {
    // Log the error
    error_log($e->getMessage());
    
    // Show a generic error message to the user
    header('HTTP/1.1 500 Internal Server Error');
    echo 'An error occurred. Please try again later.';
    if (getenv('APP_DEBUG') === 'true') {
        echo '\n\n' . $e->getMessage();
    }
    exit;
}
