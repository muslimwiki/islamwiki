<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Http\Request;

echo "🔍 Debug Authenticated Test\n";
echo "==========================\n\n";

try {
    // Create application instance
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();

    echo "✅ Application loaded successfully\n\n";

    // Simulate an authenticated user
    echo "👤 Simulating Authenticated User:\n";

    // Create a mock request with session data
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/settings';
    $_SERVER['HTTP_HOST'] = 'local.islam.wiki';
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';

    // Mock session data for user ID 1
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';
    $_SESSION['authenticated'] = true;

    // Capture the request
    $request = Request::capture();
    echo "- Request Method: " . $request->getMethod() . "\n";
    echo "- Request URI: " . $request->getUri()->getPath() . "\n";

    // Get session manager
    $session = $container->get('session');
    echo "- Session User ID: " . $session->getUserId() . "\n";
        $temp_943497ca = ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
        echo "- Session Authenticated: " . $temp_943497ca;

    // Test skin manager with authenticated user
    $skinManager = $container->get('skin.manager');
    $userSkin = $skinManager->getActiveSkinForUser(1);
        $temp_9c3f8718 = ($userSkin ? $userSkin->getName() : 'None') . "\n";
        echo "- User 1 Active Skin: " . $temp_9c3f8718;

    // Test middleware execution
    echo "\n🔧 Testing Middleware Execution:\n";

    $router = $app->getRouter();
    $reflection = new ReflectionClass($router);
    $middlewareStackProperty = $reflection->getProperty('middlewareStack');
    $middlewareStackProperty->setAccessible(true);
    $middlewareStack = $middlewareStackProperty->getValue($router);

    if ($middlewareStack) {
        echo "- Middleware Stack Available: Yes\n";

        // Force middleware stack initialization
        $initializeMethod = $reflection->getMethod('initializeMiddlewareStack');
        $initializeMethod->setAccessible(true);
        $initializeMethod->invoke($router);

        // Get updated middleware stack
        $middlewareStack = $middlewareStackProperty->getValue($router);

        if ($middlewareStack) {
            $reflection = new ReflectionClass($middlewareStack);
            $middlewareProperty = $reflection->getProperty('middleware');
            $middlewareProperty->setAccessible(true);
            $middleware = $middlewareProperty->getValue($middlewareStack);

            echo "- Middleware Count: " . count($middleware) . "\n";

            foreach ($middleware as $index => $mw) {
                echo "- Middleware {$index}: " . get_class($mw) . "\n";
            }

            // Test if SkinMiddleware is working
            foreach ($middleware as $mw) {
                if (get_class($mw) === 'IslamWiki\Http\Middleware\SkinMiddleware') {
                    echo "- SkinMiddleware Found: Yes\n";

                    // Test middleware execution
                    try {
                        $nextHandler = function ($request) {
                            return new \IslamWiki\Core\Http\Response(200, [], 'Mock Response');
                        };

                        $response = $mw->handle($request, $nextHandler);
                        echo "- SkinMiddleware Execution: Success\n";
                        $temp_26771779 = $response->getStatusCode() . "\n";
                        echo "- Response Status: " . $temp_26771779;
                    } catch (Exception $e) {
                        $temp_c9c6f9b2 = $e->getMessage() . "\n";
                        echo "- SkinMiddleware Execution Error: " . $temp_c9c6f9b2;
                    }
                    break;
                }
            }
        }
    } else {
        echo "- Middleware Stack Available: No\n";
    }

    // Test view globals after middleware
    echo "\n📋 Testing View Globals:\n";
    if ($container->has('view')) {
        $view = $container->get('view');
        echo "- View Renderer: " . get_class($view) . "\n";

        // Note: We can't directly access view globals, but we can test if the view is working
        echo "- View Available: Yes\n";
    } else {
        echo "- View Available: No\n";
    }

    echo "\n✅ Authenticated test completed successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
