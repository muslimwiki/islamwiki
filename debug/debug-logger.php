<?php
/**
 * Debug script to test LoggerInterface registration
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Load LocalSettings.php
require_once BASE_PATH . '/LocalSettings.php';

echo "=== Logger Debug Test ===\n";

// Create application
$app = new \IslamWiki\Core\Application(BASE_PATH);

// Get container
$container = $app->getContainer();

// Test if LoggerInterface is available
echo "LoggerInterface available: " . ($container->has(\Psr\Log\LoggerInterface::class) ? 'Yes' : 'No') . "\n";

if ($container->has(\Psr\Log\LoggerInterface::class)) {
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    echo "Logger class: " . get_class($logger) . "\n";
    echo "Logger implements LoggerInterface: " . (($logger instanceof \Psr\Log\LoggerInterface) ? 'Yes' : 'No') . "\n";
} else {
    echo "LoggerInterface not found in container\n";
}

// Test router
$router = new \IslamWiki\Core\Routing\IslamRouter($container);

// Check if middleware stack is initialized
$reflection = new \ReflectionClass($router);
$middlewareStackProperty = $reflection->getProperty('middlewareStack');
$middlewareStackProperty->setAccessible(true);
$middlewareStack = $middlewareStackProperty->getValue($router);

echo "Middleware Stack: " . ($middlewareStack ? get_class($middlewareStack) : 'null') . "\n";

if ($middlewareStack) {
    echo "Middleware Count: " . $middlewareStack->count() . "\n";
    $allMiddleware = $middlewareStack->getAll();
    echo "Middleware Classes:\n";
    foreach ($allMiddleware as $mw) {
        echo "  - " . get_class($mw) . "\n";
    }
}

echo "=== Test Complete ===\n"; 