<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize application
$app = new \IslamWiki\Core\Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get session
$session = $container->get('session');

echo "<h1>Session Test</h1>";

echo "<h2>Session Status</h2>";
echo "<p>Session Status: " . session_status() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";

echo "<h2>Authentication Status</h2>";
echo "<p>Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "</p>";
echo "<p>User ID: " . ($session->getUserId() ?? 'null') . "</p>";
echo "<p>Username: " . ($session->getUsername() ?? 'null') . "</p>";

echo "<h2>Session Data</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p>No active session</p>";
}

echo "<h2>Cookies</h2>";
echo "<pre>" . print_r($_COOKIE, true) . "</pre>";

echo "<h2>Test Actions</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='action' value='login'>Simulate Login</button>";
echo "<button type='submit' name='action' value='logout'>Simulate Logout</button>";
echo "<button type='submit' name='action' value='test'>Test Session</button>";
echo "</form>";

if ($_POST['action'] ?? false) {
    echo "<h2>Action Result</h2>";

    switch ($_POST['action']) {
        case 'login':
            $session->login(1, 'testuser', false);
            echo "<p>✅ Login completed</p>";
            break;
        case 'logout':
            $session->logout();
            echo "<p>✅ Logout completed</p>";
            break;
        case 'test':
            $session->put('test_value', 'test_data_' . time());
            echo "<p>✅ Test value set</p>";
            break;
    }

    echo "<p>Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "</p>";
    echo "<p>User ID: " . ($session->getUserId() ?? 'null') . "</p>";
    echo "<p>Username: " . ($session->getUsername() ?? 'null') . "</p>";
}

echo "<h2>Navigation Test</h2>";
echo "<p><a href='test-session-web.php'>Refresh this page</a></p>";
echo "<p><a href='/'>Go to Home</a></p>";
echo "<p><a href='/dashboard'>Go to Dashboard</a></p>";
