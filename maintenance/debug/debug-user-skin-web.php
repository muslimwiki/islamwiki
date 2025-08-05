<?php
/**
 * Debug User Skin Settings (Web Version)
 * 
 * This script checks what skin is stored in the database for the current user.
 * 
 * @package IslamWiki
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

// Define the application's base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer's autoloader
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('Autoload file not found. Please run `composer install` to install the project dependencies.');
}

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Database\Connection;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug User Skin Settings</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Debug User Skin Settings</h1>
        <hr>";

try {
    // Initialize the application
    $app = new NizamApplication(BASE_PATH);
    echo "<p class='success'>✅ Application created successfully</p>";
    
    // Get database connection
    $db = $app->getContainer()->get('db');
    echo "<p class='success'>✅ Database connection established</p>";
    
    // Get session
    $session = $app->getContainer()->get('session');
    echo "<p class='success'>✅ Session manager loaded</p>";
    
    if (!$session->isLoggedIn()) {
        echo "<p class='error'>❌ User is not logged in</p>";
        echo "<p>Please <a href='/login'>log in</a> first.</p>";
        echo "</div></body></html>";
        exit;
    }
    
    $userId = $session->getUserId();
    $username = $session->getUsername();
    echo "<p class='success'>✅ User logged in: <strong>$username</strong> (ID: $userId)</p>";
    
    echo "<div class='section'>
        <h2>📋 Database Settings</h2>";
    
    // Check user settings in database
    $stmt = $db->prepare("
        SELECT settings FROM user_settings 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($result) {
        $settings = json_decode($result['settings'], true) ?? [];
        echo "<p class='info'>📋 User settings found:</p>";
        echo "<p><strong>Raw settings:</strong></p>";
        echo "<pre>" . htmlspecialchars($result['settings']) . "</pre>";
        echo "<p><strong>Decoded settings:</strong></p>";
        echo "<pre>" . htmlspecialchars(print_r($settings, true)) . "</pre>";
        
        if (isset($settings['skin'])) {
            echo "<p class='success'>🎨 Stored skin: <strong>" . htmlspecialchars($settings['skin']) . "</strong></p>";
        } else {
            echo "<p class='warning'>⚠️ No skin setting found in database</p>";
        }
    } else {
        echo "<p class='error'>❌ No user settings found in database</p>";
    }
    
    echo "</div><div class='section'>
        <h2>🎨 SkinManager Results</h2>";
    
    // Check what SkinManager thinks is active
    $skinManager = $app->getContainer()->get('skin.manager');
    $activeSkin = $skinManager->getActiveSkinForUser($userId);
    $activeSkinName = $skinManager->getActiveSkinNameForUser($userId);
    
    echo "<p class='info'>🎨 SkinManager results:</p>";
    echo "<p><strong>Active skin object:</strong> " . ($activeSkin ? get_class($activeSkin) : 'null') . "</p>";
    echo "<p><strong>Active skin name:</strong> <span class='success'>" . htmlspecialchars($activeSkinName) . "</span></p>";
    
    if ($activeSkin) {
        echo "<p><strong>Skin name:</strong> " . htmlspecialchars($activeSkin->getName()) . "</p>";
        echo "<p><strong>Skin version:</strong> " . htmlspecialchars($activeSkin->getVersion()) . "</p>";
    }
    
    echo "</div><div class='section'>
        <h2>📚 Available Skins</h2>";
    
    // Check available skins
    $availableSkins = $skinManager->getSkins();
    echo "<p class='info'>📚 Available skins:</p>";
    foreach ($availableSkins as $name => $skin) {
        $isActive = (strtolower($name) === strtolower($activeSkinName));
        $status = $isActive ? " <span class='success'>(ACTIVE)</span>" : "";
        echo "<p>- <strong>" . htmlspecialchars($name) . "</strong>: " . htmlspecialchars($skin->getName()) . " (v" . htmlspecialchars($skin->getVersion()) . ")$status</p>";
    }
    
    echo "</div><div class='section'>
        <h2>🔧 Settings Controller Test</h2>";
    
    // Test what SettingsController would show
    $userSettings = [];
    if ($result) {
        $userSettings = json_decode($result['settings'], true) ?? [];
    }
    $userActiveSkin = $userSettings['skin'] ?? 'Bismillah'; // Default to Bismillah
    
    echo "<p><strong>SettingsController would show:</strong></p>";
    echo "<p>User active skin from database: <span class='success'>" . htmlspecialchars($userActiveSkin) . "</span></p>";
    
    $skinOptions = [];
    foreach ($availableSkins as $name => $skin) {
        $skinOptions[$name] = [
            'name' => $skin->getName(),
            'version' => $skin->getVersion(),
            'author' => $skin->getAuthor(),
            'description' => $skin->getDescription(),
            'active' => strtolower($name) === strtolower($userActiveSkin)
        ];
    }
    
    echo "<p><strong>Skin options that would be passed to template:</strong></p>";
    foreach ($skinOptions as $name => $skin) {
        $activeStatus = $skin['active'] ? " <span class='success'>(ACTIVE)</span>" : "";
        echo "<p>- <strong>" . htmlspecialchars($name) . "</strong>$activeStatus</p>";
    }
    
    echo "</div>";
    
} catch (\Throwable $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div></body></html>"; 