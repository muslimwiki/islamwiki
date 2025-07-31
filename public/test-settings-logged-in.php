<?php
/**
 * Test Settings Page with Logged-in User
 * 
 * This page tests the settings page functionality with a logged-in user
 * 
 * @package IslamWiki\Test
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Initialize the application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');
$db = $container->get('db');

// Simulate login for testing
$session->login(1, 'testuser', false); // User ID 1, username 'testuser', not admin

echo "<h1>Testing Settings Page with Logged-in User</h1>";

// Check if user is logged in
if ($session->isLoggedIn()) {
    $userId = $session->getUserId();
    $username = $session->getUsername();
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>✅ Logged in successfully!</strong><br>";
    echo "User ID: $userId<br>";
    echo "Username: $username<br>";
    echo "</div>";
    
    // Test User model lookup
    echo "<h2>Testing User Model Lookup</h2>";
    
    try {
        $user = \IslamWiki\Models\User::find($userId, $db);
        
        if ($user) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>✅ User found in database!</strong><br>";
            echo "User ID: " . $user->getAttribute('id') . "<br>";
            echo "Username: " . $user->getAttribute('username') . "<br>";
            echo "Email: " . $user->getAttribute('email') . "<br>";
            echo "Display Name: " . $user->getAttribute('display_name') . "<br>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>❌ User NOT found in database!</strong><br>";
            echo "User ID $userId does not exist in the users table.<br>";
            echo "This is why the navigation shows 'Sign In' instead of the user dropdown.";
            echo "</div>";
            
            // Check what users exist in the database
            echo "<h3>Existing Users in Database:</h3>";
            try {
                $stmt = $db->prepare("SELECT id, username, email FROM users LIMIT 10");
                $stmt->execute();
                $users = $stmt->fetchAll();
                
                if ($users) {
                    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<strong>Found " . count($users) . " users:</strong><br>";
                    foreach ($users as $userData) {
                        echo "- ID: {$userData['id']}, Username: {$userData['username']}, Email: {$userData['email']}<br>";
                    }
                    echo "</div>";
                } else {
                    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<strong>⚠️ No users found in database!</strong><br>";
                    echo "The users table is empty.";
                    echo "</div>";
                }
            } catch (\Exception $e) {
                echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>❌ Error checking users table:</strong><br>";
                echo $e->getMessage();
                echo "</div>";
            }
        }
        
    } catch (\Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ Error looking up user:</strong><br>";
        echo $e->getMessage();
        echo "</div>";
    }
    
    // Test accessing settings page
    echo "<h2>Testing Settings Page Access</h2>";
    
    // Create a mock request to test the settings controller
    try {
        $settingsController = new \IslamWiki\Http\Controllers\SettingsController(
            $container->get('db'),
            $container
        );
        
        // Call the index method
        $response = $settingsController->index();
        
        echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ Settings page accessible!</strong><br>";
        echo "Status Code: " . $response->getStatusCode() . "<br>";
        echo "Content Type: " . implode(', ', $response->getHeader('Content-Type')) . "<br>";
        echo "</div>";
        
        // Get the response body
        $body = $response->getBody()->getContents();
        
        // Check if the response contains user navigation elements
        if (strpos($body, 'user-dropdown') !== false) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>✅ User dropdown found in response!</strong><br>";
            echo "The settings page should show the logged-in user in the navigation.";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>❌ User dropdown NOT found in response!</strong><br>";
            echo "The settings page might not be showing the logged-in user properly.";
            echo "</div>";
        }
        
        // Check if it contains "Sign In" (which would be wrong for logged-in users)
        if (strpos($body, 'Sign In') !== false) {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>⚠️ Warning: 'Sign In' found in response!</strong><br>";
            echo "This suggests the navigation is still showing the login option instead of the user dropdown.";
            echo "</div>";
        }
        
        // Show a snippet of the response for debugging
        echo "<h3>Response Preview (first 1000 characters):</h3>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
        echo htmlspecialchars(substr($body, 0, 1000)) . "...";
        echo "</pre>";
        
        // Check for specific navigation elements
        echo "<h3>Navigation Element Analysis:</h3>";
        
        $checks = [
            'user-dropdown' => 'User dropdown container',
            'user-avatar-btn' => 'User avatar button',
            'user-name' => 'User name display',
            'Sign In' => 'Sign In link (should NOT be present)',
            'Get Started' => 'Get Started button (should NOT be present)',
            'user-menu' => 'User menu container',
            'dropdown-item' => 'Dropdown menu items'
        ];
        
        foreach ($checks as $element => $description) {
            $found = strpos($body, $element) !== false;
            $status = $found ? '✅' : '❌';
            $color = $found ? '#d4edda' : '#f8d7da';
            
            // Special handling for elements that should NOT be present
            if (in_array($element, ['Sign In', 'Get Started'])) {
                $status = $found ? '❌' : '✅';
                $color = $found ? '#f8d7da' : '#d4edda';
                $description .= ' (should NOT be present)';
            }
            
            echo "<div style='background: $color; padding: 5px; border-radius: 3px; margin: 2px 0;'>";
            echo "$status $description: " . ($found ? 'FOUND' : 'NOT FOUND');
            echo "</div>";
        }
        
    } catch (\Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ Error testing settings page:</strong><br>";
        echo $e->getMessage();
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Failed to log in!</strong>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='/test-user-settings.php'>← Back to User Settings Test</a></p>";
echo "<p><a href='/'>← Back to Home</a></p>";
?> 