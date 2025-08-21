<?php

require_once __DIR__ . '/../../vendor/autoload.php';

try {
    // Create database connection
    $db = new IslamWiki\Core\Database\Connection([
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'islamwiki',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]);

    $pdo = $db->getPdo();

    echo "Testing Quran query...\n";

    // Test the exact query from getByReference
    $sql = "SELECT a.*, t.translation_text, tr.translator, tr.language
            FROM ayahs a
            LEFT JOIN ayah_translations t ON a.id = t.ayah_id
            LEFT JOIN translations tr ON t.translation_id = tr.id
            WHERE a.surah_number = :chapter
            AND a.ayah_number = :ayah
            AND tr.language = :language
            AND tr.translator = :translator";

    $stmt = $pdo->prepare($sql);
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

        // Let's check what's in the tables
        echo "\nChecking table contents:\n";

        $stmt = $pdo->query("SELECT COUNT(*) FROM ayahs WHERE surah_number = 1 AND ayah_number = 1");
        $count = $stmt->fetchColumn();
        echo "Ayahs with surah=1, ayah=1: $count\n";

        $stmt = $pdo->query("SELECT COUNT(*) FROM ayah_translations WHERE ayah_id = 1");
        $count = $stmt->fetchColumn();
        echo "Translations for ayah_id=1: $count\n";

        $stmt = $pdo->query("SELECT * FROM translations WHERE language = 'english' AND translator = 'Saheeh International'");
        $translation = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Translation record: " . print_r($translation, true) . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
