<?php
/**
 * Test Login and Settings Page
 * 
 * This page tests the login functionality and settings page access
 * 
 * @package IslamWiki\Test
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Initialize the application
$app = new Application(__DIR__ . '/..');
$app->bootstrap();

$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

// Create a test user if it doesn't exist
$stmt = $db->prepare("SELECT id FROM users WHERE username = 'testuser'");
$stmt->execute();
$user = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$user) {
    $stmt = $db->prepare("INSERT INTO users (username, email, password, display_name, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute(['testuser', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT), 'Test User']);
    $userId = $db->lastInsertId();
} else {
    $userId = $user['id'];
}

// Log in the user
$session->setUserId($userId);
$session->setUsername('testuser');

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Test Login and Settings</title>";
echo "<link rel='stylesheet' href='/css/safa.css'>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🧪 Test Login and Settings Page</h1>";

if ($session->isLoggedIn()) {
    $userId = $session->getUserId();
    $username = $session->getUsername();
    
    echo "<div class='alert alert-success'>";
    echo "<strong>✅ Successfully logged in!</strong><br>";
    echo "User ID: $userId<br>";
    echo "Username: $username<br>";
    echo "</div>";
    
    // Test settings page access
    echo "<h2>🔧 Testing Settings Page Access</h2>";
    
    try {
        $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
        $response = $settingsController->index();
        
        if ($response->getStatusCode() === 200) {
            echo "<div class='alert alert-success'>";
            echo "<strong>✅ Settings page is accessible!</strong><br>";
            echo "Status Code: " . $response->getStatusCode() . "<br>";
            echo "</div>";
            
            // Get the response body
            $body = $response->getBody()->getContents();
            
            // Check if it contains settings page elements
            if (strpos($body, 'settings-container') !== false) {
                echo "<div class='alert alert-success'>";
                echo "<strong>✅ Settings page template is working!</strong><br>";
                echo "Contains settings-container class<br>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning'>";
                echo "<strong>⚠️ Settings page template may have issues</strong><br>";
                echo "settings-container class not found<br>";
                echo "</div>";
            }
            
            // Check for skin selection elements
            if (strpos($body, 'skin-grid') !== false) {
                echo "<div class='alert alert-success'>";
                echo "<strong>✅ Skin selection is working!</strong><br>";
                echo "Contains skin-grid class<br>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning'>";
                echo "<strong>⚠️ Skin selection may have issues</strong><br>";
                echo "skin-grid class not found<br>";
                echo "</div>";
            }
            
            // Show a preview of the settings page
            echo "<h3>📋 Settings Page Preview</h3>";
            echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0; max-height: 400px; overflow-y: auto;'>";
            echo htmlspecialchars(substr($body, 0, 2000)) . "...";
            echo "</div>";
            
        } else {
            echo "<div class='alert alert-danger'>";
            echo "<strong>❌ Settings page returned error!</strong><br>";
            echo "Status Code: " . $response->getStatusCode() . "<br>";
            echo "Response: " . $response->getBody()->getContents() . "<br>";
            echo "</div>";
        }
        
    } catch (\Exception $e) {
        echo "<div class='alert alert-danger'>";
        echo "<strong>❌ Error accessing settings page:</strong><br>";
        echo $e->getMessage() . "<br>";
        echo "</div>";
    }
    
} else {
    echo "<div class='alert alert-danger'>";
    echo "<strong>❌ Failed to log in!</strong>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='/'>← Back to Home</a></p>";
echo "<p><a href='/login'>← Go to Login Page</a></p>";
echo "<p><a href='/settings'>← Go to Settings Page (should work when logged in)</a></p>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 