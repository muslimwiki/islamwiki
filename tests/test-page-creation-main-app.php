<?php

/**
 * Test Page Creation with Main App Session Config
 *
 * This script uses the exact same session configuration as the main application.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

// Use the same session configuration as the main app
$sessionPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// Set session name before any session operations
session_name('islamwiki_session');

// Start session manually to ensure proper initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

echo "=== Test Page Creation with Main App Session Config ===\n\n";

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
    echo "   Session Path: " . session_save_path() . "\n";
} else {
    echo "   ❌ Login failed\n";
    exit(1);
}

// Step 2: Test Session (like the main app does)
echo "\n2. Testing Session authentication...\n";

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

    // Simulate the PageController authentication check
    echo "\n3. Simulating PageController authentication check...\n";

    if ($sessionManager->isLoggedIn()) {
        echo "   ✅ User is authenticated (Session)\n";

        // Check if user can create pages (basic check)
        $canCreatePages = true; // All authenticated users can create pages
        echo "   Can create pages: " . ($canCreatePages ? 'Yes' : 'No') . "\n";

        if ($canCreatePages) {
            echo "   ✅ User has permission to create pages\n";
            echo "   ✅ Page creation should be accessible\n";
        } else {
            echo "   ❌ User does not have permission to create pages\n";
        }
    } else {
        echo "   ❌ User is not authenticated (Session)\n";
        echo "   ❌ Page creation should redirect to login\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Step 3: Test the actual web interface
echo "\n4. Testing web interface...\n";

// Test page creation URL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/pages/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
curl_setopt($ch, CURLOPT_COOKIE, 'islamwiki_session=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "   HTTP Status Code: $httpCode\n";
if ($location) {
    echo "   Redirect Location: $location\n";
}

if ($httpCode === 200) {
    echo "   ✅ Page creation accessible\n";
} elseif ($httpCode === 302) {
    echo "   ❌ Page creation redirecting (likely to login)\n";
    echo "   This suggests the main app is still not seeing the session\n";
} else {
    echo "   ❓ Unexpected response: $httpCode\n";
}

echo "\n=== Test Complete ===\n";
echo "\nSession Information:\n";
echo "Session Name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Data: " . json_encode($_SESSION) . "\n";
