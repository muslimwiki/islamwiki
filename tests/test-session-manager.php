<?php

/**
 * Test Session Authentication
 *
 * This script tests what the Session sees for authentication.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

// Start session with the same configuration as the main app
session_name('islamwiki_session');
session_start();

// Initialize database connection
$pdo = new PDO(
    "mysql:host={$wgDBserver};dbname={$wgDBname};charset=utf8mb4",
    $wgDBuser,
    $wgDBpassword,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

echo "=== Test Session Authentication ===\n\n";

// Step 1: Login the user
echo "1. Logging in user...\n";
$username = 'testuser';
$password = 'password123';

// Find user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Login successful
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = $user['is_admin'];

    echo "   ✅ Login successful\n";
    echo "   User ID: {$user['id']}\n";
    echo "   Username: {$user['username']}\n";
    echo "   Session ID: " . session_id() . "\n";
} else {
    echo "   ❌ Login failed\n";
    exit(1);
}

// Step 2: Test Session
echo "\n2. Testing Session...\n";

try {
    // Create Session with the same configuration as the main app
    $config = [
        'name' => 'islamwiki_session',
        'lifetime' => 86400,
        'path' => '/',
        'secure' => false,
        'http_only' => true,
        'same_site' => 'Lax',
    ];

    $sessionManager = new \IslamWiki\Core\Session\Session($config);

    echo "   Session created successfully\n";
    echo "   Session::isLoggedIn(): " . ($sessionManager->isLoggedIn() ? 'true' : 'false') . "\n";
    echo "   Session::getUserId(): " . ($sessionManager->getUserId() ?? 'null') . "\n";
    echo "   Session::getUsername(): " . ($sessionManager->getUsername() ?? 'null') . "\n";
    echo "   Session::isAdmin(): " . ($sessionManager->isAdmin() ? 'true' : 'false') . "\n";

    // Test AuthManager with Session
    echo "\n3. Testing AuthManager with Session...\n";

    // Create a mock Connection class for testing
    class MockConnection
    {
        private $pdo;

        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }

        public function select(string $query, array $params = []): array
        {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }
    }

    $connection = new MockConnection($pdo);
    $authManager = new \IslamWiki\Core\Auth\AuthManager($sessionManager, $connection);

    echo "   AuthManager created successfully\n";
    echo "   AuthManager::check(): " . ($authManager->check() ? 'true' : 'false') . "\n";
    echo "   AuthManager::id(): " . ($authManager->id() ?? 'null') . "\n";
    echo "   AuthManager::username(): " . ($authManager->username() ?? 'null') . "\n";
    echo "   AuthManager::isAdmin(): " . ($authManager->isAdmin() ? 'true' : 'false') . "\n";
    echo "   AuthManager::can('create_pages'): " . ($authManager->can('create_pages') ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nSession Information:\n";
echo "Session Name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: " . json_encode($_SESSION) . "\n";
