<?php

declare(strict_types=1);

/**
 * Simple Wiki Tables Migration Runner
 * 
 * This script runs the SQL migration to create the wiki system tables.
 * Run this from the command line: php database/migrate_wiki_tables.php
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

try {
    // Connect to database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    echo "Connected to database: {$config['database']}\n";
    
    // Read and execute SQL migration
    $sqlFile = __DIR__ . '/migrations/2025_01_20_000001_create_wiki_tables.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt) && !str_starts_with($stmt, '--'); }
    );
    
    echo "Executing " . count($statements) . " SQL statements...\n";
    
    $pdo->beginTransaction();
    
    foreach ($statements as $i => $statement) {
        if (empty(trim($statement))) continue;
        
        echo "Executing statement " . ($i + 1) . "...\n";
        $pdo->exec($statement);
    }
    
    $pdo->commit();
    
    echo "Migration completed successfully!\n";
    echo "Wiki system tables have been created.\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    echo "Migration failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} 