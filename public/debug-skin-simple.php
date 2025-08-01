<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use IslamWiki\Core\Application;
use IslamWiki\Core\Session\SessionManager;

// Load environment variables
Dotenv::createImmutable(__DIR__ . '/..')->load();

echo "<h1>Skin Debug</h1>";

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
    
    // Get current user settings
    $stmt = $pdo->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
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
    
    // Test the skin names from the JSON files
    $skinNames = ['Bismillah', 'BlueSkin', 'GreenSkin'];
    
    echo "<h2>Testing Active Logic</h2>";
    foreach ($skinNames as $skinName) {
        $possibleNames = [
            strtolower($skinName),
            $skinName
        ];
        
        $isActive = false;
        if (in_array(strtolower($userActiveSkin), $possibleNames) || 
            in_array($userActiveSkin, $possibleNames)) {
            $isActive = true;
        }
        
        $status = $isActive ? "✅ ACTIVE" : "❌ INACTIVE";
        echo "<p>$status - '$skinName' (userActiveSkin: '$userActiveSkin')</p>";
    }
    
    // Test direct update
    if (isset($_POST['set_skin'])) {
        $newSkin = $_POST['set_skin'];
        
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
        echo "<script>setTimeout(() => window.location.reload(), 1000);</script>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test form
echo "<hr>";
echo "<h2>Test Skin Update</h2>";
echo "<form method='POST'>";
echo "<select name='set_skin'>";
echo "<option value='bismillah'>bismillah</option>";
echo "<option value='blueskin'>blueskin</option>";
echo "<option value='greenskin'>greenskin</option>";
echo "<option value='Bismillah'>Bismillah</option>";
echo "<option value='BlueSkin'>BlueSkin</option>";
echo "<option value='GreenSkin'>GreenSkin</option>";
echo "</select>";
echo "<button type='submit'>Update Skin</button>";
echo "</form>";

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
?> 