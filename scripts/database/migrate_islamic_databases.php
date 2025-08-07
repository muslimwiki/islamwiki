<?php

/**
 * Migrate Islamic Databases
 *
 * This script runs migrations for each Islamic database:
 * - Quran database migrations
 * - Hadith database migrations
 * - Scholar database migrations
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

// Load configuration
$config = require __DIR__ . '/../../config/database.php';

echo "=== Islamic Database Migration ===\n";
echo "Running migrations for Islamic content databases...\n\n";

// Database configurations
$databases = [
    'quran' => [
        'name' => 'Quran Database',
        'config' => $config['connections']['quran'],
        'migrations' => ['0002_quran_schema.php']
    ],
    'hadith' => [
        'name' => 'Hadith Database',
        'config' => $config['connections']['hadith'],
        'migrations' => ['0003_hadith_schema.php']
    ],
    'scholar' => [
        'name' => 'Scholar Database',
        'config' => $config['connections']['scholar'],
        'migrations' => ['0004_scholar_schema.php']
    ]
];

$migrationsPath = __DIR__ . '/../../database/migrations/';

foreach ($databases as $type => $dbInfo) {
    echo "=== {$dbInfo['name']} ===\n";

    try {
        // Create connection
        $connection = new Connection($dbInfo['config']);
        $pdo = $connection->getPdo();

        echo "✅ Connected to {$dbInfo['config']['database']}\n";

        // Create migrator with migration path
        $migrator = new Migrator($connection, $migrationsPath);

        // Run each migration
        foreach ($dbInfo['migrations'] as $migrationFile) {
            $migrationPath = $migrationsPath . $migrationFile;

            if (file_exists($migrationPath)) {
                echo "Running migration: {$migrationFile}\n";

                // Get the migration name without extension
                $migrationName = pathinfo($migrationFile, PATHINFO_FILENAME);

                // Run the migration
                $migrator->runMigration($migrationName, 1);

                echo "✅ Migration completed: {$migrationFile}\n";
            } else {
                echo "❌ Migration file not found: {$migrationFile}\n";
            }
        }

        // Show database statistics after migration
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        echo "📊 Tables created: " . count($tables) . "\n";

        if (!empty($tables)) {
            echo "   Tables: " . implode(', ', $tables) . "\n";
        }

        $connection->disconnect();
    } catch (Exception $e) {
        echo "❌ Migration failed: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo "=== Migration Complete ===\n";
echo "Next steps:\n";
echo "1. Test the Islamic database manager\n";
echo "2. Import sample data\n";
echo "3. Test Islamic content features\n";
