<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Http\Request;
use IslamWiki\Http\Middleware\SkinMiddleware;

echo "=== Debug Skin Middleware ===\n\n";

try {
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "✅ Application loaded\n";
    
    // Test session
    $session = $container->get('session');
    echo "Session loaded: " . get_class($session) . "\n";
    echo "Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    
    if ($session->isLoggedIn()) {
        echo "User ID: " . $session->getUserId() . "\n";
    }
    
    // Test skin manager
    $skinManager = $container->get('skin.manager');
    echo "Skin manager loaded: " . get_class($skinManager) . "\n";
    
    // Test available skins
    $availableSkins = $skinManager->getAvailableSkinNames();
    echo "Available skins: " . implode(', ', $availableSkins) . "\n";
    
    // Test active skin
    $activeSkin = $skinManager->getActiveSkin();
    echo "Active skin: " . ($activeSkin ? $activeSkin->getName() : 'null') . "\n";
    
    $activeSkinName = $skinManager->getActiveSkinName();
    echo "Active skin name: " . $activeSkinName . "\n";
    
    // Test user-specific skin
    if ($session->isLoggedIn()) {
        $userId = $session->getUserId();
        $userSkin = $skinManager->getActiveSkinForUser($userId);
        $userSkinName = $skinManager->getActiveSkinNameForUser($userId);
        
        echo "User skin: " . ($userSkin ? $userSkin->getName() : 'null') . "\n";
        echo "User skin name: " . $userSkinName . "\n";
        
        if ($userSkin) {
            echo "User skin CSS length: " . strlen($userSkin->getCssContent()) . "\n";
        }
    }
    
    // Test view renderer
    $viewRenderer = $container->get('view');
    echo "View renderer loaded: " . get_class($viewRenderer) . "\n";
    
    // Test middleware
    $middleware = new SkinMiddleware($app);
    echo "Middleware created: " . get_class($middleware) . "\n";
    
    // Create a mock request
    $request = new Request('GET', '/');
    echo "Mock request created\n";
    
    // Test middleware execution
    $response = $middleware->handle($request, function($req) {
        echo "✅ Middleware next() called\n";
        return new \IslamWiki\Core\Http\Response();
    });
    
    echo "✅ Middleware executed successfully\n";
    
    // Check if globals were set
    $twig = $viewRenderer->getTwig();
    $globals = $twig->getGlobals();
    echo "View globals:\n";
    foreach ($globals as $key => $value) {
        if (strpos($key, 'skin_') === 0) {
            echo "  $key: " . (is_string($value) ? strlen($value) . ' chars' : gettype($value)) . "\n";
        }
    }
    
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n"; 