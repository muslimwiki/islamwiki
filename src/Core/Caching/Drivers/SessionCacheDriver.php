<?php

declare(strict_types=1);

namespace IslamWiki\Core\Caching\Drivers;

use IslamWiki\Core\Caching\Interfaces\CacheDriverInterface;
use Logger;\Logger

/**
 * Session Cache Driver
 *
 * Uses PHP sessions for user-specific caching.
 */
class SessionCacheDriver implements CacheDriverInterface
{
    private Logger $logger;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0,
    ];

    /**
     * Create a new session cache driver.
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        // Only start session if not already active and not in CLI
        if (session_status() === PHP_SESSION_NONE && php_sapi_name() !== 'cli') {
            try {
                session_start();
            } catch (\Exception $e) {
                // If session start fails, just continue without session cache
                $this->logger->warning('Session cache driver: Could not start session', [
                    'error' => $e->getMessage()
                ]);
                return;
            }
        }

        if (!isset($_SESSION['rihlah_cache'])) {
            $_SESSION['rihlah_cache'] = [];
        }
    }

    /**
     * Get a value from session cache.
     */
    public function get(string $key)
    {
        try {
            if (!isset($_SESSION['rihlah_cache'][$key])) {
                $this->stats['misses']++;
                $this->logger->debug('Session cache miss', ['key' => $key]);
                return null;
            }

            $cacheData = $_SESSION['rihlah_cache'][$key];

            // Check if expired
            if (isset($cacheData['expires']) && time() > $cacheData['expires']) {
                $this->delete($key);
                $this->stats['misses']++;
                $this->logger->debug('Session cache miss - expired', ['key' => $key]);
                return null;
            }

            $this->stats['hits']++;
            $this->logger->debug('Session cache hit', ['key' => $key]);
            return $cacheData['value'];
        } catch (\Exception $e) {
            $this->logger->error('Session cache get failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Set a value in session cache.
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            $_SESSION['rihlah_cache'][$key] = [
                'value' => $value,
                'expires' => time() + $ttl,
                'created' => time(),
            ];

            $this->stats['writes']++;
            $this->logger->debug('Session cache set', [
                'key' => $key,
                'ttl' => $ttl,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Session cache set failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a value from session cache.
     */
    public function delete(string $key): bool
    {
        try {
            if (isset($_SESSION['rihlah_cache'][$key])) {
                unset($_SESSION['rihlah_cache'][$key]);
                $this->stats['deletes']++;
                $this->logger->debug('Session cache delete', ['key' => $key]);
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Session cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all session cache.
     */
    public function clear(): bool
    {
        try {
            $_SESSION['rihlah_cache'] = [];

            $this->logger->info('Session cache cleared');
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Session cache clear failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a key exists in session cache.
     */
    public function has(string $key): bool
    {
        try {
            if (!isset($_SESSION['rihlah_cache'][$key])) {
                return false;
            }

            $cacheData = $_SESSION['rihlah_cache'][$key];

            if (isset($cacheData['expires']) && time() > $cacheData['expires']) {
                $this->delete($key);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Session cache has check failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get session cache statistics.
     */
    public function getStats(): array
    {
        try {
            $totalEntries = count($_SESSION['rihlah_cache'] ?? []);
            $expiredEntries = 0;

            foreach ($_SESSION['rihlah_cache'] ?? [] as $key => $cacheData) {
                if (isset($cacheData['expires']) && time() > $cacheData['expires']) {
                    $expiredEntries++;
                }
            }

            return array_merge($this->stats, [
                'total_entries' => $totalEntries,
                'expired_entries' => $expiredEntries,
                'session_id' => session_id(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get session cache stats', [
                'error' => $e->getMessage(),
            ]);
            return $this->stats;
        }
    }

    /**
     * Clean up expired session cache entries.
     */
    public function cleanup(): int
    {
        try {
            $deleted = 0;

            foreach ($_SESSION['rihlah_cache'] ?? [] as $key => $cacheData) {
                if (isset($cacheData['expires']) && time() > $cacheData['expires']) {
                    unset($_SESSION['rihlah_cache'][$key]);
                    $deleted++;
                }
            }

            $this->logger->info('Session cache cleanup completed', ['deleted' => $deleted]);

            return $deleted;
        } catch (\Exception $e) {
            $this->logger->error('Session cache cleanup failed', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }
}
