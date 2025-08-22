<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Advanced Search Analytics Service
 * Tracks user search behavior and provides insights
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SearchAnalytics
{
    private Connection $db;
    private LoggerInterface $logger;

    public function __construct(
        Connection $db,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Track a search query
     */
    public function trackSearch(string $query, array $options = [], ?int $userId = null): void
    {
        try {
            $sql = "INSERT INTO iqra_search_logs (
                query, user_id, content_type, sort_by, sort_order, 
                results_count, response_time, user_agent, ip_address, 
                search_timestamp, session_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $params = [
                $query,
                $userId,
                $options['type'] ?? 'all',
                $options['sort'] ?? 'relevance',
                $options['order'] ?? 'desc',
                $options['results_count'] ?? 0,
                $options['response_time'] ?? 0,
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                $this->getClientIp(),
                session_id()
            ];
            
            $this->db->execute($sql, $params);
            
            $this->logger->info('Search query tracked', [
                'query' => $query,
                'user_id' => $userId,
                'options' => $options
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to track search query', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
        }
    }

    /**
     * Track search result clicks
     */
    public function trackResultClick(int $searchLogId, int $contentId, string $contentType, string $url): void
    {
        try {
            $sql = "INSERT INTO iqra_search_clicks (
                search_log_id, content_id, content_type, url, 
                click_timestamp, user_agent, ip_address
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?)";
            
            $params = [
                $searchLogId,
                $contentId,
                $contentType,
                $url,
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                $this->getClientIp()
            ];
            
            $this->db->execute($sql, $params);
            
            $this->logger->info('Search result click tracked', [
                'search_log_id' => $searchLogId,
                'content_id' => $contentId,
                'content_type' => $contentType
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to track search result click', [
                'error' => $e->getMessage(),
                'search_log_id' => $searchLogId
            ]);
        }
    }

    /**
     * Get search analytics for admin dashboard
     */
    public function getSearchAnalytics(array $filters = []): array
    {
        try {
            $whereClause = $this->buildAnalyticsWhereClause($filters);
            
            // Search volume over time
            $searchVolume = $this->getSearchVolume($whereClause);
            
            // Popular search terms
            $popularSearches = $this->getPopularSearches($whereClause);
            
            // Content type performance
            $contentTypePerformance = $this->getContentTypePerformance($whereClause);
            
            // User engagement metrics
            $userEngagement = $this->getUserEngagement($whereClause);
            
            // Search performance metrics
            $performanceMetrics = $this->getPerformanceMetrics($whereClause);
            
            return [
                'search_volume' => $searchVolume,
                'popular_searches' => $popularSearches,
                'content_type_performance' => $contentTypePerformance,
                'user_engagement' => $userEngagement,
                'performance_metrics' => $performanceMetrics
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get search analytics', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            
            return [];
        }
    }

    /**
     * Get search volume over time
     */
    private function getSearchVolume(string $whereClause): array
    {
        $sql = "
            SELECT 
                DATE(search_timestamp) as date,
                COUNT(*) as search_count,
                COUNT(DISTINCT user_id) as unique_users
            FROM iqra_search_logs 
            WHERE {$whereClause}
            GROUP BY DATE(search_timestamp)
            ORDER BY date DESC
            LIMIT 30
        ";
        
        return $this->db->query($sql);
    }

    /**
     * Get popular search terms
     */
    private function getPopularSearches(string $whereClause): array
    {
        $sql = "
            SELECT 
                query,
                COUNT(*) as search_count,
                AVG(results_count) as avg_results,
                AVG(response_time) as avg_response_time
            FROM iqra_search_logs 
            WHERE {$whereClause}
            GROUP BY query
            ORDER BY search_count DESC
            LIMIT 20
        ";
        
        return $this->db->query($sql);
    }

    /**
     * Get content type performance
     */
    private function getContentTypePerformance(string $whereClause): array
    {
        $sql = "
            SELECT 
                content_type,
                COUNT(*) as search_count,
                AVG(results_count) as avg_results,
                AVG(response_time) as avg_response_time,
                COUNT(DISTINCT user_id) as unique_users
            FROM iqra_search_logs 
            WHERE {$whereClause}
            GROUP BY content_type
            ORDER BY search_count DESC
        ";
        
        return $this->db->query($sql);
    }

    /**
     * Get user engagement metrics
     */
    private function getUserEngagement(string $whereClause): array
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT user_id) as total_users,
                COUNT(DISTINCT session_id) as total_sessions,
                AVG(searches_per_session) as avg_searches_per_session,
                AVG(results_per_search) as avg_results_per_search
            FROM (
                SELECT 
                    user_id,
                    session_id,
                    COUNT(*) as searches_per_session,
                    AVG(results_count) as results_per_search
                FROM iqra_search_logs 
                WHERE {$whereClause}
                GROUP BY user_id, session_id
            ) as session_stats
        ";
        
        return $this->db->query($sql);
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(string $whereClause): array
    {
        $sql = "
            SELECT 
                AVG(response_time) as avg_response_time,
                MIN(response_time) as min_response_time,
                MAX(response_time) as max_response_time,
                AVG(results_count) as avg_results_count,
                COUNT(*) as total_searches
            FROM iqra_search_logs 
            WHERE {$whereClause}
        ";
        
        return $this->db->query($sql);
    }

    /**
     * Get personalized search recommendations
     */
    public function getPersonalizedRecommendations(int $userId, int $limit = 10): array
    {
        try {
            // Get user's search history
            $userHistory = $this->getUserSearchHistory($userId);
            
            // Get popular searches in user's preferred content types
            $preferredTypes = $this->getUserPreferredContentTypes($userId);
            
            // Get trending searches
            $trendingSearches = $this->getTrendingSearches($preferredTypes);
            
            // Combine and rank recommendations
            $recommendations = $this->rankRecommendations($userHistory, $trendingSearches);
            
            return array_slice($recommendations, 0, $limit);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get personalized recommendations', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [];
        }
    }

    /**
     * Get user search history
     */
    private function getUserSearchHistory(int $userId): array
    {
        $sql = "
            SELECT 
                query, content_type, search_timestamp, results_count
            FROM iqra_search_logs 
            WHERE user_id = ?
            ORDER BY search_timestamp DESC
            LIMIT 50
        ";
        
        return $this->db->query($sql, [$userId]);
    }

    /**
     * Get user preferred content types
     */
    private function getUserPreferredContentTypes(int $userId): array
    {
        $sql = "
            SELECT 
                content_type,
                COUNT(*) as search_count
            FROM iqra_search_logs 
            WHERE user_id = ?
            GROUP BY content_type
            ORDER BY search_count DESC
            LIMIT 3
        ";
        
        $results = $this->db->query($sql, [$userId]);
        return array_column($results, 'content_type');
    }

    /**
     * Get trending searches for specific content types
     */
    private function getTrendingSearches(array $contentTypes): array
    {
        if (empty($contentTypes)) {
            $contentTypes = ['all'];
        }
        
        $placeholders = str_repeat('?,', count($contentTypes) - 1) . '?';
        
        $sql = "
            SELECT 
                query, content_type, COUNT(*) as search_count
            FROM iqra_search_logs 
            WHERE content_type IN ({$placeholders})
            AND search_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY query, content_type
            ORDER BY search_count DESC
            LIMIT 20
        ";
        
        return $this->db->query($sql, $contentTypes);
    }

    /**
     * Rank search recommendations
     */
    private function rankRecommendations(array $userHistory, array $trendingSearches): array
    {
        $recommendations = [];
        
        // Add trending searches with base score
        foreach ($trendingSearches as $trending) {
            $recommendations[$trending['query']] = [
                'query' => $trending['query'],
                'content_type' => $trending['content_type'],
                'score' => $trending['search_count'] * 10,
                'source' => 'trending'
            ];
        }
        
        // Boost recommendations based on user history
        foreach ($userHistory as $history) {
            if (isset($recommendations[$history['query']])) {
                $recommendations[$history['query']]['score'] += 50; // Boost for user preference
            }
        }
        
        // Sort by score and return
        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $recommendations;
    }

    /**
     * Build analytics where clause
     */
    private function buildAnalyticsWhereClause(array $filters): string
    {
        $conditions = ['1=1']; // Always true base condition
        
        if (!empty($filters['date_from'])) {
            $conditions[] = "search_timestamp >= '{$filters['date_from']}'";
        }
        
        if (!empty($filters['date_to'])) {
            $conditions[] = "search_timestamp <= '{$filters['date_to']}'";
        }
        
        if (!empty($filters['content_type']) && $filters['content_type'] !== 'all') {
            $conditions[] = "content_type = '{$filters['content_type']}'";
        }
        
        if (!empty($filters['user_id'])) {
            $conditions[] = "user_id = {$filters['user_id']}";
        }
        
        return implode(' AND ', $conditions);
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
} 