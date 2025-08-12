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
/** @var PDO $pdo */
$pdo = $manager->getQuranPdo();

function upsertTranslation(PDO $db, array $t): int
{
    $stmt = $db->prepare(
        "SELECT id FROM translations WHERE name = :name AND language = :language LIMIT 1"
    );
    $stmt->execute([':name' => $t['name'], ':language' => $t['language']]);
    $id = $stmt->fetchColumn();

    if ($id) {
        $update = $db->prepare(
            "UPDATE translations
             SET translator = :translator,
                 description = :description,
                 source = :source,
                 is_official = :is_official,
                 is_active = 1,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $update->execute([
            ':translator' => $t['translator'],
            ':description' => $t['description'] ?? null,
            ':source' => $t['source'] ?? null,
            ':is_official' => (int)($t['is_official'] ?? 0),
            ':id' => $id,
        ]);
        return (int)$id;
    }

    $insert = $db->prepare(
        "INSERT INTO translations
         (name, language, translator, description, source, is_official, is_active, created_at, updated_at)
         VALUES (:name, :language, :translator, :description, :source, :is_official, 1, NOW(), NOW())"
    );
    $insert->execute([
        ':name' => $t['name'],
        ':language' => $t['language'],
        ':translator' => $t['translator'],
        ':description' => $t['description'] ?? null,
        ':source' => $t['source'] ?? null,
        ':is_official' => (int)($t['is_official'] ?? 0),
    ]);
    return (int)$db->lastInsertId();
}

$translators = [
    [
        'name' => 'Saheeh International',
        'language' => 'en',
        'translator' => 'Saheeh International',
        'description' => 'Popular modern English translation.',
        'source' => 'https://quran.com/translations',
        'is_official' => 0,
    ],
    [
        'name' => 'Pickthall',
        'language' => 'en',
        'translator' => 'Marmaduke Pickthall',
        'description' => 'The Meaning of the Glorious Koran (1930).',
        'source' => 'https://quran.com/translations',
        'is_official' => 0,
    ],
    [
        'name' => 'Yusuf Ali',
        'language' => 'en',
        'translator' => 'Abdullah Yusuf Ali',
        'description' => 'The Holy Quran: Translation and Commentary (1934).',
        'source' => 'https://quran.com/translations',
        'is_official' => 0,
    ],
    [
        'name' => 'Muhsin Khan',
        'language' => 'en',
        'translator' => 'Hilali & Khan',
        'description' => 'Hilali-Khan translation.',
        'source' => 'https://quran.com/translations',
        'is_official' => 0,
    ],
    [
        'name' => 'Dr. Ghali',
        'language' => 'en',
        'translator' => 'Dr. M. M. Ghali',
        'description' => 'Precise modern English rendering.',
        'source' => 'https://quran.com/translations',
        'is_official' => 0,
    ],
    [
        'name' => 'Tafsir al-Muyassar',
        'language' => 'ar',
        'translator' => 'التفسير الميسر',
        'description' => 'التفسير الميسر المعتمد.',
        'source' => 'https://quran.com/translations',
        'is_official' => 0,
    ],
    [
        'name' => 'Urdu - Ahmed Ali',
        'language' => 'ur',
        'translator' => 'Ahmed Ali',
        'description' => 'Urdu translation.',
        'source' => 'https://quran.com/translations',
        'is_official' => 0,
    ],
];

try {
    $pdo->beginTransaction();

    $count = 0;
    foreach ($translators as $t) {
        upsertTranslation($pdo, $t);
        $count++;
    }

    $pdo->commit();
    echo "Seeded translations: {$count} entries.\n";
    echo "Note: verse_translations not seeded here (needs source dataset).\n";
    echo "You can import verse texts per translator later to populate verse_translations.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Error seeding translations: " . $e->getMessage() . "\n");
    exit(1);
}
