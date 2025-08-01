<?php
/**
 * Test Dynamic Skin Switching
 * 
 * Tests that skin switching works dynamically based on user settings.
 * 
 * @package IslamWiki\Tests
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Session\SessionManager;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get services
$session = $container->get('session');
$skinManager = $container->get('skin.manager');

echo "=== Dynamic Skin Switching Test ===\n\n";

// Test 1: Check current user and skin
echo "1. Current User Status:\n";
if ($session->isLoggedIn()) {
    $userId = $session->getUserId();
    echo "   ✅ User logged in - ID: $userId\n";
    
    // Get user's current skin
    $userSkin = $skinManager->getActiveSkinNameForUser($userId);
    echo "   📋 User's current skin: $userSkin\n";
    
    // Get global skin
    $globalSkin = $skinManager->getActiveSkinName();
    echo "   🌐 Global skin: $globalSkin\n";
    
} else {
    echo "   ❌ No user logged in\n";
    echo "   🌐 Global skin: " . $skinManager->getActiveSkinName() . "\n";
}

// Test 2: Available skins
echo "\n2. Available Skins:\n";
$availableSkins = $skinManager->getAvailableSkinNames();
foreach ($availableSkins as $skinName) {
    echo "   - $skinName\n";
}

// Test 3: Test skin switching for logged-in user
if ($session->isLoggedIn()) {
    $userId = $session->getUserId();
    
    echo "\n3. Testing Skin Switching:\n";
    
    // Test switching to GreenSkin
    echo "   🔄 Switching to GreenSkin...\n";
    
    // Update user settings in database
    try {
        $db = $container->get('db');
        
        // Check if user settings exist
        $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Update existing settings
            $settings = json_decode($result['settings'], true) ?? [];
            $settings['skin'] = 'GreenSkin';
            
            $stmt = $db->prepare("UPDATE user_settings SET settings = ? WHERE user_id = ?");
            $stmt->execute([json_encode($settings), $userId]);
        } else {
            // Create new settings
            $settings = ['skin' => 'GreenSkin'];
            $stmt = $db->prepare("INSERT INTO user_settings (user_id, settings) VALUES (?, ?)");
            $stmt->execute([$userId, json_encode($settings)]);
        }
        
        echo "   ✅ User settings updated to GreenSkin\n";
        
        // Check if skin changed
        $newSkin = $skinManager->getActiveSkinNameForUser($userId);
        echo "   📋 User's skin after update: $newSkin\n";
        
        if ($newSkin === 'greenskin') {
            echo "   ✅ Skin switching successful!\n";
        } else {
            echo "   ❌ Skin switching failed - expected 'greenskin', got '$newSkin'\n";
        }
        
    } catch (\Throwable $e) {
        echo "   ❌ Error updating user settings: " . $e->getMessage() . "\n";
    }
    
    // Test switching back to Bismillah
    echo "\n   🔄 Switching back to Bismillah...\n";
    
    try {
        $settings = ['skin' => 'Bismillah'];
        $stmt = $db->prepare("UPDATE user_settings SET settings = ? WHERE user_id = ?");
        $stmt->execute([json_encode($settings), $userId]);
        
        echo "   ✅ User settings updated to Bismillah\n";
        
        // Check if skin changed back
        $newSkin = $skinManager->getActiveSkinNameForUser($userId);
        echo "   📋 User's skin after update: $newSkin\n";
        
        if ($newSkin === 'bismillah') {
            echo "   ✅ Skin switching back successful!\n";
        } else {
            echo "   ❌ Skin switching back failed - expected 'bismillah', got '$newSkin'\n";
        }
        
    } catch (\Throwable $e) {
        echo "   ❌ Error updating user settings: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "\n3. Skipping skin switching test - no user logged in\n";
}

// Test 4: Test middleware approach
echo "\n4. Testing Middleware Approach:\n";

try {
    // Simulate a request with middleware
    $request = new \IslamWiki\Core\Http\Request('GET', '/test');
    
    // Create skin middleware
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    
    // Test middleware
    $response = $skinMiddleware->handle($request, function($req) {
        return new \IslamWiki\Core\Http\Response(200, [], 'Test response');
    });
    
    echo "   ✅ SkinMiddleware executed successfully\n";
    echo "   📋 Response status: " . $response->getStatusCode() . "\n";
    
} catch (\Throwable $e) {
    echo "   ❌ SkinMiddleware error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n"; 