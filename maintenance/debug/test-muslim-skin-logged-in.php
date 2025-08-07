<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Http\Request;
use IslamWiki\Http\Middleware\SkinMiddleware;

echo "=== Test Muslim Skin with Logged-in User ===\n\n";

try {
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();

    echo "✅ Application loaded\n";

    // Simulate a logged-in user (user ID 1 has Muslim skin)
    $session = $container->get('session');
    $session->login(1, 'admin', true); // Login as admin user

    echo "✅ Simulated login as admin user (ID: 1)\n";
    echo "Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "User ID: " . $session->getUserId() . "\n";
    echo "Username: " . $session->getUsername() . "\n";

    // Test skin manager
    $skinManager = $container->get('skin.manager');
    echo "✅ Skin manager loaded\n";

    // Test user-specific skin
    $userId = $session->getUserId();
    $userSkin = $skinManager->getActiveSkinForUser($userId);
    $userSkinName = $skinManager->getActiveSkinNameForUser($userId);

    echo "User skin: " . ($userSkin ? $userSkin->getName() : 'null') . "\n";
    echo "User skin name: " . $userSkinName . "\n";

    if ($userSkin) {
        echo "User skin CSS length: " . strlen($userSkin->getCssContent()) . "\n";
        echo "User skin layout path: " . $userSkin->getLayoutPath() . "\n";
    }

    // Test middleware
    $middleware = new SkinMiddleware($app);
    echo "✅ Middleware created\n";

    // Create a mock request
    $request = new Request('GET', '/');
    echo "✅ Mock request created\n";

    // Test middleware execution
    try {
        $response = $middleware->handle($request, function ($req) {
            echo "✅ Middleware next() called\n";
            return new \IslamWiki\Core\Http\Response();
        });
        echo "✅ Middleware executed successfully\n";
    } catch (\Throwable $e) {
        echo "❌ Middleware execution failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }

    // Debug: Check Twig loader paths
    $viewRenderer = $container->get('view');
    $twig = $viewRenderer->getTwig();
    $loader = $twig->getLoader();
    if ($loader instanceof \Twig\Loader\FilesystemLoader) {
        $paths = $loader->getPaths();
        echo "Twig loader paths:\n";
        foreach ($paths as $namespace => $pathList) {
            if (is_array($pathList)) {
                foreach ($pathList as $path) {
                    echo "  $namespace: $path\n";
                }
            } else {
                echo "  $namespace: $pathList\n";
            }
        }
    }

    // Check if globals were set
    $globals = $twig->getGlobals();
    echo "View globals:\n";
    foreach ($globals as $key => $value) {
        if (strpos($key, 'skin_') === 0) {
            $temp_67e25c7e = (is_string($value) ? strlen($value) . ' chars' : gettype($value)) . "\n";
            echo "  $key: " . $temp_67e25c7e;
        }
    }

    // Test rendering a page with the Muslim skin
    echo "\n=== Testing Page Rendering ===\n";

    $testData = [
        'title' => 'Test Page with Muslim Skin',
        'user' => [
            'id' => 1,
            'username' => 'admin',
            'email' => 'admin@islamwiki.local'
        ],
        'app' => [
            'request' => [
                'query' => [
                    'get' => function ($key, $default = '') {
                        return $default;
                    }
                ]
            ]
        ]
    ];

    // Add the skin data to test data
    foreach ($globals as $key => $value) {
        if (strpos($key, 'skin_') === 0) {
            $testData[$key] = $value;
        }
    }

    // Test rendering
    $rendered = $viewRenderer->render('layouts/app.twig', $testData);
    echo "✅ Page rendered successfully\n";
    echo "Rendered content length: " . strlen($rendered) . " characters\n";

    // Check if the Muslim skin layout was used
    if (strpos($rendered, 'citizen-header') !== false) {
        echo "✅ Muslim skin layout detected in rendered content\n";
    } else {
        echo "❌ Muslim skin layout not detected in rendered content\n";
    }

    // Check if Muslim skin CSS is included
    if (strpos($rendered, '--primary-color: #2c5aa0') !== false) {
        echo "✅ Muslim skin CSS detected in rendered content\n";
    } else {
        echo "❌ Muslim skin CSS not detected in rendered content\n";
    }

    // Check if the skin layout path was set
    if (strpos($rendered, 'skin:layout.twig') !== false) {
        echo "✅ Skin layout path detected in rendered content\n";
    } else {
        echo "❌ Skin layout path not detected in rendered content\n";
    }

    // Save the rendered content to a file for inspection
    file_put_contents(__DIR__ . '/test-muslim-skin-output.html', $rendered);
    echo "✅ Rendered content saved to debug/test-muslim-skin-output.html\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
