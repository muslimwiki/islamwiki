<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Performance monitoring service for WikiExtension
 * 
 * @package IslamWiki\Extensions\WikiExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class PerformanceMonitor
{
    private Connection $db;
    private LoggerInterface $logger;
    private array $metrics = [];
    private float $startTime;

    public function __construct(Connection $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->startTime = microtime(true);
    }

    /**
     * Start performance monitoring for a request
     */
    public function startMonitoring(): void
    {
        $this->startTime = microtime(true);
        $this->metrics = [
            'start_time' => $this->startTime,
            'database_queries' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }

    /**
     * Record database query execution
     */
    public function recordDatabaseQuery(string $query, float $executionTime): void
    {
        $this->metrics['database_queries']++;
        
        // Log slow queries
        if ($executionTime > 1.0) {
            $this->logger->warning('Slow database query detected', [
                'query' => $query,
                'execution_time' => $executionTime,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Record cache operation
     */
    public function recordCacheOperation(string $operation, bool $hit): void
    {
        if ($hit) {
            $this->metrics['cache_hits']++;
        } else {
            $this->metrics['cache_misses']++;
        }
    }

    /**
     * Get current performance metrics
     */
    public function getMetrics(): array
    {
        $endTime = microtime(true);
        $executionTime = $endTime - $this->startTime;
        
        $metrics = $this->metrics;
        $metrics['execution_time'] = $executionTime;
        $metrics['end_time'] = $endTime;
        $metrics['memory_usage'] = memory_get_usage(true);
        $metrics['peak_memory'] = memory_get_peak_usage(true);
        
        return $metrics;
    }

    /**
     * Check if performance meets benchmarks
     */
    public function checkPerformanceBenchmarks(): array
    {
        $metrics = $this->getMetrics();
        $benchmarks = [
            'page_load_time' => $metrics['execution_time'] < 2.0,
            'database_queries' => $metrics['database_queries'] < 20,
            'memory_usage' => $metrics['memory_usage'] < 100 * 1024 * 1024, // 100MB
            'cache_efficiency' => $this->calculateCacheEfficiency() > 0.9
        ];
        
        return [
            'metrics' => $metrics,
            'benchmarks' => $benchmarks,
            'all_passed' => !in_array(false, $benchmarks, true)
        ];
    }

    /**
     * Calculate cache efficiency
     */
    private function calculateCacheEfficiency(): float
    {
        $total = $this->metrics['cache_hits'] + $this->metrics['cache_misses'];
        return $total > 0 ? $this->metrics['cache_hits'] / $total : 1.0;
    }

    /**
     * Log performance metrics
     */
    public function logMetrics(): void
    {
        $metrics = $this->getMetrics();
        $benchmarks = $this->checkPerformanceBenchmarks();
        
        $this->logger->info('Performance metrics recorded', [
            'execution_time' => $metrics['execution_time'],
            'database_queries' => $metrics['database_queries'],
            'memory_usage' => $metrics['memory_usage'],
            'cache_efficiency' => $this->calculateCacheEfficiency(),
            'benchmarks_passed' => $benchmarks['all_passed']
        ]);
    }

    /**
     * Store performance metrics in database
     */
    public function storeMetrics(): bool
    {
        try {
            $metrics = $this->getMetrics();
            $benchmarks = $this->checkPerformanceBenchmarks();
            
            $sql = "INSERT INTO wiki_performance_metrics (
                execution_time, database_queries, memory_usage, 
                cache_hits, cache_misses, cache_efficiency,
                benchmarks_passed, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $metrics['execution_time'],
                $metrics['database_queries'],
                $metrics['memory_usage'],
                $metrics['cache_hits'],
                $metrics['cache_misses'],
                $this->calculateCacheEfficiency(),
                $benchmarks['all_passed'] ? 1 : 0
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to store performance metrics', [
                'error' => $e->getMessage(),
                'metrics' => $metrics ?? []
            ]);
            return false;
        }
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStatistics(): array
    {
        try {
            $sql = "SELECT 
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                AVG(database_queries) as avg_database_queries,
                AVG(memory_usage) as avg_memory_usage,
                AVG(cache_efficiency) as avg_cache_efficiency,
                COUNT(*) as total_requests,
                SUM(benchmarks_passed) as successful_requests
                FROM wiki_performance_metrics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($stats) {
                $stats['success_rate'] = $stats['total_requests'] > 0 
                    ? ($stats['successful_requests'] / $stats['total_requests']) * 100 
                    : 0;
            }
            
            return $stats ?: [];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get performance statistics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Generate performance report
     */
    public function generatePerformanceReport(): array
    {
        $metrics = $this->getMetrics();
        $benchmarks = $this->checkPerformanceBenchmarks();
        $statistics = $this->getPerformanceStatistics();
        
        return [
            'current_request' => [
                'execution_time' => $metrics['execution_time'],
                'database_queries' => $metrics['database_queries'],
                'memory_usage' => $metrics['memory_usage'],
                'cache_efficiency' => $this->calculateCacheEfficiency(),
                'benchmarks_passed' => $benchmarks['all_passed']
            ],
            'benchmarks' => $benchmarks['benchmarks'],
            'statistics_24h' => $statistics,
            'recommendations' => $this->generateRecommendations($metrics, $benchmarks)
        ];
    }

    /**
     * Generate performance recommendations
     */
    private function generateRecommendations(array $metrics, array $benchmarks): array
    {
        $recommendations = [];
        
        if ($metrics['execution_time'] > 2.0) {
            $recommendations[] = 'Page load time exceeds 2 seconds. Consider optimizing database queries or implementing caching.';
        }
        
        if ($metrics['database_queries'] > 20) {
            $recommendations[] = 'Database queries exceed 20 per page. Consider query optimization or result caching.';
        }
        
        if ($metrics['memory_usage'] > 100 * 1024 * 1024) {
            $recommendations[] = 'Memory usage exceeds 100MB. Consider memory optimization or garbage collection.';
        }
        
        if ($this->calculateCacheEfficiency() < 0.9) {
            $recommendations[] = 'Cache efficiency below 90%. Consider cache warming or cache strategy optimization.';
        }
        
        return $recommendations;
    }
} 