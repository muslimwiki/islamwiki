<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use IslamWiki\Core\Application;
use IslamWiki\Core\Session\SessionManager;

// Load environment variables
Dotenv::createImmutable(__DIR__ . '/..')->load();

echo "<h1>Database Settings Test</h1>";

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
    
    echo "<p>✅ Database connected successfully</p>";
    
    // Check if user_settings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_settings'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p>✅ user_settings table exists</p>";
        
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
        }
        
        // Test updating settings
        if (isset($_POST['test_update'])) {
            $newSkin = $_POST['test_update'];
            
            // Get current settings
            $currentSettings = [];
            if ($result) {
                $currentSettings = json_decode($result['settings'], true);
            }
            
            // Update skin
            $currentSettings['skin'] = $newSkin;
            $currentSettings['updated_at'] = date('Y-m-d H:i:s');
            
            echo "<p>📝 Updating settings to: " . json_encode($currentSettings) . "</p>";
            
            // Save to database
            $stmt = $pdo->prepare("
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
            $stmt = $pdo->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
            $stmt->execute([$userId]);
            $newResult = $stmt->fetch();
            
            if ($newResult) {
                $newSettings = json_decode($newResult['settings'], true);
                echo "<p>📋 New settings: " . json_encode($newSettings) . "</p>";
            } else {
                echo "<p>❌ No settings found after update</p>";
            }
        }
        
    } else {
        echo "<p>❌ user_settings table does not exist</p>";
        
        // Show all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>📋 Available tables: " . implode(', ', $tables) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test form
echo "<hr>";
echo "<h2>Test Skin Update</h2>";
echo "<form method='POST'>";
echo "<select name='test_update'>";
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