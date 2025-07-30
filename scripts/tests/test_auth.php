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

/**
 * Test Authentication System
 * 
 * This script tests the authentication system functionality.
 * Usage: php scripts/test_auth.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\User;

echo "IslamWiki Authentication Test\n";
echo "============================\n\n";

try {
    // Create database connection directly
    $dbConfig = [
        'driver' => getenv('DB_CONNECTION') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];

    $connection = new Connection($dbConfig);
    echo "✅ Database connection created\n";

    // Test user lookup
    echo "\n🔍 Testing user lookup...\n";
    
    $user = User::findByUsername('admin', $connection);
    if ($user) {
        echo "  ✅ Found user: {$user->getAttribute('username')} ({$user->getAttribute('email')})\n";
        echo "  📊 User details:\n";
        echo "    - Display Name: {$user->getDisplayName()}\n";
        echo "    - Is Admin: " . ($user->isAdmin() ? 'Yes' : 'No') . "\n";
        echo "    - Is Active: " . ($user->isActive() ? 'Yes' : 'No') . "\n";
        echo "    - Created: {$user->getAttribute('created_at')}\n";
    } else {
        echo "  ❌ User 'admin' not found\n";
    }

    // Test password verification
    echo "\n🔐 Testing password verification...\n";
    if ($user) {
        $testPassword = 'admin123';
        $isValid = $user->verifyPassword($testPassword);
        echo "  " . ($isValid ? "✅" : "❌") . " Password '{$testPassword}' is " . ($isValid ? "valid" : "invalid") . "\n";
        
        $testPassword2 = 'wrongpassword';
        $isValid2 = $user->verifyPassword($testPassword2);
        echo "  " . ($isValid2 ? "❌" : "✅") . " Password '{$testPassword2}' is " . ($isValid2 ? "valid" : "invalid") . " (expected invalid)\n";
    }

    // Test user creation
    echo "\n👤 Testing user creation...\n";
    $testUser = new User($connection, [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'testpass123',
        'display_name' => 'Test User',
        'is_admin' => false,
        'is_active' => true
    ]);
    
    $saved = $testUser->save();
    echo "  " . ($saved ? "✅" : "❌") . " Test user created successfully\n";
    
    if ($saved) {
        echo "  📊 Test user details:\n";
        echo "    - Username: {$testUser->getAttribute('username')}\n";
        echo "    - Email: {$testUser->getAttribute('email')}\n";
        echo "    - Display Name: {$testUser->getDisplayName()}\n";
        echo "    - Is Admin: " . ($testUser->isAdmin() ? 'Yes' : 'No') . "\n";
        
        // Test password verification for new user
        $isValid = $testUser->verifyPassword('testpass123');
        echo "    - Password valid: " . ($isValid ? 'Yes' : 'No') . "\n";
    }

    // Test user lookup by email
    echo "\n📧 Testing user lookup by email...\n";
    $userByEmail = User::findByEmail('admin@islamwiki.local', $connection);
    if ($userByEmail) {
        echo "  ✅ Found user by email: {$userByEmail->getAttribute('username')}\n";
    } else {
        echo "  ❌ User with email 'admin@islamwiki.local' not found\n";
    }

    echo "\n✅ Authentication system test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n"; 