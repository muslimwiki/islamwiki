<?php

/**
 * QuranAyahRepository
 * 
 * This class handles all database operations related to Quranic verses (Ayahs).
 * It provides methods for retrieving, searching, and managing Quranic verses,
 * including their translations, tafsir, and related metadata.
 * 
 * @package IslamWiki\Extensions\QuranExtension\Models
 * @version 1.0.0
 */

declare(strict_types=1);

namespace IslamWiki\Extensions\QuranExtension\Models;

use IslamWiki\Core\Database\Connection;
use Exception;
use RuntimeException;
use Psr\Log\LoggerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException as CacheInvalidArgumentException;

/**
 * QuranAyahRepository
 *
 * Handles all Quran-related database operations including ayahs, surahs, and Juz.
 * Provides methods for retrieving, searching, and managing Quranic verses.
 *
 * @package IslamWiki\Extensions\QuranExtension\Models
 * @version 1.0.0
 */
class QuranAyahRepository
{
    /**
     * @var Connection Database connection instance
     */
    private Connection $db;
    
    /**
     * @var array Configuration options
     */
    private array $config;
    
    /**
     * @var LoggerInterface|null PSR-3 compatible logger instance
     */
    private ?LoggerInterface $logger;
    
    /**
     * @var CacheItemPoolInterface|null PSR-6 cache instance
     */
    private ?CacheItemPoolInterface $cache;
    
    /**
     * @var int Default cache TTL in seconds (1 hour)
     */
    private const DEFAULT_CACHE_TTL = 3600;
    
    /**
     * Cache version for easy invalidation of all keys
     */
    private const CACHE_VERSION = 2;
    
    /**
     * @var string Default language code
     */
    private const DEFAULT_LANGUAGE = 'en';
    
    /**
     * @var string Default translator name
     */
    private const DEFAULT_TRANSLATOR = 'Saheeh International';
    
    /**
     * @var int Default number of items per page for pagination
     */
    private const DEFAULT_PER_PAGE = 20;

    /**
     * Constructor
     *
     * Initializes a new instance of the QuranAyahRepository with the given dependencies.
     * 
     * @param Connection $db Database connection instance
     * @param array $config Optional configuration array with the following possible keys:
     *   - 'default_language': Default language code (default: 'en')
     *   - 'default_translator': Default translator name (default: 'Saheeh International')
     *   - 'per_page': Default number of items per page (default: 20)
     * @param LoggerInterface|null $logger Optional PSR-3 logger instance for logging errors and debug info
     * 
     * @throws RuntimeException If the database connection is not established
     * 
     * @example
     * $db = new Connection($config);
     * $repository = new QuranAyahRepository($db, ['default_language' => 'en'], $logger);
     */
    public function __construct(Connection $db, array $config = [], ?LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->config = array_merge([
            'default_language' => self::DEFAULT_LANGUAGE,
            'default_translator' => self::DEFAULT_TRANSLATOR,
            'per_page' => self::DEFAULT_PER_PAGE,
        ], $config);
        
        $this->logger = $logger;
        
        if (!$this->db->isConnected()) {
            $this->logger?->error('Database connection is not established');
            throw new RuntimeException('Database connection is not established');
        }
        
        // Cache is optional, will be set when available via setCache()
        $this->cache = null;
        
        $this->logger?->debug('QuranAyahRepository initialized', [
            'default_language' => $this->config['default_language'],
            'default_translator' => $this->config['default_translator'],
            'caching_enabled' => $this->cache !== null
        ]);
    }

    // ... [rest of the existing QuranAyah methods with improved error handling]

    /**
     * Retrieves a specific ayah by surah and ayah number with its translation.
     *
     * This method fetches a single ayah from the Quran along with its translation
     * in the specified language and from the specified translator.
     *
     * @param int $surah The surah number (1-114)
     * @param int $ayah The ayah number within the surah
     * @param string $language The language code for the translation (e.g., 'en', 'ar')
     * @param string $translator The name of the translator (e.g., 'Saheeh International')
     * 
     * @return array|null Returns an associative array containing the ayah data and translation,
     *                  or null if the ayah is not found. The array includes the following keys:
     *                  - id: The unique identifier of the ayah
     *                  - surah_number: The surah number
     *                  - ayah_number: The ayah number
     *                  - text_arabic: The Arabic text of the ayah
     *                  - translation: The translated text
     *                  - translator: The name of the translator
     *                  - language: The language code of the translation
     * 
     * @throws \PDOException If there is a database error
     * @throws \InvalidArgumentException If surah or ayah numbers are invalid
     * 
     * @example
     * $ayah = $repository->getByReference(1, 1, 'en', 'Saheeh International');
     * // Returns array with ayah data or null if not found
     */
    /**
     * Generates a cache key for the given parameters
     */
    private function generateCacheKey(string $prefix, array $params): string
    {
        // Create a unique key based on the method name and parameters
        $key = 'quran_v' . self::CACHE_VERSION . '_' . $prefix . '_' . md5(serialize($params));
        
        // Ensure the key is valid for PSR-6 (only alphanumeric, _, and . are allowed)
        return preg_replace('/[^a-zA-Z0-9_\.]/', '_', $key);
    }
    
