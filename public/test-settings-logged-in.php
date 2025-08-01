<?php
require_once __DIR__ . '/../src/Core/Application.php';

// Start the application
$app = new IslamWiki\Core\Application(__DIR__ . '/..');
$app->bootstrap();

// Simulate login
$container = $app->getContainer();
$session = $container->get('session');

// Create a test user if it doesn't exist
$db = $container->get('database');
$stmt = $db->prepare("SELECT id FROM users WHERE username = 'testuser'");
$stmt->execute();
$user = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$user) {
    $stmt = $db->prepare("INSERT INTO users (username, email, password, display_name, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute(['testuser', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT), 'Test User']);
    $userId = $db->lastInsertId();
} else {
    $userId = $user['id'];
}

// Log in the user
$session->setUserId($userId);
$session->setUsername('testuser');

echo "Logged in as testuser (ID: $userId)\n";

// Now test the settings page
$request = new IslamWiki\Core\Http\Request('GET', '/settings', [], [], []);
$response = $app->handle($request);

echo "Settings page response status: " . $response->getStatusCode() . "\n";
echo "Settings page content length: " . strlen($response->getBody()) . "\n";

if ($response->getStatusCode() === 200) {
    echo "✅ Settings page is working!\n";
    echo "First 500 characters of response:\n";
    echo substr($response->getBody(), 0, 500) . "\n";
} else {
    echo "❌ Settings page returned status: " . $response->getStatusCode() . "\n";
    echo "Response body:\n";
    echo $response->getBody() . "\n";
} 