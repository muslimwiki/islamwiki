<?php

/**
 * Test Page Creation When Logged In
 *
 * This script logs in a user and then tests page creation access.
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

echo "=== Test Page Creation When Logged In ===\n\n";

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

// Step 2: Test page creation access
echo "\n2. Testing page creation access...\n";

// Create a simple test to simulate the PageController authentication check
$isLoggedIn = isset($_SESSION['user_id']);
echo "   Is logged in: " . ($isLoggedIn ? 'Yes' : 'No') . "\n";

if ($isLoggedIn) {
    echo "   ✅ User is authenticated\n";

    // Test if user can create pages (basic check)
    $canCreatePages = true; // All authenticated users can create pages
    echo "   Can create pages: " . ($canCreatePages ? 'Yes' : 'No') . "\n";

    if ($canCreatePages) {
        echo "   ✅ User has permission to create pages\n";
    } else {
        echo "   ❌ User does not have permission to create pages\n";
    }
} else {
    echo "   ❌ User is not authenticated\n";
}

// Step 3: Test the actual web interface
echo "\n3. Testing web interface...\n";

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
    echo "   This suggests the session is not being shared properly\n";
} else {
    echo "   ❓ Unexpected response: $httpCode\n";
}

// Step 4: Test with a simple curl that includes the session cookie
echo "\n4. Testing with session cookie...\n";

$cookieFile = tempnam('/tmp', 'curl_cookies');
file_put_contents($cookieFile, "islamwiki_session=" . session_id());

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/pages/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

unlink($cookieFile);

echo "   HTTP Status Code: $httpCode\n";
if ($location) {
    echo "   Redirect Location: $location\n";
}

if ($httpCode === 200) {
    echo "   ✅ Page creation accessible with session cookie\n";
} elseif ($httpCode === 302) {
    echo "   ❌ Still redirecting with session cookie\n";
} else {
    echo "   ❓ Unexpected response: $httpCode\n";
}

echo "\n=== Test Complete ===\n";
echo "\nSession Information:\n";
echo "Session Name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: " . json_encode($_SESSION) . "\n";
