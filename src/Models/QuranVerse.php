<?php

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;
use PDO;

/**
 * QuranVerse Model
 *
 * Handles Quran verse data and operations for Phase 4 Islamic features integration.
 * Provides functionality for Quran verse retrieval, search, and wiki integration.
 *
 * @package IslamWiki\Models
 * @version 0.0.13
 * @since Phase 4
 */
class QuranVerse
{
    private $db;
    private $table = 'verses';
    private $translationsTable = 'verse_translations';
    private $tafsirTable = 'tafsir';
    private $recitationsTable = 'verse_recitations';

    public function __construct(IslamicDatabaseManager $islamicDbManager = null)
    {
        if ($islamicDbManager === null) {
            // Create default configurations
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
            $islamicDbManager = new IslamicDatabaseManager($configs);
        }

        $this->db = $islamicDbManager->getQuranConnection();
    }

    /**
     * Get a specific Quran verse by ID
     *
     * @param int $id Verse ID
     * @return array|null Verse data or null if not found
     */
    public function findById($id)
    {
        $sql = "SELECT v.*, t.translation_text, tr.translator, tr.language
                FROM {$this->table} v
                LEFT JOIN {$this->translationsTable} t ON v.id = t.verse_id
                LEFT JOIN translations tr ON t.translation_id = tr.id
                WHERE v.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get verses by chapter and verse numbers
     *
     * @param int $chapter Chapter number (1-114)
     * @param int|null $verse Verse number (optional)
     * @return array Array of verses
     */
    public function getByChapter($chapter, $verse = null)
    {
        $sql = "SELECT v.*, t.translation_text, tr.translator, tr.language
                FROM {$this->table} v
                LEFT JOIN {$this->translationsTable} t ON v.id = t.verse_id
                LEFT JOIN translations tr ON t.translation_id = tr.id
                WHERE v.surah_number = :chapter";

        $params = [':chapter' => $chapter];

        if ($verse !== null) {
            $sql .= " AND v.verse_number = :verse";
            $params[':verse'] = $verse;
        }

        $sql .= " ORDER BY v.verse_number";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search Quran verses by text
     *
     * @param string $query Search query
     * @param string $language Language code for translations
     * @param int $limit Maximum results to return
     * @return array Search results
     */
    public function search($query, $language = 'en', $limit = 50)
    {
        $sql = "SELECT v.*, t.translation_text, tr.translator, tr.language
                FROM {$this->table} v
                LEFT JOIN {$this->translationsTable} t ON v.id = t.verse_id
                LEFT JOIN translations tr ON t.translation_id = tr.id
                WHERE (v.text_arabic LIKE :query OR t.translation_text LIKE :query)
                AND (tr.language = :language OR tr.language IS NULL)
                ORDER BY v.surah_number, v.verse_number
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $searchQuery = "%{$query}%";
        $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        $stmt->bindParam(':language', $language, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get verse by chapter and verse reference
     *
     * @param int $chapter Chapter number
     * @param int $verse Verse number
     * @return array|null Verse data or null if not found
     */
    public function getByReference($chapter, $verse)
    {
        $sql = "SELECT v.*, t.translation_text, tr.translator, tr.language
                FROM {$this->table} v
                LEFT JOIN {$this->translationsTable} t ON v.id = t.verse_id
                LEFT JOIN translations tr ON t.translation_id = tr.id
                WHERE v.surah_number = :chapter AND v.verse_number = :verse";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':chapter', $chapter, PDO::PARAM_INT);
        $stmt->bindParam(':verse', $verse, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get tafsir (interpretation) for a verse
     *
     * @param int $verseId Verse ID
     * @param string $language Language code
     * @return array|null Tafsir data or null if not found
     */
    public function getTafsir($verseId, $language = 'en')
    {
        $sql = "SELECT * FROM {$this->tafsirTable}
                WHERE verse_id = :verse_id AND language = :language";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':verse_id', $verseId, PDO::PARAM_INT);
        $stmt->bindParam(':language', $language, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get recitation audio for a verse
     *
     * @param int $verseId Verse ID
     * @param string $reciter Reciter name
     * @return array|null Recitation data or null if not found
     */
    public function getRecitation($verseId, $reciter = 'default')
    {
        $sql = "SELECT * FROM {$this->recitationsTable}
                WHERE verse_id = :verse_id AND reciter_id = (SELECT id FROM recitations WHERE name = :reciter)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':verse_id', $verseId, PDO::PARAM_INT);
        $stmt->bindParam(':reciter', $reciter, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get verses with specific keywords
     *
     * @param array $keywords Array of keywords to search for
     * @param string $language Language code
     * @return array Matching verses
     */
    public function getByKeywords($keywords, $language = 'en')
    {
        $placeholders = str_repeat('?,', count($keywords) - 1) . '?';
        $sql = "SELECT DISTINCT v.*, t.translation_text, tr.translator, tr.language
                FROM {$this->table} v
                LEFT JOIN {$this->translationsTable} t ON v.id = t.verse_id
                LEFT JOIN translations tr ON t.translation_id = tr.id
                WHERE (tr.language = ? OR tr.language IS NULL)
                AND (";

        $conditions = [];
        foreach ($keywords as $keyword) {
            $conditions[] = "t.translation_text LIKE ?";
        }
        $sql .= implode(' OR ', $conditions) . ")
                ORDER BY v.surah_number, v.verse_number";

        $stmt = $this->db->prepare($sql);
        $params = [$language];
        foreach ($keywords as $keyword) {
            $params[] = "%{$keyword}%";
        }
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get verse statistics
     *
     * @return array Statistics about Quran verses
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_verses,
                    COUNT(DISTINCT surah_number) as total_chapters,
                    MAX(surah_number) as max_chapter,
                    MAX(verse_number) as max_verse
                FROM {$this->table}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get chapter information
     *
     * @param int $chapter Chapter number
     * @return array|null Chapter information or null if not found
     */
    public function getChapterInfo($chapter)
    {
        $sql = "SELECT 
                    s.number as chapter_number,
                    s.name_arabic as chapter_name_arabic,
                    s.name_english as chapter_name_english,
                    s.revelation_type,
                    COUNT(v.id) as verse_count
                FROM surahs s
                LEFT JOIN {$this->table} v ON s.number = v.surah_number
                WHERE s.number = :chapter
                GROUP BY s.number, s.name_arabic, s.name_english, s.revelation_type";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':chapter', $chapter, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Format verse reference (e.g., "2:255")
     *
     * @param int $chapter Chapter number
     * @param int $verse Verse number
     * @return string Formatted reference
     */
    public function formatReference($chapter, $verse)
    {
        return "{$chapter}:{$verse}";
    }

    /**
     * Parse verse reference (e.g., "2:255" -> [2, 255])
     *
     * @param string $reference Verse reference
     * @return array|null [chapter, verse] or null if invalid
     */
    public function parseReference($reference)
    {
        if (preg_match('/^(\d+):(\d+)$/', $reference, $matches)) {
            return [(int)$matches[1], (int)$matches[2]];
        }
        return null;
    }

    /**
     * Get random verse
     *
     * @return array|null Random verse or null if none found
     */
    public function getRandomVerse()
    {
        $sql = "SELECT v.*, t.translation_text, tr.translator, tr.language
                FROM {$this->table} v
                LEFT JOIN {$this->translationsTable} t ON v.id = t.verse_id
                LEFT JOIN translations tr ON t.translation_id = tr.id
                ORDER BY RAND()
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get verses for daily reading
     *
     * @param int $count Number of verses to return
     * @return array Array of verses for daily reading
     */
    public function getDailyVerses($count = 10)
    {
        $sql = "SELECT v.*, t.translation_text, tr.translator, tr.language
                FROM {$this->table} v
                LEFT JOIN {$this->translationsTable} t ON v.id = t.verse_id
                LEFT JOIN translations tr ON t.translation_id = tr.id
                ORDER BY RAND()
                LIMIT :count";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':count', $count, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
