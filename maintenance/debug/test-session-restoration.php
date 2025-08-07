<?php

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Testing Session Restoration\n";
echo "==============================\n\n";

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
        $temp_6336c042 = (isset($_COOKIE['islamwiki_session']) ? $_COOKIE['islamwiki_session'] : 'none') . "\n";
        echo "- Session Cookie: " . $temp_6336c042;

    // Check if session has last_regeneration
        $temp_054545d9 = ($session->has('last_regeneration') ? 'Yes' : 'No') . "\n";
        echo "- Has last_regeneration: " . $temp_054545d9;

    // Test the regeneration logic
    echo "\n🔍 Testing Regeneration Logic:\n";
    $isEmpty = empty($_SESSION);
    $hasLastRegeneration = $session->has('last_regeneration');
    $hasCookie = isset($_COOKIE['islamwiki_session']);

    echo "- Empty session: " . ($isEmpty ? 'Yes' : 'No') . "\n";
    echo "- Has last_regeneration: " . ($hasLastRegeneration ? 'Yes' : 'No') . "\n";
    echo "- Has cookie: " . ($hasCookie ? 'Yes' : 'No') . "\n";

    $shouldRegenerate = $isEmpty && !$hasLastRegeneration && !$hasCookie;
    echo "- Should regenerate: " . ($shouldRegenerate ? 'Yes' : 'No') . "\n";

    if ($shouldRegenerate) {
        echo "⚠️  WARNING: Session will be regenerated, which will break session restoration!\n";
    }

    // Test session restoration
    echo "\n🧪 Testing Session Restoration:\n";

    // Simulate a login
    echo "- Simulating login...\n";
    $session->login(1, 'admin', true);
    $session->put('test_data', 'test_value_' . time());

    echo "- After login:\n";
    echo "  - Session ID: " . session_id() . "\n";
    echo "  - Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
        $temp_054545d9 = ($session->has('last_regeneration') ? 'Yes' : 'No') . "\n";
        echo "  - Has last_regeneration: " . $temp_054545d9;
    echo "  - Session Data: " . print_r($_SESSION, true) . "\n";

    // Simulate session write and close
    echo "\n- Writing session and closing...\n";
    session_write_close();

    // Simulate a new request (restore session)
    echo "\n- Simulating new request (session restoration)...\n";

    // Clear session data to simulate fresh request
    $_SESSION = [];

    // Start session again
    $session->start();

    echo "- After restoration:\n";
    echo "  - Session ID: " . session_id() . "\n";
    echo "  - Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "  - Session Data: " . print_r($_SESSION, true) . "\n";

    echo "\n✅ Session restoration test completed\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
