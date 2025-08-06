<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Testing Authentication Flow\n";
echo "=============================\n\n";

try {
    // Initialize application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get session and auth
    $session = $container->get('session');
    $auth = new \IslamWiki\Core\Auth\AmanSecurity($session, $container->get('db'));
    
    echo "📊 Initial State:\n";
    echo "- Session Status: " . session_status() . "\n";
    echo "- Session Name: " . session_name() . "\n";
    echo "- Session ID: " . session_id() . "\n";
    echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "- Auth Check: " . ($auth->check() ? 'Yes' : 'No') . "\n";
    echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
    echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";
    
    // Check session data
    echo "\n📋 Session Data:\n";
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "- Session Data: " . print_r($_SESSION, true) . "\n";
    } else {
        echo "- No active session\n";
    }
    
    // Test AmanSecurity::user() method
echo "\n🔍 Testing AmanSecurity::user():\n";
    $user = $auth->user();
    echo "- User returned: " . ($user ? 'Yes' : 'No') . "\n";
    if ($user) {
        echo "- User ID: " . $user['id'] . "\n";
        echo "- Username: " . $user['username'] . "\n";
        echo "- Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";
    }
    
    // Test database connection
    echo "\n🗄️ Testing Database Connection:\n";
    $db = $container->get('db');
    try {
        $result = $db->select('SELECT COUNT(*) as count FROM users');
        echo "- Database connection: ✅ Working\n";
        echo "- Users count: " . ($result[0]['count'] ?? 'unknown') . "\n";
        
        // Check if current user exists in database
        if ($session->isLoggedIn()) {
            $userId = $session->getUserId();
            $userCheck = $db->select('SELECT id, username, is_active FROM users WHERE id = ?', [$userId]);
            echo "- Current user in DB: " . (!empty($userCheck) ? 'Yes' : 'No') . "\n";
            if (!empty($userCheck)) {
                echo "- User active: " . ($userCheck[0]['is_active'] ? 'Yes' : 'No') . "\n";
            }
        }
    } catch (\Exception $e) {
        echo "- Database connection: ❌ Error: " . $e->getMessage() . "\n";
    }
    
    // Test session persistence
    echo "\n🔄 Testing Session Persistence:\n";
    
    // Simulate login
    echo "- Simulating login...\n";
    $session->login(1, 'admin', true);
    echo "- Login completed\n";
    echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
    echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";
    
    // Test auth check after login
    echo "- Auth Check: " . ($auth->check() ? 'Yes' : 'No') . "\n";
    $user = $auth->user();
    echo "- User returned: " . ($user ? 'Yes' : 'No') . "\n";
    
    // Test session write and read
    echo "\n💾 Testing Session Write/Read:\n";
    session_write_close();
    echo "- Session written and closed\n";
    
    // Reopen session
    session_start();
    echo "- Session reopened\n";
    
    // Check if still logged in
    echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "- Auth Check: " . ($auth->check() ? 'Yes' : 'No') . "\n";
    
    echo "\n✅ Authentication flow test completed\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 