<?php

/**
 * Mark Pending Migrations as Complete
 * 
 * This script marks migrations as completed when their tables already exist
 * but the migration records are missing.
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

try {
    $db = new Connection();
    echo "✅ Database connection successful\n";
    
    // Get current max batch
    $maxBatch = $db->select('SELECT MAX(batch) as max_batch FROM migrations')[0]['max_batch'] ?? 0;
    echo "Current max batch: $maxBatch\n";
    
    // Pending migrations that need to be marked as complete
    $pendingMigrations = [
        '0014_advanced_islamic_features',
        '0015_user_settings_schema', 
        '0016_bayan_knowledge_graph',
        '0017_advanced_security_schema',
        '0018_create_salah_times_table',
        '0020_user_language_preferences'
    ];
    
    $newBatch = $maxBatch + 1;
    echo "Marking migrations as completed in batch $newBatch\n";
    
    foreach ($pendingMigrations as $migration) {
        // Check if migration already exists
        $existing = $db->select('SELECT id FROM migrations WHERE migration = ?', [$migration]);
        if (count($existing) > 0) {
            echo "⚠️  Migration $migration already exists, skipping\n";
            continue;
        }
        
        // Insert migration record
        $db->statement('INSERT INTO migrations (migration, batch) VALUES (?, ?)', [$migration, $newBatch]);
        echo "✅ Marked $migration as completed\n";
    }
    
    echo "All pending migrations processed!\n";
    
    // Verify final status
    $finalCount = $db->select('SELECT COUNT(*) as count FROM migrations')[0]['count'];
    echo "Total migrations in database: $finalCount\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 