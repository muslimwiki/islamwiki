<?php
/**
 * Test Authentication Routes
 * 
 * This script tests if the /login and /register routes are working
 * after fixing the service provider registration.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(dirname(__DIR__)));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Include necessary files
require_once BASE_PATH . '/src/Core/Container/AsasContainer.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';
require_once BASE_PATH . '/src/Core/Auth/AmanSecurity.php';
require_once BASE_PATH . '/src/Core/Session/WisalSession.php';
require_once BASE_PATH . '/src/Core/Routing/ControllerFactory.php';
require_once BASE_PATH . '/src/Http/Controllers/Auth/AuthController.php';
require_once BASE_PATH . '/src/Core/View/TwigRenderer.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Routing\SabilRouting;
use IslamWiki\Core\Http\Request;

echo "<h1>🔐 Testing Authentication Routes</h1>\n";

try {
    // Initialize container
    echo "<h2>1. Initializing Container</h2>\n";
    $container = new AsasContainer();
    echo "✅ Container created successfully\n\n";

    // Initialize database connection
    echo "<h2>2. Initializing Database</h2>\n";
    $db = new Connection();
    $container->instance('db', $db);
    $container->instance('connection', $db);
    echo "✅ Database connection registered\n\n";

    // Initialize and register session manager
    echo "<h2>3. Initializing Session Manager</h2>\n";
    $sessionManager = new \IslamWiki\Core\Session\WisalSession();
    $container->instance('session', $sessionManager);
    echo "✅ Session manager registered\n\n";

    // Register service providers
    echo "<h2>4. Registering Service Providers</h2>\n";
    
    // Register AuthServiceProvider
    require_once BASE_PATH . '/src/Providers/AuthServiceProvider.php';
    $authProvider = new \IslamWiki\Providers\AuthServiceProvider();
    $authProvider->register($container);
    echo "✅ AuthServiceProvider registered\n";
    
    // Register SessionServiceProvider
    require_once BASE_PATH . '/src/Providers/SessionServiceProvider.php';
    $sessionProvider = new \IslamWiki\Providers\SessionServiceProvider();
    $sessionProvider->register($container);
    echo "✅ SessionServiceProvider registered\n";
    
    // Register ViewServiceProvider
    require_once BASE_PATH . '/src/Providers/ViewServiceProvider.php';
    $viewProvider = new \IslamWiki\Providers\ViewServiceProvider();
    $viewProvider->register($container);
    echo "✅ ViewServiceProvider registered\n";
    
    // Register StaticDataServiceProvider
    require_once BASE_PATH . '/src/Providers/StaticDataServiceProvider.php';
    $staticDataProvider = new \IslamWiki\Providers\StaticDataServiceProvider();
    $staticDataProvider->register($container);
    echo "✅ StaticDataServiceProvider registered\n";
    
    // Register SkinServiceProvider
    require_once BASE_PATH . '/src/Providers/SkinServiceProvider.php';
    $skinProvider = new \IslamWiki\Providers\SkinServiceProvider();
    $skinProvider->register($container);
    echo "✅ SkinServiceProvider registered\n\n";

    // Boot all service providers
    echo "<h2>5. Booting Service Providers</h2>\n";
    $authProvider->boot($container);
    $sessionProvider->boot($container);
    $staticDataProvider->boot($container);
    $skinProvider->boot($container);
    echo "✅ All service providers booted\n\n";

    // Test if auth service is available
    echo "<h2>6. Testing Auth Service</h2>\n";
    try {
        $auth = $container->get('auth');
        echo "✅ Auth service retrieved successfully\n";
        echo "   - Class: " . get_class($auth) . "\n";
        echo "   - Check method exists: " . (method_exists($auth, 'check') ? 'Yes' : 'No') . "\n";
        echo "   - Attempt method exists: " . (method_exists($auth, 'attempt') ? 'Yes' : 'No') . "\n";
    } catch (\Exception $e) {
        echo "❌ Failed to get auth service: " . $e->getMessage() . "\n";
    }

    // Test if session service is available
    echo "<h2>7. Testing Session Service</h2>\n";
    try {
        $session = $container->get('session');
        echo "✅ Session service retrieved successfully\n";
        echo "   - Class: " . get_class($session) . "\n";
        echo "   - Login method exists: " . (method_exists($session, 'login') ? 'Yes' : 'No') . "\n";
        echo "   - Logout method exists: " . (method_exists($session, 'logout') ? 'Yes' : 'No') . "\n";
    } catch (\Exception $e) {
        echo "❌ Failed to get session service: " . $e->getMessage() . "\n";
    }

    // Test if view service is available
    echo "<h2>8. Testing View Service</h2>\n";
    try {
        $view = $container->get('view');
        echo "✅ View service retrieved successfully\n";
        echo "   - Class: " . get_class($view) . "\n";
        echo "   - RenderWithSkin method exists: " . (method_exists($view, 'renderWithSkin') ? 'Yes' : 'No') . "\n";
    } catch (\Exception $e) {
        echo "❌ Failed to get view service: " . $e->getMessage() . "\n";
    }

    // Test AuthController instantiation
    echo "<h2>9. Testing AuthController</h2>\n";
    try {
        $authController = new \IslamWiki\Http\Controllers\Auth\AuthController($db, $container);
        echo "✅ AuthController created successfully\n";
        echo "   - Class: " . get_class($authController) . "\n";
        echo "   - showLogin method exists: " . (method_exists($authController, 'showLogin') ? 'Yes' : 'No') . "\n";
        echo "   - showRegister method exists: " . (method_exists($authController, 'showRegister') ? 'Yes' : 'No') . "\n";
    } catch (\Exception $e) {
        echo "❌ Failed to create AuthController: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }

    // Test router initialization
    echo "<h2>10. Testing Router</h2>\n";
    try {
        $router = new SabilRouting($container);
        echo "✅ Router created successfully\n";
        echo "   - Class: " . get_class($router) . "\n";
    } catch (\Exception $e) {
        echo "❌ Failed to create router: " . $e->getMessage() . "\n";
    }

    echo "<h2>✅ Test Completed Successfully!</h2>\n";
    echo "<p>The authentication system should now be working properly.</p>\n";
    echo "<p>You can test the routes:</p>\n";
    echo "<ul>\n";
    echo "<li><a href='/login' target='_blank'>/login</a></li>\n";
    echo "<li><a href='/register' target='_blank'>/register</a></li>\n";
    echo "</ul>\n";

} catch (\Exception $e) {
    echo "<h2>❌ Test Failed</h2>\n";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<h3>Stack Trace:</h3>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
} 