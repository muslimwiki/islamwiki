<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @category  Core
 * @package   IslamWiki\Core\Database
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Database;

use Logger;\Logger
use PDO;
use PDOException;
use Exception;

/**
 * Database (ميزان) - Database Management System
 *
 * Database provides "Balance" in Arabic. This class provides comprehensive
 * database management, optimization, performance monitoring, and metrics
 * collection for the IslamWiki application.
 *
 * This system ensures database performance, reliability, and provides
 * insights into database operations for optimization purposes.
 *
 * @category  Core
 * @package   IslamWiki\Core\Database
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class Database
{
    /**
     * The logging system.
     */
    protected Logger $logger;

    /**
     * The main database connection.
     */
    protected ?PDO $mainConnection = null;

    /**
     * The Quran database connection.
     */
    protected ?PDO $quranConnection = null;

    /**
     * The Hadith database connection.
     */
    protected ?PDO $hadithConnection = null;

    /**
     * The Islamic content database connection.
     */
    protected ?PDO $islamicConnection = null;

    /**
     * The cache database connection.
     */
    protected ?PDO $cacheConnection = null;

    /**
     * Database configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Performance metrics.
     *
     * @var array<string, mixed>
     */
    protected array $metrics = [];

    /**
     * Query cache.
     *
     * @var array<string, mixed>
     */
    protected array $queryCache = [];

    /**
     * Whether query caching is enabled.
     */
    protected bool $queryCachingEnabled = true;

    /**
     * Maximum query cache size.
     */
    protected int $maxQueryCacheSize = 1000;

    /**
     * Constructor.
     *
     * @param Logger $logger The logging system
     * @param array        $config Database configuration
     */
    public function __construct(Logger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeMetrics();
    }

    /**
     * Initialize performance metrics.
     *
     * @return self
     */
    protected function initializeMetrics(): self
    {
        $this->metrics = [
            'connections' => [
                'total' => 0,
                'active' => 0,
                'failed' => 0
            ],
            'queries' => [
                'total' => 0,
                'cached' => 0,
                'slow' => 0,
                'failed' => 0
            ],
            'performance' => [
                'total_time' => 0.0,
                'average_time' => 0.0,
                'slowest_query' => 0.0,
                'cache_hit_rate' => 0.0
            ],
            'resources' => [
                'memory_usage' => 0,
                'peak_memory' => 0,
                'connection_pool_size' => 0
            ]
        ];

        return $this;
    }

    /**
     * Connect to the main database.
     *
     * @return PDO
     * @throws Exception If connection fails
     */
    public function connectMain(): PDO
    {
        if ($this->mainConnection && $this->mainConnection->getAttribute(PDO::ATTR_CONNECTION_STATUS) === 'Connected') {
            return $this->mainConnection;
        }

        try {
            $this->mainConnection = $this->createConnection($this->config['main'] ?? []);
            $this->updateMetrics('connections', 'total', 1);
            $this->updateMetrics('connections', 'active', 1);
            
            $this->logger->info('Main database connection established');
            
            return $this->mainConnection;
        } catch (PDOException $e) {
            $this->updateMetrics('connections', 'failed', 1);
            $this->logger->error('Failed to connect to main database: ' . $e->getMessage());
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Connect to the Quran database.
     *
     * @return PDO
     * @throws Exception If connection fails
     */
    public function connectQuran(): PDO
    {
        if ($this->quranConnection && $this->quranConnection->getAttribute(PDO::ATTR_CONNECTION_STATUS) === 'Connected') {
            return $this->quranConnection;
        }

        try {
            $this->quranConnection = $this->createConnection($this->config['quran'] ?? []);
            $this->updateMetrics('connections', 'total', 1);
            $this->updateMetrics('connections', 'active', 1);
            
            $this->logger->info('Quran database connection established');
            
            return $this->quranConnection;
        } catch (PDOException $e) {
            $this->updateMetrics('connections', 'failed', 1);
            $this->logger->error('Failed to connect to Quran database: ' . $e->getMessage());
            throw new Exception('Quran database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Connect to the Hadith database.
     *
     * @return PDO
     * @throws Exception If connection fails
     */
    public function connectHadith(): PDO
    {
        if ($this->hadithConnection && $this->hadithConnection->getAttribute(PDO::ATTR_CONNECTION_STATUS) === 'Connected') {
            return $this->hadithConnection;
        }

        try {
            $this->hadithConnection = $this->createConnection($this->config['hadith'] ?? []);
            $this->updateMetrics('connections', 'total', 1);
            $this->updateMetrics('connections', 'active', 1);
            
            $this->logger->info('Hadith database connection established');
            
            return $this->hadithConnection;
        } catch (PDOException $e) {
            $this->updateMetrics('connections', 'failed', 1);
            $this->logger->error('Failed to connect to Hadith database: ' . $e->getMessage());
            throw new Exception('Hadith database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Connect to the Islamic content database.
     *
     * @return PDO
     * @throws Exception If connection fails
     */
    public function connectIslamic(): PDO
    {
        if ($this->islamicConnection && $this->islamicConnection->getAttribute(PDO::ATTR_CONNECTION_STATUS) === 'Connected') {
            return $this->islamicConnection;
        }

        try {
            $this->islamicConnection = $this->createConnection($this->config['islamic'] ?? []);
            $this->updateMetrics('connections', 'total', 1);
            $this->updateMetrics('connections', 'active', 1);
            
            $this->logger->info('Islamic content database connection established');
            
            return $this->islamicConnection;
        } catch (PDOException $e) {
            $this->updateMetrics('connections', 'failed', 1);
            $this->logger->error('Failed to connect to Islamic content database: ' . $e->getMessage());
            throw new Exception('Islamic content database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Connect to the cache database.
     *
     * @return PDO
     * @throws Exception If connection fails
     */
    public function connectCache(): PDO
    {
        if ($this->cacheConnection && $this->cacheConnection->getAttribute(PDO::ATTR_CONNECTION_STATUS) === 'Connected') {
            return $this->cacheConnection;
        }

        try {
            $this->cacheConnection = $this->createConnection($this->config['cache'] ?? []);
            $this->updateMetrics('connections', 'total', 1);
            $this->updateMetrics('connections', 'active', 1);
            
            $this->logger->info('Cache database connection established');
            
            return $this->cacheConnection;
        } catch (PDOException $e) {
            $this->updateMetrics('connections', 'failed', 1);
            $this->logger->error('Failed to connect to cache database: ' . $e->getMessage());
            throw new Exception('Cache database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a database connection.
     *
     * @param array<string, mixed> $config Connection configuration
     * @return PDO
     * @throws PDOException If connection fails
     */
    protected function createConnection(array $config): PDO
    {
        $dsn = $this->buildDsn($config);
        $options = $this->getConnectionOptions($config);

        $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', $options);
        
        // Set additional attributes
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    }

    /**
     * Build DSN string from configuration.
     *
     * @param array<string, mixed> $config Connection configuration
     * @return string
     */
    protected function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '';
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        if ($driver === 'mysql') {
            $dsn = "mysql:host={$host}";
            if ($port) {
                $dsn .= ";port={$port}";
            }
            if ($database) {
                $dsn .= ";dbname={$database}";
            }
            $dsn .= ";charset={$charset}";
        } elseif ($driver === 'pgsql') {
            $dsn = "pgsql:host={$host}";
            if ($port) {
                $dsn .= ";port={$port}";
            }
            if ($database) {
                $dsn .= ";dbname={$database}";
            }
        } else {
            throw new Exception("Unsupported database driver: {$driver}");
        }

        return $dsn;
    }

    /**
     * Get connection options.
     *
     * @param array<string, mixed> $config Connection configuration
     * @return array<int, mixed>
     */
    protected function getConnectionOptions(array $config): array
    {
        $options = [
            PDO::ATTR_TIMEOUT => $config['timeout'] ?? 30,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ];

        if (isset($config['ssl'])) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $config['ssl']['ca'] ?? null;
            $options[PDO::MYSQL_ATTR_SSL_CERT] = $config['ssl']['cert'] ?? null;
            $options[PDO::MYSQL_ATTR_SSL_KEY] = $config['ssl']['key'] ?? null;
        }

        return $options;
    }

    /**
     * Execute a query with performance monitoring.
     *
     * @param PDO    $connection Database connection
     * @param string $sql        SQL query
     * @param array  $params     Query parameters
     * @return PDOStatement
     * @throws Exception If query fails
     */
    public function executeQuery(PDO $connection, string $sql, array $params = []): \PDOStatement
    {
        $startTime = microtime(true);
        $cacheKey = $this->generateCacheKey($sql, $params);

        // Check query cache
        if ($this->queryCachingEnabled && isset($this->queryCache[$cacheKey])) {
            $this->updateMetrics('queries', 'cached', 1);
            $this->logger->debug("Query served from cache: {$sql}");
            return $this->queryCache[$cacheKey];
        }

        try {
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);

            $executionTime = microtime(true) - $startTime;
            $this->updateQueryMetrics($executionTime, $sql);

            // Cache the result if caching is enabled
            if ($this->queryCachingEnabled) {
                $this->cacheQuery($cacheKey, $stmt);
            }

            $this->logger->debug("Query executed successfully: {$sql} ({$executionTime}s)");

            return $stmt;

        } catch (PDOException $e) {
            $this->updateMetrics('queries', 'failed', 1);
            $this->logger->error("Query failed: {$sql} - " . $e->getMessage());
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    /**
     * Update query performance metrics.
     *
     * @param float  $executionTime Query execution time
     * @param string $sql           SQL query
     * @return self
     */
    protected function updateQueryMetrics(float $executionTime, string $sql): self
    {
        $this->updateMetrics('queries', 'total', 1);
        $this->updateMetrics('performance', 'total_time', $executionTime);

        // Update average time
        $totalQueries = $this->metrics['queries']['total'];
        $totalTime = $this->metrics['performance']['total_time'];
        $this->metrics['performance']['average_time'] = $totalTime / $totalQueries;

        // Update slowest query
        if ($executionTime > $this->metrics['performance']['slowest_query']) {
            $this->metrics['performance']['slowest_query'] = $executionTime;
        }

        // Mark as slow query if execution time > 1 second
        if ($executionTime > 1.0) {
            $this->updateMetrics('queries', 'slow', 1);
            $this->logger->warning("Slow query detected: {$sql} ({$executionTime}s)");
        }

        return $this;
    }

    /**
     * Generate cache key for query.
     *
     * @param string $sql    SQL query
     * @param array  $params Query parameters
     * @return string
     */
    protected function generateCacheKey(string $sql, array $params): string
    {
        return md5($sql . serialize($params));
    }

    /**
     * Cache query result.
     *
     * @param string        $cacheKey Cache key
     * @param \PDOStatement $stmt     Query statement
     * @return self
     */
    protected function cacheQuery(string $cacheKey, \PDOStatement $stmt): self
    {
        // Limit cache size
        if (count($this->queryCache) >= $this->maxQueryCacheSize) {
            array_shift($this->queryCache);
        }

        $this->queryCache[$cacheKey] = $stmt;

        return $this;
    }

    /**
     * Update metrics.
     *
     * @param string $category Metric category
     * @param string $metric   Metric name
     * @param mixed  $value    Metric value
     * @return self
     */
    protected function updateMetrics(string $category, string $metric, mixed $value): self
    {
        if (isset($this->metrics[$category][$metric])) {
            if (is_numeric($this->metrics[$category][$metric])) {
                $this->metrics[$category][$metric] += $value;
            } else {
                $this->metrics[$category][$metric] = $value;
            }
        }

        return $this;
    }

    /**
     * Get database performance metrics.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        // Update cache hit rate
        $totalQueries = $this->metrics['queries']['total'];
        $cachedQueries = $this->metrics['queries']['cached'];
        
        if ($totalQueries > 0) {
            $this->metrics['performance']['cache_hit_rate'] = ($cachedQueries / $totalQueries) * 100;
        }

        // Update memory usage
        $this->metrics['resources']['memory_usage'] = memory_get_usage(true);
        $this->metrics['resources']['peak_memory'] = memory_get_peak_usage(true);

        return $this->metrics;
    }

    /**
     * Get database health status.
     *
     * @return array<string, mixed>
     */
    public function getHealthStatus(): array
    {
        $connections = [
            'main' => $this->isConnectionHealthy($this->mainConnection),
            'quran' => $this->isConnectionHealthy($this->quranConnection),
            'hadith' => $this->isConnectionHealthy($this->hadithConnection),
            'islamic' => $this->isConnectionHealthy($this->islamicConnection),
            'cache' => $this->isConnectionHealthy($this->cacheConnection),
        ];

        $overallHealth = 'healthy';
        $failedConnections = array_filter($connections, fn($status) => !$status);
        
        if (count($failedConnections) > 0) {
            $overallHealth = count($failedConnections) === count($connections) ? 'critical' : 'degraded';
        }

        return [
            'status' => $overallHealth,
            'connections' => $connections,
            'failed_connections' => array_keys($failedConnections),
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check if a connection is healthy.
     *
     * @param PDO|null $connection Database connection
     * @return bool
     */
    protected function isConnectionHealthy(?PDO $connection): bool
    {
        if (!$connection) {
            return false;
        }

        try {
            $connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Enable or disable query caching.
     *
     * @param bool $enabled Whether caching is enabled
     * @return self
     */
    public function setQueryCaching(bool $enabled): self
    {
        $this->queryCachingEnabled = $enabled;
        
        if (!$enabled) {
            $this->queryCache = [];
        }

        return $this;
    }

    /**
     * Set maximum query cache size.
     *
     * @param int $size Maximum cache size
     * @return self
     */
    public function setMaxQueryCacheSize(int $size): self
    {
        $this->maxQueryCacheSize = $size;
        
        // Trim cache if necessary
        while (count($this->queryCache) > $this->maxQueryCacheSize) {
            array_shift($this->queryCache);
        }

        return $this;
    }

    /**
     * Clear query cache.
     *
     * @return self
     */
    public function clearQueryCache(): self
    {
        $this->queryCache = [];
        $this->logger->info('Query cache cleared');

        return $this;
    }

    /**
     * Close all database connections.
     *
     * @return self
     */
    public function closeConnections(): self
    {
        $this->mainConnection = null;
        $this->quranConnection = null;
        $this->hadithConnection = null;
        $this->islamicConnection = null;
        $this->cacheConnection = null;

        $this->updateMetrics('connections', 'active', 0);
        $this->logger->info('All database connections closed');

        return $this;
    }

    /**
     * Get database configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set database configuration.
     *
     * @param array<string, mixed> $config Database configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }
}
