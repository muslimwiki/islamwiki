<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;

require_once __DIR__ . '/../../vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Run this script from CLI.\n");
    exit(1);
}

// Parse options
$options = getopt('', [
    'all',
    'surah::',
    'juz::',
    'lang::',
    'translators::',
    'no-translations',
]);

$importAll = array_key_exists('all', $options);
$surahOpt = isset($options['surah']) ? (int)$options['surah'] : null;
$juzOpt = isset($options['juz']) ? (int)$options['juz'] : null;
$lang = isset($options['lang']) ? (string)$options['lang'] : 'en';
$noTranslations = array_key_exists('no-translations', $options);
$translatorNames = [];
if (isset($options['translators'])) {
    $translatorNames = array_values(array_filter(array_map('trim', explode(',', (string)$options['translators']))));
}

if (!$importAll && !$surahOpt && !$juzOpt) {
    fwrite(STDERR, "Usage:\n" .
        "  php import_quran_from_quran_com.php --all [--lang=en] [--translators=...] [--no-translations]\n" .
        "  php import_quran_from_quran_com.php --surah=1 [--lang=en] [--translators=...] [--no-translations]\n" .
        "  php import_quran_from_quran_com.php --juz=1 [--lang=en] [--translators=...] [--no-translations]\n");
    exit(1);
}

// DB setup
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
/** @var PDO $pdo */
$pdo = $manager->getQuranPdo();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function http_get_json(string $url): array
{
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 30,
            'header' => [
                'Accept: application/json',
                'User-Agent: IslamWiki-Importer/1.0'
            ],
        ]
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        throw new RuntimeException('Failed to GET ' . $url);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Invalid JSON from ' . $url);
    }
    return $data;
}

function upsertSurah(PDO $db, array $s): void
{
    $sql = "INSERT INTO surahs
            (number, name_arabic, name_english, name_translation, revelation_type, verses_count,
             juz_start, juz_end, description, created_at, updated_at)
            VALUES (:number, :name_arabic, :name_english, :name_translation, :revelation_type,
                    :verses_count, NULL, NULL, NULL, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                name_arabic = VALUES(name_arabic),
                name_english = VALUES(name_english),
                name_translation = VALUES(name_translation),
                revelation_type = VALUES(revelation_type),
                verses_count = VALUES(verses_count),
                updated_at = NOW()";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':number' => (int)$s['number'],
        ':name_arabic' => (string)$s['name_arabic'],
        ':name_english' => (string)$s['name_english'],
        ':name_translation' => (string)$s['name_translation'],
        ':revelation_type' => (string)$s['revelation_type'],
        ':verses_count' => (int)$s['verses_count'],
    ]);
}

function upsertAyah(PDO $db, array $v): int
{
    $sql = "INSERT INTO ayahs
            (surah_number, ayah_number, text_arabic, text_uthmani, text_indopak,
             juz_number, hizb_number, page_number, ruku_number, sajda_number,
             created_at, updated_at)
            VALUES (:surah_number, :ayah_number, :text_arabic, :text_uthmani, NULL,
                    :juz_number, NULL, :page_number, NULL, NULL, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                text_arabic = VALUES(text_arabic),
                text_uthmani = VALUES(text_uthmani),
                juz_number = VALUES(juz_number),
                page_number = VALUES(page_number),
                updated_at = NOW()";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':surah_number' => (int)$v['surah_number'],
        ':ayah_number' => (int)$v['verse_number'],
        ':text_arabic' => (string)($v['text_uthmani'] ?? ''),
        ':text_uthmani' => (string)($v['text_uthmani'] ?? ''),
        ':juz_number' => $v['juz_number'] !== null ? (int)$v['juz_number'] : null,
        ':page_number' => $v['page_number'] !== null ? (int)$v['page_number'] : null,
    ]);

    // Get ID
    $idStmt = $db->prepare(
        'SELECT id FROM ayahs WHERE surah_number = :s AND ayah_number = :v LIMIT 1'
    );
    $idStmt->execute([':s' => (int)$v['surah_number'], ':v' => (int)$v['verse_number']]);
    $ayahId = (int)$idStmt->fetchColumn();
    if ($ayahId <= 0) {
        throw new RuntimeException('Failed to resolve ayah id for ' . $v['surah_number'] . ':' . $v['verse_number']);
    }
    return $ayahId;
}

