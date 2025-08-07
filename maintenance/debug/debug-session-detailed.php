<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Detailed Session Debug\n";
echo "========================\n\n";

// Check initial state
echo "📊 Initial State:\n";
echo "- Session Status: " . session_status() . "\n";
echo "- Session Name: " . session_name() . "\n";
echo "- Session ID: " . session_id() . "\n";
echo "- Session Save Path: " . session_save_path() . "\n";
echo "- SAPI: " . php_sapi_name() . "\n";
echo "\n";

// Check if session is already started
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "⚠️  WARNING: Session is already active!\n";
    echo "- Session Data: " . print_r($_SESSION, true) . "\n";
    echo "\n";
}

// Create container
echo "🏗️  Creating Container...\n";
$container = new \IslamWiki\Core\Container\AsasContainer();
echo "✅ Container created\n\n";

// Register session service provider
echo "📝 Registering SessionServiceProvider...\n";
$sessionProvider = new \IslamWiki\Providers\SessionServiceProvider();
$sessionProvider->register($container);
echo "✅ SessionServiceProvider registered\n\n";

// Check state after registration
echo "📊 State After Registration:\n";
echo "- Session Status: " . session_status() . "\n";
echo "- Session Name: " . session_name() . "\n";
echo "- Session ID: " . session_id() . "\n";
echo "\n";

// Boot the session service provider
echo "🚀 Booting SessionServiceProvider...\n";
$sessionProvider->boot($container);
echo "✅ SessionServiceProvider booted\n\n";

// Check state after boot
echo "📊 State After Boot:\n";
echo "- Session Status: " . session_status() . "\n";
echo "- Session Name: " . session_name() . "\n";
echo "- Session ID: " . session_id() . "\n";
echo "- Session Save Path: " . session_save_path() . "\n";
echo "\n";

// Get session manager
echo "👤 Getting Session Manager...\n";
$session = $container->get('session');
echo "✅ Session Manager: " . get_class($session) . "\n\n";

// Check session manager state
echo "📊 Session Manager State:\n";
echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";
echo "\n";

// Check cookies
echo "🍪 Cookies:\n";
        $temp_a2fee4fa = (isset($_COOKIE['islamwiki_session']) ? $_COOKIE['islamwiki_session'] : 'Not set') . "\n";
        echo "- Session Cookie: " . $temp_a2fee4fa;
echo "- All Cookies: " . print_r($_COOKIE, true) . "\n";
echo "\n";

// Test session functionality
echo "🧪 Testing Session Functionality:\n";

// Set a test value
$session->put('test_value', 'test_data_' . time());
echo "- Set test value in session\n";

// Check if value persists
$testValue = $session->get('test_value');
echo "- Retrieved test value: " . ($testValue ?? 'null') . "\n";

// Test login
echo "\n🔐 Testing Login:\n";
$session->login(1, 'testuser', false);
echo "- Login completed\n";
echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";

// Check session data
echo "\n📋 Session Data:\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "- Session Data: " . print_r($_SESSION, true) . "\n";
} else {
    echo "- No active session\n";
}

echo "\n✅ Detailed session debugging completed\n";
