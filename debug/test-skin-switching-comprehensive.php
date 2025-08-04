<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize database connection
$db = new \IslamWiki\Core\Database\Connection([
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? ''
]);

echo "=== Comprehensive Skin Switching Test ===\n";

// Test 1: Check user settings in database
echo "\n1. Checking user settings in database...\n";
try {
    $result = $db->query("SELECT * FROM user_settings WHERE user_id = 1");
    $settings = $result->fetch();
    if ($settings) {
        echo "✅ User settings found for user ID 1\n";
        echo "📄 Settings: " . json_encode($settings) . "\n";
        
        // Parse the settings JSON
        $settingsData = json_decode($settings->settings, true);
        if ($settingsData && isset($settingsData['skin'])) {
            echo "✅ User has skin preference: " . $settingsData['skin'] . "\n";
        } else {
            echo "❌ No skin preference found in user settings\n";
        }
    } else {
        echo "❌ No user settings found for user ID 1\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking user settings: " . $e->getMessage() . "\n";
}

// Test 2: Check if user exists
echo "\n2. Checking if user exists...\n";
try {
    $result = $db->query("SELECT * FROM users WHERE id = 1");
    $user = $result->fetch();
    if ($user) {
        echo "✅ User found: " . $user->username . "\n";
        echo "📄 User data: " . json_encode($user) . "\n";
    } else {
        echo "❌ User with ID 1 not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking user: " . $e->getMessage() . "\n";
}

// Test 3: Test skin manager directly
echo "\n3. Testing skin manager directly...\n";
try {
    // Create application first
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    
    // Create container
    $container = $app->getContainer();
    $container->singleton(\IslamWiki\Core\Database\Connection::class, function() use ($db) {
        return $db;
    });
    
    // Create skin manager
    $skinManager = new \IslamWiki\Skins\SkinManager($app, $container);
    
    // Test getting active skin for user
    $activeSkin = $skinManager->getActiveSkinForUser(1);
    if ($activeSkin) {
        echo "✅ Active skin for user 1: " . $activeSkin->getName() . "\n";
        echo "📄 Skin version: " . $activeSkin->getVersion() . "\n";
        echo "📄 Skin CSS length: " . strlen($activeSkin->getCssContent()) . "\n";
        
        // Check if skin has layout
        if (method_exists($activeSkin, 'getLayoutPath')) {
            $layoutPath = $activeSkin->getLayoutPath();
            echo "📄 Skin layout path: " . $layoutPath . "\n";
            if (file_exists($layoutPath)) {
                echo "✅ Skin layout file exists\n";
            } else {
                echo "❌ Skin layout file does not exist\n";
            }
        } else {
            echo "❌ Skin does not have getLayoutPath method\n";
        }
    } else {
        echo "❌ No active skin found for user 1\n";
    }
    
    // Test getting active skin name
    $activeSkinName = $skinManager->getActiveSkinNameForUser(1);
    echo "📄 Active skin name for user 1: " . $activeSkinName . "\n";
    
} catch (Exception $e) {
    echo "❌ Error testing skin manager: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 4: Test session and authentication
echo "\n4. Testing session and authentication...\n";
try {
    // Create session manager
    $session = new \IslamWiki\Core\Session\Wisal();
    
    // Check if user is logged in
    $isLoggedIn = $session->isLoggedIn();
    echo "📄 Is user logged in: " . ($isLoggedIn ? 'Yes' : 'No') . "\n";
    
    if ($isLoggedIn) {
        $userId = $session->getUserId();
        $username = $session->getUsername();
        echo "📄 User ID: " . $userId . "\n";
        echo "📄 Username: " . $username . "\n";
    } else {
        echo "❌ User is not logged in\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing session: " . $e->getMessage() . "\n";
}

// Test 5: Test the full application flow
echo "\n5. Testing full application flow...\n";
try {
    // Create a mock request
    $request = new \IslamWiki\Core\Http\Request(
        'GET',
        new \IslamWiki\Core\Http\Uri('https://local.islam.wiki/'),
        [],
        [],
        '1.1'
    );
    
    // Create application
    $app = new \IslamWiki\Core\Application();
    
    // Create container with services
    $container = $app->getContainer();
    $container->singleton(\IslamWiki\Core\Database\Connection::class, function() use ($db) {
        return $db;
    });
    
    // Create skin manager
    $skinManager = new \IslamWiki\Skins\SkinManager($app, $container);
    $container->singleton('skin.manager', function() use ($skinManager) {
        return $skinManager;
    });
    
    // Create view renderer
    $viewRenderer = new \IslamWiki\Core\View\TwigRenderer(__DIR__ . '/../resources/views');
    $container->singleton('view', function() use ($viewRenderer) {
        return $viewRenderer;
    });
    
    // Create session
    $session = new \IslamWiki\Core\Session\Wisal();
    $container->singleton('session', function() use ($session) {
        return $session;
    });
    
    // Test skin middleware
    $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
    
    // Simulate middleware execution
    $response = $skinMiddleware->handle($request, function($req) {
        return new \IslamWiki\Core\Http\Response(200, [], 'Test response');
    });
    
    echo "✅ Skin middleware executed successfully\n";
    echo "📄 Response status: " . $response->getStatusCode() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error testing application flow: " . $e->getMessage() . "\n";
    echo "📄 Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 