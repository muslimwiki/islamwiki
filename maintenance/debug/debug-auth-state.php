<?php

/**
 * Debug Authentication State
 *
 * Tests if the authentication system is working properly and if user data is available
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

// Create application
$app = new \IslamWiki\Core\NizamApplication(__DIR__ . '/../..');

echo "=== Authentication State Debug ===\n\n";

try {
    $container = $app->getContainer();

    echo "1. Checking if auth service is registered...\n";
    if ($container->has('auth')) {
        echo "   ✅ Auth service is registered\n";

        $auth = $container->get('auth');
        echo "   Auth class: " . get_class($auth) . "\n";

        echo "\n2. Checking current user...\n";
        $user = $auth->user();

        if ($user) {
            echo "   ✅ User is logged in\n";
            echo "   Username: " . ($user['username'] ?? 'N/A') . "\n";
            echo "   User ID: " . ($user['id'] ?? 'N/A') . "\n";
            echo "   Email: " . ($user['email'] ?? 'N/A') . "\n";
        } else {
            echo "   ❌ No user logged in\n";
        }
    } else {
        echo "   ❌ Auth service is NOT registered\n";
    }

    echo "\n3. Checking session state...\n";
    if ($container->has('session')) {
        $session = $container->get('session');
        echo "   ✅ Session service is registered\n";
        echo "   Session class: " . get_class($session) . "\n";

        if (method_exists($session, 'isLoggedIn')) {
            $isLoggedIn = $session->isLoggedIn();
            echo "   Session logged in: " . ($isLoggedIn ? 'Yes' : 'No') . "\n";
        }

        if (method_exists($session, 'getUserId')) {
            $userId = $session->getUserId();
            echo "   Session user ID: " . ($userId ?? 'null') . "\n";
        }
    } else {
        echo "   ❌ Session service is NOT registered\n";
    }

    echo "\n4. Testing view renderer globals...\n";
    if ($container->has('view')) {
        $viewRenderer = $container->get('view');
        echo "   ✅ View renderer is registered\n";

        // Test adding user to globals
        $viewRenderer->addGlobals(
            [
                'user' => $user,
                'is_logged_in' => $user !== null,
            ]
        );

        echo "   Added user to template globals\n";
        echo "   User data: " . json_encode($user) . "\n";
    } else {
        echo "   ❌ View renderer is NOT registered\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
