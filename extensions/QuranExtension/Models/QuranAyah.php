<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\QuranExtension\Models;

use IslamWiki\Core\Database\Connection;
use Exception;
use RuntimeException;

/**
 * QuranAyah Model
 *
 * Handles all Quran-related database operations including ayahs, surahs, and Juz.
 */
class QuranAyah
{
    private Connection $db;
    private array $config;

    public function __construct(?Connection $db = null)
    {
        if (!$db) {
            // Try to get from container if available
            try {
                $container = app();
                if ($container && method_exists($container, 'get')) {
                    $db = $container->get('db');
                }
            } catch (Exception $e) {
                error_log("Could not get database connection from container: " . $e->getMessage());
            }
        }

        if (!$db) {
            throw new RuntimeException("Database connection is required for QuranAyah model");
        }

        $this->db = $db;
        $this->config = config('quran', []);
    }

    /**
     * Get table name
     */
    protected function getTable(): string
    {
        return 'verses';
    }

    /**
     * Get ayah by ID
     */
    public function findById(int $id, string $language = 'english', string $translator = 'Saheeh International'): ?array
    {
        try {
            $sql = "
                SELECT 
                    v.*,
                    vt.translation as translation,
                    vt.translator,
                    vt.language
                FROM quran_ayahs v
                LEFT JOIN quran_translations vt ON v.id = vt.ayah_id
                WHERE v.id = ? AND vt.language = ? AND vt.translator = ?
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$language, $translator, $id]);
            $result = $stmt->fetch();

            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error finding ayah by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get ayah by surah and ayah number
     */
    public function getByReference(int $surah, int $ayah, string $language = 'english', string $translator = 'Saheeh International'): ?array
    {
        try {
            $sql = "
                SELECT 
                    v.*,
                    vt.translation as translation,
                    vt.translator,
                    vt.language
                FROM quran_ayahs v
                LEFT JOIN quran_translations vt ON v.id = vt.ayah_id
                WHERE v.surah_number = ? AND v.ayah_number = ? AND vt.language = ? AND vt.translator = ?
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surah, $ayah, $language, $translator]);
            $result = $stmt->fetch();

            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting ayah by reference: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all ayahs in a surah
     */
    public function getBySurah(int $surah, string $language = 'english', string $translator = 'Saheeh International'): array
    {
        try {
            $sql = "
                SELECT 
                    v.*,
                    vt.translation as translation,
                    vt.translator,
                    vt.language
                FROM quran_ayahs v
                LEFT JOIN quran_translations vt ON v.id = vt.ayah_id
                WHERE v.surah_number = ? AND vt.language = ? AND vt.translator = ?
                ORDER BY v.ayah_number ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$language, $translator, $surah]);

            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Error getting ayahs by surah: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all ayahs in a Juz
     */
    public function getByJuz(int $juz, string $language = 'english', string $translator = 'Saheeh International'): array
    {
        try {
            $sql = "
                SELECT 
                    v.*,
                    vt.translation,
                    vt.translator,
                    vt.language
                FROM quran_ayahs v
                LEFT JOIN quran_translations vt ON v.id = vt.ayah_id
                    AND vt.language = ?
                    AND vt.translator = ?
                WHERE v.juz_number = ?
                ORDER BY v.surah_number ASC, v.ayah_number ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$language, $translator, $juz]);

            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Error getting ayahs by Juz: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all ayahs on a specific page
     */
    public function getByPage(int $page, string $language = 'english', string $translator = 'Saheeh International'): array
    {
        try {
            $sql = "
                SELECT 
                    v.*,
                    vt.translation,
                    vt.translator,
                    vt.language
                FROM quran_ayahs v
                LEFT JOIN quran_translations vt ON v.id = vt.ayah_id
                    AND vt.translator = ? AND vt.language = ?
                WHERE v.page_number = ?
                ORDER BY v.surah_number ASC, v.ayah_number ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$translator, $language, $page]);

            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Error getting ayahs by page: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search ayahs by keywords
     */
    public function getByKeywords(array $keywords, string $language = 'english', int $limit = 50): array
    {
        try {
            $placeholders = str_repeat('?,', count($keywords) - 1) . '?';
            $sql = "
                SELECT DISTINCT
                    v.*,
                    vt.translation as translation,
                    vt.translator,
                    vt.language
                FROM quran_ayahs v
                LEFT JOIN quran_translations vt ON v.id = vt.ayah_id
                WHERE vt.language = ? AND vt.translation LIKE CONCAT('%', ?, '%')
                OR vt.translation LIKE CONCAT('%', ?, '%')
                OR vt.translation LIKE CONCAT('%', ?, '%')
                LIMIT ?
            ";

            $params = array_merge([$language], $keywords, [$limit]);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Error searching ayahs by keywords: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get random ayah
     */
    public function getRandomAyah(string $language = 'english', string $translator = 'Saheeh International'): ?array
    {
        try {
            $sql = "
                SELECT 
                    v.*,
                    vt.translation as translation,
                    vt.translator,
                    vt.language
                FROM quran_ayahs v
                LEFT JOIN quran_translations vt ON v.id = vt.ayah_id
                WHERE vt.language = ? AND vt.translator = ?
                ORDER BY RAND()
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$language, $translator]);
            $result = $stmt->fetch();

            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting random ayah: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get daily ayahs (for homepage)
     */
    public function getDailyAyahs(int $limit = 10): array
    {
        try {
            $sql = "
                SELECT 
                    v.*,
                    vt.translation_text as translation,
                    t.translator,
                    t.language
                FROM ayahs v
                LEFT JOIN ayah_translations vt ON v.id = vt.ayah_id
                LEFT JOIN translations t ON vt.translation_id = t.id
                    AND t.language = 'english' AND t.name = 'Saheeh International'
                ORDER BY v.surah_number ASC, v.ayah_number ASC
                LIMIT ?
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);

            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Error getting daily ayahs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get surah information
     */
    public function getSurahInfo(int $surah): ?array
    {
        try {
            $sql = "
                SELECT 
                    s.*,
                    COUNT(v.id) as ayah_count
                FROM surahs s
                LEFT JOIN ayahs v ON s.number = v.surah_number
                WHERE s.number = ?
                GROUP BY s.number
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$surah]);
            $result = $stmt->fetch();

            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting surah info: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all surahs
     */
    public function getAllSurahs(): array
    {
        try {
            $sql = "
                SELECT 
                    s.*,
                    COUNT(v.id) as ayah_count
                FROM quran_surahs s
                LEFT JOIN quran_ayahs v ON s.surah_number = v.surah_number
                GROUP BY s.surah_number
                ORDER BY s.surah_number ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Error getting all surahs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Juz statistics
     */
    public function getJuzStats(): array
    {
        try {
            $sql = "
                SELECT 
                    juz_number as juz,
                    COUNT(*) as ayah_count,
                    MIN(surah_number) as start_surah,
                    MAX(surah_number) as end_surah
                FROM quran_ayahs
                GROUP BY juz_number
                ORDER BY juz_number ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            error_log("Error getting Juz stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all available translators for a language
     */
    public function getAllTranslators(string $language = 'english'): array
    {
        try {
            // Map common language names to database codes
            $languageMap = [
                'english' => 'en',
                'en' => 'en',
                'arabic' => 'ar',
                'ar' => 'ar',
                'urdu' => 'ur',
                'ur' => 'ur'
            ];
            
            $dbLanguage = $languageMap[$language] ?? $language;
            
            $sql = "
                SELECT DISTINCT
                    translator,
                    language,
                    COUNT(*) as translation_count
                FROM quran_translations
                WHERE language = ?
                GROUP BY translator, language
                ORDER BY translator ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$dbLanguage]);

            $results = $stmt->fetchAll() ?: [];

            // Add IDs for consistency
            foreach ($results as &$result) {
                $result['id'] = $result['translator'];
                $result['name'] = $result['translator'];
            }

            return $results;
        } catch (Exception $e) {
            error_log("Error getting translators: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get tafsir for an ayah
     */
    public function getTafsir(int $ayahId, string $language = 'english'): ?array
    {
        try {
            // For now, return empty array since tafsir tables don't exist yet
            // TODO: Implement when tafsir tables are created
            return [];
        } catch (Exception $e) {
            error_log("Error getting tafsir: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recitation for an ayah
     */
    public function getRecitation(int $ayahId, string $reciter = 'default'): ?array
    {
        try {
            // For now, return empty array since recitation tables don't exist yet
            // TODO: Implement when recitation tables are created
            return [];
        } catch (Exception $e) {
            error_log("Error getting recitation: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Quran statistics
     */
    public function getStatistics(): array
    {
        try {
            $sql = "
                SELECT 
                    COUNT(DISTINCT s.surah_number) as total_chapters,
                    COUNT(v.id) as total_ayahs,
                    MAX(s.surah_number) as max_chapter,
                    MAX(v.ayah_number) as max_ayah
                FROM quran_surahs s
                LEFT JOIN quran_ayahs v ON s.surah_number = v.surah_number
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();

            return $result ?: [
                'total_chapters' => 114,
                'total_ayahs' => 6236,
                'max_chapter' => 114,
                'max_ayah' => 286
            ];
        } catch (Exception $e) {
            error_log("Error getting Quran statistics: " . $e->getMessage());
            return [
                'total_chapters' => 114,
                'total_ayahs' => 6236,
                'max_chapter' => 114,
                'max_ayah' => 286
            ];
        }
    }

    /**
     * Search ayahs (legacy method for backward compatibility)
     */
    public function search(string $query, string $language = 'english', int $limit = 50, string $translator = 'Saheeh International'): array
    {
        return $this->getByKeywords([$query], $language, $limit);
    }
}
