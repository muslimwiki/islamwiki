<?php

/**
 * Debug Authentication for Page Creation
 *
 * This script tests the authentication system in the context of page creation.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

// Start session with the same configuration as the main app
session_name('islamwiki_session');
session_start();

echo "=== Debug Authentication for Page Creation ===\n\n";

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

echo "✅ Database connection successful\n";

// Test 1: Check session data
echo "\n1. Session Information:\n";
echo "   Session Name: " . session_name() . "\n";
echo "   Session ID: " . session_id() . "\n";
echo "   Session Status: " . session_status() . "\n";
echo "   Session Data: " . json_encode($_SESSION) . "\n";

// Test 2: Check if user is logged in
echo "\n2. Authentication Check:\n";
$isLoggedIn = isset($_SESSION['user_id']);
echo "   Is Logged In: " . ($isLoggedIn ? 'Yes' : 'No') . "\n";

if ($isLoggedIn) {
    echo "   User ID: " . $_SESSION['user_id'] . "\n";
    echo "   Username: " . ($_SESSION['username'] ?? 'null') . "\n";
    echo "   Is Admin: " . ($_SESSION['is_admin'] ? 'Yes' : 'No') . "\n";

    // Get user data from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        echo "   User found in database: " . $user['username'] . "\n";
    } else {
        echo "   ❌ User not found in database\n";
    }
} else {
    echo "   ❌ No user logged in\n";
}

// Test 3: Simulate PageController authentication check
echo "\n3. Simulating PageController Authentication:\n";

try {
    // Create a mock container and session manager
    class MockContainer
    {
        private $session;

        public function __construct($session)
        {
            $this->session = $session;
        }

        public function get($service)
        {
            if ($service === 'session') {
                return $this->session;
            }
            throw new Exception("Service not found: $service");
        }
    }

    class MockSession
    {
        private $sessionData;

        public function __construct($sessionData)
        {
            $this->sessionData = $sessionData;
        }

        public function isLoggedIn(): bool
        {
            return isset($this->sessionData['user_id']);
        }

        public function getUserId(): ?int
        {
            return $this->sessionData['user_id'] ?? null;
        }

        public function getUsername(): ?string
        {
            return $this->sessionData['username'] ?? null;
        }

        public function isAdmin(): bool
        {
            return $this->sessionData['is_admin'] ?? false;
        }
    }

    $sessionManager = new MockSession($_SESSION);
    $container = new MockContainer($sessionManager);

    // Create AuthManager
    $authManager = new \IslamWiki\Core\Auth\AuthManager($sessionManager, $pdo);

    echo "   AuthManager created successfully\n";
        $temp_e68f181a = ($authManager->check() ? 'true' : 'false') . "\n";
        echo "   AuthManager::check(): " . $temp_e68f181a;
    echo "   AuthManager::id(): " . ($authManager->id() ?? 'null') . "\n";
        $temp_edf989df = ($authManager->username() ?? 'null') . "\n";
        echo "   AuthManager::username(): " . $temp_edf989df;
        $temp_5938818e = ($authManager->isAdmin() ? 'true' : 'false') . "\n";
        echo "   AuthManager::isAdmin(): " . $temp_5938818e;
        $temp_2ab7d887 = ($authManager->can('create_pages') ? 'true' : 'false') . "\n";
        echo "   AuthManager::can('create_pages'): " . $temp_2ab7d887;
} catch (Exception $e) {
    echo "   ❌ Error creating AuthManager: " . $e->getMessage() . "\n";
}

// Test 4: Test page creation access
echo "\n4. Page Creation Access Test:\n";

if ($isLoggedIn) {
    echo "   ✅ User is logged in, should be able to access page creation\n";

    // Test the actual page creation URL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/pages/create');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
    curl_setopt($ch, CURLOPT_COOKIE, 'islamwiki_session=' . session_id());

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "   HTTP Status Code: $httpCode\n";

    if ($httpCode === 200) {
        echo "   ✅ Page creation accessible\n";
    } elseif ($httpCode === 302) {
        echo "   ❌ Page creation redirecting (likely to login)\n";
    } else {
        echo "   ❓ Unexpected response: $httpCode\n";
    }
} else {
    echo "   ❌ User not logged in, page creation should redirect to login\n";
}

echo "\n=== Debug Complete ===\n";
