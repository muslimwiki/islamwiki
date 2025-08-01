<?php
/**
 * Test Dynamic Skin Switching with User Login
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
$db = $container->get('db');

echo "=== Dynamic Skin Switching Test with User ===\n\n";

// Step 1: Create or get a test user
echo "1. Setting up test user...\n";

try {
    // Check if test user exists
    $stmt = $db->prepare("SELECT id, username FROM users WHERE username = 'testuser'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Create test user
        $stmt = $db->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $passwordHash = password_hash('testpass123', PASSWORD_DEFAULT);
        $stmt->execute(['testuser', 'test@example.com', $passwordHash]);
        $userId = $db->lastInsertId();
        echo "   ✅ Created test user with ID: $userId\n";
    } else {
        $userId = $user['id'];
        echo "   ✅ Found existing test user with ID: $userId\n";
    }
    
    // Log in the user
    $session->login($userId, 'testuser');
    echo "   ✅ Logged in test user\n";
    
} catch (\Throwable $e) {
    echo "   ❌ Error setting up test user: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Check current skin
echo "\n2. Current Skin Status:\n";
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
    exit(1);
}

// Step 3: Test skin switching
echo "\n3. Testing Skin Switching:\n";

// Test switching to GreenSkin
echo "   🔄 Switching to GreenSkin...\n";

try {
    // Check if user settings exist
    $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
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

// Test switching to BlueSkin
echo "\n   🔄 Switching to BlueSkin...\n";

try {
    $settings = ['skin' => 'BlueSkin'];
    $stmt = $db->prepare("UPDATE user_settings SET settings = ? WHERE user_id = ?");
    $stmt->execute([json_encode($settings), $userId]);
    
    echo "   ✅ User settings updated to BlueSkin\n";
    
    // Check if skin changed
    $newSkin = $skinManager->getActiveSkinNameForUser($userId);
    echo "   📋 User's skin after update: $newSkin\n";
    
    if ($newSkin === 'blueskin') {
        echo "   ✅ Skin switching successful!\n";
    } else {
        echo "   ❌ Skin switching failed - expected 'blueskin', got '$newSkin'\n";
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

// Step 4: Test middleware with user
echo "\n4. Testing Middleware with User:\n";

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
    
    // Check if skin data was updated
    $viewRenderer = $container->get('view');
    $twig = $viewRenderer->getTwig();
    $globals = $twig->getGlobals();
    
    echo "   📋 Active skin in globals: " . ($globals['active_skin'] ?? 'not set') . "\n";
    echo "   📋 Skin name in globals: " . ($globals['skin_name'] ?? 'not set') . "\n";
    
} catch (\Throwable $e) {
    echo "   ❌ SkinMiddleware error: " . $e->getMessage() . "\n";
}

// Step 5: Cleanup
echo "\n5. Cleanup:\n";
$session->logout();
echo "   ✅ Logged out test user\n";

echo "\n=== Test Complete ===\n"; 