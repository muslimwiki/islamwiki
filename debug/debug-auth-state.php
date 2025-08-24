<?php
/**
 * Debug Authentication State
 * 
 * This script helps debug authentication issues by checking:
 * - Session state
 * - Container bindings
 * - Auth service availability
 * - User data retrieval
 */

require_once __DIR__ . '/../src/helpers.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Auth\AmanSecurity;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\ShahidLogger;

echo "=== Authentication State Debug ===\n\n";

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
    
    echo "✅ Container initialized successfully\n";
    
    // Test session
    $session = $container->get('session');
    echo "✅ Session service retrieved: " . get_class($session) . "\n";
    echo "   Session status: " . session_status() . "\n";
    echo "   Session ID: " . session_id() . "\n";
    
    // Check if session is active
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "   Session is active\n";
        echo "   Session data: " . print_r($_SESSION, true) . "\n";
        
        if (isset($_SESSION['user_id'])) {
            echo "   User ID in session: " . $_SESSION['user_id'] . "\n";
        } else {
            echo "   No user_id in session\n";
        }
        
        if (isset($_SESSION['username'])) {
            echo "   Username in session: " . $_SESSION['username'] . "\n";
        } else {
            echo "   No username in session\n";
        }
    } else {
        echo "   Session is not active\n";
    }
    
    // Test auth service retrieval by alias
    echo "\n--- Testing Auth Service by Alias ---\n";
    try {
        $authByAlias = $container->get('auth');
        echo "✅ Auth service retrieved by alias 'auth': " . get_class($authByAlias) . "\n";
        
        $userByAlias = $authByAlias->user();
        if ($userByAlias) {
            echo "✅ User retrieved via alias: " . json_encode($userByAlias) . "\n";
        } else {
            echo "❌ No user returned via alias\n";
        }
    } catch (Exception $e) {
        echo "❌ Failed to get auth service by alias: " . $e->getMessage() . "\n";
    }
    
    // Test auth service retrieval by class name
    echo "\n--- Testing Auth Service by Class Name ---\n";
    try {
        $authByClass = $container->get(AmanSecurity::class);
        echo "✅ Auth service retrieved by class name: " . get_class($authByClass) . "\n";
        
        $userByClass = $authByClass->user();
        if ($userByClass) {
            echo "✅ User retrieved via class name: " . json_encode($userByClass) . "\n";
        } else {
            echo "❌ No user returned via class name\n";
        }
    } catch (Exception $e) {
        echo "❌ Failed to get auth service by class name: " . $e->getMessage() . "\n";
    }
    
    // Test session methods
    echo "\n--- Testing Session Methods ---\n";
    try {
        echo "   isLoggedIn(): " . ($session->isLoggedIn() ? 'true' : 'false') . "\n";
        echo "   getUserId(): " . ($session->getUserId() ?? 'null') . "\n";
        echo "   get('username'): " . ($session->get('username') ?? 'null') . "\n";
        echo "   get('user_id'): " . ($session->get('user_id') ?? 'null') . "\n";
    } catch (Exception $e) {
        echo "❌ Session method error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Debug Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
