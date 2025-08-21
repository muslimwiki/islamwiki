<?php

/**
 * Simple test to bypass session issues and test settings page
 */

// Load the application
require_once __DIR__ . '/../vendor/autoload.php';

echo "<h1>🔍 Simple Settings Test</h1>";

try {
    // Initialize the application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    echo "<h2>1. Application initialized</h2>";
    echo "<p>✓ Application created successfully</p>";

    // Get the container
    $container = $app->getContainer();
    echo "<h2>2. Container obtained</h2>";
    echo "<p>✓ Container retrieved successfully</p>";

    // Get the database connection
    $db = $container->get('db');
    echo "<h2>3. Database connection obtained</h2>";
    echo "<p>✓ Database connection retrieved successfully</p>";

    // Create SettingsController directly
    $settingsController = new \IslamWiki\Http\Controllers\SettingsController($db, $container);
    echo "<h2>4. SettingsController created</h2>";
    echo "<p>✓ SettingsController created successfully</p>";

    // Mock the session to bypass authentication
    $sessionManager = $container->get('session');

    // Manually set session data to simulate logged in user
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['is_admin'] = false;
    $_SESSION['logged_in_at'] = time();

    echo "<h2>5. Session data set manually</h2>";
    echo "<p>✓ Session data set to simulate logged in user</p>";

    // Call the index method and capture the response
    echo "<h2>6. Calling SettingsController::index()</h2>";
    try {
        $response = $settingsController->index();
        echo "<p>✓ SettingsController::index() called successfully</p>";
        echo "<p>Response status: " . $response->getStatusCode() . "</p>";
        echo "<p>Response body length: " . strlen($response->getBody()) . " characters</p>";

        // Get the body content
        $body = $response->getBody();

        // Check for specific content
        echo "<h3>7. Content Analysis</h3>";

        if (strpos($body, 'skinOptions') !== false) {
            echo "<p>✅ Response contains 'skinOptions'</p>";
        } else {
            echo "<p>❌ Response does not contain 'skinOptions'</p>";
        }

        if (strpos($body, 'Bismillah') !== false) {
            echo "<p>✅ Response contains 'Bismillah' skin</p>";
        } else {
            echo "<p>❌ Response does not contain 'Bismillah' skin</p>";
        }

        if (strpos($body, 'GreenSkin') !== false) {
            echo "<p>✅ Response contains 'GreenSkin' skin</p>";
        } else {
            echo "<p>❌ Response does not contain 'GreenSkin' skin</p>";
        }

        if (strpos($body, 'DEBUG: skinOptions count') !== false) {
            echo "<p>✅ Response contains debug comments</p>";
        } else {
            echo "<p>❌ Response does not contain debug comments</p>";
        }

        // Look for the skin grid section
        if (strpos($body, 'skin-grid') !== false) {
            echo "<p>✅ Response contains 'skin-grid' section</p>";
        } else {
            echo "<p>❌ Response does not contain 'skin-grid' section</p>";
        }

        // Look for skin cards
        if (strpos($body, 'skin-card') !== false) {
            echo "<p>✅ Response contains 'skin-card' elements</p>";
        } else {
            echo "<p>❌ Response does not contain 'skin-card' elements</p>";
        }

        // Show the skin grid section specifically
        $skinGridStart = strpos($body, 'skin-grid');
        if ($skinGridStart !== false) {
            $skinGridEnd = strpos($body, '</div>', $skinGridStart);
            if ($skinGridEnd !== false) {
                $skinGridSection = substr($body, $skinGridStart, $skinGridEnd - $skinGridStart + 6);
                echo "<h3>8. Skin Grid Section:</h3>";
                echo "<pre>" . htmlspecialchars($skinGridSection) . "</pre>";
            }
        }
    } catch (\Throwable $e) {
        echo "<p>❌ Error calling SettingsController::index(): " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} catch (\Throwable $e) {
    echo "<h2>❌ Error occurred</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
