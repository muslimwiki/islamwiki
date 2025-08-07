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

echo "Testing Query Builder\n";
echo "===================\n\n";

// Test direct connection
echo "Direct connection query:\n";
$rawData = $connection->select('SELECT username, password FROM users WHERE username = ?', ['admin']);
if (!empty($rawData)) {
    echo "  Raw data: " . json_encode($rawData[0]) . "\n";
}

echo "\n";

// Test Query Builder
echo "Query Builder test:\n";
$query = $connection->table('users')->where('username', '=', 'admin');
$sql = $query->toSql();
echo "  SQL: " . $sql . "\n";

$result = $query->first(['username', 'password']);
echo "  Result type: " . gettype($result) . "\n";
echo "  Result: " . json_encode($result) . "\n";

if (is_object($result)) {
    echo "  Object properties: " . implode(', ', get_object_vars($result)) . "\n";
} elseif (is_array($result)) {
    echo "  Array keys: " . implode(', ', array_keys($result)) . "\n";
}
