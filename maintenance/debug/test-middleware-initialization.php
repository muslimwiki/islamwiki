<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Define constants
define('ROOT_PATH', __DIR__ . '/..');

require_once __DIR__ . '/../src/helpers.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Testing Middleware Stack Initialization ===\n";

// Test 1: Create application and check container
echo "\n1. Testing application and container...\n";
try {
    // Create application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    echo "✅ Application created successfully\n";

    // Check if logger is available
    if ($container->has(\Psr\Log\LoggerInterface::class)) {
        echo "✅ LoggerInterface is available in container\n";
        $logger = $container->get(\Psr\Log\LoggerInterface::class);
        echo "📄 Logger class: " . get_class($logger) . "\n";
    } else {
        echo "❌ LoggerInterface is not available in container\n";
    }

    // Check if application is available
    if ($container->has(\IslamWiki\Core\Application::class)) {
        echo "✅ Application is available in container\n";
    } else {
        echo "❌ Application is not available in container\n";
    }

    if ($container->has('app')) {
        echo "✅ 'app' alias is available in container\n";
    } else {
        echo "❌ 'app' alias is not available in container\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing application: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 2: Test router middleware initialization
echo "\n2. Testing router middleware initialization...\n";
try {
    // Create application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Create router
    $router = new \IslamWiki\Core\Routing\IslamRouter($container);
    echo "✅ Router created successfully\n";

    // Try to access the middleware stack (this will trigger initialization)
    $reflection = new ReflectionClass($router);
    $middlewareStackProperty = $reflection->getProperty('middlewareStack');
    $middlewareStackProperty->setAccessible(true);

    $middlewareStack = $middlewareStackProperty->getValue($router);
    if ($middlewareStack) {
        echo "✅ Middleware stack is initialized\n";
        echo "📄 Middleware count: " . $middlewareStack->count() . "\n";
    } else {
        echo "❌ Middleware stack is not initialized\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing router: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: Test skin middleware creation
echo "\n3. Testing skin middleware creation...\n";
try {
    // Create application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');

    // Create skin middleware
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    echo "✅ SkinMiddleware created successfully\n";

    // Test if the middleware can access the application
    $reflection = new ReflectionClass($skinMiddleware);
    $appProperty = $reflection->getProperty('app');
    $appProperty->setAccessible(true);

    $boundApp = $appProperty->getValue($skinMiddleware);
    if ($boundApp) {
        echo "✅ SkinMiddleware has access to application\n";
        echo "📄 App class: " . get_class($boundApp) . "\n";
    } else {
        echo "❌ SkinMiddleware does not have access to application\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing skin middleware: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
