<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Deep Session Investigation\n";
echo "============================\n\n";

try {
    // Test 1: Check if session is already started
    echo "📊 Test 1: Initial Session State\n";
    echo "- Session Status: " . session_status() . "\n";
    echo "- Session Name: " . session_name() . "\n";
    echo "- Session ID: " . session_id() . "\n";
    echo "- Session Data: " . print_r($_SESSION, true) . "\n";
    
    // Test 2: Check session configuration
    echo "\n📊 Test 2: Session Configuration\n";
    echo "- session.use_strict_mode: " . ini_get('session.use_strict_mode') . "\n";
    echo "- session.use_cookies: " . ini_get('session.use_cookies') . "\n";
    echo "- session.use_only_cookies: " . ini_get('session.use_only_cookies') . "\n";
    echo "- session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
    echo "- session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
    echo "- session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
    echo "- session.cookie_path: " . ini_get('session.cookie_path') . "\n";
    echo "- session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "\n";
    echo "- session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "\n";
    echo "- session.save_path: " . ini_get('session.save_path') . "\n";
    
    // Test 3: Check if session is being started multiple times
    echo "\n📊 Test 3: Multiple Session Starts\n";
    
    // Initialize application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "- Before getting session from container:\n";
    echo "  - Session Status: " . session_status() . "\n";
    echo "  - Session Name: " . session_name() . "\n";
    echo "  - Session ID: " . session_id() . "\n";
    
    // Get session from container
    $session = $container->get('session');
    
    echo "- After getting session from container:\n";
    echo "  - Session Status: " . session_status() . "\n";
    echo "  - Session Name: " . session_name() . "\n";
    echo "  - Session ID: " . session_id() . "\n";
    
    // Test 4: Check session name consistency
    echo "\n📊 Test 4: Session Name Consistency\n";
    echo "- Expected session name: islamwiki_session\n";
    echo "- Actual session name: " . session_name() . "\n";
    echo "- Session name matches: " . (session_name() === 'islamwiki_session' ? 'Yes' : 'No') . "\n";
    
    // Test 5: Check if session is properly configured
    echo "\n📊 Test 5: Session Configuration Check\n";
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "- Session is active\n";
        echo "- Session name: " . session_name() . "\n";
        echo "- Session ID: " . session_id() . "\n";
        echo "- Session data count: " . count($_SESSION) . "\n";
    } else {
        echo "- Session is not active\n";
        echo "- Session status: " . session_status() . "\n";
    }
    
    // Test 6: Check if session save path is correct
    echo "\n📊 Test 6: Session Save Path\n";
    $expectedPath = __DIR__ . '/../storage/sessions';
    $actualPath = ini_get('session.save_path');
    echo "- Expected save path: " . $expectedPath . "\n";
    echo "- Actual save path: " . $actualPath . "\n";
    echo "- Path matches: " . ($actualPath === $expectedPath ? 'Yes' : 'No') . "\n";
    echo "- Expected path exists: " . (is_dir($expectedPath) ? 'Yes' : 'No') . "\n";
    echo "- Expected path writable: " . (is_writable($expectedPath) ? 'Yes' : 'No') . "\n";
    
    echo "\n✅ Deep session investigation completed\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 