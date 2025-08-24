<?php
/**
 * Debug Wiki Authentication Issue
 * This script tests why non-admin users can't access /wiki properly
 */

require_once __DIR__ . '/../src/helpers.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Auth\AmanSecurity;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\ShahidLogger;

echo "🔍 Debugging Wiki Authentication Issue\n";
echo "=====================================\n\n";

try {
    // Initialize container
    $container = new AsasContainer();
    
    // Register basic services
    $container->set('db', function() {
        return new Connection(
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_NAME') ?: 'islamwiki',
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: ''
        );
    });
    
    $container->set('session', function() {
        return new WisalSession(new ShahidLogger(__DIR__ . '/../logs'));
    });
    
    // Register auth service
    $container->set(AmanSecurity::class, function (AsasContainer $container) {
        $session = $container->get('session');
        $db = $container->get('db');
        return new AmanSecurity($session, $db);
    });
    
    echo "✅ Container initialized successfully\n";
    
    // Test session state
    $session = $container->get('session');
    echo "\n🔐 Session State:\n";
    echo "-----------------\n";
    echo "Session ID: " . session_id() . "\n";
    echo "Session status: " . session_status() . "\n";
    echo "Is logged in: " . ($session->isLoggedIn() ? 'YES' : 'NO') . "\n";
    
    if ($session->isLoggedIn()) {
        echo "User ID: " . ($session->getUserId() ?? 'NULL') . "\n";
        echo "Username: " . ($session->get('username') ?? 'NULL') . "\n";
        echo "Is Admin: " . ($session->get('is_admin') ?? 'NULL') . "\n";
        echo "Logged in at: " . ($session->get('logged_in_at') ?? 'NULL') . "\n";
    }
    
    // Test auth service
    $auth = $container->get(AmanSecurity::class);
    echo "\n🔑 Auth Service Test:\n";
    echo "---------------------\n";
    echo "Auth service class: " . get_class($auth) . "\n";
    echo "Is authenticated: " . ($auth->check() ? 'YES' : 'NO') . "\n";
    
    if ($auth->check()) {
        $user = $auth->user();
        echo "User data retrieved: " . ($user ? 'YES' : 'NO') . "\n";
        if ($user) {
            echo "User ID: " . ($user['id'] ?? 'NULL') . "\n";
            echo "Username: " . ($user['username'] ?? 'NULL') . "\n";
            echo "Email: " . ($user['email'] ?? 'NULL') . "\n";
            echo "Is Admin: " . (($user['is_admin'] ?? 0) ? 'YES' : 'NO') . "\n";
            echo "Role: " . ($user['role'] ?? 'NULL') . "\n";
            echo "Raw user data: " . print_r($user, true) . "\n";
        }
    } else {
        echo "No user authenticated\n";
    }
    
    // Test database connection and check users
    echo "\n🗄️ Database Test:\n";
    echo "-----------------\n";
    
    $db = $container->get('db');
    echo "Database connected: " . ($db ? 'YES' : 'NO') . "\n";
    
    if ($db) {
        try {
            // Check if users table exists
            $stmt = $db->getPdo()->prepare("SHOW TABLES LIKE 'users'");
            $stmt->execute();
            $tables = $stmt->fetchAll();
            
            if (empty($tables)) {
                echo "❌ Users table does not exist!\n";
            } else {
                echo "✅ Users table exists\n";
                
                // Check for non-admin users
                $stmt = $db->getPdo()->prepare("SELECT id, username, email, is_admin, role FROM users WHERE is_admin = 0 LIMIT 5");
                $stmt->execute();
                $nonAdminUsers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                echo "Non-admin users found: " . count($nonAdminUsers) . "\n";
                foreach ($nonAdminUsers as $user) {
                    echo "  - ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}, Is Admin: {$user['is_admin']}\n";
                }
                
                // Check for admin users
                $stmt = $db->getPdo()->prepare("SELECT id, username, email, is_admin, role FROM users WHERE is_admin = 1 LIMIT 5");
                $stmt->execute();
                $adminUsers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                echo "Admin users found: " . count($adminUsers) . "\n";
                foreach ($adminUsers as $user) {
                    echo "  - ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}, Is Admin: {$user['is_admin']}\n";
                }
            }
        } catch (\Exception $e) {
            echo "❌ Database error: " . $e->getMessage() . "\n";
        }
    }
    
    // Test WikiController creation
    echo "\n📚 Wiki Controller Test:\n";
    echo "------------------------\n";
    
    try {
        $wikiController = new \IslamWiki\Http\Controllers\WikiController($container);
        echo "✅ WikiController created successfully\n";
        
        // Test the dashboard method logic
        echo "\nTesting dashboard method logic...\n";
        
        // Simulate what the dashboard method does
        $user = null;
        if (isset($container)) {
            try {
                $auth = $container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                if ($auth && method_exists($auth, 'user')) {
                    $user = $auth->user();
                    echo "✅ User retrieved from auth service: " . ($user ? 'YES' : 'NO') . "\n";
                    if ($user) {
                        echo "User data: " . print_r($user, true) . "\n";
                    }
                } else {
                    echo "❌ Auth service or user method not available\n";
                }
            } catch (\Exception $e) {
                echo "❌ Error getting auth service: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Container not available\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ WikiController creation failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Debug script completed\n"; 