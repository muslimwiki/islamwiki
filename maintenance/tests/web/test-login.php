<?php

// Simple login test
echo "Testing login with admin user...\n";

// Start session
session_start();

// Test login credentials
$username = 'admin';
$password = 'password';

// Connect to database
$db = new PDO(
    'mysql:host=127.0.0.1;dbname=islamwiki;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Check if user exists
$stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ User found: " . $user['username'] . "\n";
    echo "User ID: " . $user['id'] . "\n";
    echo "Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "\n";

    // Verify password
    if (password_verify($password, $user['password'])) {
        echo "✅ Password is correct!\n";

        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['is_logged_in'] = true;

        echo "✅ Session data set!\n";
        echo "Session ID: " . session_id() . "\n";
        echo "Session data: " . print_r($_SESSION, true) . "\n";
    } else {
        echo "❌ Password is incorrect!\n";
    }
} else {
    echo "❌ User not found!\n";
}
