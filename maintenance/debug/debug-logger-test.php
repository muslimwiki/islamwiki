<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;

echo "🔍 Debug Logger Test\n";
echo "===================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();

    echo "✅ Application loaded successfully\n\n";

    // Test if logger is available
    echo "🔧 Testing Logger Availability:\n";

    $loggerAvailable = $container->has(\Psr\Log\LoggerInterface::class);
    echo "- Logger Available: " . ($loggerAvailable ? 'Yes' : 'No') . "\n";

    if ($loggerAvailable) {
        $logger = $container->get(\Psr\Log\LoggerInterface::class);
        echo "- Logger Class: " . get_class($logger) . "\n";
        echo "- Logger Instance: " . ($logger ? 'Valid' : 'Invalid') . "\n";

        // Test logger functionality
        try {
            $logger->info('Test log message');
            echo "- Logger Functionality: Working\n";
        } catch (Exception $e) {
            echo "- Logger Functionality Error: " . $e->getMessage() . "\n";
        }
    }

    // Test middleware stack initialization
    echo "\n🔧 Testing Middleware Stack Initialization:\n";

    $router = $app->getRouter();
    $reflection = new ReflectionClass($router);
    $middlewareStackProperty = $reflection->getProperty('middlewareStack');
    $middlewareStackProperty->setAccessible(true);
    $middlewareStack = $middlewareStackProperty->getValue($router);

        $temp_c3c36b07 = ($middlewareStack ? get_class($middlewareStack) : 'None') . "\n";
        echo "- Initial Middleware Stack: " . $temp_c3c36b07;

    // Force middleware stack initialization
    $initializeMethod = $reflection->getMethod('initializeMiddlewareStack');
    $initializeMethod->setAccessible(true);

    try {
        $initializeMethod->invoke($router);
        echo "- Middleware Stack Initialization: Success\n";
    } catch (Exception $e) {
        echo "- Middleware Stack Initialization Error: " . $e->getMessage() . "\n";
    }

    // Check if middleware stack was created
    $middlewareStack = $middlewareStackProperty->getValue($router);
        $temp_c3c36b07 = ($middlewareStack ? get_class($middlewareStack) : 'None') . "\n";
        echo "- Final Middleware Stack: " . $temp_c3c36b07;

    if ($middlewareStack) {
        $reflection = new ReflectionClass($middlewareStack);
        $middlewareProperty = $reflection->getProperty('middleware');
        $middlewareProperty->setAccessible(true);
        $middleware = $middlewareProperty->getValue($middlewareStack);

        echo "- Middleware Count: " . count($middleware) . "\n";

        foreach ($middleware as $index => $mw) {
            echo "- Middleware {$index}: " . get_class($mw) . "\n";
        }
    }

    echo "\n✅ Logger test completed successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
