<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

echo "<h1>Direct Database Test</h1>";

// Create database connection
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? 'islamwiki';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

$dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "<p>✅ Database connected</p>";

// Test the exact query from SettingsController
$userId = 1;
echo "<p>Testing getUserSettings for user ID: $userId</p>";

$stmt = $pdo->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
$stmt->execute([$userId]);
$result = $stmt->fetch();

if ($result) {
    $settings = json_decode($result['settings'], true) ?? [];
    echo "<p>✅ Found settings: " . json_encode($settings) . "</p>";
    
    $userActiveSkin = $settings['skin'] ?? 'bismillah';
    echo "<p>✅ userActiveSkin = '$userActiveSkin'</p>";
    
    // Test the comparison logic
    echo "<h2>Comparison Test</h2>";
    $testSkins = ['Bismillah', 'BlueSkin', 'GreenSkin'];
    
    foreach ($testSkins as $displayName) {
        $isActive = strtolower($displayName) === strtolower($userActiveSkin);
        echo "<p>Skin: $displayName - userActiveSkin: $userActiveSkin - isActive: " . ($isActive ? 'TRUE' : 'FALSE') . "</p>";
    }
} else {
    echo "<p>❌ No settings found for user $userId</p>";
}

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
?> 