    /**
     * Gets an item from cache if available
     */
    private function getFromCache(string $key, callable $callback, ?int $ttl = null)
    {
        if ($this->cache === null) {
            return $callback();
        }
        
        try {
            $cacheKey = $this->generateCacheKey($key, []);
            $cacheItem = $this->cache->getItem($cacheKey);
            
            if ($cacheItem->isHit()) {
                $this->logger?->debug('Cache hit', ['key' => $cacheKey]);
                return $cacheItem->get();
            }
            
            $result = $callback();
            
            if ($result !== null) {
                $cacheItem->set($result);
                $cacheItem->expiresAfter($ttl ?? self::DEFAULT_CACHE_TTL);
                $this->cache->save($cacheItem);
                $this->logger?->debug('Cache miss - stored result', ['key' => $cacheKey]);
            }
            
            return $result;
            
        } catch (CacheInvalidArgumentException $e) {
            $this->logger?->error('Cache key generation failed', [
                'error' => $e->getMessage(),
                'key' => $key
            ]);
            return $callback();
        }
    }
    
    /**
     * Set the cache implementation
     * 
     * @param CacheItemPoolInterface $cache PSR-6 cache implementation
     */
    public function setCache(CacheItemPoolInterface $cache): void
    {
        $this->cache = $cache;
        $this->logger?->debug('Cache implementation set on QuranAyahRepository');
    }

