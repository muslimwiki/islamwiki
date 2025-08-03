<?php
declare(strict_types=1);

/**
 * Debug Session State
 * 
 * Checks the actual session state and authentication status.
 * 
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Debugging Session State\n";
echo "==========================\n\n";

// Check if session is started
echo "📊 Session Status:\n";
echo "==================\n";
echo "Session status: " . session_status() . "\n";
echo "Session name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n\n";

// Check session data
echo "📋 Session Data:\n";
echo "================\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Session is active\n";
    if (empty($_SESSION)) {
        echo "Session is empty\n";
    } else {
        echo "Session contains " . count($_SESSION) . " keys:\n";
        foreach ($_SESSION as $key => $value) {
            echo "  - $key: " . (is_string($value) ? $value : gettype($value)) . "\n";
        }
    }
} else {
    echo "Session is not active\n";
}

echo "\n";

// Check cookies
echo "🍪 Cookies:\n";
echo "===========\n";
if (empty($_COOKIE)) {
    echo "No cookies found\n";
} else {
    echo "Found " . count($_COOKIE) . " cookies:\n";
    foreach ($_COOKIE as $name => $value) {
        echo "  - $name: $value\n";
    }
}

echo "\n";

// Test the session manager
echo "🔧 Testing Session Manager:\n";
echo "===========================\n";

try {
    // Initialize session manager
    $config = [
        'name' => getenv('SESSION_NAME') ?: 'islamwiki_session',
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 86400),
        'path' => getenv('SESSION_PATH') ?: '/',
        'secure' => getenv('SESSION_SECURE') !== 'false',
        'http_only' => getenv('SESSION_HTTP_ONLY') !== 'false',
        'same_site' => getenv('SESSION_SAME_SITE') ?: 'Lax',
    ];
    
    echo "Session config:\n";
    foreach ($config as $key => $value) {
        echo "  - $key: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
    }
    
    $session = new \IslamWiki\Core\Session\Wisal($config);
    echo "\n✅ Session manager created successfully\n";
    
    // Start session
    $session->start();
    echo "✅ Session started\n";
    
    // Check authentication
    echo "🔐 Authentication Check:\n";
    echo "=======================\n";
    echo "Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "User ID: " . ($session->getUserId() ?? 'null') . "\n";
    echo "Username: " . ($session->getUsername() ?? 'null') . "\n";
    echo "Is admin: " . ($session->isAdmin() ? 'Yes' : 'No') . "\n";
    
    // Check session data after manager
    echo "\n📋 Session Data After Manager:\n";
    echo "==============================\n";
    if (empty($_SESSION)) {
        echo "Session is empty\n";
    } else {
        echo "Session contains " . count($_SESSION) . " keys:\n";
        foreach ($_SESSION as $key => $value) {
            echo "  - $key: " . (is_string($value) ? $value : gettype($value)) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";

// Test database connection and user lookup
echo "🗄️ Database Test:\n";
echo "=================\n";

try {
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
    
    $db = new \IslamWiki\Core\Database\Connection($dbConfig);
    echo "✅ Database connection successful\n";
    
    // Check if admin user exists
    $adminUser = $db->first("SELECT * FROM users WHERE username = 'admin'");
    if ($adminUser) {
        echo "✅ Admin user found in database\n";
        echo "  - ID: {$adminUser->id}\n";
        echo "  - Username: {$adminUser->username}\n";
        echo "  - Email: {$adminUser->email}\n";
        echo "  - Is admin: " . ($adminUser->is_admin ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ Admin user not found in database\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n✅ Session state debug completed!\n"; 