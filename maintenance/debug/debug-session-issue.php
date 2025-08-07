<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Session\Wisal;
use IslamWiki\Providers\SessionServiceProvider;

try {
    // Create container
    $container = new AsasContainer();

    // Register session service provider
    $sessionProvider = new SessionServiceProvider();
    $sessionProvider->register($container);

    // Start session before any output
    $sessionProvider->boot($container);

    echo "🔍 Debug Session Issue\n";
    echo "=====================\n\n";

    // For CLI, we need to modify session configuration
    if (php_sapi_name() === 'cli') {
        echo "🖥️  CLI Environment Detected\n";
        echo "- Session configuration will be handled by Wisal class\n";
    }

    // Get session manager
    $session = $container->get('session');

    echo "✅ Session Service Provider registered and booted\n";
    echo "✅ Session Manager retrieved: " . get_class($session) . "\n";

    // Check session status
    echo "\n📊 Session Status:\n";
        $temp_f9d5ecab = session_status() . " (" . getSessionStatusText(session_status()) . ")\n";
        echo "- Session Status: " . $temp_f9d5ecab;
    echo "- Session Name: " . session_name() . "\n";
    echo "- Session ID: " . (session_id() ?: 'Not set') . "\n";
    echo "- Session Save Path: " . session_save_path() . "\n";
    echo "- SAPI: " . php_sapi_name() . "\n";

    // Check if user is logged in
    echo "\n👤 Authentication Status:\n";
    echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
    echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";
    echo "- Is Admin: " . ($session->isAdmin() ? 'Yes' : 'No') . "\n";

    // Check session data
    echo "\n📋 Session Data:\n";
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "- Session Data: " . print_r($_SESSION, true) . "\n";
    } else {
        echo "- No active session\n";
    }

    // Check cookies
    echo "\n🍪 Cookies:\n";
        $temp_a2fee4fa = (isset($_COOKIE['islamwiki_session']) ? $_COOKIE['islamwiki_session'] : 'Not set') . "\n";
        echo "- Session Cookie: " . $temp_a2fee4fa;
    echo "- All Cookies: " . print_r($_COOKIE, true) . "\n";

    // Test session persistence
    echo "\n🧪 Testing Session Persistence:\n";

    // Set a test value
    $session->put('test_value', 'test_data_' . time());
    echo "- Set test value in session\n";

    // Check if value persists
    $testValue = $session->get('test_value');
    echo "- Retrieved test value: " . ($testValue ?? 'null') . "\n";

    // Test login simulation
    echo "\n🔐 Testing Login Simulation:\n";
    $session->login(1, 'testuser', false);
    echo "- Simulated login for user ID 1, username 'testuser'\n";

    // Check login status
    echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
    echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";

    // Check session data after login
    echo "\n📋 Session Data After Login:\n";
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "- Session Data: " . print_r($_SESSION, true) . "\n";
    }

    // Test session write
    echo "\n💾 Testing Session Write:\n";
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
        echo "- Session written and closed\n";

        // Reopen session using Wisal
        $session->start();
        echo "- Session reopened using Wisal\n";

        // Check if data persists
        $session = $container->get('session');
        echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
        echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
        echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";
    }

    // Test session file creation
    echo "\n📁 Testing Session File Creation:\n";
    $sessionPath = __DIR__ . '/../storage/sessions';
    $sessionFiles = glob($sessionPath . '/sess_*');
    echo "- Session directory: " . $sessionPath . "\n";
    echo "- Session files count: " . count($sessionFiles) . "\n";

    if (session_id()) {
        $sessionFile = $sessionPath . '/sess_' . session_id();
        echo "- Current session file: " . $sessionFile . "\n";
        $temp_59388fe8 = (file_exists($sessionFile) ? 'Yes' : 'No') . "\n";
        echo "- Session file exists: " . $temp_59388fe8;
        if (file_exists($sessionFile)) {
            echo "- Session file size: " . filesize($sessionFile) . " bytes\n";
            echo "- Session file content: " . file_get_contents($sessionFile) . "\n";
        }
    }

    echo "\n✅ Session debugging completed successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

/**
 * Get human-readable session status text
 */
function getSessionStatusText($status)
{
    switch ($status) {
        case PHP_SESSION_DISABLED:
            return 'PHP_SESSION_DISABLED';
        case PHP_SESSION_NONE:
            return 'PHP_SESSION_NONE';
        case PHP_SESSION_ACTIVE:
            return 'PHP_SESSION_ACTIVE';
        default:
            return 'UNKNOWN';
    }
}
