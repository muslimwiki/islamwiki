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

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Http\Controllers\Auth\AuthController;
use IslamWiki\Models\User;

/**
 * Simple Login Test (Bypassing CSRF)
 */

echo "🔐 Testing Login Functionality (Simple)\n";
echo "=======================================\n\n";

try {
    // Create database connection
    $connection = new Connection([
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ]);

    // Create container
    $container = new Container();
    $container->singleton('db', $connection);

    // Create session manager
    $sessionManager = new \IslamWiki\Core\Session\SessionManager();
    $sessionManager->start();
    $container->singleton('session', $sessionManager);

    // Create AuthController
    $authController = new AuthController($connection, $container);

    // Test user lookup
    echo "👤 Testing user lookup...\n";
    $user = User::findByUsername('admin', $connection);

    if (!$user) {
        echo "❌ Admin user not found\n";
        exit(1);
    }

    echo "✅ Admin user found: " . $user->getAttribute('username') . "\n";

    // Test password verification
    echo "\n🔑 Testing password verification...\n";
    $password = 'password';
    $isValid = $user->verifyPassword($password);

    echo "Password verification result: " . ($isValid ? '✅ SUCCESS' : '❌ FAILED') . "\n";

    if (!$isValid) {
        echo "❌ Password verification failed\n";
        exit(1);
    }

    // Test session login
    echo "\n🔐 Testing session login...\n";
    $sessionManager->login(
        $user->getAttribute('id'),
        $user->getAttribute('username'),
        $user->isAdmin()
    );

    echo "✅ Session login successful\n";
    echo "User ID: " . $sessionManager->getUserId() . "\n";
    echo "Username: " . $sessionManager->getUsername() . "\n";
    echo "Is Admin: " . ($sessionManager->isAdmin() ? 'Yes' : 'No') . "\n";
    echo "Is Logged In: " . ($sessionManager->isLoggedIn() ? 'Yes' : 'No') . "\n";

    // Test logout
    echo "\n🚪 Testing logout...\n";
    $sessionManager->logout();

    echo "✅ Logout successful\n";
    echo "Is Logged In: " . ($sessionManager->isLoggedIn() ? 'Yes' : 'No') . "\n";

    echo "\n✅ All login tests passed!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
