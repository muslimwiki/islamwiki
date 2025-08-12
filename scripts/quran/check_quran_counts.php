<?php

declare(strict_types=1);

use PDO;
use Throwable;
use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;

require_once __DIR__ . '/../../vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Run this script from CLI.\n");
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

try {
    $manager = new IslamicDatabaseManager($configs);
    /** @var PDO $pdo */
    $pdo = $manager->getQuranPdo();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = ['surahs', 'ayahs', 'translations', 'ayah_translations'];
    $counts = [];
    foreach ($tables as $t) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `{$t}`");
            $counts[$t] = (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $counts[$t] = -1; // table missing or query failed
        }
    }

    // Map table names to output keys
    $counts = [
        'surahs' => $counts['surahs'],
        'verses' => $counts['ayahs'],
        'translations' => $counts['translations'],
        'verse_translations' => $counts['ayah_translations']
    ];

    echo json_encode($counts, JSON_PRETTY_PRINT) . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
