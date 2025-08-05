<?php
require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Testing Session Persistence Across Requests\n";
echo "=============================================\n\n";

// Simulate first request - login
echo "📝 Request 1: Login\n";
echo "-------------------\n";

// Create container and session
$container = new \IslamWiki\Core\Container\AsasContainer();
$sessionProvider = new \IslamWiki\Providers\SessionServiceProvider();
$sessionProvider->register($container);
$sessionProvider->boot($container);

$session = $container->get('session');

echo "- Session Status: " . session_status() . "\n";
echo "- Session Name: " . session_name() . "\n";
echo "- Session ID: " . session_id() . "\n";

// Simulate login
$session->login(1, 'testuser', false);
echo "- Login completed\n";
echo "- Is Logged In: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "\n";
echo "- User ID: " . ($session->getUserId() ?? 'null') . "\n";
echo "- Username: " . ($session->getUsername() ?? 'null') . "\n";

// Store session data
$sessionData = $_SESSION;
$sessionId = session_id();
$sessionName = session_name();

echo "- Session Data: " . print_r($sessionData, true) . "\n";
echo "- Session ID: $sessionId\n";
echo "- Session Name: $sessionName\n";

// Simulate session write
session_write_close();
echo "- Session written and closed\n\n";

// Simulate second request - check if still logged in
echo "📝 Request 2: Check Authentication\n";
echo "----------------------------------\n";

// Create new container and session (simulating new request)
$container2 = new \IslamWiki\Core\Container\AsasContainer();
$sessionProvider2 = new \IslamWiki\Providers\SessionServiceProvider();
$sessionProvider2->register($container2);
$sessionProvider2->boot($container2);

$session2 = $container2->get('session');

echo "- Session Status: " . session_status() . "\n";
echo "- Session Name: " . session_name() . "\n";
echo "- Session ID: " . session_id() . "\n";

// Check if still logged in
echo "- Is Logged In: " . ($session2->isLoggedIn() ? 'Yes' : 'No') . "\n";
echo "- User ID: " . ($session2->getUserId() ?? 'null') . "\n";
echo "- Username: " . ($session2->getUsername() ?? 'null') . "\n";

// Check session data
echo "- Session Data: " . print_r($_SESSION, true) . "\n";

// Test navigation to another page
echo "\n📝 Request 3: Navigate to Dashboard\n";
echo "-----------------------------------\n";

// Simulate navigation to dashboard
$container3 = new \IslamWiki\Core\Container\AsasContainer();
$sessionProvider3 = new \IslamWiki\Providers\SessionServiceProvider();
$sessionProvider3->register($container3);
$sessionProvider3->boot($container3);

$session3 = $container3->get('session');

echo "- Session Status: " . session_status() . "\n";
echo "- Session Name: " . session_name() . "\n";
echo "- Session ID: " . session_id() . "\n";

// Check if still logged in
echo "- Is Logged In: " . ($session3->isLoggedIn() ? 'Yes' : 'No') . "\n";
echo "- User ID: " . ($session3->getUserId() ?? 'null') . "\n";
echo "- Username: " . ($session3->getUsername() ?? 'null') . "\n";

// Check session data
echo "- Session Data: " . print_r($_SESSION, true) . "\n";

echo "\n✅ Session persistence test completed\n"; 