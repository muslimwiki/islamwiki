<?php

require_once __DIR__ . '/vendor/autoload.php';

use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;
use IslamWiki\Models\QuranAyah;

// Test database connection
$configs = [
    'quran' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'islamwiki',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
];

try {
    $manager = new IslamicDatabaseManager($configs);
    $quranAyah = new QuranAyah($manager);
    
    echo "Testing Quran system...\n";
    
    // Test getBySurah method
    echo "\n1. Testing getBySurah(1, 'english', 'Saheeh International'):\n";
    $surah1 = $quranAyah->getBySurah(1, 'english', 'Saheeh International');
    if ($surah1) {
        echo "Found " . count($surah1) . " ayahs in surah 1\n";
        if (count($surah1) > 0) {
            echo "First ayah: " . json_encode($surah1[0], JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "No ayahs found\n";
    }
    
    // Test getAllTranslators method
    echo "\n2. Testing getAllTranslators('english'):\n";
    $translators = $quranAyah->getAllTranslators('english');
    if ($translators) {
        echo "Found " . count($translators) . " translators for English\n";
        foreach ($translators as $t) {
            echo "- " . $t['translator'] . " (" . $t['language'] . ")\n";
        }
    } else {
        echo "No translators found\n";
    }
    
    // Test getStatistics method
    echo "\n3. Testing getStatistics():\n";
    $stats = $quranAyah->getStatistics();
    if ($stats) {
        echo "Statistics: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "No statistics found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
