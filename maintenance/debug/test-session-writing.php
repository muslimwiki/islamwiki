<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Testing Session Writing\n";
echo "==========================\n\n";

try {
    // Initialize application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get session
    $session = $container->get('session');

    echo "📊 Initial Session State:\n";
    echo "- Session Status: " . session_status() . "\n";
    echo "- Session Name: " . session_name() . "\n";
    echo "- Session ID: " . session_id() . "\n";
    echo "- Session Data: " . print_r($_SESSION, true) . "\n";

    // Test session writing
    echo "\n🧪 Testing Session Writing:\n";

    // Test 1: Simple session write
    echo "- Test 1: Writing simple data...\n";
    $session->put('test_key', 'test_value_' . time());
    echo "  - Session data after write: " . print_r($_SESSION, true) . "\n";

    // Test 2: Login simulation
    echo "\n- Test 2: Simulating login...\n";
    $session->login(1, 'admin', true);
    echo "  - Session data after login: " . print_r($_SESSION, true) . "\n";
    echo "  - Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";

    // Test 3: Check session file
    $sessionId = session_id();
    $sessionFile = __DIR__ . '/../storage/sessions/sess_' . $sessionId;
    echo "\n- Test 3: Checking session file...\n";
    echo "  - Session ID: " . $sessionId . "\n";
    echo "  - Session file: " . $sessionFile . "\n";
    echo "  - File exists: " . (file_exists($sessionFile) ? 'Yes' : 'No') . "\n";
    if (file_exists($sessionFile)) {
        echo "  - File size: " . filesize($sessionFile) . " bytes\n";
        $temp_bd8379ea = (is_readable($sessionFile) ? 'Yes' : 'No') . "\n";
        echo "  - File readable: " . $temp_bd8379ea;
        $temp_e78630c6 = (is_writable($sessionFile) ? 'Yes' : 'No') . "\n";
        echo "  - File writable: " . $temp_e78630c6;
    }

    // Test 4: Force session write
    echo "\n- Test 4: Forcing session write...\n";
    session_write_close();
    echo "  - Session written and closed\n";

    // Test 5: Restart session
    echo "\n- Test 5: Restarting session...\n";
    session_start();
    echo "  - Session restarted\n";
    echo "  - Session data after restart: " . print_r($_SESSION, true) . "\n";

    // Test 6: Check session file again
    echo "\n- Test 6: Checking session file after write...\n";
    if (file_exists($sessionFile)) {
        echo "  - File size after write: " . filesize($sessionFile) . " bytes\n";
    }

    echo "\n✅ Session writing test completed\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
