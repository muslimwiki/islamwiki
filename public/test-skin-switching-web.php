<?php
/**
 * Test Skin Switching Web Interface
 * 
 * This script tests the skin switching functionality in a web context
 * with a simulated logged-in user.
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

use IslamWiki\Core\Application;
use IslamWiki\Skins\SkinManager;

// Initialize the application
$app = new Application(BASE_PATH);

// Simulate a logged-in user
$session = $app->getContainer()->get('session');
$session->login(1, 'testuser', false);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Skin Switching Test - IslamWiki</title>
    <link rel='stylesheet' href='/css/safa.css'>
    <style>
        .test-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .skin-test { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .skin-preview { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-secondary { background: var(--secondary-color); color: white; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class='test-container'>
        <h1>🧪 Skin Switching Test</h1>
        <p>This page tests the skin switching functionality with the Safa CSS framework.</p>";

try {
    // Get the skin manager
    $skinManager = $app->getContainer()->get('skin.manager');
    
    echo "<h2>📋 Available Skins</h2>";
    $availableSkins = $skinManager->getSkins();
    foreach ($availableSkins as $name => $skin) {
        echo "<div class='skin-test'>
            <h3>{$skin->getName()} (v{$skin->getVersion()})</h3>
            <p><strong>Author:</strong> {$skin->getAuthor()}</p>
            <p><strong>Description:</strong> {$skin->getDescription()}</p>
            <div class='skin-preview'>
                <h4>Preview with {$skin->getName()} skin:</h4>
                <div class='btn btn-primary'>Primary Button</div>
                <div class='btn btn-secondary'>Secondary Button</div>
                <div class='card'>
                    <h5>Sample Card</h5>
                    <p>This is how content looks with the {$skin->getName()} skin.</p>
                </div>
            </div>
            <button class='btn btn-primary' onclick='switchSkin(\"{$name}\")'>Switch to {$skin->getName()}</button>
        </div>";
    }
    
    // Test current user's skin
    $userId = $session->getUserId();
    $userActiveSkin = $skinManager->getActiveSkinForUser($userId);
    $userActiveSkinName = $skinManager->getActiveSkinNameForUser($userId);
    
    echo "<h2>👤 Current User Settings</h2>
        <p><strong>User ID:</strong> {$userId}</p>
        <p><strong>Active Skin:</strong> " . ($userActiveSkin ? $userActiveSkin->getName() : 'None') . "</p>
        <p><strong>Active Skin Name:</strong> {$userActiveSkinName}</p>";
    
    // Test skin switching via API
    echo "<h2>🔄 Skin Switching Test</h2>";
    
    if (isset($_POST['test_skin'])) {
        $skinName = $_POST['test_skin'];
        $result = $skinManager->setActiveSkin($skinName);
        
        if ($result) {
            echo "<p class='success'>✅ Successfully switched to {$skinName}</p>";
        } else {
            echo "<p class='error'>❌ Failed to switch to {$skinName}</p>";
        }
    }
    
    echo "<form method='POST'>
        <select name='test_skin'>";
    foreach (array_keys($availableSkins) as $skinName) {
        $selected = ($skinName === $userActiveSkinName) ? 'selected' : '';
        echo "<option value='{$skinName}' {$selected}>{$skinName}</option>";
    }
    echo "</select>
        <button type='submit' class='btn btn-primary'>Test Switch</button>
    </form>";
    
    // Display current skin CSS variables
    echo "<h2>🎨 Current Skin CSS Variables</h2>
        <div class='skin-preview'>
            <p><strong>Primary Color:</strong> <span style='color: var(--primary-color);'>var(--primary-color)</span></p>
            <p><strong>Secondary Color:</strong> <span style='color: var(--secondary-color);'>var(--secondary-color)</span></p>
            <p><strong>Background Color:</strong> <span style='background: var(--background-color); padding: 5px;'>var(--background-color)</span></p>
            <p><strong>Text Primary:</strong> <span style='color: var(--text-primary);'>var(--text-primary)</span></p>
        </div>";
    
} catch (\Throwable $e) {
    echo "<p class='error'>❌ Error during skin switching test: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<script>
function switchSkin(skinName) {
    if (confirm('Switch to ' + skinName + ' skin?')) {
        fetch('/settings/skin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ skin: skinName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Skin switched successfully! Please refresh the page to see changes.');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}
</script>
</body>
</html>"; 