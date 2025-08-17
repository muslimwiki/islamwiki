<?php
/**
 * Test Authentication System
 * 
 * This script tests the authentication system to see if sessions are working
 */

// Start output buffering
ob_start();

echo "<h1>🧪 Test Authentication System</h1>";
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
    
    // Check if user is logged in
    $isLoggedIn = $auth->check();
    echo "<p><strong>User Logged In:</strong> " . ($isLoggedIn ? 'Yes' : 'No') . "</p>";
    
    if ($isLoggedIn) {
        $user = $auth->user();
        echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
        echo "<p><strong>Username:</strong> " . $user['username'] . "</p>";
        echo "<p><strong>Is Admin:</strong> " . ($user['is_admin'] ? 'Yes' : 'No') . "</p>";
    } else {
        echo "<p><strong>No user logged in</strong></p>";
    }
    
    // Test session data
    echo "<h2>📊 Session Data</h2>";
    $sessionData = $session->getAll();
    if (empty($sessionData)) {
        echo "<p>No session data</p>";
    } else {
        echo "<pre>" . htmlspecialchars(print_r($sessionData, true)) . "</pre>";
    }
    
    // Test CSRF token
    echo "<h2>🔒 CSRF Token</h2>";
    $csrfToken = $session->getCsrfToken();
    echo "<p><strong>CSRF Token:</strong> " . $csrfToken . "</p>";
    
    // Test token verification
    $isValid = $session->verifyCsrfToken($csrfToken);
    echo "<p><strong>Token Valid:</strong> " . ($isValid ? 'Yes' : 'No') . "</p>";
    
    echo "<h2>✅ Test Complete</h2>";
    
} catch (\Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} 