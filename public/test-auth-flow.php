<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get session and auth
$session = $container->get('session');
$auth = new \IslamWiki\Core\Auth\Aman($session, $container->get('db'));

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Authentication Flow Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 10px 15px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔐 Authentication Flow Test</h1>
    
    <div class='test-section'>
        <h2>📊 Current Status</h2>
        <div class='status " . ($session->isLoggedIn() ? 'success' : 'error') . "'>
            <strong>Session Status:</strong> " . session_status() . " (" . session_name() . ")<br>
            <strong>Session ID:</strong> " . session_id() . "<br>
            <strong>Is Logged In:</strong> " . ($session->isLoggedIn() ? 'Yes' : 'No') . "<br>
            <strong>Auth Check:</strong> " . ($auth->check() ? 'Yes' : 'No') . "<br>
            <strong>User ID:</strong> " . ($session->getUserId() ?? 'null') . "<br>
            <strong>Username:</strong> " . ($session->getUsername() ?? 'null') . "<br>
        </div>
    </div>

    <div class='test-section'>
        <h2>📋 Session Data</h2>
        <pre>" . print_r($_SESSION, true) . "</pre>
    </div>

    <div class='test-section'>
        <h2>🍪 Cookies</h2>
        <pre>" . print_r($_COOKIE, true) . "</pre>
    </div>

    <div class='test-section'>
        <h2>🧪 Test Actions</h2>
        <form method='post' style='margin-bottom: 20px;'>
            <button type='submit' name='action' value='login' class='btn btn-success'>Simulate Login</button>
            <button type='submit' name='action' value='logout' class='btn btn-danger'>Simulate Logout</button>
            <button type='submit' name='action' value='test' class='btn btn-warning'>Test Session</button>
            <button type='submit' name='action' value='refresh' class='btn btn-primary'>Refresh Page</button>
        </form>
    </div>";

if ($_POST['action'] ?? false) {
    echo "<div class='test-section'>
        <h2>🔄 Action Result</h2>";
    
    switch ($_POST['action']) {
        case 'login':
            $session->login(1, 'admin', true);
            echo "<div class='status success'>✅ Login completed</div>";
            break;
        case 'logout':
            $session->logout();
            echo "<div class='status warning'>✅ Logout completed</div>";
            break;
        case 'test':
            $session->put('test_value', 'test_data_' . time());
            echo "<div class='status info'>✅ Test value set</div>";
            break;
        case 'refresh':
            echo "<div class='status info'>✅ Page refreshed</div>";
            break;
    }
    
    echo "<div class='status " . ($session->isLoggedIn() ? 'success' : 'error') . "'>
        <strong>Updated Status:</strong><br>
        Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "<br>
        User ID: " . ($session->getUserId() ?? 'null') . "<br>
        Username: " . ($session->getUsername() ?? 'null') . "<br>
        Auth Check: " . ($auth->check() ? 'Yes' : 'No') . "
    </div>
    </div>";
}

echo "<div class='test-section'>
    <h2>🧭 Navigation Test</h2>
    <p>Click these links to test navigation and see if authentication persists:</p>
    <a href='test-auth-flow.php' class='btn btn-primary'>Refresh This Page</a>
    <a href='/' class='btn btn-success'>Go to Home</a>
    <a href='/dashboard' class='btn btn-warning'>Go to Dashboard</a>
    <a href='/profile' class='btn btn-info'>Go to Profile</a>
    <a href='/settings' class='btn btn-secondary'>Go to Settings</a>
    <a href='/login' class='btn btn-danger'>Go to Login</a>
</div>

<div class='test-section'>
    <h2>🔍 Debug Information</h2>
    <h3>Database Connection</h3>";

try {
    $db = $container->get('db');
    $result = $db->select('SELECT COUNT(*) as count FROM users');
    echo "<div class='status success'>✅ Database connection working</div>";
    echo "<div class='status info'>Users count: " . ($result[0]['count'] ?? 'unknown') . "</div>";
    
    if ($session->isLoggedIn()) {
        $userId = $session->getUserId();
        $userCheck = $db->select('SELECT id, username, is_active FROM users WHERE id = ?', [$userId]);
        echo "<div class='status " . (!empty($userCheck) ? 'success' : 'error') . "'>";
        echo "Current user in DB: " . (!empty($userCheck) ? 'Yes' : 'No') . "<br>";
        if (!empty($userCheck)) {
            echo "User active: " . ($userCheck[0]['is_active'] ? 'Yes' : 'No');
        }
        echo "</div>";
    }
} catch (\Exception $e) {
    echo "<div class='status error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div>

<div class='test-section'>
    <h2>📝 Instructions</h2>
    <ol>
        <li><strong>Test Login:</strong> Click 'Simulate Login' to log in as admin</li>
        <li><strong>Test Navigation:</strong> Click the navigation links to see if authentication persists</li>
        <li><strong>Test Logout:</strong> Click 'Simulate Logout' to clear the session</li>
        <li><strong>Monitor:</strong> Watch the status section to see if authentication changes</li>
        <li><strong>Report:</strong> Note exactly when the authentication is lost</li>
    </ol>
    
    <h3>🔍 What to Look For</h3>
    <ul>
        <li>Does the 'Is Logged In' status change unexpectedly?</li>
        <li>Does the 'Auth Check' return false when it should be true?</li>
        <li>Does the session data disappear?</li>
        <li>Does the session ID change?</li>
        <li>Are there any error messages in the browser console?</li>
    </ul>
</div>

<script>
// Add some client-side monitoring
console.log('Authentication Test Page Loaded');
console.log('Session Status:', '" . ($session->isLoggedIn() ? 'true' : 'false') . "');
console.log('User ID:', '" . ($session->getUserId() ?? 'null') . "');

// Monitor for navigation events
window.addEventListener('beforeunload', function() {
    console.log('Page is being unloaded');
});

// Check if we can access session data via JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking for session cookies');
    console.log('Cookies:', document.cookie);
});
</script>

</body>
</html>"; 