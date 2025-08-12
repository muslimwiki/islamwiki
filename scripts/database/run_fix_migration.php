<?php

/**
 * Run Fix Migration for Quran Table Names
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

echo "Running Fix Migration for Quran Table Names\n";
echo "==========================================\n\n";

try {
    // Create database connection
    $connection = new Connection([
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'islamwiki',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ]);

    echo "✅ Database connection successful\n";

    // Create migrator
    $migrationPath = __DIR__ . '/../../database/migrations';
    $migrator = new Migrator($connection, $migrationPath);

    echo "✅ Migrator created\n";

    // Check if the migration has already been run
    $ran = $migrator->getRanMigrations();
    if (in_array('0013_fix_quran_table_names', $ran)) {
        echo "⚠️  Migration 0013_fix_quran_table_names has already been run\n";
        exit(0);
    }

    // Run the specific migration
    echo "🔄 Running migration: 0013_fix_quran_table_names\n";
    $migrator->runMigration('0013_fix_quran_table_names', $migrator->getNextBatchNumber());

    echo "✅ Migration completed successfully!\n";

    // Verify the changes
    echo "\n🔍 Verifying changes:\n";
    $tables = $connection->select("SHOW TABLES LIKE '%ayah%'");
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "  - $tableName ✓\n";
    }

    echo "\n🎉 Quran table names have been fixed!\n";
    echo "The application should now work with the 'ayah*' naming convention.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
