<?php

declare(strict_types=1);

namespace IslamWiki\Core\Caching\Drivers;

use IslamWiki\Core\Caching\Interfaces\CacheDriverInterface;
use IslamWiki\Core\Logging\ShahidLogger;

/**
 * File Cache Driver
 *
 * Uses file system for persistent caching.
 */
class FileCacheDriver implements CacheDriverInterface
{
    private ShahidLogger $logger;
    private string $cacheDir;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0,
    ];

    /**
     * Create a new file cache driver.
     */
    public function __construct(ShahidLogger $logger, string $cacheDir)
    {
        $this->logger = $logger;
        $this->cacheDir = rtrim($cacheDir, '/');

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get a value from file cache.
     */
    public function get(string $key)
    {
        try {
            $filename = $this->getCacheFilename($key);

            if (!file_exists($filename)) {
                $this->stats['misses']++;
                $this->logger->debug('File cache miss', ['key' => $key]);
                return null;
            }

            $data = file_get_contents($filename);
            $cacheData = json_decode($data, true);

            if (!$cacheData || !isset($cacheData['expires']) || !isset($cacheData['value'])) {
                $this->stats['misses']++;
                $this->logger->debug('File cache miss - invalid data', ['key' => $key]);
                return null;
            }

            // Check if expired
            if (time() > $cacheData['expires']) {
                $this->delete($key);
                $this->stats['misses']++;
                $this->logger->debug('File cache miss - expired', ['key' => $key]);
                return null;
            }

            $this->stats['hits']++;
            $this->logger->debug('File cache hit', ['key' => $key]);
            return $cacheData['value'];
        } catch (\Exception $e) {
            $this->logger->error('File cache get failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Set a value in file cache.
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            $filename = $this->getCacheFilename($key);
            $cacheData = [
                'value' => $value,
                'expires' => time() + $ttl,
                'created' => time(),
            ];

            $data = json_encode($cacheData);
            $success = file_put_contents($filename, $data, LOCK_EX);

            if ($success !== false) {
                $this->stats['writes']++;
                $this->logger->debug('File cache set', [
                    'key' => $key,
                    'ttl' => $ttl,
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error('File cache set failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a value from file cache.
     */
    public function delete(string $key): bool
    {
        try {
            $filename = $this->getCacheFilename($key);

            if (file_exists($filename)) {
                $success = unlink($filename);

                if ($success) {
                    $this->stats['deletes']++;
                    $this->logger->debug('File cache delete', ['key' => $key]);
                }

                return $success;
            }

            return true; // File doesn't exist, consider it deleted
        } catch (\Exception $e) {
            $this->logger->error('File cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all file cache.
     */
    public function clear(): bool
    {
        try {
            $files = glob($this->cacheDir . '/*.cache');
            $deleted = 0;

            foreach ($files as $file) {
                if (unlink($file)) {
                    $deleted++;
                }
            }

            $this->logger->info('File cache cleared', ['deleted_files' => $deleted]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('File cache clear failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a key exists in file cache.
     */
    public function has(string $key): bool
    {
        try {
            $filename = $this->getCacheFilename($key);

            if (!file_exists($filename)) {
                return false;
            }

            $data = file_get_contents($filename);
            $cacheData = json_decode($data, true);

            if (!$cacheData || !isset($cacheData['expires'])) {
                return false;
            }

            return time() <= $cacheData['expires'];
        } catch (\Exception $e) {
            $this->logger->error('File cache has check failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get file cache statistics.
     */
    public function getStats(): array
    {
        try {
            $files = glob($this->cacheDir . '/*.cache');
            $totalSize = 0;
            $expiredFiles = 0;

            foreach ($files as $file) {
                $totalSize += filesize($file);

                $data = file_get_contents($file);
                $cacheData = json_decode($data, true);

                if ($cacheData && isset($cacheData['expires']) && time() > $cacheData['expires']) {
                    $expiredFiles++;
                }
            }

            return array_merge($this->stats, [
                'files' => count($files),
                'total_size' => $totalSize,
                'total_size_human' => $this->formatBytes($totalSize),
                'expired_files' => $expiredFiles,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get file cache stats', [
                'error' => $e->getMessage(),
            ]);
            return $this->stats;
        }
    }

    /**
     * Get cache filename for a key.
     */
    private function getCacheFilename(string $key): string
    {
        $hash = md5($key);
        return $this->cacheDir . '/' . $hash . '.cache';
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
