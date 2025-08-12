<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;

require_once __DIR__ . '/../../vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    echo "Run this script from CLI.\n";
    exit(1);
}

echo "Checking Quran table structure...\n\n";

try {
    // Get database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'islamwiki';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

    $config = [
        'host' => $host,
        'port' => $port,
        'database' => $database,
        'username' => $username,
        'password' => $password,
        'charset' => $charset,
        'driver' => 'mysql'
    ];

    $connection = new Connection($config);
    $pdo = $connection->getPdo();

    // Tables to check
    $tablesToCheck = [
        'quran_surahs',
        'quran_ayahs',
        'quran_translations',
        'ayah_translations',
        'translations',
        'surahs',
        'ayahs'
    ];

    foreach ($tablesToCheck as $tableName) {
        echo "Table: {$tableName}\n";
        echo str_repeat('-', strlen($tableName) + 7) . "\n";
        
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
            if ($stmt->fetch()) {
                $stmt = $pdo->query("DESCRIBE {$tableName}");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($columns as $column) {
                    echo "  {$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']}\n";
                }
            } else {
                echo "  Table does not exist\n";
            }
        } catch (Exception $e) {
            echo "  Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
