<?php
/**
 * Debug script to test authentication for non-admin users
 * This will help identify why non-admin users can't access wiki routes
 */

require_once __DIR__ . '/../src/helpers.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Auth\AmanSecurity;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\ShahidLogger;

echo "🔍 Debugging Non-Admin User Authentication\n";
echo "==========================================\n\n";

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
    
    // Register auth service like the provider does
    $container->set(AmanSecurity::class, function (AsasContainer $container) {
        $session = $container->get('session');
        $db = $container->get('db');
        return new AmanSecurity($session, $db);
    });
    
    // Register 'auth' alias
    $container->alias('auth', AmanSecurity::class);
    echo "✅ Container loaded successfully\n";
    
    // Get the auth service
    $auth = $container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
    echo "✅ Auth service loaded successfully\n";
    
    // Check if any user is currently logged in
    echo "\n📊 Current Authentication State:\n";
    echo "--------------------------------\n";
    echo "Is logged in: " . ($auth->check() ? 'YES' : 'NO') . "\n";
    
    if ($auth->check()) {
        $user = $auth->user();
        echo "User ID: " . ($user['id'] ?? 'NULL') . "\n";
        echo "Username: " . ($user['username'] ?? 'NULL') . "\n";
        echo "Email: " . ($user['email'] ?? 'NULL') . "\n";
        echo "Is Admin: " . (($user['is_admin'] ?? 0) ? 'YES' : 'NO') . "\n";
        echo "Role: " . ($user['role'] ?? 'NULL') . "\n";
        echo "Raw user data: " . print_r($user, true) . "\n";
    } else {
        echo "No user currently logged in\n";
    }
    
    // Test database connection and user table
    echo "\n🗄️ Database Connection Test:\n";
    echo "----------------------------\n";
    
    $db = $container->get('db');
    echo "✅ Database service loaded\n";
    
    // Check if users table exists and has data
    $stmt = $db->getPdo()->prepare("SHOW TABLES LIKE 'users'");
    $stmt->execute();
    $tables = $stmt->fetchAll();
    
    if (empty($tables)) {
        echo "❌ Users table does not exist!\n";
    } else {
        echo "✅ Users table exists\n";
        
        // Check table structure
        $stmt = $db->getPdo()->prepare("DESCRIBE users");
        $stmt->execute();
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        echo "📋 Users table structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']}: {$column['Type']} ({$column['Null']})\n";
        }
        
        // Check if there are any non-admin users
        $stmt = $db->getPdo()->prepare("SELECT id, username, email, is_admin, role FROM users WHERE is_admin = 0 LIMIT 5");
        $stmt->execute();
        $nonAdminUsers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        echo "\n👥 Non-admin users in database:\n";
        if (empty($nonAdminUsers)) {
            echo "❌ No non-admin users found in database\n";
        } else {
            foreach ($nonAdminUsers as $user) {
                echo "  - ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}, Is Admin: {$user['is_admin']}\n";
            }
        }
        
        // Check if there are any admin users
        $stmt = $db->getPdo()->prepare("SELECT id, username, email, is_admin, role FROM users WHERE is_admin = 1 LIMIT 5");
        $stmt->execute();
        $adminUsers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        echo "\n👑 Admin users in database:\n";
        if (empty($adminUsers)) {
            echo "❌ No admin users found in database\n";
        } else {
            foreach ($adminUsers as $user) {
                echo "  - ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}, Is Admin: {$user['is_admin']}\n";
            }
        }
    }
    
    // Test session state
    echo "\n🔐 Session State Test:\n";
    echo "----------------------\n";
    
    $session = $container->get(\IslamWiki\Core\Session\WisalSession::class);
    echo "✅ Session service loaded\n";
    echo "Session ID: " . session_id() . "\n";
    echo "Session status: " . session_status() . "\n";
    echo "Is logged in: " . ($session->isLoggedIn() ? 'YES' : 'NO') . "\n";
    
    if ($session->isLoggedIn()) {
        echo "User ID: " . ($session->getUserId() ?? 'NULL') . "\n";
        echo "Username: " . ($session->get('username') ?? 'NULL') . "\n";
        echo "Is Admin: " . ($session->get('is_admin') ?? 'NULL') . "\n";
        echo "Logged in at: " . ($session->get('logged_in_at') ?? 'NULL') . "\n";
    }
    
    // Test WikiController methods
    echo "\n📚 Wiki Controller Test:\n";
    echo "------------------------\n";
    
    // Create a mock request
    $request = new \IslamWiki\Core\Http\Request('GET', '/wiki', [], [], []);
    
    // Test if we can create WikiController
    try {
        $wikiController = new \IslamWiki\Http\Controllers\WikiController($container);
        echo "✅ WikiController created successfully\n";
        
        // Test dashboard method
        try {
            echo "Testing dashboard method...\n";
            $response = $wikiController->dashboard($request);
            echo "✅ Dashboard method executed successfully\n";
            echo "Response status: " . $response->getStatusCode() . "\n";
        } catch (\Exception $e) {
            echo "❌ Dashboard method failed: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
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