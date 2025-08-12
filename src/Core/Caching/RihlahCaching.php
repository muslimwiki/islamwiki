<?php

/**
 * Rihlah (رحلة) - Caching System
 *
 * Comprehensive caching system for IslamWiki performance optimization.
 * Rihlah means "journey" in Arabic, representing the system that manages
 * the journey of data through various cache layers for optimal performance.
 *
 * @package IslamWiki\Core\Caching
 * @version 0.0.40
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Caching;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Database\Connection;

/**
 * Rihlah Caching System
 *
 * Handles comprehensive caching for performance optimization including:
 * - Memory caching (APCu, Redis)
 * - File-based caching
 * - Database query caching
 * - Session caching
 * - API response caching
 * - Template caching
 */
class RihlahCaching
{
    private AsasContainer $container;
    private ShahidLogger $logger;
    private Connection $db;
    private array $drivers = [];
    private array $config = [];
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0,
    ];

    /**
     * Create a new Rihlah caching system.
     */
    public function __construct(AsasContainer $container, ShahidLogger $logger, Connection $db)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->db = $db;
        $this->initializeDrivers();
    }

    /**
     * Initialize cache drivers.
     */
    private function initializeDrivers(): void
    {
        // Initialize memory cache driver (APCu if available, otherwise file-based)
        if (extension_loaded('apcu') && ini_get('apc.enabled')) {
            $this->drivers['memory'] = new \IslamWiki\Core\Caching\Drivers\MemoryCacheDriver($this->logger);
        } else {
            $this->drivers['memory'] = new \IslamWiki\Core\Caching\Drivers\FileCacheDriver($this->logger, 'cache/memory');
        }

        // Initialize Redis cache driver (if available)
        if (extension_loaded('redis')) {
            try {
                $redisConfig = $this->container->get('cache.config')['drivers']['redis'] ?? [];
                $this->drivers['redis'] = new \IslamWiki\Core\Caching\Drivers\RedisCacheDriver($this->logger, $redisConfig);
            } catch (\Exception $e) {
                $this->logger->warning('Redis cache driver initialization failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Initialize file cache driver
        $this->drivers['file'] = new \IslamWiki\Core\Caching\Drivers\FileCacheDriver($this->logger, 'cache/files');

        // Initialize database cache driver
        $this->drivers['database'] = new \IslamWiki\Core\Caching\Drivers\DatabaseCacheDriver($this->logger, $this->db);

        // Initialize session cache driver
        $this->drivers['session'] = new \IslamWiki\Core\Caching\Drivers\SessionCacheDriver($this->logger);

        $this->logger->info('Rihlah cache drivers initialized', [
            'drivers' => array_keys($this->drivers),
        ]);
    }

    /**
     * Get a value from cache.
     */
    public function get(string $key, string $driver = 'memory')
    {
        try {
            if (!isset($this->drivers[$driver])) {
                throw new \InvalidArgumentException("Unknown cache driver: {$driver}");
            }

            $value = $this->drivers[$driver]->get($key);

            if ($value !== null) {
                $this->stats['hits']++;
                $this->logger->debug('Cache hit', [
                    'key' => $key,
                    'driver' => $driver,
                ]);
            } else {
                $this->stats['misses']++;
                $this->logger->debug('Cache miss', [
                    'key' => $key,
                    'driver' => $driver,
                ]);
            }

            return $value;
        } catch (\Exception $e) {
            $this->logger->error('Cache get failed', [
                'key' => $key,
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Set a value in cache.
     */
    public function set(string $key, $value, int $ttl = 3600, string $driver = 'memory'): bool
    {
        try {
            if (!isset($this->drivers[$driver])) {
                throw new \InvalidArgumentException("Unknown cache driver: {$driver}");
            }

            $success = $this->drivers[$driver]->set($key, $value, $ttl);

            if ($success) {
                $this->stats['writes']++;
                $this->logger->debug('Cache set', [
                    'key' => $key,
                    'driver' => $driver,
                    'ttl' => $ttl,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Cache set failed', [
                'key' => $key,
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a value from cache.
     */
    public function delete(string $key, string $driver = 'memory'): bool
    {
        try {
            if (!isset($this->drivers[$driver])) {
                throw new \InvalidArgumentException("Unknown cache driver: {$driver}");
            }

            $success = $this->drivers[$driver]->delete($key);

            if ($success) {
                $this->stats['deletes']++;
                $this->logger->debug('Cache delete', [
                    'key' => $key,
                    'driver' => $driver,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Cache delete failed', [
                'key' => $key,
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clear all cache for a driver.
     */
    public function clear(string $driver = 'memory'): bool
    {
        try {
            if (!isset($this->drivers[$driver])) {
                throw new \InvalidArgumentException("Unknown cache driver: {$driver}");
            }

            $success = $this->drivers[$driver]->clear();

            if ($success) {
                $this->logger->info('Cache cleared', [
                    'driver' => $driver,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Cache clear failed', [
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a key exists in cache.
     */
    public function has(string $key, string $driver = 'memory'): bool
    {
        try {
            if (!isset($this->drivers[$driver])) {
                throw new \InvalidArgumentException("Unknown cache driver: {$driver}");
            }

            return $this->drivers[$driver]->has($key);
        } catch (\Exception $e) {
            $this->logger->error('Cache has check failed', [
                'key' => $key,
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get cache statistics.
     */
    public function getStats(): array
    {
        $driverStats = [];
        foreach ($this->drivers as $name => $driver) {
            $driverStats[$name] = $driver->getStats();
        }

        return [
            'global' => $this->stats,
            'drivers' => $driverStats,
        ];
    }

    /**
     * Cache a function result.
     */
    public function remember(string $key, callable $callback, int $ttl = 3600, string $driver = 'memory')
    {
        $value = $this->get($key, $driver);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl, $driver);

        return $value;
    }

    /**
     * Cache database query results.
     */
    public function rememberQuery(string $key, callable $query, int $ttl = 3600): array
    {
        return $this->remember($key, $query, $ttl, 'database');
    }

    /**
     * Cache API responses.
     */
    public function rememberApiResponse(string $key, callable $apiCall, int $ttl = 1800): array
    {
        return $this->remember($key, $apiCall, $ttl, 'memory');
    }

    /**
     * Cache template rendering.
     */
    public function rememberTemplate(string $key, callable $template, int $ttl = 7200): string
    {
        return $this->remember($key, $template, $ttl, 'file');
    }

    /**
     * Get cache driver.
     */
    public function getDriver(string $driver): ?CacheDriverInterface
    {
        return $this->drivers[$driver] ?? null;
    }

    /**
     * Get all available drivers.
     */
    public function getDrivers(): array
    {
        return array_keys($this->drivers);
    }

    /**
     * Warm up cache with common data.
     */
    public function warmUp(): void
    {
        try {
            $this->logger->info('Starting cache warm-up');

            // Warm up common queries
            $this->warmUpQueries();

            // Warm up API responses
            $this->warmUpApiResponses();

            // Warm up templates
            $this->warmUpTemplates();

            $this->logger->info('Cache warm-up completed');
        } catch (\Exception $e) {
            $this->logger->error('Cache warm-up failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm up common database queries.
     */
    private function warmUpQueries(): void
    {
        $commonQueries = [
            'site_stats' => 'SELECT COUNT(*) as total_pages FROM pages',
            'user_count' => 'SELECT COUNT(*) as total_users FROM users',
            'recent_pages' => 'SELECT * FROM pages ORDER BY created_at DESC LIMIT 10',
        ];

        foreach ($commonQueries as $key => $query) {
            $this->rememberQuery("warmup:{$key}", function () use ($query) {
                return $this->db->select($query);
            }, 3600);
        }
    }

    /**
     * Warm up API responses.
     */
    private function warmUpApiResponses(): void
    {
        // Cache common API responses
        $this->rememberApiResponse('api:quran:ayahs', function () {
            return ['status' => 'cached', 'data' => []];
        }, 1800);
    }

    /**
     * Warm up templates.
     */
    private function warmUpTemplates(): void
    {
        // Cache common template fragments
        $this->rememberTemplate('template:header', function () {
            return '<header>Header content</header>';
        }, 7200);
    }
}
