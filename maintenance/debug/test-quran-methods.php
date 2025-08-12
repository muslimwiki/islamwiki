<?php

/**
 * Comprehensive test script for Quran methods
 *
 * This script tests all the updated Quran methods to ensure they work correctly
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

    echo "Testing Quran methods with updated column names...\n";
    echo "================================================\n\n";

    // Test 1: getBySurah method
    echo "Test 1: getBySurah method\n";
    echo "-------------------------\n";
    $ayahs = $quranAyah->getBySurah(1, 'english', 'Saheeh International');
    if (empty($ayahs)) {
        echo "❌ No ayahs found for Surah 1\n";
    } else {
        echo "✅ Found " . count($ayahs) . " ayahs for Surah 1\n";
        echo "First ayah has juz_number: " . ($ayahs[0]['juz_number'] ?? 'NULL') . "\n";
        echo "First ayah has page_number: " . ($ayahs[0]['page_number'] ?? 'NULL') . "\n";
    }
    echo "\n";

    // Test 2: getByJuz method
    echo "Test 2: getByJuz method\n";
    echo "-----------------------\n";
    $juzAyahs = $quranAyah->getByJuz(1, 'english', 'Saheeh International');
    if (empty($juzAyahs)) {
        echo "❌ No ayahs found for Juz 1\n";
    } else {
        echo "✅ Found " . count($juzAyahs) . " ayahs for Juz 1\n";
        echo "First ayah has juz_number: " . ($juzAyahs[0]['juz_number'] ?? 'NULL') . "\n";
        echo "First ayah has page_number: " . ($juzAyahs[0]['page_number'] ?? 'NULL') . "\n";
    }
    echo "\n";

    // Test 3: getByPage method
    echo "Test 3: getByPage method\n";
    echo "------------------------\n";
    $pageAyahs = $quranAyah->getByPage(1, 'english', 'Saheeh International');
    if (empty($pageAyahs)) {
        echo "❌ No ayahs found for Page 1\n";
    } else {
        echo "✅ Found " . count($pageAyahs) . " ayahs for Page 1\n";
        echo "First ayah has juz_number: " . ($pageAyahs[0]['juz_number'] ?? 'NULL') . "\n";
        echo "First ayah has page_number: " . ($pageAyahs[0]['page_number'] ?? 'NULL') . "\n";
    }
    echo "\n";

    // Test 4: getByReference method
    echo "Test 4: getByReference method\n";
    echo "-----------------------------\n";
    $referenceAyah = $quranAyah->getByReference(1, 1, 'english', 'Saheeh International');
    if (!$referenceAyah) {
        echo "❌ No ayah found for Surah 1, Ayah 1\n";
    } else {
        echo "✅ Found ayah for Surah 1, Ayah 1\n";
        echo "Ayah has juz_number: " . ($referenceAyah['juz_number'] ?? 'NULL') . "\n";
        echo "Ayah has page_number: " . ($referenceAyah['page_number'] ?? 'NULL') . "\n";
    }
    echo "\n";

    // Test 5: Check column names in results
    echo "Test 5: Column name verification\n";
    echo "--------------------------------\n";
    if (!empty($ayahs)) {
        $firstAyah = $ayahs[0];
        $expectedColumns = [
            'id', 'surah_number', 'ayah_number', 'text_arabic', 'text_uthmani',
            'text_indopak', 'juz_number', 'page_number', 'hizb_number',
            'ruku_number', 'sajda_number', 'translation', 'translator', 'language'
        ];
        $missingColumns = [];

        foreach ($expectedColumns as $column) {
            if (!array_key_exists($column, $firstAyah)) {
                $missingColumns[] = $column;
            }
        }

        if (empty($missingColumns)) {
            echo "✅ All expected columns are present\n";
        } else {
            echo "❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
        }

        echo "Available columns: " . implode(', ', array_keys($firstAyah)) . "\n";
    }
    echo "\n";

    echo "All tests completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
