<?php
declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Simple router test
echo "<h1>Router Test</h1>";

try {
    // Try to include the autoloader
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        throw new Exception("Autoloader not found at: $autoloadPath");
    }
    require $autoloadPath;
    
    // Try to create a simple route
    $router = new \Bramus\Router\Router();
    
    // Test route
    $router->get('/test-route', function() {
        echo "<p>Test route is working!</p>";
    });
    
    echo "<p>Router initialized successfully. <a href='/test-route'>Test the route</a></p>";
    
    // Run the router
    $router->run();
    
} catch (Exception $e) {
    echo "<div style='color:red; padding:10px; border:1px solid #f00;'>";
    echo "<h2>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<h2>Debug Info:</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Current file: " . __FILE__ . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "</pre>";
