<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Debugging User Session\n";
echo "=========================\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    $db = new \IslamWiki\Core\Database\Connection([
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_NAME'] ?? 'islamwiki',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
    ]);
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Find Admin User
echo "\n2. Testing User Lookup...\n";
try {
    $user = \IslamWiki\Models\User::findByUsername('admin', $db);
    if ($user) {
        echo "   ✅ Admin user found: " . $user->getAttribute('username') . "\n";
        echo "   📊 User ID: " . $user->getAttribute('id') . "\n";
        echo "   📧 Email: " . $user->getAttribute('email') . "\n";
        echo "   👑 Is Admin: " . ($user->isAdmin() ? 'Yes' : 'No') . "\n";
        echo "   ✅ Is Active: " . ($user->isActive() ? 'Yes' : 'No') . "\n";

        // Test all attributes
        echo "\n   🔍 All User Attributes:\n";
        $attributes = [
            'id', 'username', 'email', 'display_name', 'is_admin', 'is_active',
            'email_verified_at', 'last_login_at', 'last_login_ip', 'remember_token'
        ];
        foreach ($attributes as $attr) {
            $value = $user->getAttribute($attr);
            if (is_null($value)) {
                echo "      $attr: null\n";
            } elseif ($value instanceof DateTime) {
                echo "      $attr: " . $value->format('Y-m-d H:i:s') . "\n";
            } else {
                echo "      $attr: $value\n";
            }
        }
    } else {
        echo "   ❌ Admin user not found\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ User lookup failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Session Management
echo "\n3. Testing Session Management...\n";
try {
    $container = new \IslamWiki\Core\Container();
    $container->singleton('session', function () {
        return new \IslamWiki\Core\Session\SessionManager();
    });

    $session = $container->get('session');
    $session->start();

    echo "   📊 Session started\n";
    echo "   📊 Session isLoggedIn before login: " . ($session->isLoggedIn() ? 'true' : 'false') . "\n";

    // Test login
    $session->login(
        $user->getAttribute('id'),
        $user->getAttribute('username'),
        $user->isAdmin()
    );

    echo "   📊 Session isLoggedIn after login: " . ($session->isLoggedIn() ? 'true' : 'false') . "\n";
    echo "   📊 User ID in session: " . $session->getUserId() . "\n";
    echo "   👤 Username in session: " . $session->getUsername() . "\n";
    echo "   👑 Is Admin in session: " . ($session->isAdmin() ? 'Yes' : 'No') . "\n";

    // Test user lookup from session
    echo "\n   🔍 Testing User Lookup from Session...\n";
    $userId = $session->getUserId();
    echo "   📊 User ID from session: $userId\n";

    $sessionUser = \IslamWiki\Models\User::find($userId, $db);
    if ($sessionUser) {
        echo "   ✅ User found from session ID\n";
        echo "   👤 Username: " . $sessionUser->getAttribute('username') . "\n";
        echo "   📧 Email: " . $sessionUser->getAttribute('email') . "\n";
    } else {
        echo "   ❌ User not found from session ID\n";
    }
} catch (Exception $e) {
    echo "   ❌ Session management failed: " . $e->getMessage() . "\n";
    echo "   📊 Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🎉 Debug complete!\n";
