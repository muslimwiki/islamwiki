<?php

// Quran.com Translations Importer
// Fetches all translations from Quran.com API v4 and upserts into quran_translations.
// Requirements: composer require guzzlehttp/guzzle

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

// DB config - adjust via env if desired
$host = getenv('DB_HOST') ?: '127.0.0.1';
$database = getenv('DB_NAME') ?: 'islamwiki';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// Config
$BASE_URL = 'https://api.quran.com/api/v4/';
$PER_PAGE = 300; // Max verses per chapter < 300
$START_CHAPTER = 1;
$END_CHAPTER = 114;
$TIMEOUT = 30;

function logln(string $msg): void { echo $msg . "\n"; }

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    logln('Connected to database');
} catch (Throwable $e) {
    fwrite(STDERR, 'DB connection failed: ' . $e->getMessage() . "\n");
    exit(1);
}

$client = new Client([
    'base_uri' => $BASE_URL,
    'timeout' => $TIMEOUT,
    'headers' => [
        'Accept' => 'application/json',
        'User-Agent' => 'IslamWikiImporter/1.0'
    ],
]);

/** Fetch JSON helper */
function apiGet(Client $client, string $uri, array $query = []): array {
    try {
        $res = $client->get($uri, ['query' => $query]);
        $code = $res->getStatusCode();
        if ($code !== 200) {
            throw new RuntimeException("HTTP $code for $uri");
        }
        $data = json_decode((string) $res->getBody(), true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON');
        }
        return $data;
    } catch (GuzzleException $e) {
        throw new RuntimeException('Request failed: ' . $e->getMessage(), 0, $e);
    }
}

