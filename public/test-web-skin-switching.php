<?php
/**
 * Test Web Skin Switching
 * 
 * Test to simulate web interface skin switching with middleware.
 * 
 * @package IslamWiki\Tests
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get services
$session = $container->get('session');
$skinManager = $container->get('skin.manager');

echo "=== Test Web Skin Switching ===\n\n";

// Step 1: Login a user
echo "1. Logging in user...\n";
$session->login(1, 'testuser'); // Use user ID 1 which has GreenSkin
echo "   ✅ User logged in\n";

// Step 2: Check current skin
echo "\n2. Current skin before middleware:\n";
$currentSkin = $skinManager->getActiveSkinNameForUser(1);
echo "   📋 User's skin: $currentSkin\n";

// Step 3: Test middleware
echo "\n3. Testing middleware...\n";
try {
    // Create a request
    $request = new Request('GET', '/test');
    
    // Create skin middleware
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    
    // Process the request through middleware
    $response = $skinMiddleware->handle($request, function($req) {
        return new Response(200, [], 'Test response');
    });
    
    echo "   ✅ Middleware executed successfully\n";
    
    // Check if skin data was updated in the view
    $viewRenderer = $container->get('view');
    $twig = $viewRenderer->getTwig();
    $globals = $twig->getGlobals();
    
    echo "   📋 Active skin in globals: " . ($globals['active_skin'] ?? 'not set') . "\n";
    echo "   📋 Skin name in globals: " . ($globals['skin_name'] ?? 'not set') . "\n";
    echo "   📋 Skin CSS length: " . strlen($globals['skin_css'] ?? '') . " characters\n";
    
    // Check if the skin is actually GreenSkin
    if (strpos($globals['skin_css'] ?? '', 'green') !== false) {
        echo "   ✅ GreenSkin CSS detected in globals\n";
    } else {
        echo "   ❌ GreenSkin CSS not detected in globals\n";
    }
    
} catch (\Throwable $e) {
    echo "   ❌ Middleware error: " . $e->getMessage() . "\n";
}

// Step 4: Test switching to BlueSkin
echo "\n4. Testing skin switching to BlueSkin...\n";
try {
    $db = $container->get('db');
    $settings = ['skin' => 'BlueSkin'];
    $stmt = $db->prepare("UPDATE user_settings SET settings = ? WHERE user_id = ?");
    $stmt->execute([json_encode($settings), 1]);
    
    echo "   ✅ Updated user settings to BlueSkin\n";
    
    // Test middleware again
    $request = new Request('GET', '/test');
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    $response = $skinMiddleware->handle($request, function($req) {
        return new Response(200, [], 'Test response');
    });
    
    $viewRenderer = $container->get('view');
    $twig = $viewRenderer->getTwig();
    $globals = $twig->getGlobals();
    
    echo "   📋 Active skin in globals: " . ($globals['active_skin'] ?? 'not set') . "\n";
    echo "   📋 Skin name in globals: " . ($globals['skin_name'] ?? 'not set') . "\n";
    
    if (strpos($globals['skin_css'] ?? '', 'blue') !== false) {
        echo "   ✅ BlueSkin CSS detected in globals\n";
    } else {
        echo "   ❌ BlueSkin CSS not detected in globals\n";
    }
    
} catch (\Throwable $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Step 5: Cleanup
echo "\n5. Cleanup:\n";
$session->logout();
echo "   ✅ Logged out user\n";

echo "\n=== Test Complete ===\n"; 