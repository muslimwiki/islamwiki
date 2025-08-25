<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Http\Controllers\Auth\AuthController;
use IslamWiki\Http\Controllers\HomeController;
use IslamWiki\Http\Controllers\WikiController;
use IslamWiki\Http\Controllers\SettingsController;
use IslamWiki\Http\Controllers\DashboardController;
use IslamWiki\Http\Controllers\SearchController;

echo "Testing controller instantiation...\n";

try {
    // Create application
    $app = new Application();
    echo "✓ Application created successfully\n";
    
    // Get container
    $container = $app->getContainer();
    echo "✓ Container retrieved successfully\n";
    
    // Test database service
    try {
        $database = $container->get('database');
        echo "✓ Database service retrieved successfully\n";
    } catch (Exception $e) {
        echo "✗ Database service error: " . $e->getMessage() . "\n";
        return;
    }
    
    // Test controller instantiation
    echo "\nTesting controllers:\n";
    
    try {
        $authController = new AuthController($database, $container);
        echo "✓ AuthController created successfully\n";
    } catch (Exception $e) {
        echo "✗ AuthController error: " . $e->getMessage() . "\n";
    }
    
    try {
        $homeController = new HomeController($database, $container);
        echo "✓ HomeController created successfully\n";
    } catch (Exception $e) {
        echo "✗ HomeController error: " . $e->getMessage() . "\n";
    }
    
    try {
        $wikiController = new WikiController($database, $container);
        echo "✓ WikiController created successfully\n";
    } catch (Exception $e) {
        echo "✗ WikiController error: " . $e->getMessage() . "\n";
    }
    
    try {
        $settingsController = new SettingsController($database, $container);
        echo "✓ SettingsController created successfully\n";
    } catch (Exception $e) {
        echo "✗ SettingsController error: " . $e->getMessage() . "\n";
    }
    
    try {
        $dashboardController = new DashboardController($database, $container);
        echo "✓ DashboardController created successfully\n";
    } catch (Exception $e) {
        echo "✗ DashboardController error: " . $e->getMessage() . "\n";
    }
    
    try {
        $searchController = new SearchController($database, $container);
        echo "✓ SearchController created successfully\n";
    } catch (Exception $e) {
        echo "✗ SearchController error: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Application error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 