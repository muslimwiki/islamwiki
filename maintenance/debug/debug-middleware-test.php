<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Http\Middleware\SkinMiddleware;

echo "🔍 Debug Middleware Test\n";
echo "========================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "✅ Application loaded successfully\n\n";
    
    // Test if middleware stack is being created
    echo "🔧 Testing Middleware Stack:\n";
    
    // Get the router
    $router = $container->get('router');
    echo "- Router: " . get_class($router) . "\n";
    
    // Check if middleware stack exists
    $reflection = new ReflectionClass($router);
    $middlewareStackProperty = $reflection->getProperty('middlewareStack');
    $middlewareStackProperty->setAccessible(true);
    $middlewareStack = $middlewareStackProperty->getValue($router);
    
    echo "- Middleware Stack: " . ($middlewareStack ? get_class($middlewareStack) : 'None') . "\n";
    
    if ($middlewareStack) {
        // Check middleware count
        $reflection = new ReflectionClass($middlewareStack);
        $middlewareProperty = $reflection->getProperty('middleware');
        $middlewareProperty->setAccessible(true);
        $middleware = $middlewareProperty->getValue($middlewareStack);
        
        echo "- Middleware Count: " . count($middleware) . "\n";
        
        foreach ($middleware as $index => $mw) {
            echo "- Middleware {$index}: " . get_class($mw) . "\n";
        }
    }
    
    // Test creating SkinMiddleware directly
    echo "\n🔧 Testing SkinMiddleware Creation:\n";
    try {
        $skinMiddleware = new SkinMiddleware($app);
        echo "- SkinMiddleware Created: " . get_class($skinMiddleware) . "\n";
        
        // Test if the middleware can access container services
        $reflection = new ReflectionClass($skinMiddleware);
        $appProperty = $reflection->getProperty('app');
        $appProperty->setAccessible(true);
        $appInstance = $appProperty->getValue($skinMiddleware);
        
        echo "- App Instance: " . ($appInstance ? get_class($appInstance) : 'None') . "\n";
        
        if ($appInstance) {
            $container = $appInstance->getContainer();
            echo "- Container Available: " . ($container ? 'Yes' : 'No') . "\n";
            
            if ($container) {
                $services = [
                    'session' => $container->has('session'),
                    'skin.manager' => $container->has('skin.manager'),
                    'view' => $container->has('view')
                ];
                
                echo "- Container Services:\n";
                foreach ($services as $service => $available) {
                    echo "  - {$service}: " . ($available ? 'Available' : 'Not available') . "\n";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "- Error creating SkinMiddleware: " . $e->getMessage() . "\n";
    }
    
    // Test middleware execution simulation
    echo "\n🔧 Testing Middleware Execution Simulation:\n";
    
    if (isset($skinMiddleware)) {
        try {
            // Create a mock request
            $request = new \IslamWiki\Core\Http\Request(
                'GET',
                new \GuzzleHttp\Psr7\Uri('https://local.islam.wiki/'),
                [],
                null,
                '1.1'
            );
            
            echo "- Mock Request Created: " . get_class($request) . "\n";
            
            // Create a mock next handler
            $nextHandler = function($request) {
                return new \IslamWiki\Core\Http\Response(200, [], 'Mock Response');
            };
            
            echo "- Mock Next Handler Created\n";
            
            // Try to execute the middleware
            try {
                $response = $skinMiddleware->handle($request, $nextHandler);
                echo "- Middleware Execution: Success\n";
                echo "- Response Status: " . $response->getStatusCode() . "\n";
            } catch (Exception $e) {
                echo "- Middleware Execution Error: " . $e->getMessage() . "\n";
            }
            
        } catch (Exception $e) {
            echo "- Error in middleware execution test: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Middleware test completed successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 