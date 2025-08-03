<?php
declare(strict_types=1);

/**
 * Debug Login Test
 * 
 * Tests the login process and session management.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Login and Session Management\n";
echo "======================================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

echo "✅ Application initialized\n";
echo "✅ Session manager: " . get_class($session) . "\n";
echo "✅ Database connection: " . get_class($db) . "\n\n";

// Test 1: Check if session is started
echo "📊 Test 1: Session Status\n";
echo "==========================\n";
echo "Session status: " . session_status() . "\n";
echo "Session name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n\n";

// Test 2: Check if admin user exists
echo "📊 Test 2: Database Check\n";
echo "==========================\n";
try {
    $adminUser = $db->first("SELECT * FROM users WHERE username = 'admin'");
    if ($adminUser) {
        echo "✅ Admin user found:\n";
        echo "  - ID: {$adminUser->id}\n";
        echo "  - Username: {$adminUser->username}\n";
        echo "  - Email: {$adminUser->email}\n";
        echo "  - Is admin: " . ($adminUser->is_admin ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ Admin user not found\n";
    }
} catch (\Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Simulate login
echo "📊 Test 3: Simulating Login\n";
echo "============================\n";
try {
    // Simulate login
    $session->login(1, 'admin', true);
    echo "✅ Login simulation completed\n";
    echo "Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "User ID: " . ($session->getUserId() ?? 'null') . "\n";
    echo "Username: " . ($session->getUsername() ?? 'null') . "\n";
    echo "Is admin: " . ($session->isAdmin() ? 'Yes' : 'No') . "\n";
    
    // Check session data
    echo "\nSession data:\n";
    if (empty($_SESSION)) {
        echo "  - Session is empty\n";
    } else {
        foreach ($_SESSION as $key => $value) {
            echo "  - $key: " . (is_string($value) ? $value : gettype($value)) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Login error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Test SettingsController with authenticated session
echo "📊 Test 4: Testing SettingsController\n";
echo "=====================================\n";
try {
    $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
    
    // Use reflection to access the private index method
    $reflection = new ReflectionClass($settingsController);
    $method = $reflection->getMethod('index');
    $method->setAccessible(true);
    
    $response = $method->invoke($settingsController);
    
    echo "✅ SettingsController executed successfully\n";
    echo "Response status: " . $response->getStatusCode() . "\n";
    
    // Check if response contains skin data
    $body = $response->getBody();
    if (strpos($body, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
    } else {
        echo "❌ Response does not contain skin cards\n";
    }
    
} catch (\Exception $e) {
    echo "❌ SettingsController error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Login test completed!\n"; 