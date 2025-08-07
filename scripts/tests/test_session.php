<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Session\SessionManager;
use IslamWiki\Core\Container;

echo "Testing Session Management\n";
echo "=========================\n\n";

try {
    // Create container
    $container = new Container();

    // Create session manager
    $session = new SessionManager([
        'name' => 'test_session',
        'lifetime' => 3600,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'Lax'
    ]);

    echo "✅ Session manager created\n";

    // Start session
    $session->start();
    echo "✅ Session started\n";

    // Test session operations
    $session->put('test_key', 'test_value');
    echo "✅ Session data set\n";

    $value = $session->get('test_key');
    echo "✅ Session data retrieved: {$value}\n";

    // Test CSRF token
    $token = $session->getCsrfToken();
    echo "✅ CSRF token generated: " . substr($token, 0, 20) . "...\n";

    $isValid = $session->verifyCsrfToken($token);
    echo "✅ CSRF token verification: " . ($isValid ? 'VALID' : 'INVALID') . "\n";

    // Test login/logout
    $session->login(1, 'testuser', false);
    echo "✅ User logged in\n";

    echo "  - Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "  - User ID: " . $session->getUserId() . "\n";
    echo "  - Username: " . $session->getUsername() . "\n";
    echo "  - Is admin: " . ($session->isAdmin() ? 'Yes' : 'No') . "\n";

    $session->logout();
    echo "✅ User logged out\n";

    echo "  - Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";

    // Test remember token
    $session->setRememberToken('test_remember_token');
    $rememberToken = $session->getRememberToken();
    echo "✅ Remember token set: " . substr($rememberToken, 0, 20) . "...\n";

    echo "\n✅ Session management test completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";
