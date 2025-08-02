<?php

/**
 * Debug Bayan System
 */

require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Initialize the application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "<h1>Bayan System Debug</h1>\n";
    
    // Check if BayanManager class exists
    echo "<h2>Class Check</h2>\n";
    if (class_exists('\IslamWiki\Core\Bayan\BayanManager')) {
        echo "<p>✅ BayanManager class exists</p>\n";
    } else {
        echo "<p>❌ BayanManager class not found</p>\n";
    }
    
    // Check if BayanServiceProvider class exists
    if (class_exists('\IslamWiki\Providers\BayanServiceProvider')) {
        echo "<p>✅ BayanServiceProvider class exists</p>\n";
    } else {
        echo "<p>❌ BayanServiceProvider class not found</p>\n";
    }
    
    // List available bindings
    echo "<h2>Available Bindings</h2>\n";
    echo "<ul>\n";
    echo "<li>app: " . (class_exists($container->get('app')::class) ? '✅' : '❌') . "</li>\n";
    echo "<li>db: " . (class_exists($container->get('db')::class) ? '✅' : '❌') . "</li>\n";
    echo "<li>logger: " . (class_exists($container->get(\Psr\Log\LoggerInterface::class)::class) ? '✅' : '❌') . "</li>\n";
    echo "</ul>\n";
    
    // Try to get BayanManager directly
    echo "<h2>Direct BayanManager Test</h2>\n";
    try {
        $bayanManager = new \IslamWiki\Core\Bayan\BayanManager(
            $container->get('db'),
            $container->get(\Psr\Log\LoggerInterface::class)
        );
        echo "<p>✅ BayanManager created successfully</p>\n";
        
        // Test basic functionality
        $stats = $bayanManager->getStatistics();
        echo "<p>✅ Statistics retrieved: " . print_r($stats, true) . "</p>\n";
        
    } catch (\Exception $e) {
        echo "<p>❌ Error creating BayanManager: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
    // Check if 'bayan' binding exists
    echo "<h2>Container Binding Test</h2>\n";
    try {
        $bayan = $container->get('bayan');
        echo "<p>✅ 'bayan' binding found</p>\n";
    } catch (\Exception $e) {
        echo "<p>❌ 'bayan' binding not found: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
} catch (\Exception $e) {
    echo "<h1>❌ Error in Debug</h1>\n";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
} 