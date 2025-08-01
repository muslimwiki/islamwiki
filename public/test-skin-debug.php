<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use IslamWiki\Core\Application;
use IslamWiki\Core\Session\SessionManager;

// Load environment variables
Dotenv::createImmutable(__DIR__ . '/..')->load();

echo "<h1>Skin Debug Test</h1>";

// Initialize application and session
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();
$session = $container->get('session');

// Check if user is logged in
if (!$session->isLoggedIn()) {
    echo "<p>❌ User not logged in</p>";
    echo "<p><a href='/login'>Login first</a></p>";
    exit;
}

$userId = $session->getUserId();
echo "<p>✅ User logged in: ID $userId</p>";

// Get database connection
$db = $container->get('db');

// Get current user settings
$stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
$stmt->execute([$userId]);
$result = $stmt->fetch();

if ($result) {
    $settings = json_decode($result['settings'], true);
    echo "<p>📋 Current settings: " . json_encode($settings) . "</p>";
    
    $userActiveSkin = $settings['skin'] ?? 'bismillah';
    echo "<p>🎨 User active skin: <strong>$userActiveSkin</strong></p>";
} else {
    echo "<p>❌ No user settings found</p>";
    $userActiveSkin = 'bismillah';
}

// Test skin names
$skinNames = ['Bismillah', 'BlueSkin', 'GreenSkin'];
echo "<h2>Testing Active Logic</h2>";
foreach ($skinNames as $skinName) {
    $isActive = strtolower($skinName) === strtolower($userActiveSkin);
    $status = $isActive ? "✅ ACTIVE" : "❌ INACTIVE";
    echo "<p>$status - '$skinName' (userActiveSkin: '$userActiveSkin')</p>";
}

// Test updating skin
if (isset($_POST['test_skin'])) {
    $newSkin = $_POST['test_skin'];
    
    // Get current settings
    $currentSettings = [];
    if ($result) {
        $currentSettings = json_decode($result['settings'], true);
    }
    
    // Update skin
    $currentSettings['skin'] = strtolower($newSkin);
    $currentSettings['updated_at'] = date('Y-m-d H:i:s');
    
    echo "<p>📝 Updating settings to: " . json_encode($currentSettings) . "</p>";
    
    // Save to database
    $stmt = $db->prepare("
        INSERT INTO user_settings (user_id, settings, created_at, updated_at) 
        VALUES (?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
        settings = VALUES(settings), 
        updated_at = VALUES(updated_at)
    ");
    
    $settingsJson = json_encode($currentSettings);
    $updateResult = $stmt->execute([$userId, $settingsJson]);
    
    echo "<p>✅ Update result: " . ($updateResult ? 'success' : 'failed') . "</p>";
    
    // Verify the update
    $stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $newResult = $stmt->fetch();
    
    if ($newResult) {
        $newSettings = json_decode($newResult['settings'], true);
        echo "<p>📋 New settings: " . json_encode($newSettings) . "</p>";
    } else {
        echo "<p>❌ No settings found after update</p>";
    }
}

// Test form
echo "<hr>";
echo "<h2>Test Skin Update</h2>";
echo "<form method='POST'>";
echo "<select name='test_skin'>";
echo "<option value='bismillah'>bismillah</option>";
echo "<option value='blueskin'>blueskin</option>";
echo "<option value='greenskin'>greenskin</option>";
echo "<option value='Bismillah'>Bismillah</option>";
echo "<option value='BlueSkin'>BlueSkin</option>";
echo "<option value='GreenSkin'>GreenSkin</option>";
echo "</select>";
echo "<button type='submit'>Test Update</button>";
echo "</form>";

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
?> 