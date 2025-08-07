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
use IslamWiki\Models\User;

/**
 * Test Password Verification
 */

echo "🔐 Testing Password Verification\n";
echo "===============================\n\n";

try {
    $connection = new Connection([
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ]);

    // Find admin user
    $user = User::findByUsername('admin', $connection);

    if (!$user) {
        echo "❌ Admin user not found\n";
        exit(1);
    }

    echo "✅ Admin user found\n";
    echo "ID: " . $user->getAttribute('id') . "\n";
    echo "Username: " . $user->getAttribute('username') . "\n";
    echo "Email: " . $user->getAttribute('email') . "\n";
    echo "Is Admin: " . ($user->isAdmin() ? 'Yes' : 'No') . "\n";
    echo "Is Active: " . ($user->isActive() ? 'Yes' : 'No') . "\n";

    // Test password verification
    echo "\n🔑 Testing password verification...\n";

    $testPassword = 'password';
    $result = $user->verifyPassword($testPassword);

    echo "Password 'password' verification result: " . ($result ? '✅ SUCCESS' : '❌ FAILED') . "\n";

    // Test wrong password
    $wrongPassword = 'wrongpassword';
    $wrongResult = $user->verifyPassword($wrongPassword);

    echo "Password 'wrongpassword' verification result: " . ($wrongResult ? '❌ SHOULD FAIL' : '✅ CORRECTLY FAILED') . "\n";

    echo "\n✅ Password verification test completed\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
