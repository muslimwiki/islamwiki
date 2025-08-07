<?php

/**
 * Update User Skin
 *
 * Script to update user's skin for testing.
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

// Get database connection
$db = $container->get('db');

echo "=== Update User Skin ===\n\n";

// Update user ID 1 to Bismillah
$userId = 1;
$settings = ['skin' => 'Bismillah'];

try {
    $stmt = $db->prepare("UPDATE user_settings SET settings = ? WHERE user_id = ?");
    $stmt->execute([json_encode($settings), $userId]);

    echo "✅ Updated user ID $userId to Bismillah\n";

    // Verify the update
    $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($result) {
        $settings = json_decode($result['settings'], true);
        echo "📋 User's current skin: " . ($settings['skin'] ?? 'none') . "\n";
    }
} catch (\Throwable $e) {
    echo "❌ Error updating user skin: " . $e->getMessage() . "\n";
}

echo "\n=== Update Complete ===\n";
