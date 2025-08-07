<?php

declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Simple homepage test
echo "<h1>Welcome to IslamWiki</h1>";
echo "<p>If you can see this, the homepage is working!</p>";

// Test database connection
try {
    // Try to load .env file if it exists
    $envPath = dirname(__DIR__) . '/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            putenv(trim($line));
        }
    }

    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_DATABASE') ?: 'islamwiki';
    $dbUser = getenv('DB_USERNAME') ?: 'root';
    $dbPass = getenv('DB_PASSWORD') ?: '';

    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

    $db = new PDO(
        $dsn,
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    echo "<p style='color:green;'>✓ Database connection successful to $dbName@$dbHost</p>";
} catch (PDOException $e) {
    echo "<div style='color:red; background:#fee; padding:10px; margin:10px 0; border:1px solid #fcc;'>";
    echo "<p>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your .env file or database configuration.</p>";
    echo "</div>";
}

// Test file permissions
$testFile = __DIR__ . '/test-write.txt';
if (file_put_contents($testFile, 'test') !== false) {
    unlink($testFile);
    echo "<p style='color:green;'>✓ File permissions are correct!</p>";
} else {
    echo "<p style='color:red;'>✗ Cannot write to directory: " . htmlspecialchars(__DIR__) . "</p>";
}

// Show PHP info
echo "<p><a href='/phpinfo.php' target='_blank'>View PHP Info</a></p>";