// Phase 0: Seed all Arabic ayahs into quran_ayahs so translation upserts always have ayah_id
logln('Seeding Arabic ayahs into quran_ayahs...');
// Insert all required fields to satisfy NOT NULL columns (juz/page/etc.)
$insAyahStmt = $pdo->prepare('INSERT INTO quran_ayahs (surah_number, ayah_number, text, juz, page, hizb, ruku, sajda) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE text = VALUES(text), juz = VALUES(juz), page = VALUES(page), hizb = VALUES(hizb), ruku = VALUES(ruku), sajda = VALUES(sajda)');
for ($chapter = $START_CHAPTER; $chapter <= $END_CHAPTER; $chapter++) {
    $query = [
        'language' => 'ar',
        'words' => 'false',
        'per_page' => $PER_PAGE,
        'page' => 1,
        'fields' => 'text_uthmani',
    ];
    $data = apiGet($client, "verses/by_chapter/{$chapter}", $query);
    $verses = $data['verses'] ?? [];
    if (empty($verses)) { continue; }
    $pdo->beginTransaction();
    try {
        foreach ($verses as $v) {
            $verseKey = $v['verse_key'] ?? null; // e.g., 1:1
            if (!$verseKey) { continue; }
            [$s, $a] = array_map('intval', explode(':', $verseKey));
            if (!$s || !$a) { continue; }
            $textAr = $v['text_uthmani'] ?? ($v['text'] ?? '');
            $juz = isset($v['juz_number']) ? (int)$v['juz_number'] : 0;
            $page = isset($v['page_number']) ? (int)$v['page_number'] : 0;
            $hizb = isset($v['hizb_number']) ? (int)$v['hizb_number'] : 0;
            $ruku = isset($v['ruku_number']) ? (int)$v['ruku_number'] : 0;
            $sajda = 0;
            if (isset($v['sajdah'])) {
                // Quran.com may return boolean or object for sajdah
                $sajda = is_array($v['sajdah']) ? 1 : ((int)!!$v['sajdah']);
            }
            $insAyahStmt->execute([$s, $a, $textAr, $juz, $page, $hizb, $ruku, $sajda]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        logln('Failed seeding chapter ' . $chapter . ': ' . $e->getMessage());
    }
}
logln('Arabic ayahs seeding complete.');

// Step 1: Fetch languages for code mapping
logln('Fetching languages...');
$languagesRes = apiGet($client, 'resources/languages');
$langById = [];
if (isset($languagesRes['languages']) && is_array($languagesRes['languages'])) {
    foreach ($languagesRes['languages'] as $lang) {
        // Quran.com returns id, name, native_name, iso_code
        if (isset($lang['id'])) {
            $langById[(int)$lang['id']] = [
                'iso_code' => $lang['iso_code'] ?? null,
                'name' => $lang['name'] ?? null,
            ];
        }
    }
}
logln('Languages loaded: ' . count($langById));

// Helper to guess ISO code from slug or language name
function guessIsoFromResource(array $resItem, array $langById): ?string {
    // Allowable ISO-639-1 codes we expect
    $iso2 = ['af','am','ar','az','be','bg','bn','bs','ca','cs','da','de','dv','el','en','es','et','fa','fi','fr','gu','ha','he','hi','hr','hu','hy','id','it','ja','ka','kk','km','ko','ku','ky','lt','lv','ml','mr','ms','nb','ne','nl','no','or','pa','pl','ps','pt','ro','ru','si','sk','sl','so','sq','sr','sv','sw','ta','te','tg','th','tr','tt','ug','uk','ur','uz','vi','yo','zh'];

    // 1) Use explicit language_id mapping if present
    if (!empty($resItem['language_id'])) {
        $id = (int)$resItem['language_id'];
        $code = $langById[$id]['iso_code'] ?? null;
        if ($code) { $code = strtolower($code); }
        if ($code && in_array($code, $iso2, true)) { return $code; }
    }

    // 2) Prefer mapping by language_name (more reliable than slug prefixes)
    $name = strtolower((string)($resItem['language_name'] ?? ''));
    $map = [
        'english' => 'en', 'arabic' => 'ar', 'urdu' => 'ur', 'indonesian' => 'id', 'malay' => 'ms',
        'french' => 'fr', 'german' => 'de', 'spanish' => 'es', 'portuguese' => 'pt', 'italian' => 'it',
        'turkish' => 'tr', 'russian' => 'ru', 'persian' => 'fa', 'bengali' => 'bn', 'hindi' => 'hi',
        'japanese' => 'ja', 'korean' => 'ko', 'swahili' => 'sw', 'dutch' => 'nl', 'bosnian' => 'bs',
        'albanian' => 'sq', 'thai' => 'th', 'malayalam' => 'ml', 'tamil' => 'ta', 'telugu' => 'te',
        'chinese' => 'zh', 'kazakh' => 'kk', 'uzbek' => 'uz', 'somali' => 'so', 'hausa' => 'ha',
        'tajik' => 'tg', 'portuguese translation' => 'pt', 'norwegian' => 'no', 'roman urdu' => 'ur'
    ];
    if ($name && isset($map[$name])) { return $map[$name]; }

    // 3) Parse slug prefixes like 'en-', 'quran.en.' or 'quran.fr.'
    if (!empty($resItem['slug'])) {
        $slug = strtolower((string)$resItem['slug']);
        if (preg_match('/^([a-z]{2})[-_.]/', $slug, $m)) {
            $code = $m[1];
            // Fix common false positives (e.g., 'al-*' is not Albanian)
            if ($code === 'al') { $code = 'sq'; }
            if (in_array($code, $iso2, true)) { return $code; }
        }
        if (preg_match('/^quran\.([a-z]{2})\./', $slug, $m)) {
            $code = $m[1];
            if (in_array($code, $iso2, true)) { return $code; }
        }
    }

    return null;
}

// Step 2: Fetch all translation resources
logln('Fetching translations resources...');
$transRes = apiGet($client, 'resources/translations');
$resources = $transRes['translations'] ?? [];
logln('Translation resources: ' . count($resources));

if (empty($resources)) {
    logln('No translation resources found. Exiting.');
    exit(0);
}

// Prepare DB statements
$selAyahIdStmt = $pdo->prepare('SELECT id FROM quran_ayahs WHERE surah_number = ? AND ayah_number = ? LIMIT 1');
$insTransStmt = $pdo->prepare('INSERT INTO quran_translations (ayah_id, language, translator, translation) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE translation = VALUES(translation)');

$totalInserted = 0;

foreach ($resources as $resItem) {
    $resId = $resItem['id'] ?? null; // numeric id
    $resName = $resItem['name'] ?? ($resItem['author_name'] ?? '');
    if (!$resId) { continue; }

    // Determine ISO language code for this resource
    $langCode = guessIsoFromResource($resItem, $langById);
    if (!$langCode) { continue; }

    // Quick probe: check if this resource returns any translations for chapter 1
    try {
        $probe = apiGet($client, "verses/by_chapter/1", [
            'language' => 'en',
            'words' => 'false',
            'per_page' => 10,
            'page' => 1,
            'translations' => $resId,
            'translation_fields' => 'resource_name',
        ]);
        $hasAny = false;
        foreach (($probe['verses'] ?? []) as $pv) {
            if (!empty($pv['translations'])) { $hasAny = true; break; }
        }
        if (!$hasAny) {
            // Skip resources that don't return data via verses endpoint
            continue;
        }
    } catch (Throwable $e) {
        // Skip on probe error
        continue;
    }

    logln("Importing translator {$resName} (ID {$resId}) lang {$langCode}...");

    for ($chapter = $START_CHAPTER; $chapter <= $END_CHAPTER; $chapter++) {
        $pageNum = 1;
        do {
            // Fetch verses with this translation attached
            $query = [
                'language' => 'en',
                'words' => 'false',
                'per_page' => $PER_PAGE,
                'page' => $pageNum,
                'translations' => $resId,
                'translation_fields' => 'resource_name',
            ];
            $data = apiGet($client, "verses/by_chapter/{$chapter}", $query);
            $verses = $data['verses'] ?? [];
            if (empty($verses)) { break; }

            $pdo->beginTransaction();
            try {
                foreach ($verses as $v) {
                    $verseKey = $v['verse_key'] ?? null;
                    if (!$verseKey) { continue; }
                    [$s, $a] = array_map('intval', explode(':', $verseKey));
                    if (!$s || !$a) { continue; }

                    $tList = $v['translations'] ?? [];
                    if (empty($tList)) { continue; }

                    foreach ($tList as $t) {
                        $text = $t['text'] ?? null;
                        if ($text === null) { continue; }

                        $selAyahIdStmt->execute([$s, $a]);
                        $row = $selAyahIdStmt->fetch();
                        if (!$row || !isset($row['id'])) { continue; }
                        $ayahId = (int)$row['id'];

                        $insTransStmt->execute([$ayahId, $langCode, $resName, $text]);
                        $totalInserted++;
                    }
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                logln('Failed chapter ' . $chapter . ' page ' . $pageNum . ' for translator ' . $resName . ': ' . $e->getMessage());
            }

            $pageNum++;
            $total = isset($data['pagination']['total_records']) ? (int)$data['pagination']['total_records'] : 0;
            $from = isset($data['pagination']['from']) ? (int)$data['pagination']['from'] : 0;
            $to = isset($data['pagination']['to']) ? (int)$data['pagination']['to'] : 0;
            $hasMore = $to < $total && $to > 0;
        } while ($hasMore);
    }
}

logln('Done. Total translations upserted: ' . $totalInserted);
