<?php

/**
 * Simple Database Test for Phase 4
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;

echo "🔍 Testing Phase 4 Database Setup\n";
echo "==================================\n\n";

try {
    // Test basic database connection
    echo "📊 Testing Basic Database Connection:\n";
    $config = [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ];

    echo "Database config: " . json_encode($config) . "\n";

    $connection = new Connection($config);
    $pdo = $connection->getPdo();
    echo "✅ Basic database connection successful\n";

    // Check existing tables
    echo "\n📋 Checking Existing Tables:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "  - {$table}\n";
    }

    // Test Islamic database manager
    echo "\n🕌 Testing Islamic Database Manager:\n";
    $islamicConfigs = [
        'quran' => $config,
        'hadith' => $config,
        'wiki' => $config,
        'scholar' => $config
    ];

    $islamicDbManager = new IslamicDatabaseManager($islamicConfigs);
    echo "✅ IslamicDatabaseManager created successfully\n";

    // Test Quran connection
    try {
        $quranConnection = $islamicDbManager->getQuranConnection();
        echo "✅ Quran database connection successful\n";

        // Check Quran tables
        $quranPdo = $quranConnection->getPdo();
        $quranTables = $quranPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Quran database has " . count($quranTables) . " tables:\n";
        foreach ($quranTables as $table) {
            echo "  - {$table}\n";
        }

        // Check if verses table exists
        if (in_array('verses', $quranTables)) {
            echo "✅ Verses table exists\n";

            // Check verse count
            $verseCount = $quranPdo->query("SELECT COUNT(*) FROM verses")->fetchColumn();
            echo "Verses table has {$verseCount} records\n";
        } else {
            echo "❌ Verses table does not exist\n";
        }

        // Check if surahs table exists
        if (in_array('surahs', $quranTables)) {
            echo "✅ Surahs table exists\n";

            // Check surah count
            $surahCount = $quranPdo->query("SELECT COUNT(*) FROM surahs")->fetchColumn();
            echo "Surahs table has {$surahCount} records\n";
        } else {
            echo "❌ Surahs table does not exist\n";
        }
    } catch (Exception $e) {
        echo "❌ Quran connection failed: " . $e->getMessage() . "\n";
    }

    echo "\n🎉 Database test completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Database test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
