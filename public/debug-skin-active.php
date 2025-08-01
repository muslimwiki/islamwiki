<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Container;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Session\SessionManager;
use IslamWiki\Skins\SkinManager;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get services
$db = $container->get('database');
$session = $container->get('session');
$skinManager = $container->get('skin.manager');

echo "<h1>Skin Active Status Debug</h1>";

// Check if user is logged in
if (!$session->isLoggedIn()) {
    echo "<p>❌ User not logged in</p>";
    exit;
}

$userId = $session->getUserId();
echo "<p>✅ User logged in: ID $userId</p>";

// Get user settings
$stmt = $db->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
$stmt->execute([$userId]);
$result = $stmt->fetch();

if ($result) {
    $settings = json_decode($result['settings'], true);
    echo "<p>📋 User settings: " . json_encode($settings) . "</p>";
    
    $activeSkin = $settings['skin'] ?? 'bismillah';
    echo "<p>🎨 Active skin: $activeSkin</p>";
} else {
    echo "<p>❌ No user settings found</p>";
}

// Get available skins
$availableSkins = $skinManager->getSkins();
echo "<p>📚 Available skins: " . implode(', ', array_keys($availableSkins)) . "</p>";

// Check each skin's active status
foreach ($availableSkins as $name => $skin) {
    $isActive = strtolower($name) === strtolower($activeSkin);
    $status = $isActive ? "✅ ACTIVE" : "❌ INACTIVE";
    echo "<p>$status - $name</p>";
}

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
?> 