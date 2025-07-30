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

echo "🌐 Testing Web Session Flow\n";
echo "===========================\n\n";

// Simulate the web application setup
try {
    // 1. Setup database connection
    $db = new \IslamWiki\Core\Database\Connection([
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_NAME'] ?? 'islamwiki',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
    ]);
    echo "✅ Database connection established\n";
    
    // 2. Setup container and session
    $container = new \IslamWiki\Core\Container();
    $container->singleton('session', function() {
        return new \IslamWiki\Core\Session\SessionManager();
    });
    
    $session = $container->get('session');
    $session->start();
    echo "✅ Session started\n";
    
    // 3. Find admin user
    $user = \IslamWiki\Models\User::findByUsername('admin', $db);
    if (!$user) {
        echo "❌ Admin user not found\n";
        exit(1);
    }
    echo "✅ Admin user found: " . $user->getAttribute('username') . "\n";
    
    // 4. Simulate login (like AuthController does)
    echo "\n🔐 Simulating Login...\n";
    $session->login(
        $user->getAttribute('id'),
        $user->getAttribute('username'),
        $user->isAdmin()
    );
    
    echo "   📊 Session isLoggedIn: " . ($session->isLoggedIn() ? 'true' : 'false') . "\n";
    echo "   📊 User ID in session: " . $session->getUserId() . "\n";
    echo "   👤 Username in session: " . $session->getUsername() . "\n";
    
    // 5. Simulate dashboard access (like DashboardController does)
    echo "\n📊 Simulating Dashboard Access...\n";
    $dashboardUser = null;
    
    if ($session->isLoggedIn()) {
        $userId = $session->getUserId();
        echo "   📊 User ID from session: $userId\n";
        
        $dashboardUser = \IslamWiki\Models\User::find($userId, $db);
        if ($dashboardUser) {
            echo "   ✅ User found from session ID\n";
            echo "   👤 Username: " . $dashboardUser->getAttribute('username') . "\n";
            echo "   📧 Email: " . $dashboardUser->getAttribute('email') . "\n";
            echo "   👑 Is Admin: " . ($dashboardUser->isAdmin() ? 'Yes' : 'No') . "\n";
        } else {
            echo "   ❌ User not found from session ID\n";
        }
    } else {
        echo "   ❌ User not logged in\n";
    }
    
    // 6. Test template data
    echo "\n📝 Testing Template Data...\n";
    $templateData = [
        'title' => 'Dashboard - IslamWiki',
        'message' => 'Welcome to your IslamWiki dashboard',
        'user' => $dashboardUser,
    ];
    
    echo "   📊 Template data prepared\n";
    echo "   👤 User object: " . ($templateData['user'] ? 'present' : 'null') . "\n";
    if ($templateData['user']) {
        echo "   👤 Username for template: " . $templateData['user']->getAttribute('username') . "\n";
    }
    
    echo "\n🎉 Web session flow test complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📊 Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
} 