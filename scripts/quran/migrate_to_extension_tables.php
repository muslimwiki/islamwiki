<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;

echo "Migrating Quran Data to Extension Tables\n";
echo "========================================\n\n";

try {
    // Initialize database connection
    $configs = [
        'quran' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'database' => getenv('DB_CONNECTION') ?: 'islamwiki',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ];

    $manager = new IslamicDatabaseManager($configs);
    $pdo = $manager->getQuranPdo();

    echo "✅ Database connection established\n";

    // Step 1: Migrate surahs
    echo "\n1. Migrating surahs...\n";
    $surahCount = $pdo->query("SELECT COUNT(*) FROM surahs")->fetchColumn();
    echo "   - Found {$surahCount} surahs in legacy table\n";

    if ($surahCount > 0) {
        $pdo->exec("INSERT IGNORE INTO quran_surahs
            (surah_number, name_arabic, name_english, name_translation, revelation_type, verses_count, created_at, updated_at)
            SELECT
                number,
                name_arabic,
                name_english,
                name_translation,
                CASE
                    WHEN revelation_type = 'makkah' THEN 'Meccan'
                    WHEN revelation_type = 'madina' THEN 'Medinan'
                    ELSE 'Meccan'
                END,
                verses_count,
                created_at,
                updated_at
            FROM surahs
        ");

        $migratedSurahs = $pdo->query("SELECT COUNT(*) FROM quran_surahs")->fetchColumn();
        echo "   - Migrated {$migratedSurahs} surahs to extension table\n";
    }

    // Step 2: Migrate ayahs
    echo "\n2. Migrating ayahs...\n";
    $ayahCount = $pdo->query("SELECT COUNT(*) FROM ayahs")->fetchColumn();
    echo "   - Found {$ayahCount} ayahs in legacy table\n";

    if ($ayahCount > 0) {
        $pdo->exec("INSERT IGNORE INTO quran_ayahs
            (surah_number, ayah_number, text, translation, juz, page, hizb, ruku, sajda, created_at, updated_at)
            SELECT
                surah_number,
                ayah_number,
                text_arabic,
                text_uthmani,
                juz_number,
                page_number,
                hizb_number,
                ruku_number,
                sajda_number,
                created_at,
                updated_at
            FROM ayahs
        ");

        $migratedAyahs = $pdo->query("SELECT COUNT(*) FROM quran_ayahs")->fetchColumn();
        echo "   - Migrated {$migratedAyahs} ayahs to extension table\n";
    }

    // Step 3: Create Juz mapping
    echo "\n3. Creating Juz mapping...\n";

    // Clear existing Juz data
    $pdo->exec("DELETE FROM quran_juz");

    // Create Juz entries based on ayah data
    $juzData = $pdo->query("
        SELECT
            juz,
            MIN(CONCAT(surah_number, ':', ayah_number)) as start_reference,
            MAX(CONCAT(surah_number, ':', ayah_number)) as end_reference,
            COUNT(*) as ayah_count
        FROM quran_ayahs
        WHERE juz IS NOT NULL
        GROUP BY juz
        ORDER BY juz
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($juzData as $juz) {
        $startParts = explode(':', $juz['start_reference']);
        $endParts = explode(':', $juz['end_reference']);

        $pdo->prepare("
            INSERT INTO quran_juz
            (juz_number, name, start_surah, start_ayah, end_surah, end_ayah, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ")->execute([
            $juz['juz'],
            "Juz " . $juz['juz'],
            $startParts[0],
            $startParts[1],
            $endParts[0],
            $endParts[1]
        ]);
    }

    $juzCount = $pdo->query("SELECT COUNT(*) FROM quran_juz")->fetchColumn();
    echo "   - Created {$juzCount} Juz entries\n";

    // Step 4: Verify migration
    echo "\n4. Verifying migration...\n";

    $finalStats = [
        'quran_surahs' => $pdo->query("SELECT COUNT(*) FROM quran_surahs")->fetchColumn(),
        'quran_ayahs' => $pdo->query("SELECT COUNT(*) FROM quran_ayahs")->fetchColumn(),
        'quran_juz' => $pdo->query("SELECT COUNT(*) FROM quran_juz")->fetchColumn(),
    ];

    echo "   - Final counts:\n";
    foreach ($finalStats as $table => $count) {
        echo "     * {$table}: {$count}\n";
    }

    // Step 5: Test the extension
    echo "\n5. Testing extension functionality...\n";

    // Test getting statistics
    $statsSql = "
        SELECT
            COUNT(DISTINCT s.surah_number) as total_chapters,
            COUNT(v.id) as total_ayahs,
            MAX(s.surah_number) as max_chapter,
            MAX(v.ayah_number) as max_ayah
        FROM quran_surahs s
        LEFT JOIN quran_ayahs v ON s.surah_number = v.surah_number
    ";

    $stats = $pdo->query($statsSql)->fetch(PDO::FETCH_ASSOC);
    echo "   - Statistics test:\n";
    echo "     * Total chapters: {$stats['total_chapters']}\n";
    echo "     * Total ayahs: {$stats['total_ayahs']}\n";
    echo "     * Max chapter: {$stats['max_chapter']}\n";
    echo "     * Max ayah: {$stats['max_ayah']}\n";

    echo "\n✅ Migration completed successfully!\n";
    echo "\nThe Quran extension should now work properly with the web interface.\n";

} catch (Exception $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
