<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

echo "Testing container services...\n";

try {
    // Create application
    $app = new Application();
    echo "✓ Application created successfully\n";
    
    // Get container
    $container = $app->getContainer();
    echo "✓ Container retrieved successfully\n";
    
    // List available services
    echo "\nAvailable services:\n";
    
    // Try to get common services
    $services = [
        'database',
        'db',
        'connection',
        'router',
        'view',
        'auth',
        'session',
        'logger',
        'config',
        'i18n.service',
        'skin.manager'
    ];
    
    foreach ($services as $service) {
        try {
            $instance = $container->get($service);
            echo "✓ Service '$service' found: " . get_class($instance) . "\n";
        } catch (Exception $e) {
            echo "✗ Service '$service' not found: " . $e->getMessage() . "\n";
        }
    }
    

    
} catch (Exception $e) {
    echo "✗ Application error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 