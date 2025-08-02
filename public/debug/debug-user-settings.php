<?php
/**
 * Debug User Settings
 * 
 * Debug script to check user settings in the database.
 * 
 * @package IslamWiki\Tests
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get services
$session = $container->get('session');
$db = $container->get('db');

echo "=== Debug User Settings ===\n\n";

// Check if user is logged in
if ($session->isLoggedIn()) {
    $userId = $session->getUserId();
    echo "User logged in - ID: $userId\n\n";
    
    // Check user settings in database
    $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    if ($result) {
        echo "User settings found:\n";
        $settings = json_decode($result['settings'], true);
        echo "Raw settings: " . $result['settings'] . "\n";
        echo "Decoded settings: " . print_r($settings, true) . "\n";
        
        if (isset($settings['skin'])) {
            echo "Skin setting: " . $settings['skin'] . "\n";
        } else {
            echo "No skin setting found\n";
        }
    } else {
        echo "No user settings found for user ID: $userId\n";
    }
    
} else {
    echo "No user logged in\n";
}

// Check all user settings
echo "\nAll user settings in database:\n";
$stmt = $db->prepare("SELECT user_id, settings FROM user_settings");
$stmt->execute();
$results = $stmt->fetchAll();

if ($results) {
    foreach ($results as $row) {
        echo "User ID: " . $row['user_id'] . "\n";
        echo "Settings: " . $row['settings'] . "\n";
        $settings = json_decode($row['settings'], true);
        echo "Decoded: " . print_r($settings, true) . "\n\n";
    }
} else {
    echo "No user settings found in database\n";
}

echo "=== Debug Complete ===\n"; 