<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Application;\Application
use IslamWiki\Skins\SkinManager;

echo "🔍 Debug User Skin Test\n";
echo "=======================\n\n";

try {
    // Create application instance
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();

    // Get skin manager
    $skinManager = $container->get('skin.manager');

    echo "✅ Application and SkinManager loaded successfully\n\n";

    // Test user ID 1 (simulate logged in user)
    $testUserId = 1;

    echo "👤 Testing User {$testUserId} Skin Preferences:\n";

    // Get current user skin
    $userActiveSkin = $skinManager->getActiveSkinNameForUser($testUserId);
    echo "- Current User Skin: {$userActiveSkin}\n";

    $userSkin = $skinManager->getActiveSkinForUser($testUserId);
    echo "- User Skin Object: " . ($userSkin ? $userSkin->getName() : 'None') . "\n";

    // Test setting user skin to Muslim
    echo "\n🔄 Setting User {$testUserId} Skin to Muslim:\n";

    // Simulate the settings controller's updateUserSkin method
    $db = $container->get('db');

    // Get current settings
    $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
    $stmt->execute([$testUserId]);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($result) {
        $currentSettings = json_decode($result['settings'], true) ?? [];
        echo "- Current Settings: " . json_encode($currentSettings) . "\n";
    } else {
        $currentSettings = [];
        echo "- No existing settings found\n";
    }

    // Update skin setting
    $currentSettings['skin'] = 'muslim';
    $currentSettings['updated_at'] = date('Y-m-d H:i:s');

    // Save to database
    $settingsJson = json_encode($currentSettings);
    $stmt = $db->prepare("
        INSERT INTO user_settings (user_id, settings, created_at, updated_at) 
        VALUES (?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
        settings = VALUES(settings), 
        updated_at = VALUES(updated_at)
    ");
    $result = $stmt->execute([$testUserId, $settingsJson]);

    echo "- Save Result: " . ($result ? 'Success' : 'Failed') . "\n";
    echo "- Updated Settings: " . json_encode($currentSettings) . "\n";

    // Test reading the updated skin
    echo "\n🔄 Reading Updated User Skin:\n";
    $updatedUserSkin = $skinManager->getActiveSkinNameForUser($testUserId);
    echo "- Updated User Skin: {$updatedUserSkin}\n";

    $updatedUserSkinObject = $skinManager->getActiveSkinForUser($testUserId);
        $temp_753e679f = ($updatedUserSkinObject ? $updatedUserSkinObject->getName() : 'None') . "\n";
        echo "- Updated User Skin Object: " . $temp_753e679f;

    // Test switching back to Bismillah
    echo "\n🔄 Setting User {$testUserId} Skin Back to Bismillah:\n";
    $currentSettings['skin'] = 'bismillah';
    $currentSettings['updated_at'] = date('Y-m-d H:i:s');

    $settingsJson = json_encode($currentSettings);
    $stmt = $db->prepare("
        INSERT INTO user_settings (user_id, settings, created_at, updated_at) 
        VALUES (?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
        settings = VALUES(settings), 
        updated_at = VALUES(updated_at)
    ");
    $result = $stmt->execute([$testUserId, $settingsJson]);

    echo "- Save Result: " . ($result ? 'Success' : 'Failed') . "\n";

    // Test reading the updated skin
    echo "\n🔄 Reading Updated User Skin:\n";
    $finalUserSkin = $skinManager->getActiveSkinNameForUser($testUserId);
    echo "- Final User Skin: {$finalUserSkin}\n";

    $finalUserSkinObject = $skinManager->getActiveSkinForUser($testUserId);
        $temp_54cce7e4 = ($finalUserSkinObject ? $finalUserSkinObject->getName() : 'None') . "\n";
        echo "- Final User Skin Object: " . $temp_54cce7e4;

    echo "\n✅ User skin test completed successfully\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
