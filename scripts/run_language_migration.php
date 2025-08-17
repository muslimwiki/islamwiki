<?php

declare(strict_types=1);

/**
 * Script to run the language preferences migration
 * 
 * This script sets up the database structure needed for user language preferences
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

echo "🚀 Starting Language Preferences Migration\n";
echo "==========================================\n\n";

try {
    // Create database connection
    $config = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci'
    ];

    echo "📡 Connecting to database...\n";
    $db = new Connection($config);
    
    // Test connection
    $db->getPdo();
    echo "✅ Database connection established\n\n";

    // Run migration
    echo "🔧 Running migration...\n";
    $migration = new Migration_0020_UserLanguagePreferences($db);
    $migration->up();

    echo "\n🎉 Migration completed successfully!\n";
    echo "\nThe system now supports:\n";
    echo "• User language preferences stored in database\n";
    echo "• Automatic redirects to preferred language\n";
    echo "• Settings page at /settings for language selection\n";
    echo "• Separate language sites at /en, /ar, /tr, /ur, etc.\n";

} catch (\Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✨ Setup complete! You can now:\n";
echo "1. Visit /settings to set your language preference\n";
echo "2. Browse content in your preferred language\n";
echo "3. Switch languages through the settings page\n";
echo "\n"; 