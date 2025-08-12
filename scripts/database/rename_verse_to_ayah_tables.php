<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;

require_once __DIR__ . '/../../vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    echo "Run this script from CLI.\n";
    exit(1);
}

echo "Renaming verse tables to ayah tables...\n";

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

    // Tables to rename
    $tablesToRename = [
        'quran_verse_comments' => 'quran_ayah_comments',
        'quran_verse_references' => 'quran_ayah_references',
        'quran_verse_stats' => 'quran_ayah_stats',
        'quran_verse_tag_assignments' => 'quran_ayah_tag_assignments',
        'quran_verse_tags' => 'quran_ayah_tags'
    ];

    foreach ($tablesToRename as $oldName => $newName) {
        // Check if old table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '{$oldName}'");
        if ($stmt->fetch()) {
            echo "Renaming {$oldName} to {$newName}...\n";
            // Rename the table
            $pdo->exec("RENAME TABLE {$oldName} TO {$newName}");
            echo "✓ Successfully renamed {$oldName} to {$newName}\n";
        } else {
            echo "Table {$oldName} does not exist, skipping...\n";
        }
    }
    echo "\nAll tables renamed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
