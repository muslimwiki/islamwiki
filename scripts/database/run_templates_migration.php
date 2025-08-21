<?php

declare(strict_types=1);

/**
 * Run Templates System Migration
 * 
 * This script runs the templates system migration to create the necessary
 * database tables for the MediaWiki-style template system.
 * 
 * Usage: php scripts/database/run_templates_migration.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

// Initialize database connection
$config = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];

try {
    $connection = new Connection($config);
    echo "✅ Database connection established\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if migration file exists
$migrationFile = __DIR__ . '/../../database/migrations/0005_templates_system.php';
if (!file_exists($migrationFile)) {
    echo "❌ Migration file not found: {$migrationFile}\n";
    exit(1);
}

echo "📝 Running templates system migration...\n";

try {
    // Include and run the migration
    $migration = include $migrationFile;
    
    if (is_callable($migration)) {
        $migration($connection);
        echo "✅ Migration completed successfully!\n";
    } else {
        echo "❌ Migration file is not callable\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🎉 Templates system migration completed!\n";
echo "\n🔧 Next steps:\n";
echo "  1. Seed default templates: php scripts/templates/seed_default_templates.php\n";
echo "  2. Test templates in wiki pages\n";
echo "  3. Create custom templates as needed\n"; 