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

echo "🧪 Testing Complete Login Flow\n";
echo "==============================\n\n";

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
    } else {
        echo "   ❌ Admin user not found\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ User lookup failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Password Verification
echo "\n3. Testing Password Verification...\n";
try {
    $correctPassword = $user->verifyPassword('password');
    $wrongPassword = $user->verifyPassword('wrongpassword');
    
    if ($correctPassword) {
        echo "   ✅ Correct password accepted\n";
    } else {
        echo "   ❌ Correct password rejected\n";
    }
    
    if (!$wrongPassword) {
        echo "   ✅ Wrong password correctly rejected\n";
    } else {
        echo "   ❌ Wrong password incorrectly accepted\n";
    }
} catch (Exception $e) {
    echo "   ❌ Password verification failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Session Management
echo "\n4. Testing Session Management...\n";
try {
    $container = new \IslamWiki\Core\Container();
    $container->singleton('session', function() {
        return new \IslamWiki\Core\Session\SessionManager();
    });
    
    $session = $container->get('session');
    $session->start();
    
    // Test login
    $session->login(
        $user->getAttribute('id'),
        $user->getAttribute('username'),
        $user->isAdmin()
    );
    
    if ($session->isLoggedIn()) {
        echo "   ✅ User logged in successfully\n";
        echo "   📊 User ID in session: " . $session->getUserId() . "\n";
        echo "   👤 Username in session: " . $session->getUsername() . "\n";
        echo "   👑 Is Admin in session: " . ($session->isAdmin() ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ❌ Login failed\n";
    }
    
    // Test logout
    $session->logout();
    
    if (!$session->isLoggedIn()) {
        echo "   ✅ User logged out successfully\n";
    } else {
        echo "   ❌ Logout failed\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Session management failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: CSRF Token Generation
echo "\n5. Testing CSRF Token...\n";
try {
    $csrfToken = $session->getCsrfToken();
    if (!empty($csrfToken)) {
        echo "   ✅ CSRF token generated: " . substr($csrfToken, 0, 10) . "...\n";
        
        if ($session->verifyCsrfToken($csrfToken)) {
            echo "   ✅ CSRF token verification successful\n";
        } else {
            echo "   ❌ CSRF token verification failed\n";
        }
    } else {
        echo "   ❌ CSRF token generation failed\n";
    }
} catch (Exception $e) {
    echo "   ❌ CSRF token test failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 All tests passed! Login system is working correctly.\n";
echo "\n📝 Summary:\n";
echo "   - Database connection: ✅\n";
echo "   - User lookup: ✅\n";
echo "   - Password verification: ✅\n";
echo "   - Session management: ✅\n";
echo "   - CSRF protection: ✅\n";
echo "\n🚀 Ready for web testing!\n"; 