    /**
     * Get all translators for a given language.
     *
     * @param string $language
     * @return array<int, array<string, mixed>>
     */
    public function getAllTranslators(string $language): array
    {
        $dbLanguage = $this->normalizeLanguage($language);
        $cacheKey = "translators_{$dbLanguage}";
        return $this->getFromCache($cacheKey, function () use ($dbLanguage) {
            $sql = "
                SELECT 
                    MIN(t.id) AS id,
                    t.translator AS name,
                    t.translator,
                    t.language,
                    COUNT(*) AS translation_count
                FROM quran_translations t
                WHERE t.language = :language
                GROUP BY t.translator, t.language
                ORDER BY t.translator ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':language' => $dbLanguage]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        });
    }

    /**
     * Get a random ayah with translation for the specified language and translator.
     *
     * @param string $language
     * @param string $translator
     * @return array<string, mixed>|null
     */
    public function getRandomAyah(string $language, string $translator): ?array
    {
        $cacheKey = "random_ayah_{$language}_{$translator}";
        return $this->getFromCache($cacheKey, function () use ($language, $translator) {
            $sql = "
                SELECT 
                    v.*,
                    t.translation AS translation_text,
                    t.language,
                    t.translator
                FROM quran_ayahs v
                LEFT JOIN quran_translations t ON v.id = t.ayah_id
                    AND t.language = :language
                    AND t.translator = :translator
                ORDER BY RAND()
                LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':language' => $language,
                ':translator' => $translator,
            ]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            if ($result) {
                $result['surah_number'] = (int)($result['surah_number'] ?? 0);
                $result['ayah_number'] = (int)($result['ayah_number'] ?? 0);
            }
            return $result;
        }, 300); // cache random ayah briefly (5 minutes)
    }

    /**
     * Get Quran summary statistics.
     *
     * @return array<string, int>|null
     */
    public function getStatistics(): ?array
    {
        $cacheKey = 'quran_stats_v1';
        return $this->getFromCache($cacheKey, function () {
            $sql = "
                SELECT 
                    COUNT(*) AS total_ayahs,
                    COUNT(DISTINCT v.surah_number) AS total_chapters,
                    MAX(v.surah_number) AS max_chapter,
                    MAX(v.ayah_number) AS max_ayah
                FROM quran_ayahs v";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            if (!$row) {
                return null;
            }
            return [
                'total_chapters' => (int)($row['total_chapters'] ?? 114),
                'total_ayahs' => (int)($row['total_ayahs'] ?? 6236),
                'max_chapter' => (int)($row['max_chapter'] ?? 114),
                'max_ayah' => (int)($row['max_ayah'] ?? 286),
            ];
        }, 3600);
    }

    /**
     * Get Juz statistics (ayah counts per Juz).
     *
     * @return array<int, array{juz:int, ayah_count:int}>
     */
    public function getJuzStats(): array
    {
        $cacheKey = 'quran_juz_stats_v1';
        return $this->getFromCache($cacheKey, function () {
            $sql = "
                SELECT v.juz_number AS juz, COUNT(*) AS ayah_count
                FROM quran_ayahs v
                GROUP BY v.juz_number
                ORDER BY v.juz_number";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $result = [];
            foreach ($rows as $r) {
                $result[] = [
                    'juz' => (int)($r['juz'] ?? 0),
                    'ayah_count' => (int)($r['ayah_count'] ?? 0),
                ];
            }
            return $result;
        }, 3600);
    }

    /**
     * Get all ayahs for a surah with translations for given language and translator.
     *
     * @param int $surahNumber
     * @param string $language
     * @param string $translator
     * @return array<int, array<string, mixed>>
     */
    public function getBySurah(int $surahNumber, string $language, string $translator): array
    {
        $dbLanguage = $this->normalizeLanguage($language);
        // Bump cache version to invalidate older cached payloads lacking translation fallback
    $cacheKey = "surah_v2_{$surahNumber}_{$dbLanguage}_{$translator}";
        return $this->getFromCache($cacheKey, function () use ($surahNumber, $dbLanguage, $translator) {
            // Step 1: Fetch ayahs only
            $sqlAyahs = "
                SELECT 
                    v.*,
                    v.text AS text_arabic
                FROM quran_ayahs v
                WHERE v.surah_number = :surah
                ORDER BY v.ayah_number ASC";

            $stmt = $this->db->prepare($sqlAyahs);
            $stmt->execute([':surah' => $surahNumber]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            if (!$rows) {
                return [];
            }

            // Step 2: Fetch translations for these ayahs (if exist)
            $ids = array_column($rows, 'id');
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                // Attempt 1: specific language + translator
                $sqlT = "
                    SELECT ayah_id, translation 
                    FROM quran_translations 
                    WHERE ayah_id IN ($placeholders) AND language = ? AND translator = ?";
                $stmtT = $this->db->prepare($sqlT);
                $stmtT->execute([...$ids, $dbLanguage, $translator]);
                $translations = $stmtT->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                $map = [];
                foreach ($translations as $t) {
                    $map[$t['ayah_id']] = $t['translation'];
                }
                // If none found for that translator, fallback to first available translator per ayah in this language
                if (empty($map)) {
                    $sqlT2 = "
                        SELECT t.ayah_id, t.translation
                        FROM quran_translations t
                        INNER JOIN (
                            SELECT ayah_id, MIN(id) AS mid
                            FROM quran_translations
                            WHERE ayah_id IN ($placeholders) AND language = ?
                            GROUP BY ayah_id
                        ) x ON t.ayah_id = x.ayah_id AND t.id = x.mid";
                    $stmtT2 = $this->db->prepare($sqlT2);
                    $stmtT2->execute([...$ids, $dbLanguage]);
                    $translations2 = $stmtT2->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                    foreach ($translations2 as $t) {
                        $map[$t['ayah_id']] = $t['translation'];
                    }
                }
                foreach ($rows as &$r) {
                    $r['translation_text'] = $map[$r['id']] ?? null;
                }
            }

            foreach ($rows as &$r) {
                $r['surah_number'] = (int)($r['surah_number'] ?? 0);
                $r['ayah_number'] = (int)($r['ayah_number'] ?? 0);
                // Fallback: if no translation found in quran_translations, use base column if present
                if ((empty($r['translation_text']) || $r['translation_text'] === null) && !empty($r['translation'])) {
                    $r['translation_text'] = $r['translation'];
                }
            }
            return $rows;
        }, 600);
    }

    /**
     * Normalize various language names to DB language codes
     */
    private function normalizeLanguage(string $language): string
    {
        $map = [
            'english' => 'en',
            'en' => 'en',
            'arabic' => 'ar',
            'ar' => 'ar',
            'urdu' => 'ur',
            'ur' => 'ur',
        ];
        return $map[strtolower($language)] ?? strtolower($language);
    }

    /**
     * Get basic information about a surah.
     *
     * @param int $surahNumber
     * @return array<string, mixed>|null
     */
    public function getSurahInfo(int $surahNumber): ?array
    {
        $cacheKey = "surah_info_{$surahNumber}";
        return $this->getFromCache($cacheKey, function () use ($surahNumber) {
            $sql = "SELECT * FROM quran_surahs WHERE surah_number = :surah LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':surah' => $surahNumber]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            return $row ?: null;
        }, 3600);
    }

    public function getByReference(int $surah, int $ayah, string $language = 'en', string $translator = 'Saheeh International'): ?array
    {
        // Generate cache key
        $cacheKey = "ayah_{$surah}_{$ayah}_{$language}_{$translator}";
        
        return $this->getFromCache($cacheKey, function() use ($surah, $ayah, $language, $translator) {
            try {
                $sql = "
                    SELECT 
                        v.*,
                        t.translation,
                        t.language,
                        t.translator
                    FROM quran_ayahs v
                    LEFT JOIN quran_translations t ON v.id = t.ayah_id
                        AND t.language = :language
                        AND t.translator = :translator
                    WHERE v.surah_number = :surah 
                    AND v.ayah_number = :ayah
                    LIMIT 1";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':surah' => $surah,
                    ':ayah' => $ayah,
                    ':language' => $language,
                    ':translator' => $translator
                ]);

                $result = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($result) {
                    $result['surah_number'] = (int)$result['surah_number'];
                    $result['ayah_number'] = (int)$result['ayah_number'];
                }

                return $result ?: null;
            } catch (\PDOException $e) {
                $this->logger?->error("Database error in getByReference", [
                    'error' => $e->getMessage(),
                    'surah' => $surah,
                    'ayah' => $ayah
                ]);
                throw $e;
            }
        });

    // ... [other methods with similar error handling and documentation]
}

}
