<?php

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Islamic\IslamicDatabaseManager;
use PDO;

/**
 * Hadith Model
 *
 * Handles Hadith data and operations for Phase 4 Islamic features integration.
 * Provides functionality for Hadith retrieval, search, and wiki integration.
 *
 * @package IslamWiki\Models
 * @version 0.0.14
 * @since Phase 4
 */
class Hadith
{
    private $db;
    private $table = 'hadiths';
    private $collectionsTable = 'hadith_collections';
    private $narratorsTable = 'narrators';
    private $chainsTable = 'hadith_chains';
    private $commentariesTable = 'hadith_commentaries';

    public function __construct(IslamicDatabaseManager $islamicDbManager = null)
    {
        if ($islamicDbManager === null) {
            // Create default configurations
            $configs = [
                'hadith' => [
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

        $this->db = $islamicDbManager->getHadithConnection();
    }

    /**
     * Get a specific Hadith by ID
     *
     * @param int $id Hadith ID
     * @return array|null Hadith data or null if not found
     */
    public function findById($id)
    {
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                WHERE h.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get Hadiths by collection
     *
     * @param int $collectionId Collection ID
     * @param int $limit Maximum results to return
     * @return array Array of Hadiths
     */
    public function getByCollection($collectionId, $limit = 50)
    {
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                WHERE h.collection_id = :collection_id
                ORDER BY CAST(h.hadith_number AS UNSIGNED)
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':collection_id', $collectionId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search Hadiths by text
     *
     * @param string $query Search query
     * @param string $language Language code for search
     * @param int $limit Maximum results to return
     * @return array Search results
     */
    public function search($query, $language = 'en', $limit = 50)
    {
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                WHERE (h.arabic_text LIKE :query OR h.english_text LIKE :query)
                ORDER BY h.collection_id, CAST(h.hadith_number AS UNSIGNED)
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $searchQuery = "%{$query}%";
        $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Hadith by collection and number
     *
     * @param int $collectionId Collection ID
     * @param int $hadithNumber Hadith number within collection
     * @return array|null Hadith data or null if not found
     */
    public function getByReference($collectionId, $hadithNumber)
    {
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                WHERE h.collection_id = :collection_id AND h.hadith_number = :hadith_number";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':collection_id', $collectionId, PDO::PARAM_INT);
        $stmt->bindParam(':hadith_number', $hadithNumber, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get chain of narrators (isnad) for a Hadith
     *
     * @param int $hadithId Hadith ID
     * @return array Chain of narrators
     */
    public function getChain($hadithId)
    {
        $sql = "SELECT n.*, c.chain_order as position_in_chain
                FROM {$this->narratorsTable} n
                JOIN {$this->chainsTable} c ON n.id = c.narrator_id
                WHERE c.hadith_id = :hadith_id
                ORDER BY c.chain_order";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':hadith_id', $hadithId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get commentary for a Hadith
     *
     * @param int $hadithId Hadith ID
     * @param string $language Language code
     * @return array|null Commentary data or null if not found
     */
    public function getCommentary($hadithId, $language = 'en')
    {
        $sql = "SELECT commentator_name, commentary_text, language FROM {$this->commentariesTable}
                WHERE hadith_id = :hadith_id AND language = :language";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':hadith_id', $hadithId, PDO::PARAM_INT);
        $stmt->bindParam(':language', $language, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get Hadith collections
     *
     * @return array Array of Hadith collections
     */
    public function getCollections()
    {
        $sql = "SELECT c.*, COUNT(h.id) as hadith_count
                FROM {$this->collectionsTable} c
                LEFT JOIN {$this->table} h ON c.id = h.collection_id
                GROUP BY c.id
                ORDER BY c.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Hadiths by narrator
     *
     * @param int $narratorId Narrator ID
     * @param int $limit Maximum results to return
     * @return array Array of Hadiths
     */
    public function getByNarrator($narratorId, $limit = 50)
    {
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                JOIN {$this->chainsTable} ch ON h.id = ch.hadith_id
                WHERE ch.narrator_id = :narrator_id
                ORDER BY h.collection_id, h.hadith_number
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':narrator_id', $narratorId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Hadiths by topic/keyword
     *
     * @param array $keywords Array of keywords to search for
     * @param string $language Language code
     * @return array Matching Hadiths
     */
    public function getByKeywords($keywords, $language = 'en')
    {
        $placeholders = str_repeat('?,', count($keywords) - 1) . '?';
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                WHERE (h.language = ? OR h.language IS NULL)
                AND (";

        $conditions = [];
        foreach ($keywords as $keyword) {
            $conditions[] = "h.english_text LIKE ? OR h.translation LIKE ?";
        }
        $sql .= implode(' OR ', $conditions) . ")
                ORDER BY h.collection_id, h.hadith_number";

        $stmt = $this->db->prepare($sql);
        $params = [$language];
        foreach ($keywords as $keyword) {
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Hadith statistics
     *
     * @return array Statistics about Hadiths
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_hadiths,
                    COUNT(DISTINCT collection_id) as total_collections,
                    COUNT(DISTINCT hc.id) as total_narrators,
                    MAX(CAST(hadith_number AS UNSIGNED)) as max_hadith_number
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} hc ON h.collection_id = hc.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get collection information
     *
     * @param int $collectionId Collection ID
     * @return array|null Collection information or null if not found
     */
    public function getCollectionInfo($collectionId)
    {
        $sql = "SELECT c.*, COUNT(h.id) as hadith_count
                FROM {$this->collectionsTable} c
                LEFT JOIN {$this->table} h ON c.id = h.collection_id
                WHERE c.id = :collection_id
                GROUP BY c.id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':collection_id', $collectionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Format Hadith reference (e.g., "Bukhari 1")
     *
     * @param string $collectionName Collection name
     * @param int $hadithNumber Hadith number
     * @return string Formatted reference
     */
    public function formatReference($collectionName, $hadithNumber)
    {
        return "{$collectionName} {$hadithNumber}";
    }

    /**
     * Parse Hadith reference (e.g., "Bukhari 1" -> ["Bukhari", 1])
     *
     * @param string $reference Hadith reference
     * @return array|null [collection, number] or null if invalid
     */
    public function parseReference($reference)
    {
        if (preg_match('/^([A-Za-z]+)\s+(\d+)$/', $reference, $matches)) {
            return [$matches[1], (int)$matches[2]];
        }
        return null;
    }

    /**
     * Get random Hadith
     *
     * @return array|null Random Hadith or null if none found
     */
    public function getRandomHadith()
    {
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                ORDER BY RAND()
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get Hadiths for daily reading
     *
     * @param int $count Number of Hadiths to return
     * @return array Array of Hadiths for daily reading
     */
    public function getDailyHadiths($count = 5)
    {
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                ORDER BY RAND()
                LIMIT :count";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':count', $count, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Hadiths by authenticity level
     *
     * @param string $authenticityLevel Authenticity level (sahih, hasan, daif, etc.)
     * @param int $limit Maximum results to return
     * @return array Array of Hadiths
     */
    public function getByAuthenticity($authenticityLevel, $limit = 50)
    {
        $sql = "SELECT h.*, c.name as collection_name, c.arabic_name as collection_arabic_name
                FROM {$this->table} h
                LEFT JOIN {$this->collectionsTable} c ON h.collection_id = c.id
                WHERE h.grade = :authenticity_level
                ORDER BY h.collection_id, CAST(h.hadith_number AS UNSIGNED)
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':authenticity_level', $authenticityLevel, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
