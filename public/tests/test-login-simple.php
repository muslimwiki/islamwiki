<?php
/**
 * Simple Login Test
 * 
 * Test the login functionality directly.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

// Start session
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

// Test login
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
    
    echo "✅ Login successful!\n";
    echo "User ID: {$user['id']}\n";
    echo "Username: {$user['username']}\n";
    echo "Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";
    echo "Session ID: " . session_id() . "\n";
    
    // Test page creation access
    echo "\nTesting page creation access...\n";
    
    // Simulate the authentication check
    if (isset($_SESSION['user_id'])) {
        echo "✅ User is authenticated\n";
        echo "✅ Can access page creation\n";
    } else {
        echo "❌ User is not authenticated\n";
    }
    
} else {
    echo "❌ Login failed\n";
    if (!$user) {
        echo "User not found\n";
    } else {
        echo "Invalid password\n";
    }
} 