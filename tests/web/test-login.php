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

// Simple test page for login functionality
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test - IslamWiki</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test-section { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>🔐 IslamWiki Login Test</h1>
    
    <div class="test-section">
        <h2>Database Connection Test</h2>
        <?php
        try {
            $connection = new Connection([
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
            ]);
            
            $pdo = $connection->getPdo();
            echo "<p class='success'>✅ Database connection successful</p>";
            
            // Test user lookup
            $user = User::findByUsername('admin', $connection);
            if ($user) {
                echo "<p class='success'>✅ Admin user found: " . htmlspecialchars($user->getAttribute('username')) . "</p>";
                echo "<p class='info'>User ID: " . $user->getAttribute('id') . "</p>";
                echo "<p class='info'>Email: " . htmlspecialchars($user->getAttribute('email')) . "</p>";
                echo "<p class='info'>Is Admin: " . ($user->isAdmin() ? 'Yes' : 'No') . "</p>";
                echo "<p class='info'>Is Active: " . ($user->isActive() ? 'Yes' : 'No') . "</p>";
            } else {
                echo "<p class='error'>❌ Admin user not found</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>Password Verification Test</h2>
        <?php
        if (isset($user)) {
            $testPassword = 'password';
            $isValid = $user->verifyPassword($testPassword);
            
            if ($isValid) {
                echo "<p class='success'>✅ Password 'password' verification successful</p>";
            } else {
                echo "<p class='error'>❌ Password 'password' verification failed</p>";
            }
            
            // Test wrong password
            $wrongPassword = 'wrongpassword';
            $wrongResult = $user->verifyPassword($wrongPassword);
            
            if (!$wrongResult) {
                echo "<p class='success'>✅ Wrong password correctly rejected</p>";
            } else {
                echo "<p class='error'>❌ Wrong password incorrectly accepted</p>";
            }
        } else {
            echo "<p class='error'>❌ Cannot test password verification - user not found</p>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>Manual Login Test</h2>
        <p>Use these credentials to test the login:</p>
        <ul>
            <li><strong>Username:</strong> admin</li>
            <li><strong>Password:</strong> password</li>
        </ul>
        
        <form action="/login" method="POST" style="margin-top: 20px;">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="admin" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="password" required>
            </div>
            
            <button type="submit">Test Login</button>
        </form>
        
        <p style="margin-top: 20px;">
            <a href="/login" target="_blank">Open Login Page in New Tab</a> |
            <a href="/register" target="_blank">Open Register Page in New Tab</a>
        </p>
    </div>

    <div class="test-section">
        <h2>Sample Pages Test</h2>
        <p>Test these sample pages:</p>
        <ul>
            <li><a href="/welcome" target="_blank">Welcome Page</a></li>
            <li><a href="/about-islam" target="_blank">About Islam</a></li>
            <li><a href="/islamic-history" target="_blank">Islamic History</a></li>
            <li><a href="/islamic-sciences" target="_blank">Islamic Sciences</a></li>
            <li><a href="/contributing" target="_blank">Contributing Guidelines</a></li>
        </ul>
    </div>

    <div class="test-section">
        <h2>Quick Links</h2>
        <p>
            <a href="/" target="_blank">Homepage</a> |
            <a href="/dashboard" target="_blank">Dashboard</a> |
            <a href="/pages" target="_blank">All Pages</a>
        </p>
    </div>
</body>
</html> 