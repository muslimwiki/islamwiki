<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get database credentials from environment or prompt
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$database = $_ENV['DB_DATABASE'] ?? 'islamwiki';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

// If no password is set, try common defaults or prompt
if (empty($password)) {
    // Try common default passwords
    $commonPasswords = ['', 'root', 'password', 'mysql'];
    $connected = false;

    foreach ($commonPasswords as $pwd) {
        try {
            $dsn = "mysql:host=$host;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $pwd, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            ]);
            $password = $pwd;
            $connected = true;
            break;
        } catch (PDOException $e) {
            continue;
        }
    }

    if (!$connected) {
        // If we get here, none of the common passwords worked
        echo "Could not connect with empty password. Please enter your MySQL password: ";
        $password = trim(fgets(STDIN));
    }
}

// Database configuration
$config = [
    'driver' => 'mysql',
    'host' => $host,
    'database' => $database,
    'username' => $username,
    'password' => $password,
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

try {
    // Create connection
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        $config['options']
    );

    // Test connection
    $pdo->query('SELECT 1');
    echo "Database connection successful!\n";

    // Show database version
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "Database version: " . $version . "\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}
