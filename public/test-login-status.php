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

echo "<h1>Login Status Test</h1>";

// Check session
session_start();
echo "<h2>Session Data</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session data: " . json_encode($_SESSION) . "</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p>✅ User ID in session: {$_SESSION['user_id']}</p>";
    
    // Get user settings
    $stmt = $pdo->prepare("SELECT settings FROM user_settings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    
    if ($result) {
        $settings = json_decode($result['settings'], true) ?? [];
        echo "<p>✅ User settings: " . json_encode($settings) . "</p>";
        echo "<p>✅ Active skin: " . ($settings['skin'] ?? 'none') . "</p>";
    } else {
        echo "<p>❌ No settings found for user {$_SESSION['user_id']}</p>";
    }
} else {
    echo "<p>❌ No user_id in session - NOT LOGGED IN</p>";
    echo "<p><a href='/login'>Click here to login</a></p>";
}

echo "<hr>";
echo "<p><a href='/settings'>Go to Settings</a></p>";
echo "<p><a href='/login'>Go to Login</a></p>";
?> 