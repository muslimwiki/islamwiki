<?php
/**
 * Test Skin Query
 * 
 * Simple test to check the database query directly.
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

// Get database connection
$db = $container->get('db');

echo "=== Test Skin Query ===\n\n";

// Test the exact query from SkinManager
$userId = 1;
echo "Testing query for user ID: $userId\n";

$stmt = $db->prepare("
    SELECT settings FROM user_settings 
    WHERE user_id = ?
");
$stmt->execute([$userId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Query result: " . print_r($result, true) . "\n";

if ($result) {
    $settings = json_decode($result['settings'], true) ?? [];
    echo "Decoded settings: " . print_r($settings, true) . "\n";
    
    $userSkin = $settings['skin'] ?? null;
    echo "User skin: " . ($userSkin ?? 'null') . "\n";
    
    if ($userSkin) {
        $lowercaseSkin = strtolower($userSkin);
        echo "Lowercase skin: $lowercaseSkin\n";
    }
} else {
    echo "No result found\n";
}

echo "\n=== Test Complete ===\n"; 