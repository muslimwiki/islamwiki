<?php

/**
 * Run Bayan Knowledge Graph Migration
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers.php';

try {
    // Initialize the application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');

    // Get database connection
    $connection = $app->getContainer()->get('db');

    echo "Running Bayan Knowledge Graph Migration...\n";

    // Include and run the migration
    $migration = require __DIR__ . '/../database/migrations/0016_bayan_knowledge_graph.php';

    if (isset($migration['up']) && is_callable($migration['up'])) {
        $migration['up']($connection);
        echo "✅ Bayan migration completed successfully!\n";
    } else {
        echo "❌ Invalid migration file\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
