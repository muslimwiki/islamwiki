<?php

/**
 * Test Session State
 *
 * Tests if the session is working properly and maintaining user state
 *
 * @category  Debug
 * @package   IslamWiki
 * @author    IslamWiki Development Team
 * @license   MIT
 * @link      https://islam.wiki
 * @since     0.0.1
 */

// Load the autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load LocalSettings
require_once __DIR__ . '/../../LocalSettings.php';

// Load helpers
require_once __DIR__ . '/../../src/helpers.php';

echo "=== Session Test ===\n\n";

try {
    // Create application
    $app = new \IslamWiki\Core\NizamApplication(__DIR__ . '/../..');
    $container = $app->getContainer();

    echo "1. Testing session service...\n";
    $session = $container->get('session');
    echo "   ✅ Session service available\n";
    echo "   Session class: " . get_class($session) . "\n";

    echo "\n2. Testing authentication service...\n";
    $auth = $container->get('auth');
    echo "   ✅ Auth service available\n";
    echo "   Auth class: " . get_class($auth) . "\n";

    echo "\n3. Testing initial user state...\n";
    $user = $auth->user();
    echo "   User: " . ($user ? $user['username'] : 'null') . "\n";

    echo "\n4. Testing login...\n";
    $loginResult = $auth->attempt('admin', 'password');
    echo "   Login result: " . ($loginResult ? 'SUCCESS' : 'FAILED') . "\n";

    if ($loginResult) {
        echo "\n5. Testing user state after login...\n";
        $user = $auth->user();
        echo "   User: " . ($user ? $user['username'] : 'null') . "\n";

        echo "\n6. Testing session state...\n";
        if (method_exists($session, 'isLoggedIn')) {
            $isLoggedIn = $session->isLoggedIn();
            echo "   Session logged in: " . ($isLoggedIn ? 'Yes' : 'No') . "\n";
        }

        if (method_exists($session, 'getUserId')) {
            $userId = $session->getUserId();
            echo "   Session user ID: " . ($userId ?? 'null') . "\n";
        }

        echo "\n7. Testing session data...\n";
        if (method_exists($session, 'get')) {
            $sessionData = $session->get('user_id');
            echo "   Session user_id: " . ($sessionData ?? 'null') . "\n";

            $sessionData = $session->get('username');
            echo "   Session username: " . ($sessionData ?? 'null') . "\n";
        }

        echo "\n8. Testing view renderer with user state...\n";
        if ($container->has('view')) {
            $viewRenderer = $container->get('view');
            echo "   ✅ View renderer available\n";

            // Add user to globals
            $viewRenderer->addGlobals(
                [
                    'user' => $user,
                    'is_logged_in' => $user !== null,
                ]
            );

            echo "   Added user to template globals\n";
            echo "   User data: " . json_encode($user) . "\n";
        }

        echo "\n9. Testing logout...\n";
        $auth->logout();
        $userAfterLogout = $auth->user();
        $logoutStatus = $userAfterLogout ? 'Still logged in' : 'Logged out';
        echo "   User after logout: " . $logoutStatus . "\n";
    } else {
        echo "   ❌ Login failed\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
