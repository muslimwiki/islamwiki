<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// Simulate the web context
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/quran/ayahs/1/1';

try {
    echo "Testing web context...\n";
    
    // Create the same instance as the controller
    $quranAyah = new IslamWiki\Models\QuranAyah(null);
    echo "QuranAyah model created successfully\n";
    
    // Test the getByReference method
    $result = $quranAyah->getByReference(1, 1, 'english', 'Saheeh International');
    
    if ($result) {
        echo "getByReference successful!\n";
        echo "Found ayah: " . $result['text_arabic'] . "\n";
        echo "Translation: " . $result['translation_text'] . "\n";
    } else {
        echo "getByReference returned no results\n";
        
        // Let's debug what's happening
        echo "\nDebugging...\n";
        
        // Check if the database connection is working
        try {
            $db = $quranAyah->db;
            echo "Database connection exists\n";
            
            // Test a simple query
            $stmt = $db->query("SELECT COUNT(*) FROM ayahs WHERE surah_number = 1 AND ayah_number = 1");
            $count = $stmt->fetchColumn();
            echo "Simple query count: $count\n";
            
        } catch (Exception $e) {
            echo "Database error: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
