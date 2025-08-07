<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;

echo "🔍 Debug Force Middleware Test\n";
echo "==============================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');

    echo "✅ Application loaded successfully\n\n";

    // Get the router
    $router = $app->getRouter();
    echo "✅ Router loaded: " . get_class($router) . "\n\n";

    // Force middleware stack initialization
    echo "🔧 Forcing Middleware Stack Initialization:\n";

    $reflection = new ReflectionClass($router);
    $initializeMethod = $reflection->getMethod('initializeMiddlewareStack');
    $initializeMethod->setAccessible(true);

    try {
        $initializeMethod->invoke($router);
        echo "- Middleware stack initialization completed\n";
    } catch (Exception $e) {
        echo "- Middleware stack initialization error: " . $e->getMessage() . "\n";
    }

    // Check if middleware stack was created
    $middlewareStackProperty = $reflection->getProperty('middlewareStack');
    $middlewareStackProperty->setAccessible(true);
    $middlewareStack = $middlewareStackProperty->getValue($router);

    echo "\n🔧 Middleware Stack Status After Initialization:\n";
        $temp_c3c36b07 = ($middlewareStack ? get_class($middlewareStack) : 'None') . "\n";
        echo "- Middleware Stack: " . $temp_c3c36b07;

    if ($middlewareStack) {
        $reflection = new ReflectionClass($middlewareStack);
        $middlewareProperty = $reflection->getProperty('middleware');
        $middlewareProperty->setAccessible(true);
        $middleware = $middlewareProperty->getValue($middlewareStack);

        echo "- Middleware Count: " . count($middleware) . "\n";

        foreach ($middleware as $index => $mw) {
            echo "- Middleware {$index}: " . get_class($mw) . "\n";
        }
    } else {
        echo "- No middleware stack found after initialization\n";
    }

    echo "\n✅ Force middleware test completed successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
