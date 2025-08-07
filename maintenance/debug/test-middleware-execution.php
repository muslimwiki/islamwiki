<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== Testing Middleware Execution ===\n";

// Test 1: Create application and test middleware directly
echo "\n1. Testing middleware execution directly...\n";
try {
    // Create application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Check if application is bound to container
    if ($container->has(\IslamWiki\Core\Application::class)) {
        echo "✅ Application is bound to container\n";
        $boundApp = $container->get(\IslamWiki\Core\Application::class);
        echo "📄 Bound app class: " . get_class($boundApp) . "\n";
    } else {
        echo "❌ Application is not bound to container\n";
    }

    if ($container->has('app')) {
        echo "✅ 'app' alias is bound to container\n";
        $boundApp = $container->get('app');
        echo "📄 Bound app class: " . get_class($boundApp) . "\n";
    } else {
        echo "❌ 'app' alias is not bound to container\n";
    }

    // Create skin middleware
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    echo "✅ SkinMiddleware created successfully\n";

    // Create a mock request
    $request = new \IslamWiki\Core\Http\Request(
        'GET',
        new \IslamWiki\Core\Http\Uri('https://local.islam.wiki/'),
        [],
        [],
        '1.1'
    );

    // Test middleware execution
    $response = $skinMiddleware->handle($request, function ($req) {
        return new \IslamWiki\Core\Http\Response(200, [], 'Test response');
    });

    echo "✅ SkinMiddleware executed successfully\n";
    echo "📄 Response status: " . $response->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "❌ Error testing middleware: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 2: Test the actual web request to see if middleware is called
echo "\n2. Testing web request middleware execution...\n";
try {
    // Start session and login user
    session_start();
    $session = new \IslamWiki\Core\Session\Wisal();
    $session->login(1, 'admin', true);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'IslamWiki-Debug/1.0');
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "❌ cURL error: " . $error . "\n";
    } else {
        echo "📄 HTTP Status Code: " . $httpCode . "\n";
        if ($httpCode === 200) {
            echo "✅ Homepage is accessible\n";

            // Check for Muslim skin specific content
            if (strpos($response, 'citizen-header') !== false) {
                echo "✅ Muslim skin layout is being used (citizen-header found)\n";
            } else {
                echo "❌ Muslim skin layout not being used (citizen-header not found)\n";
            }

            // Check for ZamZam directives in the response
            if (strpos($response, 'z-data') !== false) {
                echo "✅ ZamZam directives found in response\n";
            } else {
                echo "❌ ZamZam directives not found in response\n";
            }

            // Save response for inspection
            file_put_contents(__DIR__ . '/test-middleware-response.html', $response);
            echo "📄 Response saved to test-middleware-response.html for inspection\n";
        } else {
            echo "❌ Homepage returned status code: " . $httpCode . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing web request: " . $e->getMessage() . "\n";
}

// Test 3: Check logs for middleware execution
echo "\n3. Checking logs for middleware execution...\n";
try {
    $logFile = __DIR__ . '/../storage/logs/debug.log';
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        $recentLines = array_slice($lines, -50); // Last 50 lines

        $middlewareLogs = array_filter($recentLines, function ($line) {
            return strpos($line, 'SkinMiddleware') !== false ||
                   strpos($line, 'MiddlewareStack') !== false;
        });

        if (!empty($middlewareLogs)) {
            echo "✅ Found middleware logs:\n";
            foreach ($middlewareLogs as $log) {
                echo "📄 " . $log . "\n";
            }
        } else {
            echo "❌ No middleware logs found in recent entries\n";
        }
    } else {
        echo "❌ Log file not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking logs: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
