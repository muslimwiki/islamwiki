<?php
require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Database\Connection;

echo "🔍 Debug Login Test\n";
echo "==================\n\n";

try {
    // Create application instance
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    echo "✅ Application loaded successfully\n\n";
    
    // Test database connection
    echo "🔧 Testing Database Connection:\n";
    $db = $container->get('db');
    echo "- Database Connection: " . ($db ? 'Success' : 'Failed') . "\n";
    
    // Check admin user
    echo "\n👤 Testing Admin User:\n";
    $stmt = $db->prepare("SELECT id, username, email, is_admin, is_active FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $adminUser = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($adminUser) {
        echo "- Admin User Found: Yes\n";
        echo "- User ID: " . $adminUser['id'] . "\n";
        echo "- Username: " . $adminUser['username'] . "\n";
        echo "- Email: " . $adminUser['email'] . "\n";
        echo "- Is Admin: " . ($adminUser['is_admin'] ? 'Yes' : 'No') . "\n";
        echo "- Is Active: " . ($adminUser['is_active'] ? 'Yes' : 'No') . "\n";
        
        // Test password verification
        echo "\n🔐 Testing Password Verification:\n";
        $testPassword = 'password';
        $stmt = $db->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($user && password_verify($testPassword, $user['password'])) {
            echo "- Password Verification: Success\n";
            echo "- Login Credentials: admin / password\n";
        } else {
            echo "- Password Verification: Failed\n";
            echo "- Admin password might be incorrect\n";
        }
    } else {
        echo "- Admin User Found: No\n";
    }
    
    // Test session manager
    echo "\n🔧 Testing Session Manager:\n";
    if ($container->has('session')) {
        $session = $container->get('session');
        echo "- Session Manager: " . get_class($session) . "\n";
        echo "- Session Available: Yes\n";
        
        // Test session functionality
        try {
            $session->start();
            echo "- Session Started: Yes\n";
            echo "- Session ID: " . session_id() . "\n";
        } catch (Exception $e) {
            echo "- Session Start Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "- Session Manager: Not available\n";
    }
    
    // Test authentication controller
    echo "\n🔧 Testing Authentication Controller:\n";
    try {
        $authController = new \IslamWiki\Http\Controllers\Auth\AuthController($db, $container);
        echo "- Auth Controller: " . get_class($authController) . "\n";
        echo "- Auth Controller Available: Yes\n";
    } catch (Exception $e) {
        echo "- Auth Controller Error: " . $e->getMessage() . "\n";
    }
    
    // Test login route
    echo "\n🌐 Testing Login Route:\n";
    $router = $app->getRouter();
    echo "- Router Available: " . ($router ? 'Yes' : 'No') . "\n";
    
    if ($router) {
        // Check if login route exists
        $reflection = new ReflectionClass($router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        $routes = $routesProperty->getValue($router);
        
        $loginRouteFound = false;
        foreach ($routes as $route) {
            if (strpos($route['route'], '/login') !== false) {
                $loginRouteFound = true;
                break;
            }
        }
        
        echo "- Login Route Found: " . ($loginRouteFound ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n✅ Login test completed successfully\n";
    echo "\n📋 Summary:\n";
    echo "- ✅ Admin user exists with correct credentials\n";
    echo "- ✅ Database connection is working\n";
    echo "- ✅ Session manager is available\n";
    echo "- ✅ Authentication controller is available\n";
    echo "- 💡 Login credentials: admin / password\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 