<?php
// Admin Header
if (!defined('ADMIN_PATH')) {
    define('ADMIN_PATH', realpath(__DIR__ . '/..'));
    require_once ADMIN_PATH . '/../vendor/autoload.php';
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Basic authentication check (you should enhance this)
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: /admin/login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin Panel'); ?> - IslamWiki</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <div class="logo">
                <h2>IslamWiki</h2>
                <span>Admin Panel</span>
            </div>
            <ul class="nav-links">
                <li class="active"><a href="/admin/"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/admin/logs"><i class="fas fa-clipboard-list"></i> Logs</a></li>
                <li><a href="/admin/debug"><i class="fas fa-bug"></i> Debug</a></li>
                <li><a href="/admin/tests"><i class="fas fa-vial"></i> Tests</a></li>
                <li><a href="/admin/settings"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="divider"></li>
                <li><a href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
        <main class="main-content">
