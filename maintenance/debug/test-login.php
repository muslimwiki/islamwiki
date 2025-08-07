<?php

/**
 * Test Login Functionality
 *
 * Tests the login process step by step to identify issues
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

echo "=== Login Test ===\n\n";

try {
    // Create application
    $app = new \IslamWiki\Core\NizamApplication(__DIR__ . '/../..');
    $container = $app->getContainer();

    echo "1. Testing database connection...\n";
    $db = $container->get('db');
    echo "   ✅ Database connected\n";

    echo "\n2. Checking for admin user...\n";
    $users = $db->select(
        'SELECT id, username, email, is_active FROM users WHERE username = ?',
        ['admin']
    );

    if (!empty($users)) {
        $user = $users[0];
        echo "   ✅ Admin user found\n";
        echo "   ID: " . $user['id'] . "\n";
        echo "   Username: " . $user['username'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ❌ Admin user not found\n";
        exit(1);
    }

    echo "\n3. Testing session service...\n";
    $session = $container->get('session');
    echo "   ✅ Session service available\n";
    echo "   Session class: " . get_class($session) . "\n";

    echo "\n4. Testing authentication service...\n";
    $auth = $container->get('auth');
    echo "   ✅ Auth service available\n";
    echo "   Auth class: " . get_class($auth) . "\n";

    echo "\n5. Testing login attempt...\n";
    $loginResult = $auth->attempt('admin', 'password');
    echo "   Login result: " . ($loginResult ? 'SUCCESS' : 'FAILED') . "\n";

    if ($loginResult) {
        echo "\n6. Testing user state after login...\n";
        $user = $auth->user();
        if ($user) {
            echo "   ✅ User logged in successfully\n";
            echo "   Username: " . $user['username'] . "\n";
            echo "   User ID: " . $user['id'] . "\n";
        } else {
            echo "   ❌ User not found after login\n";
        }

        echo "\n7. Testing session state...\n";
        if (method_exists($session, 'isLoggedIn')) {
            $isLoggedIn = $session->isLoggedIn();
            echo "   Session logged in: " . ($isLoggedIn ? 'Yes' : 'No') . "\n";
        }

        if (method_exists($session, 'getUserId')) {
            $userId = $session->getUserId();
            echo "   Session user ID: " . ($userId ?? 'null') . "\n";
        }

        echo "\n8. Testing logout...\n";
        $auth->logout();
        $userAfterLogout = $auth->user();
        $logoutStatus = $userAfterLogout ? 'Still logged in' : 'Logged out';
        echo "   User after logout: " . $logoutStatus . "\n";
    } else {
        echo "   ❌ Login failed - checking password hash...\n";

        // Check the password hash
        $userData = $db->select(
            'SELECT password FROM users WHERE username = ?',
            ['admin']
        );
        if (!empty($userData)) {
            $storedHash = $userData[0]['password'];
            echo "   Stored hash: " . $storedHash . "\n";

            $passwordValid = password_verify('password', $storedHash);
            $temp_6abf8e29 = ($passwordValid ? 'VALID' : 'INVALID') . "\n";
            echo "   Password verification: " . $temp_6abf8e29;

            if (!$passwordValid) {
                echo "   🔧 Fixing password hash...\n";
                $newHash = password_hash('password', PASSWORD_DEFAULT);
                $db->update(
                    'UPDATE users SET password = ? WHERE username = ?',
                    [$newHash, 'admin']
                );
                echo "   ✅ Password hash updated\n";

                // Test login again
                echo "\n9. Testing login with fixed password...\n";
                $loginResult2 = $auth->attempt('admin', 'password');
                $loginResult2Status = $loginResult2 ? 'SUCCESS' : 'FAILED';
                echo "   Login result: " . $loginResult2Status . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
