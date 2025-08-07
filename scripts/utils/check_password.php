<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use IslamWiki\Core\Database\Connection;

$connection = new Connection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
]);

$user = $connection->select('SELECT username, password FROM users WHERE username = ?', ['admin']);

if (!empty($user)) {
    echo "Password hash: " . $user[0]['password'] . "\n";
    echo "Hash length: " . strlen($user[0]['password']) . "\n";
    echo "Hash starts with: " . substr($user[0]['password'], 0, 10) . "\n";

    // Test password verification
    $testPassword = 'admin123';
    $isValid = password_verify($testPassword, $user[0]['password']);
    echo "Password '{$testPassword}' is " . ($isValid ? "valid" : "invalid") . "\n";

    // Test with wrong password
    $wrongPassword = 'wrongpassword';
    $isValidWrong = password_verify($wrongPassword, $user[0]['password']);
    echo "Password '{$wrongPassword}' is " . ($isValidWrong ? "valid" : "invalid") . " (expected invalid)\n";
} else {
    echo "User not found\n";
}
