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

echo "<h1>Login Debug Test</h1>";

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

// Check if this is a login attempt
if ($_POST) {
    echo "<h2>Login Attempt</h2>";
    echo "<p>Username: " . ($_POST['username'] ?? 'none') . "</p>";
    echo "<p>Password: " . ($_POST['password'] ? '***' : 'none') . "</p>";
    
    // Find user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$_POST['username'], $_POST['username']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>✅ User found: " . $user['username'] . "</p>";
        echo "<p>User ID: " . $user['id'] . "</p>";
        
        // Check password
        if (password_verify($_POST['password'], $user['password'])) {
            echo "<p>✅ Password verified!</p>";
            
            // Start session
            session_start();
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['logged_in_at'] = time();
            
            echo "<p>✅ Session data set:</p>";
            echo "<p>Session ID: " . session_id() . "</p>";
            echo "<p>Session data: " . json_encode($_SESSION) . "</p>";
            
            echo "<p><a href='/test-login-status.php'>Check Login Status</a></p>";
            echo "<p><a href='/settings'>Go to Settings</a></p>";
        } else {
            echo "<p>❌ Password verification failed!</p>";
        }
    } else {
        echo "<p>❌ User not found!</p>";
    }
} else {
    echo "<h2>Login Form</h2>";
    echo "<form method='POST'>";
    echo "<p><label>Username: <input type='text' name='username' value='admin'></label></p>";
    echo "<p><label>Password: <input type='password' name='password' value='password'></label></p>";
    echo "<p><button type='submit'>Login</button></p>";
    echo "</form>";
}

echo "<hr>";
echo "<p><a href='/test-login-status.php'>Check Login Status</a></p>";
echo "<p><a href='/login'>Go to Login Page</a></p>";
?> 