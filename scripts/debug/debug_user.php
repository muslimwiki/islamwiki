<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\User;

$connection = new Connection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
]);

echo "Debugging User Model\n";
echo "===================\n\n";

// Get raw data from database
$rawData = $connection->select('SELECT * FROM users WHERE username = ?', ['admin']);
echo "Raw database data:\n";
if (!empty($rawData)) {
    foreach ($rawData[0] as $key => $value) {
        echo "  {$key}: " . (is_null($value) ? 'NULL' : $value) . "\n";
    }
}

echo "\n";

// Test User model
$user = User::findByUsername('admin', $connection);
if ($user) {
    echo "User model data:\n";
    echo "  Username: " . $user->getAttribute('username') . "\n";
    echo "  Email: " . $user->getAttribute('email') . "\n";
    echo "  Password: " . ($user->getAttribute('password') ? 'SET' : 'NOT SET') . "\n";
    echo "  Password length: " . strlen($user->getAttribute('password') ?: '') . "\n";
    echo "  Password hash: " . substr($user->getAttribute('password') ?: '', 0, 20) . "...\n";
    
    // Test password verification
    $testPassword = 'admin123';
    $isValid = $user->verifyPassword($testPassword);
    echo "  Password '{$testPassword}' verification: " . ($isValid ? 'VALID' : 'INVALID') . "\n";
    
    // Test direct password_verify
    $directValid = password_verify($testPassword, $user->getAttribute('password') ?: '');
    echo "  Direct password_verify: " . ($directValid ? 'VALID' : 'INVALID') . "\n";
} else {
    echo "User not found\n";
} 