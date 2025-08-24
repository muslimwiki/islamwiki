<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Application;\Application

echo "🔍 Debug Simple Request Test\n";
echo "============================\n\n";

try {
    // Create application instance
    $app = new Application(__DIR__ . '/..');

    echo "✅ Application loaded successfully\n\n";

    // Get the router
    $router = $app->getRouter();
    echo "✅ Router loaded: " . get_class($router) . "\n\n";

    // Test if middleware stack is initialized
    $reflection = new ReflectionClass($router);
    $middlewareStackProperty = $reflection->getProperty('middlewareStack');
    $middlewareStackProperty->setAccessible(true);
    $middlewareStack = $middlewareStackProperty->getValue($router);

    echo "🔧 Middleware Stack Status:\n";
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
        echo "- No middleware stack found\n";
    }

    echo "\n✅ Simple request test completed successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
