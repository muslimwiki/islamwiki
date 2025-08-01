<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
Dotenv::createImmutable(__DIR__ . '/..')->load();

// Simple test to check skin update
echo "<h1>Skin Update Test</h1>";

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
        echo "<p>🎨 Active skin: $activeSkin</p>";
    } else {
        echo "<p>❌ No user settings found</p>";
    }
    
    // Test skin update
    if (isset($_POST['test_skin'])) {
        $newSkin = $_POST['test_skin'];
        
        // Get current settings
        $currentSettings = [];
        if ($result) {
            $currentSettings = json_decode($result['settings'], true);
        }
        
        // Update skin
        $currentSettings['skin'] = $newSkin;
        $currentSettings['updated_at'] = date('Y-m-d H:i:s');
        
        // Save to database
        $stmt = $pdo->prepare("
            INSERT INTO user_settings (user_id, settings, created_at, updated_at) 
            VALUES (?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
            settings = VALUES(settings), 
            updated_at = VALUES(updated_at)
        ");
        
        $settingsJson = json_encode($currentSettings);
        $stmt->execute([$userId, $settingsJson]);
        
        echo "<p>✅ Updated skin to: $newSkin</p>";
        echo "<p><a href='/settings'>Go to Settings</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test form
echo "<hr>";
echo "<h2>Test Skin Update</h2>";
echo "<form method='POST'>";
echo "<select name='test_skin'>";
echo "<option value='bismillah'>Bismillah</option>";
echo "<option value='blueskin'>BlueSkin</option>";
echo "<option value='greenskin'>GreenSkin</option>";
echo "</select>";
echo "<button type='submit'>Update Skin</button>";
echo "</form>";

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
?> 