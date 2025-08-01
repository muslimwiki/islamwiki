<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Http\Request;
use IslamWiki\Http\Middleware\SkinMiddleware;

echo "=== Test Skin with Login ===\n\n";

try {
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Simulate login for user ID 1
    $session = $container->get('session');
    $session->login(1, 'testuser');
    
    echo "✅ User logged in: " . $session->getUserId() . " (" . $session->getUsername() . ")\n";
    
    // Test skin manager with logged-in user
    $skinManager = $container->get('skin.manager');
    $userId = $session->getUserId();
    
    $userSkin = $skinManager->getActiveSkinForUser($userId);
    $userSkinName = $skinManager->getActiveSkinNameForUser($userId);
    
    echo "User skin: " . ($userSkin ? $userSkin->getName() : 'null') . "\n";
    echo "User skin name: " . $userSkinName . "\n";
    
    if ($userSkin) {
        echo "User skin CSS length: " . strlen($userSkin->getCssContent()) . "\n";
    }
    
    // Test middleware with logged-in user
    $middleware = new SkinMiddleware($app);
    $request = new Request('GET', '/');
    
    $response = $middleware->handle($request, function($req) {
        echo "✅ Middleware executed with logged-in user\n";
        return new \IslamWiki\Core\Http\Response();
    });
    
    // Check globals
    $viewRenderer = $container->get('view');
    $twig = $viewRenderer->getTwig();
    $globals = $twig->getGlobals();
    
    echo "View globals with login:\n";
    foreach ($globals as $key => $value) {
        if (strpos($key, 'skin_') === 0) {
            echo "  $key: " . (is_string($value) ? strlen($value) . ' chars' : gettype($value)) . "\n";
        }
    }
    
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n"; 