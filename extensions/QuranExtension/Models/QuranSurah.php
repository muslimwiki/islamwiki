<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\QuranExtension\Models;

use IslamWiki\Core\Database\Connection;
use PDO;
use Exception;
use RuntimeException;

/**
 * QuranSurah Model
 *
 * Handles Quran surah data and operations.
 * Provides functionality for surah retrieval, information, and management.
 *
 * @package IslamWiki\Extensions\QuranExtension\Models
 * @version 0.0.1
 */
class QuranSurah
{
    private Connection $db;
    private string $table = 'quran_surahs';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Get all surahs
     *
     * @return array Array of surahs
     */
    public function getAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY surah_number";
            $result = $this->db->select($sql);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error getting all surahs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get surah by number
     *
     * @param int $surahNumber Surah number (1-114)
     * @return array|null Surah data or null if not found
     */
    public function getByNumber(int $surahNumber): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE surah_number = ?";
            $result = $this->db->select($sql, [$surahNumber]);
            return $result[0] ?? null;
        } catch (Exception $e) {
            error_log("Error getting surah {$surahNumber}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get surah by name
     *
     * @param string $name Surah name (Arabic, English, or translation)
     * @return array|null Surah data or null if not found
     */
    public function getByName(string $name): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE name_arabic = ? OR name_english = ? OR name_translation = ?";
            $result = $this->db->select($sql, [$name, $name, $name]);
            return $result[0] ?? null;
        } catch (Exception $e) {
            error_log("Error getting surah by name '{$name}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get surahs by revelation type
     *
     * @param string $type Revelation type ('Meccan' or 'Medinan')
     * @return array Array of surahs
     */
    public function getByRevelationType(string $type): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE revelation_type = ? ORDER BY surah_number";
            $result = $this->db->select($sql, [$type]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error getting surahs by revelation type '{$type}': " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get surah statistics
     *
     * @return array Statistics about surahs
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_surahs,
                        SUM(verses_count) as total_verses,
                        AVG(verses_count) as avg_verses,
                        MIN(verses_count) as min_verses,
                        MAX(verses_count) as max_verses,
                        COUNT(CASE WHEN revelation_type = 'Meccan' THEN 1 END) as meccan_count,
                        COUNT(CASE WHEN revelation_type = 'Medinan' THEN 1 END) as medinan_count
                    FROM {$this->table}";
            
            $result = $this->db->select($sql);
            return $result[0] ?? [];
        } catch (Exception $e) {
            error_log("Error getting surah statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search surahs
     *
     * @param string $query Search query
     * @param int $limit Maximum results
     * @return array Search results
     */
    public function search(string $query, int $limit = 50): array
    {
        try {
            $searchTerm = "%{$query}%";
            $sql = "SELECT * FROM {$this->table} 
                    WHERE name_arabic LIKE ? OR name_english LIKE ? OR name_translation LIKE ?
                    ORDER BY surah_number 
                    LIMIT ?";
            
            $result = $this->db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error searching surahs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get surahs with ayah count
     *
     * @return array Array of surahs with ayah counts
     */
    public function getWithAyahCounts(): array
    {
        try {
            $sql = "SELECT s.*, COUNT(a.id) as actual_ayah_count
                    FROM {$this->table} s
                    LEFT JOIN quran_ayahs a ON s.surah_number = a.surah_number
                    GROUP BY s.surah_number
                    ORDER BY s.surah_number";
            
            $result = $this->db->select($sql);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error getting surahs with ayah counts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new surah
     *
     * @param array $data Surah data
     * @return bool Success status
     */
    public function create(array $data): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (
                        surah_number, name_arabic, name_english, name_translation,
                        revelation_type, verses_count, rukus_count, sajda_ayahs
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $this->db->statement($sql, [
                $data['surah_number'],
                $data['name_arabic'],
                $data['name_english'],
                $data['name_translation'],
                $data['revelation_type'],
                $data['verses_count'],
                $data['rukus_count'] ?? null,
                $data['sajda_ayahs'] ?? null
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error creating surah: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update surah
     *
     * @param int $surahNumber Surah number
     * @param array $data Update data
     * @return bool Success status
     */
    public function update(int $surahNumber, array $data): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET 
                        name_arabic = ?, name_english = ?, name_translation = ?,
                        revelation_type = ?, verses_count = ?, rukus_count = ?, 
                        sajda_ayahs = ?, updated_at = NOW()
                    WHERE surah_number = ?";
            
            $this->db->statement($sql, [
                $data['name_arabic'],
                $data['name_english'],
                $data['name_translation'],
                $data['revelation_type'],
                $data['verses_count'],
                $data['rukus_count'] ?? null,
                $data['sajda_ayahs'] ?? null,
                $surahNumber
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log("Error updating surah {$surahNumber}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete surah
     *
     * @param int $surahNumber Surah number
     * @return bool Success status
     */
    public function delete(int $surahNumber): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE surah_number = ?";
            $this->db->statement($sql, [$surahNumber]);
            return true;
        } catch (Exception $e) {
            error_log("Error deleting surah {$surahNumber}: " . $e->getMessage());
            return false;
        }
    }
}
