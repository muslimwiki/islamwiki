<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
Dotenv::createImmutable(__DIR__ . '/..')->load();

echo "<h1>Simple Skin Test</h1>";

// Check if user is logged in
session_start();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo "<p>❌ User not logged in</p>";
    echo "<p><a href='/login'>Login first</a></p>";
    exit;
}

echo "<p>✅ User logged in: ID $userId</p>";

// Connect to database
$dbConfig = [
    'driver' => getenv('DB_CONNECTION') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'database' => getenv('DB_DATABASE') ?: 'islamwiki',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get current user settings
    $stmt = $pdo->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();

    if ($result) {
        $settings = json_decode($result['settings'], true);
        echo "<p>📋 Current settings: " . json_encode($settings) . "</p>";

        $activeSkin = $settings['skin'] ?? 'bismillah';
        echo "<p>🎨 Active skin: <strong>$activeSkin</strong></p>";
    } else {
        echo "<p>❌ No user settings found</p>";
        $activeSkin = 'bismillah';
    }

    // Test different skin names
    $testSkins = ['bismillah', 'Bismillah', 'blueskin', 'BlueSkin', 'greenskin', 'GreenSkin'];

    echo "<h2>Testing Skin Name Matching</h2>";
    foreach ($testSkins as $skinName) {
        $isActive = strtolower($skinName) === strtolower($activeSkin);
        $status = $isActive ? "✅ ACTIVE" : "❌ INACTIVE";
        echo "<p>$status - '$skinName' (activeSkin: '$activeSkin')</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
