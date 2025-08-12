<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\QuranExtension\Models;

use IslamWiki\Core\Database\Connection;
use PDO;
use Exception;
use RuntimeException;
use Psr\Log\LoggerInterface;

/**
 * QuranSurahRepository
 *
 * Handles Quran surah data and operations.
 * Provides functionality for surah retrieval, information, and management.
 *
 * @package IslamWiki\Extensions\QuranExtension\Models
 * @version 1.0.0
 */
class QuranSurahRepository
{
    private Connection $db;
    private string $table = 'quran_surahs';
    private ?LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param Connection $db Database connection
     * @param LoggerInterface|null $logger Optional logger instance
     * @throws RuntimeException If database connection is invalid
     */
    public function __construct(Connection $db, ?LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
        
        if (!$this->db->isConnected()) {
            throw new RuntimeException("Database connection is not established");
        }
    }

    /**
     * Get all surahs
     *
     * @return array Array of surahs, ordered by surah number
     * @throws \PDOException On database error
     */
    public function getAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY surah_number";
            $result = $this->db->select($sql);
            return $result ?: [];
        } catch (\PDOException $e) {
            $this->logger?->error("Database error in getAll", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get surah by number
     *
     * @param int $surahNumber Surah number (1-114)
     * @return array|null Surah data or null if not found
     * @throws \PDOException On database error
     */
    public function getByNumber(int $surahNumber): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE surah_number = ?";
            $result = $this->db->select($sql, [$surahNumber]);
            
            if (empty($result)) {
                $this->logger?->warning("Surah not found", [
                    'surahNumber' => $surahNumber
                ]);
                return null;
            }
            
            return $result[0];
        } catch (\PDOException $e) {
            $this->logger?->error("Database error in getByNumber", [
                'error' => $e->getMessage(),
                'surahNumber' => $surahNumber
            ]);
            throw $e;
        }
    }

    // ... [other methods with similar error handling and documentation]
}
