<?php

/**
 * Test script for getBySurah method
 *
 * This script tests the fixed getBySurah method to ensure it works correctly
 * with the proper table names and column names.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Extensions\QuranExtension\Models\QuranAyah;

try {
    // Initialize the application
    $app = new NizamApplication(__DIR__ . '/../..');
    $container = $app->getContainer();

    // Get database connection
    $db = $container->get(\IslamWiki\Core\Database\Connection::class);

    // Create QuranAyah model instance
    $quranAyah = new QuranAyah($db);

    echo "Testing getBySurah method...\n";
    echo "================================\n\n";

    // Test with Surah Al-Fatiha (surah number 1)
    echo "Testing Surah Al-Fatiha (surah 1):\n";
    $ayahs = $quranAyah->getBySurah(1, 'english', 'Saheeh International');

    if (empty($ayahs)) {
        echo "❌ No ayahs found for Surah 1\n";
        echo "This might mean:\n";
        echo "1. The database tables don't exist yet\n";
        echo "2. The tables are empty\n";
        echo "3. There's still an issue with the method\n\n";

        // Check if tables exist
        echo "Checking if tables exist...\n";
        $tables = $db->query("SHOW TABLES LIKE 'quran_ayahs'")->fetchAll();
        if (empty($tables)) {
            echo "❌ quran_ayahs table does not exist\n";
        } else {
            echo "✅ quran_ayahs table exists\n";
        }

        $tables = $db->query("SHOW TABLES LIKE 'quran_translations'")->fetchAll();
        if (empty($tables)) {
            echo "❌ quran_translations table does not exist\n";
        } else {
            echo "✅ quran_translations table exists\n";
        }

        // Check table structure
        echo "\nChecking table structure...\n";
        try {
            $columns = $db->query("DESCRIBE quran_ayahs")->fetchAll();
            echo "quran_ayahs columns:\n";
            foreach ($columns as $column) {
                echo "  - {$column['Field']} ({$column['Type']})\n";
            }
        } catch (Exception $e) {
            echo "❌ Error checking quran_ayahs structure: " . $e->getMessage() . "\n";
        }

        try {
            $columns = $db->query("DESCRIBE quran_translations")->fetchAll();
            echo "\nquran_translations columns:\n";
            foreach ($columns as $column) {
                echo "  - {$column['Field']} ({$column['Type']})\n";
            }
        } catch (Exception $e) {
            echo "❌ Error checking quran_translations structure: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✅ Found " . count($ayahs) . " ayahs for Surah 1\n";
        echo "\nFirst ayah details:\n";
        $firstAyah = $ayahs[0];
        foreach ($firstAyah as $key => $value) {
            if (is_string($value) && strlen($value) > 100) {
                echo "  {$key}: " . substr($value, 0, 100) . "...\n";
            } else {
                echo "  {$key}: " . (is_null($value) ? 'NULL' : $value) . "\n";
            }
        }
    }

    echo "\nTest completed.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
