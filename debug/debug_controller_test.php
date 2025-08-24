<?php

// Debug script to test QuranController instantiation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== QuranController Debug Test ===\n\n";

// Set base path
define('BASE_PATH', __DIR__);

// Load Composer autoloader first
require_once BASE_PATH . '/vendor/autoload.php';

// Load required files
require_once BASE_PATH . '/src/Core/Container/Container.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';
require_once BASE_PATH . '/src/Http/Controllers/Controller.php';
require_once BASE_PATH . '/extensions/QuranExtension/src/Http/Controllers/QuranController.php';
require_once BASE_PATH . '/extensions/QuranExtension/src/Models/QuranAyah.php';

echo "1. Core classes loaded successfully\n";

// Create container
$container = new \IslamWiki\Core\Container\Container

echo "2. Container created successfully\n";

// Create a mock database connection
$db = new \IslamWiki\Core\Database\Connection([
    'host' => 'localhost',
    'database' => 'test',
    'username' => 'test',
    'password' => 'test'
]);

echo "3. Mock database connection created\n";

// Try to instantiate QuranController
echo "4. Attempting to instantiate QuranController...\n";
try {
    $controller = new \IslamWiki\Extensions\QuranExtension\Http\Controllers\QuranController($db, $container);
    echo "✅ QuranController instantiated successfully\n";
    
    // Test if the controller has the required methods
    $methods = get_class_methods($controller);
    echo "5. Controller methods: " . implode(', ', $methods) . "\n";
    
    // Check if indexPage method exists
    if (method_exists($controller, 'indexPage')) {
        echo "✅ indexPage method exists\n";
    } else {
        echo "❌ indexPage method missing\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error instantiating QuranController: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
