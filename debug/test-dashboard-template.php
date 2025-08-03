<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Testing Dashboard Template Data\n";
echo "=================================\n\n";

try {
    // Initialize application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Get session and auth
    $session = $container->get('session');
    $auth = $container->get('auth');
    
    // Simulate login
    $session->login(1, 'admin', true);
    
    echo "📊 After Login:\n";
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
        echo "- Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";
    }
    
    // Simulate DashboardController data
    $data = [
        'title' => 'Dashboard - IslamWiki',
        'user' => $user,
        'userStats' => [],
        'recentActivity' => [],
        'watchlist' => [],
        'quickStats' => [],
        'siteStats' => [],
        'activeSkin' => 'Bismillah',
        'currentTime' => date('Y-m-d H:i:s'),
        'isLoggedIn' => $session->isLoggedIn()
    ];
    
    echo "\n📋 Template Data:\n";
    echo "- User in data: " . (isset($data['user']) ? 'Yes' : 'No') . "\n";
    echo "- User value: " . ($data['user'] ? 'Not null' : 'null') . "\n";
    if ($data['user']) {
        echo "- User ID: " . $data['user']['id'] . "\n";
        echo "- Username: " . $data['user']['username'] . "\n";
    }
    
    // Test the view method
    $controller = new \IslamWiki\Http\Controllers\DashboardController($container->get('db'), $container);
    $request = new \IslamWiki\Core\Http\Request();
    
    echo "\n🧪 Testing Controller View Method:\n";
    
    // Test the base Controller view method
    $viewData = $data;
    if (!isset($viewData['user'])) {
        try {
            $auth = $container->get('auth');
            $viewData['user'] = $auth->user();
            echo "- Added user to view data: " . ($viewData['user'] ? 'Yes' : 'No') . "\n";
        } catch (\Exception $e) {
            echo "- Error getting user: " . $e->getMessage() . "\n";
            $viewData['user'] = null;
        }
    }
    
    echo "- Final user in view data: " . ($viewData['user'] ? 'Not null' : 'null') . "\n";
    
    echo "\n✅ Dashboard template test completed\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 