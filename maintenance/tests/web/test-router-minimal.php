<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Routing\IslamRouter;

try {
    echo "<h1>Testing IslamRouter (Minimal)</h1>";
    
    // Create application with base path
    $app = new NizamApplication(__DIR__ . '/..');
    echo "<p>✅ Application created</p>";
    
    // Create router
    $router = new IslamRouter($app->getContainer());
    echo "<p>✅ IslamRouter created</p>";
    
    // Test getContainer method
    $container = $router->getContainer();
    echo "<p>✅ getContainer() method works</p>";
    
    // Test adding a simple closure route
    $router->get('/test', function($request) {
        return new \IslamWiki\Core\Http\Response(200, [], 'Test route works!');
    });
    echo "<p>✅ Route added successfully</p>";
    
    // Test router methods
    echo "<p>Router class: " . get_class($router) . "</p>";
    echo "<p>Container class: " . get_class($container) . "</p>";
    
    // Test if we can get services from container
    try {
        $logger = $container->get(\Psr\Log\LoggerInterface::class);
        echo "<p>✅ Logger service available</p>";
    } catch (Exception $e) {
        echo "<p>❌ Logger service error: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>✅ IslamRouter basic functionality works!</h2>";
    
} catch (Exception $e) {
    echo "<h1>❌ Error testing IslamRouter</h1>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 