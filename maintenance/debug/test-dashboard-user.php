<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Testing Dashboard User Retrieval\n";
echo "==================================\n\n";

try {
    // Initialize application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get session and auth
    $session = $container->get('session');
    $auth = $container->get('auth');
    
    echo "📊 Session Status:\n";
    echo "- Session Status: " . session_status() . "\n";
    echo "- Session Name: " . session_name() . "\n";
    echo "- Session ID: " . session_id() . "\n";
    echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
    echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";
    
    echo "\n🔍 Auth Service Test:\n";
    echo "- Auth Check: " . ($auth->check() ? 'Yes' : 'No') . "\n";
    $user = $auth->user();
    echo "- User returned: " . ($user ? 'Yes' : 'No') . "\n";
    if ($user) {
        echo "- User ID: " . $user['id'] . "\n";
        echo "- Username: " . $user['username'] . "\n";
        echo "- Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n📋 Session Data:\n";
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "- Session Data: " . print_r($_SESSION, true) . "\n";
    } else {
        echo "- No active session\n";
    }
    
    echo "\n🧪 Dashboard Controller Simulation:\n";
    
    // Simulate what DashboardController does
    $user = null;
    try {
        $auth = $container->get('auth');
        $user = $auth->user();
        echo "- Auth service retrieved: " . ($auth ? 'Yes' : 'No') . "\n";
        echo "- User from auth: " . ($user ? 'Yes' : 'No') . "\n";
        if ($user) {
            echo "- User ID: " . $user['id'] . "\n";
            echo "- Username: " . $user['username'] . "\n";
        }
    } catch (\Exception $e) {
        echo "- Error getting user: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Dashboard user test completed\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 