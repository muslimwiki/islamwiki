<?php

declare(strict_types=1);

/**
 * Wiki Tables Rollback Script
 * 
 * This script removes all wiki system tables.
 * Use with caution - this will delete all wiki data!
 * Run this only if you need to completely remove the wiki system.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Database configuration
$config = [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

echo "⚠️  WARNING: This will delete ALL wiki tables and data!\n";
echo "Are you sure you want to continue? (yes/no): ";

$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) !== 'yes') {
    echo "Rollback cancelled.\n";
    exit(0);
}

try {
    // Connect to database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    echo "Connected to database: {$config['database']}\n";
    
    // Check which wiki tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'wiki_%'");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($existingTables)) {
        echo "No wiki tables found to remove.\n";
        exit(0);
    }
    
    echo "Found " . count($existingTables) . " wiki tables to remove:\n";
    foreach ($existingTables as $table) {
        echo "  - {$table}\n";
    }
    
    echo "\nProceeding with rollback...\n";
    
    // Drop tables in reverse order to avoid foreign key constraint issues
    $tablesToDrop = [
        'wiki_page_watches',
        'wiki_page_locks', 
        'wiki_search_logs',
        'wiki_page_views',
        'wiki_page_tags',
        'wiki_tags',
        'wiki_page_categories',
        'wiki_revisions',
        'wiki_categories',
        'wiki_pages'
    ];
    
    $pdo->beginTransaction();
    
    foreach ($tablesToDrop as $table) {
        if (in_array($table, $existingTables)) {
            echo "Dropping table: {$table}\n";
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }
    }
    
    $pdo->commit();
    
    echo "\n✅ Rollback completed successfully!\n";
    echo "All wiki tables have been removed.\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    echo "Rollback failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} 