<?php

/**
 * Test Configuration Manager
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Configuration\ConfigurationManager;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Testing Configuration Manager...\n";

try {
    // Initialize application
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "✓ Application initialized\n";
    
    // Get configuration manager
    $configManager = $container->get(ConfigurationManager::class);
    
    echo "✓ ConfigurationManager loaded\n";
    
    // Test basic functionality
    $categories = $configManager->getCategories();
    echo "✓ Categories loaded: " . count($categories) . " categories\n";
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 