function ensureTranslation(PDO $db, array $t): int
{
    // Find by name + language
    $find = $db->prepare(
        'SELECT id FROM translations WHERE name = :name AND language = :language LIMIT 1'
    );
    $find->execute([':name' => (string)$t['name'], ':language' => (string)$t['language']]);
    $id = $find->fetchColumn();
    if ($id) {
        return (int)$id;
    }
    $ins = $db->prepare(
        'INSERT INTO translations (name, language, translator, description, source,
         is_official, is_active, created_at, updated_at)
         VALUES (:name, :language, :translator, :description, :source, 0, 1, NOW(), NOW())'
    );
    $ins->execute([
        ':name' => (string)$t['name'],
        ':language' => (string)$t['language'],
        ':translator' => (string)($t['translator'] ?? $t['name']),
        ':description' => (string)($t['description'] ?? ''),
        ':source' => (string)($t['source'] ?? ''),
    ]);
    return (int)$db->lastInsertId();
}

function upsertAyahTranslation(PDO $db, int $ayahId, int $translationId, string $text): void
{
    // Unique (ayah_id, translation_id)
    $sql = "INSERT INTO ayah_translations
            (ayah_id, translation_id, translation_text, created_at, updated_at)
            VALUES (:ayah_id, :translation_id, :translation_text, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                translation_text = VALUES(translation_text),
                updated_at = NOW()";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':ayah_id' => $ayahId,
        ':translation_id' => $translationId,
        ':translation_text' => $text,
    ]);
}

function fetchTranslationsList(): array
{
    $url = 'https://api.quran.com/api/v4/resources/translations';
    $json = http_get_json($url);
    $list = $json['translations'] ?? [];
    $result = [];
    foreach ($list as $t) {
        $result[] = [
            'id' => (int)$t['id'],
            'name' => (string)$t['name'],
            'language' => (string)($t['language_name'] ?? 'en'),
        ];
    }
    return $result;
}

function fetchChapters(string $lang): array
{
    $url = 'https://api.quran.com/api/v4/chapters?language=' . urlencode($lang);
    $json = http_get_json($url);
    $chapters = $json['chapters'] ?? [];
    $out = [];
    foreach ($chapters as $c) {
        $out[] = [
            'number' => (int)$c['id'],
            'name_arabic' => (string)$c['name_arabic'],
            'name_english' => (string)$c['name_simple'],
            'name_translation' => (string)($c['translated_name']['name'] ?? $c['name_simple']),
            'revelation_type' => (string)$c['revelation_place'],
            'verses_count' => (int)$c['verses_count'],
        ];
    }
    return $out;
}

function fetchAyahsByChapter(
    int $chapter,
    array $translationIds,
    string $lang
): array {
    $all = [];
    $page = 1;
    $base = 'https://api.quran.com/api/v4/verses/by_chapter/' . $chapter;
    $fields = 'text_uthmani,juz_number,page_number';
    $translations = $translationIds ? implode(',', $translationIds) : '';

    do {
        $qs = http_build_query([
            'language' => $lang,
            'fields' => $fields,
            'per_page' => 50,
            'page' => $page,
            'translations' => $translations,
        ]);
        $url = $base . '?' . $qs;
        $json = http_get_json($url);
        $verses = $json['verses'] ?? [];
        $pagination = $json['pagination'] ?? [];
        foreach ($verses as $v) {
            $all[] = $v;
        }
        $totalPages = (int)($pagination['total_pages'] ?? $page);
        $page++;
    } while ($page <= $totalPages);

    return $all;
}

/**
 * Clean translation text by removing footnote tags and other HTML-like elements
 */
function cleanTranslationText(string $text): string
{
    // Remove footnote tags like <sup foot_note=195932>
    $text = preg_replace('/<sup[^>]*>.*?<\/sup>/s', '', $text);
    
    // Remove other HTML-like tags that might be present
    $text = preg_replace('/<[^>]+>/', '', $text);
    
    // Clean up extra whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Trim whitespace
    $text = trim($text);
    
    return $text;
}

