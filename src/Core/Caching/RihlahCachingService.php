<?php

declare(strict_types=1);

namespace IslamWiki\Core\Caching;

use IslamWiki\Core\Database\MizanDatabase;

/**
 * Rihlah Caching Service (رحلة - Journey)
 * 
 * Multi-level caching system for performance optimization.
 * Part of the User Interface Layer in the Islamic core architecture.
 * 
 * @package IslamWiki\Core\Caching
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class RihlahCachingService
{
    private array $config;
    private array $stores = [];
    private string $defaultStore;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'default' => 'file',
            'stores' => [
                'file' => [
                    'driver' => 'file',
                    'path' => __DIR__ . '/../../storage/framework/cache',
                ],
                'database' => [
                    'driver' => 'database',
                    'table' => 'mizan_cache',
                    'connection' => 'default',
                ],
                'memory' => [
                    'driver' => 'memory',
                ],
            ],
            'prefix' => 'rihlah_',
            'ttl' => 3600,
        ], $config);

        $this->defaultStore = $this->config['default'];
        $this->initializeStores();
    }

    /**
     * Initialize cache stores
     */
    private function initializeStores(): void
    {
        foreach ($this->config['stores'] as $name => $config) {
            $this->stores[$name] = $this->createStore($config);
        }
    }

    /**
     * Create cache store instance
     */
    private function createStore(array $config): RihlahStoreInterface
    {
        return match ($config['driver']) {
            'file' => new RihlahFileStore($config),
            'database' => new RihlahDatabaseStore($config),
            'memory' => new RihlahMemoryStore($config),
            default => throw new \InvalidArgumentException("Unsupported cache driver: {$config['driver']}"),
        };
    }

    /**
     * Get cache value
     */
    public function get(string $key, $default = null)
    {
        $store = $this->getStore();
        $fullKey = $this->getFullKey($key);

        $value = $store->get($fullKey);
        
        if ($value === null) {
            return $default;
        }

        return $value;
    }

    /**
     * Set cache value
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $store = $this->getStore();
        $fullKey = $this->getFullKey($key);
        $ttl = $ttl ?? $this->config['ttl'];

        return $store->set($fullKey, $value, $ttl);
    }

    /**
     * Check if cache has key
     */
    public function has(string $key): bool
    {
        $store = $this->getStore();
        $fullKey = $this->getFullKey($key);

        return $store->has($fullKey);
    }

    /**
     * Delete cache value
     */
    public function delete(string $key): bool
    {
        $store = $this->getStore();
        $fullKey = $this->getFullKey($key);

        return $store->delete($fullKey);
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $store = $this->getStore();
        return $store->clear();
    }

    /**
     * Get or set cache value
     */
    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Increment cache value
     */
    public function increment(string $key, int $value = 1): int
    {
        $store = $this->getStore();
        $fullKey = $this->getFullKey($key);

        return $store->increment($fullKey, $value);
    }

    /**
     * Decrement cache value
     */
    public function decrement(string $key, int $value = 1): int
    {
        $store = $this->getStore();
        $fullKey = $this->getFullKey($key);

        return $store->decrement($fullKey, $value);
    }

    /**
     * Get cache store
     */
    private function getStore(): RihlahStoreInterface
    {
        if (!isset($this->stores[$this->defaultStore])) {
            throw new \RuntimeException("Cache store '{$this->defaultStore}' not found");
        }

        return $this->stores[$this->defaultStore];
    }

    /**
     * Get full cache key with prefix
     */
    private function getFullKey(string $key): string
    {
        return $this->config['prefix'] . $key;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $stats = [];
        
        foreach ($this->stores as $name => $store) {
            $stats[$name] = $store->getStats();
        }

        return $stats;
    }

    /**
     * Get cache configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

/**
 * Cache Store Interface
 */
interface RihlahStoreInterface
{
    public function get(string $key);
    public function set(string $key, $value, int $ttl): bool;
    public function has(string $key): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function increment(string $key, int $value): int;
    public function decrement(string $key, int $value): int;
    public function getStats(): array;
}

/**
 * File Cache Store
 */
class RihlahFileStore implements RihlahStoreInterface
{
    private string $path;
    private array $stats = ['hits' => 0, 'misses' => 0, 'writes' => 0];

    public function __construct(array $config)
    {
        $this->path = $config['path'];
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function get(string $key)
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            $this->stats['misses']++;
            return null;
        }

        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            unlink($file);
            $this->stats['misses']++;
            return null;
        }

        $this->stats['hits']++;
        return $data['value'];
    }

    public function set(string $key, $value, int $ttl): bool
    {
        $file = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
        ];

        $result = file_put_contents($file, serialize($data));
        
        if ($result !== false) {
            $this->stats['writes']++;
            return true;
        }

        return false;
    }

    public function has(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));
        return $data['expires'] >= time();
    }

    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->path . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    public function increment(string $key, int $value): int
    {
        $current = $this->get($key) ?? 0;
        $newValue = $current + $value;
        $this->set($key, $newValue, 3600);
        
        return $newValue;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, -$value);
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    private function getFilePath(string $key): string
    {
        return $this->path . '/' . md5($key);
    }
}

/**
 * Memory Cache Store
 */
class RihlahMemoryStore implements RihlahStoreInterface
{
    private array $data = [];
    private array $expires = [];
    private array $stats = ['hits' => 0, 'misses' => 0, 'writes' => 0];

    public function __construct(array $config)
    {
        // Memory store configuration
    }

    public function get(string $key)
    {
        if (!isset($this->data[$key])) {
            $this->stats['misses']++;
            return null;
        }

        if (isset($this->expires[$key]) && $this->expires[$key] < time()) {
            unset($this->data[$key], $this->expires[$key]);
            $this->stats['misses']++;
            return null;
        }

        $this->stats['hits']++;
        return $this->data[$key];
    }

    public function set(string $key, $value, int $ttl): bool
    {
        $this->data[$key] = $value;
        $this->expires[$key] = time() + $ttl;
        $this->stats['writes']++;
        
        return true;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]) && 
               (!isset($this->expires[$key]) || $this->expires[$key] >= time());
    }

    public function delete(string $key): bool
    {
        unset($this->data[$key], $this->expires[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->data = [];
        $this->expires = [];
        return true;
    }

    public function increment(string $key, int $value): int
    {
        $current = $this->get($key) ?? 0;
        $newValue = $current + $value;
        $this->set($key, $newValue, 3600);
        
        return $newValue;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, -$value);
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}

/**
 * Database Cache Store
 */
class RihlahDatabaseStore implements RihlahStoreInterface
{
    private string $table;
    private array $stats = ['hits' => 0, 'misses' => 0, 'writes' => 0];

    public function __construct(array $config)
    {
        $this->table = $config['table'];
    }

    public function get(string $key)
    {
        // This would need database connection to implement
        // For now, return null to indicate not implemented
        $this->stats['misses']++;
        return null;
    }

    public function set(string $key, $value, int $ttl): bool
    {
        // This would need database connection to implement
        // For now, return false to indicate not implemented
        return false;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function delete(string $key): bool
    {
        // This would need database connection to implement
        return false;
    }

    public function clear(): bool
    {
        // This would need database connection to implement
        return false;
    }

    public function increment(string $key, int $value): int
    {
        $current = $this->get($key) ?? 0;
        $newValue = $current + $value;
        $this->set($key, $newValue, 3600);
        
        return $newValue;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, -$value);
    }

    public function getStats(): array
    {
        return $this->stats;
    }
} 