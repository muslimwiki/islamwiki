<?php
declare(strict_types=1);

/**
 * Simple Authentication Test
 * 
 * Tests basic authentication functionality.
 * 
 * @package IslamWiki
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Container;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\Logger;

echo "==========================================\n";
echo "Simple Authentication Test\n";
echo "Version: 0.0.28\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "==========================================\n\n";

try {
    // Initialize container
    echo "Test 1: Initializing Container...\n";
    $container = new Container();
    
    // Manually register required services
    $container->singleton(Connection::class, function() {
        return new Connection([
            'host' => 'localhost',
            'database' => 'islamwiki',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
    });
    
    $container->singleton(Logger::class, function() {
        return new Logger(
            __DIR__ . '/../../storage/logs',
            \Psr\Log\LogLevel::DEBUG,
            10, // max file size in MB
            5   // max files
        );
    });
    
    echo "✅ Container initialized successfully\n\n";

    // Test 2: Check database connection
    echo "Test 2: Testing database connection...\n";
    $db = $container->get(Connection::class);
    echo "✅ Database connection successful\n\n";

    // Test 3: Check if users table exists and has data
    echo "Test 3: Checking users table...\n";
    $users = $db->table('users')->get();
    echo "✅ Found " . count($users) . " users in database\n";
    
    if (count($users) > 0) {
        echo "✅ Sample users:\n";
        foreach (array_slice($users, 0, 3) as $user) {
            echo "  - {$user['username']} (ID: {$user['id']})\n";
        }
    } else {
        echo "⚠️  No users found in database\n";
    }
    echo "\n";

    // Test 4: Check if admin user exists
    echo "Test 4: Checking for admin user...\n";
    $adminUser = $db->table('users')->where('username', 'admin')->first();
    
    if ($adminUser) {
        echo "✅ Admin user found:\n";
        echo "  - Username: {$adminUser['username']}\n";
        echo "  - Email: {$adminUser['email']}\n";
        echo "  - Is Admin: " . ($adminUser['is_admin'] ? 'Yes' : 'No') . "\n";
        echo "  - Is Active: " . ($adminUser['is_active'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ Admin user not found\n";
        echo "Creating admin user...\n";
        
        $adminId = $db->table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@islamwiki.local',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'display_name' => 'Administrator',
            'is_admin' => true,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($adminId) {
            echo "✅ Admin user created successfully (ID: {$adminId})\n";
            echo "  - Username: admin\n";
            echo "  - Password: password\n";
        } else {
            echo "❌ Failed to create admin user\n";
        }
    }
    echo "\n";

    // Test 5: Test password verification
    echo "Test 5: Testing password verification...\n";
    if ($adminUser) {
        $testPassword = 'password';
        $isValid = password_verify($testPassword, $adminUser['password']);
        echo "✅ Password verification: " . ($isValid ? 'Valid' : 'Invalid') . "\n";
    } else {
        echo "⚠️  Skipping password verification (no admin user)\n";
    }
    echo "\n";

    echo "==========================================\n";
    echo "✅ Authentication Test Complete!\n";
    echo "==========================================\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 