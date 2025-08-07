<?php

declare(strict_types=1);

namespace IslamWiki\Core\Caching\Drivers;

use IslamWiki\Core\Caching\Interfaces\CacheDriverInterface;
use IslamWiki\Core\Logging\Shahid;

/**
 * Memory Cache Driver
 *
 * Uses APCu for high-performance memory caching.
 */
class MemoryCacheDriver implements CacheDriverInterface
{
    private Shahid $logger;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0,
    ];

    /**
     * Create a new memory cache driver.
     */
    public function __construct(Shahid $logger)
    {
        $this->logger = $logger;

        if (!extension_loaded('apcu')) {
            throw new \RuntimeException('APCu extension is required for memory caching');
        }
    }

    /**
     * Get a value from memory cache.
     */
    public function get(string $key)
    {
        try {
            $value = apcu_fetch($key, $success);

            if ($success) {
                $this->stats['hits']++;
                $this->logger->debug('Memory cache hit', ['key' => $key]);
                return $value;
            } else {
                $this->stats['misses']++;
                $this->logger->debug('Memory cache miss', ['key' => $key]);
                return null;
            }
        } catch (\Exception $e) {
            $this->logger->error('Memory cache get failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Set a value in memory cache.
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            $success = apcu_store($key, $value, $ttl);

            if ($success) {
                $this->stats['writes']++;
                $this->logger->debug('Memory cache set', [
                    'key' => $key,
                    'ttl' => $ttl,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Memory cache set failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a value from memory cache.
     */
    public function delete(string $key): bool
    {
        try {
            $success = apcu_delete($key);

            if ($success) {
                $this->stats['deletes']++;
                $this->logger->debug('Memory cache delete', ['key' => $key]);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Memory cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all memory cache.
     */
    public function clear(): bool
    {
        try {
            $success = apcu_clear_cache();

            if ($success) {
                $this->logger->info('Memory cache cleared');
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Memory cache clear failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a key exists in memory cache.
     */
    public function has(string $key): bool
    {
        try {
            return apcu_exists($key);
        } catch (\Exception $e) {
            $this->logger->error('Memory cache has check failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get memory cache statistics.
     */
    public function getStats(): array
    {
        try {
            $apcuStats = apcu_cache_info();

            return array_merge($this->stats, [
                'memory_usage' => $apcuStats['mem_size'] ?? 0,
                'memory_usage_human' => $this->formatBytes($apcuStats['mem_size'] ?? 0),
                'entries' => $apcuStats['num_entries'] ?? 0,
                'hits' => $apcuStats['num_hits'] ?? 0,
                'misses' => $apcuStats['num_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($apcuStats),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get memory cache stats', [
                'error' => $e->getMessage(),
            ]);
            return $this->stats;
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

    /**
     * Calculate hit rate percentage.
     */
    private function calculateHitRate(array $stats): float
    {
        $hits = $stats['num_hits'] ?? 0;
        $misses = $stats['num_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }
}
