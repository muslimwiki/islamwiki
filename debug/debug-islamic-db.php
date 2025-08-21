<?php

require_once __DIR__ . '/../../vendor/autoload.php';

try {
    echo "Testing IslamicDatabaseManager...\n";
    
    // Create the same configuration as QuranAyah model
    $configs = [
        'quran' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'database' => getenv('DB_DATABASE') ?: 'islamwiki',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]
    ];
    
    echo "Config: " . print_r($configs, true) . "\n";
    
    $islamicDbManager = new IslamWiki\Core\Database\Islamic\IslamicDatabaseManager($configs);
    echo "IslamicDatabaseManager created successfully\n";
    
    $quranConnection = $islamicDbManager->getQuranConnection();
    echo "Quran connection obtained\n";
    
    // Test the connection
    $pdo = $quranConnection->getPdo();
    echo "PDO connection obtained\n";
    
    $stmt = $pdo->query('SELECT 1');
    echo "Connection test successful\n";
    
    // Now test the actual Quran query
    $sql = "SELECT a.*, t.translation_text, tr.translator, tr.language
            FROM ayahs a
            LEFT JOIN ayah_translations t ON a.id = t.ayah_id
            LEFT JOIN translations tr ON t.translation_id = tr.id
            WHERE a.surah_number = :chapter
            AND a.ayah_number = :ayah
            AND tr.language = :language
            AND tr.translator = :translator";

    $stmt = $quranConnection->prepare($sql);
    $chapter = 1;
    $ayah = 1;
    $language = 'english';
    $translator = 'Saheeh International';
    $stmt->bindParam(':chapter', $chapter, PDO::PARAM_INT);
    $stmt->bindParam(':ayah', $ayah, PDO::PARAM_INT);
    $stmt->bindParam(':language', $language, PDO::PARAM_STR);
    $stmt->bindParam(':translator', $translator, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "Query successful!\n";
        echo "Found ayah: " . $result['text_arabic'] . "\n";
        echo "Translation: " . $result['translation_text'] . "\n";
    } else {
        echo "Query returned no results\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
