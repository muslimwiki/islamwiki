<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Testing Base Controller View Method\n";
echo "=====================================\n\n";

try {
    // Initialize application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get session and auth
    $session = $container->get('session');
    $auth = $container->get('auth');
    
    // Simulate login
    $session->login(1, 'admin', true);
    
    echo "📊 Session Status:\n";
    echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
    echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
    echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";
    
    // Get user from auth
    $user = $auth->user();
    echo "\n🔍 Auth User:\n";
    echo "- User returned: " . ($user ? 'Yes' : 'No') . "\n";
    if ($user) {
        echo "- User ID: " . $user['id'] . "\n";
        echo "- Username: " . $user['username'] . "\n";
    }
    
    // Test the base Controller view method
    $controller = new \IslamWiki\Http\Controllers\Controller($container->get('db'), $container);
    
    echo "\n🧪 Testing Base Controller View Method:\n";
    
    // Test with no user in data
    $data = [
        'title' => 'Test Page',
        'message' => 'This is a test'
    ];
    
    echo "- Data before view method: " . (isset($data['user']) ? 'Has user' : 'No user') . "\n";
    
    // The view method should automatically add user data
    // Let's simulate what happens in the view method
    if (!isset($data['user'])) {
        try {
            $auth = $container->get('auth');
            $data['user'] = $auth->user();
            echo "- Added user to data: " . ($data['user'] ? 'Yes' : 'No') . "\n";
        } catch (\Exception $e) {
            echo "- Error getting user: " . $e->getMessage() . "\n";
            $data['user'] = null;
        }
    }
    
    echo "- Data after view method: " . (isset($data['user']) ? 'Has user' : 'No user') . "\n";
    if (isset($data['user']) && $data['user']) {
        echo "- User ID: " . $data['user']['id'] . "\n";
        echo "- Username: " . $data['user']['username'] . "\n";
    }
    
    echo "\n✅ Base controller view test completed\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 