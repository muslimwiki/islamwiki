<?php

declare(strict_types=1);

namespace IslamWiki\Core\Caching\Interfaces;

/**
 * Cache Driver Interface
 *
 * Defines the contract for cache drivers that implement
 * different caching strategies (memory, file, database, etc.).
 */
interface CacheDriverInterface
{
    /**
     * Get a value from cache.
     *
     * @param string $key The cache key
     * @return mixed The cached value or null if not found
     */
    public function get(string $key);

    /**
     * Set a value in cache.
     *
     * @param string $key The cache key
     * @param mixed $value The value to cache
     * @param int $ttl Time to live in seconds
     * @return bool True if successful
     */
    public function set(string $key, $value, int $ttl = 3600): bool;

    /**
     * Delete a value from cache.
     *
     * @param string $key The cache key
     * @return bool True if successful
     */
    public function delete(string $key): bool;

    /**
     * Clear all cache.
     *
     * @return bool True if successful
     */
    public function clear(): bool;

    /**
     * Check if a key exists in cache.
     *
     * @param string $key The cache key
     * @return bool True if key exists
     */
    public function has(string $key): bool;

    /**
     * Get cache statistics.
     *
     * @return array Cache statistics
     */
    public function getStats(): array;
}
