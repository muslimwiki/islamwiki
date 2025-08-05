<?php
/**
 * Debug Skin Method
 * 
 * Debug script to test the getActiveSkinNameForUser method directly.
 * 
 * @package IslamWiki\Tests
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;

// Initialize application
$app = new NizamApplication(__DIR__ . '/..');
$container = $app->getContainer();

// Get services
$skinManager = $container->get('skin.manager');
$db = $container->get('db');

echo "=== Debug Skin Method ===\n\n";

// Test with user ID 1 (should have GreenSkin)
echo "Testing with user ID 1:\n";
$skin1 = $skinManager->getActiveSkinNameForUser(1);
echo "  Result: $skin1\n";

// Test with user ID 2 (should have Bismillah)
echo "\nTesting with user ID 2:\n";
$skin2 = $skinManager->getActiveSkinNameForUser(2);
echo "  Result: $skin2\n";

// Test with non-existent user ID
echo "\nTesting with user ID 999:\n";
$skin3 = $skinManager->getActiveSkinNameForUser(999);
echo "  Result: $skin3\n";

// Check what's in the database directly
echo "\nChecking database directly:\n";
$stmt = $db->prepare("SELECT user_id, settings FROM user_settings WHERE user_id IN (1, 2)");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    echo "  User ID: {$row['user_id']}\n";
    echo "  Settings: {$row['settings']}\n";
    $settings = json_decode($row['settings'], true);
    if (isset($settings['skin'])) {
        echo "  Skin: {$settings['skin']}\n";
        echo "  Lowercase: " . strtolower($settings['skin']) . "\n";
    }
    echo "\n";
}

echo "=== Debug Complete ===\n"; 