try {
    // Prepare translation mapping
    $apiTranslations = fetchTranslationsList();
    $apiNameToId = [];
    $apiIdToMeta = [];
    foreach ($apiTranslations as $t) {
        $apiNameToId[$t['name']] = $t['id'];
        $apiIdToMeta[$t['id']] = $t;
    }

    $selectedApiIds = [];
    if ($noTranslations) {
        $selectedApiIds = [];
        fwrite(STDOUT, "Info: importing ayahs only (no translations).\n");
    } elseif ($translatorNames) {
        foreach ($translatorNames as $name) {
            if (isset($apiNameToId[$name])) {
                $selectedApiIds[] = (int)$apiNameToId[$name];
            } else {
                fwrite(STDERR, "Warning: translator not found on API: {$name}\n");
            }
        }
    } else {
        // Default: ALL available translators
        $selectedApiIds = array_values(array_map(fn($t) => (int)$t['id'], $apiTranslations));
        fwrite(STDOUT, "Info: importing with all available translators (" . count($selectedApiIds) . ")\n");
    }

    if ($importAll || $surahOpt) {
        // Fetch and upsert surahs (in chosen UI language for names)
        $chapters = fetchChapters($lang);
        foreach ($chapters as $c) {
            upsertSurah($pdo, $c);
        }

        // Determine which chapters to import ayahs for
        $chapterList = $surahOpt ? [ (int)$surahOpt ] : array_map(fn($c) => (int)$c['number'], $chapters);

        foreach ($chapterList as $ch) {
            echo "Importing Surah {$ch}...\n";
            $ayahs = fetchAyahsByChapter($ch, $selectedApiIds, $lang);
            foreach ($ayahs as $v) {
                $vk = (string)$v['verse_key'];
                if (strpos($vk, ':') !== false) {
                    [$sNum, $aNum] = array_map('intval', explode(':', $vk, 2));
                } else {
                    $sNum = (int)$ch;
                    $aNum = (int)($v['verse_number'] ?? 0);
                }

                $ayahRow = [
                    'surah_number' => $sNum,
                    'verse_number' => $aNum,
                    'text_arabic' => (string)($v['text_uthmani'] ?? ''),
                    'text_uthmani' => (string)($v['text_uthmani'] ?? ''),
                    'juz_number' => isset($v['juz_number']) ? (int)$v['juz_number'] : null,
                    'page_number' => isset($v['page_number']) ? (int)$v['page_number'] : null,
                ];
                $ayahId = upsertAyah($pdo, $ayahRow);

                if ($noTranslations) {
                    continue;
                }
                // Translations from API
                $vtList = (array)($v['translations'] ?? []);
                foreach ($vtList as $vt) {
                    // Prefer mapping by resource_id; fallback to provided names
                    $resourceId = isset($vt['resource_id']) ? (int)$vt['resource_id'] : null;
                    $apiText = (string)($vt['text'] ?? '');
                    if ($apiText === '') {
                        continue;
                    }
                    $meta = $resourceId && isset($apiIdToMeta[$resourceId]) ? $apiIdToMeta[$resourceId] : null;
                    $transName = $meta['name'] ?? (string)($vt['resource_name'] ?? '');
                    $transLang = $meta['language'] ?? (string)($vt['language_name'] ?? $lang);
                    if ($transName === '') {
                        // Skip if we cannot resolve a name
                        continue;
                    }
                    $localTransId = ensureTranslation($pdo, [
                        'name' => $transName,
                        'language' => $transLang,
                        'translator' => $transName,
                        'description' => 'Imported from api.quran.com',
                        'source' => 'quran.com',
                    ]);
                    $cleanText = cleanTranslationText($apiText);
                    upsertAyahTranslation($pdo, $ayahId, $localTransId, $cleanText);
                }
            }
        }
    }

    if ($juzOpt) {
        fwrite(STDOUT, "Note: --juz currently imports via chapters data (already handled).\n");
    }

    echo "Import completed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
