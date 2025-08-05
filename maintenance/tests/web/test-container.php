<?php
/**
 * Test Container Setup
 */

// Direct include approach
require_once __DIR__ . '/../src/Core/NizamApplication.php';
require_once __DIR__ . '/../src/Core/Container.php';
require_once __DIR__ . '/../src/Core/Database/Connection.php';
require_once __DIR__ . '/../src/Core/Logging/Logger.php';
require_once __DIR__ . '/../src/Core/Routing/ControllerFactory.php';
require_once __DIR__ . '/../src/Http/Controllers/Controller.php';
require_once __DIR__ . '/../src/Http/Controllers/IqraSearchController.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Container;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Routing\ControllerFactory;
use IslamWiki\Http\Controllers\IqraSearchController;

echo "<h1>Container Test</h1>";

try {
    // Create application
    $app = new NizamApplication(__DIR__ . '/..');
    echo "✅ Application created<br>";
    
    // Get container
    $container = $app->getContainer();
    echo "✅ Container retrieved<br>";
    
    // Test if controller.factory exists
    $hasFactory = $container->has('controller.factory');
    echo "✅ Container has 'controller.factory': " . ($hasFactory ? 'YES' : 'NO') . "<br>";
    
    if ($hasFactory) {
        // Get the factory
        $factory = $container->get('controller.factory');
        echo "✅ ControllerFactory retrieved: " . get_class($factory) . "<br>";
        
        // Test creating IqraSearchController
        try {
            $controller = $factory->create('IslamWiki\Http\Controllers\IqraSearchController');
            echo "✅ IqraSearchController created successfully: " . get_class($controller) . "<br>";
            
            // Test if the controller has the search engine
            if (property_exists($controller, 'searchEngine')) {
                echo "✅ Controller has searchEngine property<br>";
            } else {
                echo "❌ Controller missing searchEngine property<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Failed to create IqraSearchController: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ ControllerFactory not found in container<br>";
        
        // List all container bindings
        echo "<h2>Container Bindings:</h2>";
        $reflection = new ReflectionClass($container);
        $bindingsProperty = $reflection->getProperty('bindings');
        $bindingsProperty->setAccessible(true);
        $bindings = $bindingsProperty->getValue($container);
        
        echo "<ul>";
        foreach ($bindings as $key => $binding) {
            echo "<li><strong>{$key}:</strong> " . gettype($binding['concrete']) . "</li>";
        }
        echo "</ul>";
        
        // List all container instances
        echo "<h2>Container Instances:</h2>";
        $instancesProperty = $reflection->getProperty('instances');
        $instancesProperty->setAccessible(true);
        $instances = $instancesProperty->getValue($container);
        
        echo "<ul>";
        foreach ($instances as $key => $instance) {
            echo "<li><strong>{$key}:</strong> " . get_class($instance) . "</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
} 