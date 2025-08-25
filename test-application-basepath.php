<?php
// Test Application class basePath
require_once __DIR__ . "/vendor/autoload.php";

try {
    echo "Testing Application class basePath...\n";
    
    $app = new \IslamWiki\Core\Application(__DIR__);
    echo "Application created successfully!\n";
    
    echo "Base path: " . $app->getBasePath() . "\n";
    
    // Check if routes file exists
    $routesFile = $app->getBasePath() . "/config/routes.php";
    echo "Routes file path: " . $routesFile . "\n";
    echo "Routes file exists: " . (file_exists($routesFile) ? "YES" : "NO") . "\n";
    
    // Check if we can read the routes file
    if (file_exists($routesFile)) {
        echo "Routes file content (first 100 chars):\n";
        echo substr(file_get_contents($routesFile), 0, 100) . "\n";
    }
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
