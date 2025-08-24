<?php

declare(strict_types=1);

namespace IslamWiki\Core\Caching\Drivers;

use IslamWiki\Core\Caching\Interfaces\CacheDriverInterface;
use Logger;\Logger
use IslamWiki\Core\Database\Connection;

/**
 * Database Cache Driver
 *
 * Uses database for persistent caching.
 */
class DatabaseCacheDriver implements CacheDriverInterface
{
    private Logger $logger;
    private Connection $db;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0,
    ];

    /**
     * Create a new database cache driver.
     */
    public function __construct(Logger $logger, Connection $db)
    {
        $this->logger = $logger;
        $this->db = $db;
        $this->ensureTableExists();
    }

    /**
     * Ensure cache table exists.
     */
    private function ensureTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS rihlah_cache (
            `key` VARCHAR(255) PRIMARY KEY,
            `value` LONGTEXT NOT NULL,
            `expires_at` TIMESTAMP NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_expires_at (expires_at)
        )";

        $this->db->statement($sql);
    }

    /**
     * Get a value from database cache.
     */
    public function get(string $key)
    {
        try {
            $result = $this->db->select(
                'SELECT value, expires_at FROM rihlah_cache WHERE `key` = ? AND expires_at > NOW()',
                [$key]
            );

            if (empty($result)) {
                $this->stats['misses']++;
                $this->logger->debug('Database cache miss', ['key' => $key]);
                return null;
            }

            $this->stats['hits']++;
            $this->logger->debug('Database cache hit', ['key' => $key]);
            return json_decode($result[0]['value'], true);
        } catch (\Exception $e) {
            $this->logger->error('Database cache get failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Set a value in database cache.
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
            $jsonValue = json_encode($value);

            $this->db->statement(
                'INSERT INTO rihlah_cache (`key`, value, expires_at) VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE value = ?, expires_at = ?',
                [$key, $jsonValue, $expiresAt, $jsonValue, $expiresAt]
            );

            $this->stats['writes']++;
            $this->logger->debug('Database cache set', [
                'key' => $key,
                'ttl' => $ttl,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Database cache set failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a value from database cache.
     */
    public function delete(string $key): bool
    {
        try {
            $this->db->statement(
                'DELETE FROM rihlah_cache WHERE `key` = ?',
                [$key]
            );

            $this->stats['deletes']++;
            $this->logger->debug('Database cache delete', ['key' => $key]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Database cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all database cache.
     */
    public function clear(): bool
    {
        try {
            $this->db->statement('DELETE FROM rihlah_cache');

            $this->logger->info('Database cache cleared');
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Database cache clear failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a key exists in database cache.
     */
    public function has(string $key): bool
    {
        try {
            $result = $this->db->select(
                'SELECT COUNT(*) as count FROM rihlah_cache WHERE `key` = ? AND expires_at > NOW()',
                [$key]
            );

            return $result[0]['count'] > 0;
        } catch (\Exception $e) {
            $this->logger->error('Database cache has check failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get database cache statistics.
     */
    public function getStats(): array
    {
        try {
            $totalResult = $this->db->select('SELECT COUNT(*) as count FROM rihlah_cache');
            $expiredResult = $this->db->select('SELECT COUNT(*) as count FROM rihlah_cache WHERE expires_at <= NOW()');
            $sizeResult = $this->db->select('SELECT SUM(LENGTH(value)) as size FROM rihlah_cache');

            return array_merge($this->stats, [
                'total_entries' => $totalResult[0]['count'] ?? 0,
                'expired_entries' => $expiredResult[0]['count'] ?? 0,
                'total_size' => $sizeResult[0]['size'] ?? 0,
                'total_size_human' => $this->formatBytes($sizeResult[0]['size'] ?? 0),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get database cache stats', [
                'error' => $e->getMessage(),
            ]);
            return $this->stats;
        }
    }

    /**
     * Clean up expired cache entries.
     */
    public function cleanup(): int
    {
        try {
            $result = $this->db->statement(
                'DELETE FROM rihlah_cache WHERE expires_at <= NOW()'
            );

            $deleted = $result->rowCount();
            $this->logger->info('Database cache cleanup completed', ['deleted' => $deleted]);

            return $deleted;
        } catch (\Exception $e) {
            $this->logger->error('Database cache cleanup failed', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
