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
 */

declare(strict_types=1);

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use Exception;

class Search
{
    protected Connection $db;
    protected string $table = 'search_statistics';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Log a search query for analytics
     */
    public function logSearch(string $query, string $searchType, int $resultsCount, int $searchTimeMs, ?int $userId = null): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO search_statistics (query, search_type, results_count, search_time_ms, user_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $query,
                $searchType,
                $resultsCount,
                $searchTimeMs,
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Log error but don't break search functionality
            error_log("Failed to log search: " . $e->getMessage());
        }
    }

    /**
     * Get search cache
     */
    public function getCachedResults(string $query, string $searchType): ?array
    {
        try {
            $queryHash = hash('sha256', $query . $searchType);

            $stmt = $this->db->prepare("
                SELECT results_data, results_count, cache_hits
                FROM search_cache 
                WHERE query_hash = ? AND search_type = ? AND expires_at > NOW()
            ");

            $stmt->execute([$queryHash, $searchType]);
            $result = $stmt->fetch();

            if ($result) {
                // Update cache hits
                $this->updateCacheHits($queryHash, $searchType);
                return json_decode($result['results_data'], true);
            }

            return null;
        } catch (Exception $e) {
            error_log("Failed to get cached results: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cache search results
     */
    public function cacheResults(string $query, string $searchType, array $results, int $resultsCount): void
    {
        try {
            $queryHash = hash('sha256', $query . $searchType);
            $resultsData = json_encode($results);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->db->prepare("
                INSERT INTO search_cache (query_hash, query_text, search_type, results_data, results_count, expires_at)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    results_data = VALUES(results_data),
                    results_count = VALUES(results_count),
                    expires_at = VALUES(expires_at)
            ");

            $stmt->execute([$queryHash, $query, $searchType, $resultsData, $resultsCount, $expiresAt]);
        } catch (Exception $e) {
            error_log("Failed to cache results: " . $e->getMessage());
        }
    }

    /**
     * Update cache hit count
     */
    private function updateCacheHits(string $queryHash, string $searchType): void
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE search_cache 
                SET cache_hits = cache_hits + 1 
                WHERE query_hash = ? AND search_type = ?
            ");

            $stmt->execute([$queryHash, $searchType]);
        } catch (Exception $e) {
            error_log("Failed to update cache hits: " . $e->getMessage());
        }
    }

    /**
     * Get search suggestions
     */
    public function getSuggestions(string $query, int $limit = 10): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT suggestion_type, suggestion_text, suggestion_url, relevance_score
                FROM search_suggestions 
                WHERE query LIKE ? 
                ORDER BY relevance_score DESC, click_count DESC 
                LIMIT ?
            ");

            $stmt->execute(["%$query%", $limit]);

            $suggestions = [];
            while ($row = $stmt->fetch()) {
                $suggestions[] = [
                    'type' => $row['suggestion_type'],
                    'text' => $row['suggestion_text'],
                    'url' => $row['suggestion_url'],
                    'relevance' => $row['relevance_score']
                ];
            }

            return $suggestions;
        } catch (Exception $e) {
            error_log("Failed to get suggestions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update suggestion click count
     */
    public function updateSuggestionClick(string $suggestionText, string $suggestionUrl): void
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE search_suggestions 
                SET click_count = click_count + 1 
                WHERE suggestion_text = ? AND suggestion_url = ?
            ");

            $stmt->execute([$suggestionText, $suggestionUrl]);
        } catch (Exception $e) {
            error_log("Failed to update suggestion click: " . $e->getMessage());
        }
    }

    /**
     * Get search analytics for a specific date
     */
    public function getAnalytics(string $date): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM search_analytics WHERE date = ?
            ");

            $stmt->execute([$date]);
            $result = $stmt->fetch();

            if ($result) {
                return [
                    'total_searches' => $result['total_searches'],
                    'unique_users' => $result['unique_users'],
                    'avg_results_per_search' => $result['avg_results_per_search'],
                    'avg_search_time_ms' => $result['avg_search_time_ms'],
                    'most_popular_queries' => json_decode($result['most_popular_queries'], true),
                    'search_type_distribution' => json_decode($result['search_type_distribution'], true)
                ];
            }

            return $this->calculateDailyAnalytics($date);
        } catch (Exception $e) {
            error_log("Failed to get analytics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate daily analytics
     */
    private function calculateDailyAnalytics(string $date): array
    {
        try {
            // Get total searches
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_searches,
                       COUNT(DISTINCT user_id) as unique_users,
                       AVG(results_count) as avg_results,
                       AVG(search_time_ms) as avg_time
                FROM search_statistics 
                WHERE DATE(created_at) = ?
            ");

            $stmt->execute([$date]);
            $stats = $stmt->fetch();

            // Get most popular queries
            $stmt = $this->db->prepare("
                SELECT query, COUNT(*) as count
                FROM search_statistics 
                WHERE DATE(created_at) = ?
                GROUP BY query 
                ORDER BY count DESC 
                LIMIT 10
            ");

            $stmt->execute([$date]);
            $popularQueries = [];
            while ($row = $stmt->fetch()) {
                $popularQueries[] = [
                    'query' => $row['query'],
                    'count' => $row['count']
                ];
            }

            // Get search type distribution
            $stmt = $this->db->prepare("
                SELECT search_type, COUNT(*) as count
                FROM search_statistics 
                WHERE DATE(created_at) = ?
                GROUP BY search_type
            ");

            $stmt->execute([$date]);
            $typeDistribution = [];
            while ($row = $stmt->fetch()) {
                $typeDistribution[$row['search_type']] = $row['count'];
            }

            // Insert analytics
            $stmt = $this->db->prepare("
                INSERT INTO search_analytics (
                    date, total_searches, unique_users, avg_results_per_search, 
                    avg_search_time_ms, most_popular_queries, search_type_distribution
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $date,
                $stats['total_searches'] ?? 0,
                $stats['unique_users'] ?? 0,
                $stats['avg_results'] ?? 0,
                $stats['avg_time'] ?? 0,
                json_encode($popularQueries),
                json_encode($typeDistribution)
            ]);

            return [
                'total_searches' => $stats['total_searches'] ?? 0,
                'unique_users' => $stats['unique_users'] ?? 0,
                'avg_results_per_search' => $stats['avg_results'] ?? 0,
                'avg_search_time_ms' => $stats['avg_time'] ?? 0,
                'most_popular_queries' => $popularQueries,
                'search_type_distribution' => $typeDistribution
            ];
        } catch (Exception $e) {
            error_log("Failed to calculate analytics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get search statistics summary
     */
    public function getStatisticsSummary(): array
    {
        try {
            // Today's stats
            $today = date('Y-m-d');
            $todayStats = $this->getAnalytics($today);

            // This week's stats
            $weekStart = date('Y-m-d', strtotime('-7 days'));
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_searches,
                       COUNT(DISTINCT user_id) as unique_users
                FROM search_statistics 
                WHERE created_at >= ?
            ");

            $stmt->execute([$weekStart]);
            $weekStats = $stmt->fetch();

            // This month's stats
            $monthStart = date('Y-m-d', strtotime('-30 days'));
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_searches,
                       COUNT(DISTINCT user_id) as unique_users
                FROM search_statistics 
                WHERE created_at >= ?
            ");

            $stmt->execute([$monthStart]);
            $monthStats = $stmt->fetch();

            return [
                'today' => $todayStats,
                'this_week' => [
                    'total_searches' => $weekStats['total_searches'] ?? 0,
                    'unique_users' => $weekStats['unique_users'] ?? 0
                ],
                'this_month' => [
                    'total_searches' => $monthStats['total_searches'] ?? 0,
                    'unique_users' => $monthStats['unique_users'] ?? 0
                ]
            ];
        } catch (Exception $e) {
            error_log("Failed to get statistics summary: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean expired cache entries
     */
    public function cleanExpiredCache(): int
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM search_cache WHERE expires_at < NOW()
            ");

            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Failed to clean expired cache: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get search performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        try {
            // Average search time
            $stmt = $this->db->prepare("
                SELECT AVG(search_time_ms) as avg_search_time
                FROM search_statistics 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");

            $stmt->execute();
            $avgTime = $stmt->fetchColumn();

            // Cache hit rate
            $stmt = $this->db->prepare("
                SELECT 
                    SUM(cache_hits) as total_hits,
                    COUNT(*) as total_requests
                FROM search_cache 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");

            $stmt->execute();
            $cacheStats = $stmt->fetch();

            $hitRate = 0;
            if ($cacheStats['total_requests'] > 0) {
                $hitRate = ($cacheStats['total_hits'] / $cacheStats['total_requests']) * 100;
            }

            return [
                'avg_search_time_ms' => round($avgTime ?? 0, 2),
                'cache_hit_rate' => round($hitRate, 2),
                'total_cache_hits' => $cacheStats['total_hits'] ?? 0,
                'total_cache_requests' => $cacheStats['total_requests'] ?? 0
            ];
        } catch (Exception $e) {
            error_log("Failed to get performance metrics: " . $e->getMessage());
            return [];
        }
    }
}
