<?php
session_start();

// Simple authentication function
function is_authenticated() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Simple login function
function login($username, $password) {
    // TODO: Replace with proper authentication and hashing in production
    $valid_username = 'admin';
    $valid_password = 'admin123';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    return false;
}

// Logout function
function logout() {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

// Protect admin pages
function require_auth() {
    if (!is_authenticated() && basename($_SERVER['PHP_SELF']) !== 'login.php') {
        header('Location: login.php');
        exit;
    }
}
?>
