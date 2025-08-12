<?php

/**
 * Debug QuranAyah Model
 *
 * This script tests the QuranAyah model to identify any issues.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Extensions\QuranExtension\Models\QuranAyah;

try {
    echo "Testing QuranAyah model...\n";

    // Create database connection
    $db = new Connection([
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'islamwiki_quran',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ]);
    echo "Database connection created successfully.\n";

    // Test the model
    $model = new QuranAyah($db);
    echo "QuranAyah model created successfully.\n";

    // Test basic methods
    echo "Testing getStatistics...\n";
    $stats = $model->getStatistics();
    echo "Statistics: " . print_r($stats, true) . "\n";

    echo "Testing getAllSurahs...\n";
    $surahs = $model->getAllSurahs();
    echo "Found " . count($surahs) . " surahs.\n";

    echo "Testing getAllTranslators...\n";
    $translators = $model->getAllTranslators('english');
    echo "Found " . count($translators) . " translators.\n";

    echo "All tests passed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
