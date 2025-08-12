<?php

declare(strict_types=1);

use PDO;
use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;

require_once __DIR__ . '/../../vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    echo "Run this script from CLI.\n";
    exit(1);
}

$configs = [
    'quran' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
];

$manager = new IslamicDatabaseManager($configs);
$pdo = $manager->getQuranPdo();

echo "Quran Database Data Check\n";
echo "========================\n\n";

// Check surahs
$stmt = $pdo->query("SELECT COUNT(*) as count FROM surahs");
$surahCount = $stmt->fetchColumn();
echo "📚 Surahs: {$surahCount}\n";

// Check ayahs
$stmt = $pdo->query("SELECT COUNT(*) as count FROM ayahs");
$ayahCount = $stmt->fetchColumn();
echo "📖 Ayahs: {$ayahCount}\n";

// Check translations
$stmt = $pdo->query("SELECT COUNT(*) as count FROM translations");
$translationCount = $stmt->fetchColumn();
echo "🌐 Translations: {$translationCount}\n";

// Check ayah translations
$stmt = $pdo->query("SELECT COUNT(*) as count FROM ayah_translations");
$ayahTranslationCount = $stmt->fetchColumn();
echo "📝 Ayah Translations: {$ayahTranslationCount}\n";

// Check recitations
$stmt = $pdo->query("SELECT COUNT(*) as count FROM recitations");
$recitationCount = $stmt->fetchColumn();
echo "🎵 Recitations: {$recitationCount}\n";

// Check tafsir sources
$stmt = $pdo->query("SELECT COUNT(*) as count FROM tafsir_sources");
$tafsirCount = $stmt->fetchColumn();
echo "📚 Tafsir Sources: {$tafsirCount}\n";

// Check tajweed rules
$stmt = $pdo->query("SELECT COUNT(*) as count FROM tajweed_rules");
$tajweedCount = $stmt->fetchColumn();
echo "🔤 Tajweed Rules: {$tajweedCount}\n";

// Check topics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM quranic_topics");
$topicCount = $stmt->fetchColumn();
echo "🏷️  Topics: {$topicCount}\n";

echo "\n";

// Show sample data if available
if ($surahCount > 0) {
    echo "Sample Surah:\n";
    $stmt = $pdo->query("SELECT * FROM surahs LIMIT 1");
    $surah = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($surah);
    echo "\n";
}

if ($ayahCount > 0) {
    echo "Sample Ayah:\n";
    $stmt = $pdo->query("SELECT * FROM ayahs LIMIT 1");
    $ayah = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($ayah);
    echo "\n";
}

if ($translationCount > 0) {
    echo "Sample Translation:\n";
    $stmt = $pdo->query("SELECT * FROM translations LIMIT 1");
    $translation = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($translation);
    echo "\n";
}

echo "\nQuran data check complete.\n";
