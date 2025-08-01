<?php
/**
 * Test SkinManager Direct
 * 
 * Test to call the SkinManager method directly.
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

// Get SkinManager
$skinManager = $container->get('skin.manager');

echo "=== Test SkinManager Direct ===\n\n";

// Test the method directly
$userId = 1;
echo "Testing getActiveSkinNameForUser for user ID: $userId\n";

$result = $skinManager->getActiveSkinNameForUser($userId);
echo "Result: $result\n";

// Test with user ID 2
$userId2 = 2;
echo "\nTesting getActiveSkinNameForUser for user ID: $userId2\n";

$result2 = $skinManager->getActiveSkinNameForUser($userId2);
echo "Result: $result2\n";

// Test global skin
echo "\nTesting getActiveSkinName (global):\n";
$globalSkin = $skinManager->getActiveSkinName();
echo "Global skin: $globalSkin\n";

echo "\n=== Test Complete ===\n"; 