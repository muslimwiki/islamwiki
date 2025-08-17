<?php
/**
 * Debug Authentication Process
 * 
 * This script tests the authentication process step by step
 */

// Start output buffering
ob_start();

echo "<h1>🔍 Debug Authentication Process</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Include the application
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

try {
    // Create container
    $container = new \IslamWiki\Core\Container\AsasContainer();
    
    // Register services
    $dbProvider = new \IslamWiki\Providers\DatabaseServiceProvider();
    $dbProvider->register($container);
    
    $sessionProvider = new \IslamWiki\Providers\SessionServiceProvider();
    $sessionProvider->register($container);
    $sessionProvider->boot($container);
    
    $authProvider = new \IslamWiki\Providers\AuthServiceProvider();
    $authProvider->register($container);
    
    echo "<h2>✅ Services Registered</h2>";
    
    // Get session
    $session = $container->get('session');
    echo "<p><strong>Session Class:</strong> " . get_class($session) . "</p>";
    echo "<p><strong>Session Status:</strong> " . $session->getStatus() . "</p>";
    echo "<p><strong>Session Started:</strong> " . ($session->isStarted() ? 'Yes' : 'No') . "</p>";
    
    // Get auth
    $auth = $container->get('auth');
    echo "<p><strong>Auth Class:</strong> " . get_class($auth) . "</p>";
    
    // Check if user is logged in before
    $isLoggedInBefore = $auth->check();
    echo "<p><strong>User Logged In (Before):</strong> " . ($isLoggedInBefore ? 'Yes' : 'No') . "</p>";
    
    // Test authentication
    echo "<h2>🧪 Testing Authentication</h2>";
    echo "<p><strong>Testing with:</strong> testuser / password123</p>";
    
    $authResult = $auth->attempt('testuser', 'password123');
    echo "<p><strong>Authentication Result:</strong> " . ($authResult ? 'Success' : 'Failed') . "</p>";
    
    // Check if user is logged in after
    $isLoggedInAfter = $auth->check();
    echo "<p><strong>User Logged In (After):</strong> " . ($isLoggedInAfter ? 'Yes' : 'No') . "</p>";
    
    if ($isLoggedInAfter) {
        $user = $auth->user();
        echo "<p><strong>User Data:</strong></p>";
        echo "<pre>" . htmlspecialchars(print_r($user, true)) . "</pre>";
    }
    
    // Check session data
    echo "<h2>📊 Session Data After Authentication</h2>";
    $sessionData = $session->getAll();
    if (empty($sessionData)) {
        echo "<p>No session data</p>";
    } else {
        echo "<pre>" . htmlspecialchars(print_r($sessionData, true)) . "</pre>";
    }
    
    // Check if session has user data
    echo "<h2>👤 User Session Data</h2>";
    echo "<p><strong>user_id:</strong> " . ($session->get('user_id') ?: 'Not set') . "</p>";
    echo "<p><strong>username:</strong> " . ($session->get('username') ?: 'Not set') . "</p>";
    echo "<p><strong>user_authenticated:</strong> " . ($session->get('user_authenticated') ?: 'Not set') . "</p>";
    
    echo "<h2>✅ Debug Complete</h2>";
    
} catch (\Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} 