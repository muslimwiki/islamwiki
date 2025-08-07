<?php

/**
 * Migrate Islamic User Fields
 *
 * This script runs the Islamic user fields migration on the main database.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

// Load configuration
$config = require __DIR__ . '/../../config/database.php';

echo "=== Islamic User Fields Migration ===\n";
echo "Adding Islamic user fields to main database...\n\n";

try {
    // Create connection to main database
    $connection = new Connection($config['connections']['mysql']);
    $pdo = $connection->getPdo();

    echo "✅ Connected to main database: {$config['connections']['mysql']['database']}\n";

    // Create migrator
    $migrationsPath = __DIR__ . '/../../database/migrations/';
    $migrator = new Migrator($connection, $migrationsPath);

    // Run the Islamic user fields migration
    echo "Running migration: 0005_islamic_user_fields.php\n";

    $migrationName = '0005_islamic_user_fields';
    $migrator->runMigration($migrationName, 1);

    echo "✅ Migration completed: 0005_islamic_user_fields.php\n";

    // Show updated table structure
    $result = $pdo->query("DESCRIBE users");
    $columns = $result->fetchAll(\PDO::FETCH_ASSOC);

    echo "\n📊 Updated users table structure:\n";
    foreach ($columns as $column) {
        echo "   - {$column['Field']}: {$column['Type']}\n";
    }

    $connection->disconnect();
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Migration Complete ===\n";
echo "Next steps:\n";
echo "1. Test the Islamic user model\n";
echo "2. Test Islamic authentication\n";
echo "3. Test scholar verification\n";
