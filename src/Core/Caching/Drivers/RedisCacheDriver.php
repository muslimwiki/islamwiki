<?php

declare(strict_types=1);

namespace IslamWiki\Core\Caching\Drivers;

use IslamWiki\Core\Caching\Interfaces\CacheDriverInterface;
use Logging;\Logger

/**
 * Redis Cache Driver
 *
 * Uses Redis for high-performance distributed caching.
 */
class RedisCacheDriver implements CacheDriverInterface
{
    private Logging $logger;
    private \Redis $redis;
    private array $config;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0,
    ];

    /**
     * Create a new Redis cache driver.
     */
    public function __construct(Logging $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 0.0,
            'retry_interval' => 0,
            'read_timeout' => 0,
            'database' => 0,
            'prefix' => 'rihlah:',
        ], $config);

        $this->connect();
    }

    /**
     * Connect to Redis.
     */
    private function connect(): void
    {
        try {
            $this->redis = new \Redis();

            $connected = $this->redis->connect(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );

            if (!$connected) {
                throw new \RuntimeException('Failed to connect to Redis');
            }

            // Set prefix
            $this->redis->setOption(\Redis::OPT_PREFIX, $this->config['prefix']);

            // Select database
            $this->redis->select($this->config['database']);

            $this->logger->info('Redis cache driver connected', [
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'database' => $this->config['database'],
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Redis connection failed', [
                'error' => $e->getMessage(),
                'config' => $this->config,
            ]);
            throw $e;
        }
    }

    /**
     * Get a value from Redis cache.
     */
    public function get(string $key)
    {
        try {
            $value = $this->redis->get($key);

            if ($value === false) {
                $this->stats['misses']++;
                $this->logger->debug('Redis cache miss', ['key' => $key]);
                return null;
            }

            $this->stats['hits']++;
            $this->logger->debug('Redis cache hit', ['key' => $key]);
            return json_decode($value, true);
        } catch (\Exception $e) {
            $this->logger->error('Redis cache get failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Set a value in Redis cache.
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            $jsonValue = json_encode($value);
            $success = $this->redis->setex($key, $ttl, $jsonValue);

            if ($success) {
                $this->stats['writes']++;
                $this->logger->debug('Redis cache set', [
                    'key' => $key,
                    'ttl' => $ttl,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Redis cache set failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a value from Redis cache.
     */
    public function delete(string $key): bool
    {
        try {
            $deleted = $this->redis->del($key);

            if ($deleted > 0) {
                $this->stats['deletes']++;
                $this->logger->debug('Redis cache delete', ['key' => $key]);
            }

            return $deleted > 0;
        } catch (\Exception $e) {
            $this->logger->error('Redis cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all Redis cache.
     */
    public function clear(): bool
    {
        try {
            $keys = $this->redis->keys('*');
            $deleted = 0;

            if (!empty($keys)) {
                $deleted = $this->redis->del($keys);
            }

            $this->logger->info('Redis cache cleared', ['deleted_keys' => $deleted]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Redis cache clear failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a key exists in Redis cache.
     */
    public function has(string $key): bool
    {
        try {
            return $this->redis->exists($key);
        } catch (\Exception $e) {
            $this->logger->error('Redis cache has check failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get Redis cache statistics.
     */
    public function getStats(): array
    {
        try {
            $info = $this->redis->info();
            $keys = $this->redis->keys('*');

            return array_merge($this->stats, [
                'total_keys' => count($keys),
                'memory_usage' => $info['used_memory'] ?? 0,
                'memory_usage_human' => $this->formatBytes($info['used_memory'] ?? 0),
                'connected_clients' => $info['connected_clients'] ?? 0,
                'uptime' => $info['uptime_in_seconds'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Redis cache stats', [
                'error' => $e->getMessage(),
            ]);
            return $this->stats;
        }
    }

    /**
     * Get keys by pattern.
     */
    public function getKeys(string $pattern = '*'): array
    {
        try {
            return $this->redis->keys($pattern);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Redis keys', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Invalidate keys by pattern.
     */
    public function invalidateByPattern(string $pattern): int
    {
        try {
            $keys = $this->redis->keys($pattern);
            $deleted = 0;

            if (!empty($keys)) {
                $deleted = $this->redis->del($keys);
            }

            $this->logger->info('Redis cache invalidated by pattern', [
                'pattern' => $pattern,
                'deleted_keys' => $deleted,
            ]);

            return $deleted;
        } catch (\Exception $e) {
            $this->logger->error('Redis cache invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Set multiple values at once.
     */
    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        try {
            $pipeline = $this->redis->multi();

            foreach ($values as $key => $value) {
                $jsonValue = json_encode($value);
                $pipeline->setex($key, $ttl, $jsonValue);
            }

            $results = $pipeline->exec();
            $success = !in_array(false, $results, true);

            if ($success) {
                $this->stats['writes'] += count($values);
                $this->logger->debug('Redis cache set multiple', [
                    'count' => count($values),
                    'ttl' => $ttl,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Redis cache set multiple failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get multiple values at once.
     */
    public function getMultiple(array $keys): array
    {
        try {
            $values = $this->redis->mget($keys);
            $result = [];

            foreach ($keys as $index => $key) {
                $value = $values[$index] ?? false;

                if ($value !== false) {
                    $result[$key] = json_decode($value, true);
                    $this->stats['hits']++;
                } else {
                    $result[$key] = null;
                    $this->stats['misses']++;
                }
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Redis cache get multiple failed', [
                'error' => $e->getMessage(),
            ]);
            return array_fill_keys($keys, null);
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
    private function calculateHitRate(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }
}
