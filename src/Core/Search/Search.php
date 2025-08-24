<?php

/**
 * Core Search
 *
 * Centralized search system for IslamWiki.
 * Handles content search and indexing operations.
 *
 * @package IslamWiki\Core\Search
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Search;

use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Database\Connection;

/**
 * Core Search - Centralized Search System
 *
 * This class provides comprehensive search capabilities for
 * content indexing and retrieval throughout the application.
 */
class Search
{
    /**
     * The logging system instance.
     */
    protected Logger $logger;

    /**
     * Database connection.
     */
    protected Connection $db;

    /**
     * Search configuration.
     */
    private array $config;

    /**
     * Create a new search instance.
     *
     * @param Logger $logger The logging system
     * @param Connection $db The database connection
     * @param array $config Search configuration
     */
    public function __construct(Logger $logger, Connection $db, array $config = [])
    {
        $this->logger = $logger;
        $this->db = $db;
        $this->config = array_merge([
            'max_results' => 50,
            'min_score' => 0.1,
            'enable_fuzzy' => true,
            'index_pages' => true
        ], $config);

        $this->logger->info('Search system initialized');
    }

    /**
     * Search for content.
     *
     * @param string $query The search query
     * @param array $options Search options
     * @return array Search results
     */
    public function search(string $query, array $options = []): array
    {
        try {
            $this->logger->info('Search query executed', ['query' => $query]);

            $results = $this->performSearch($query, $options);
            
            $this->logger->info('Search completed', [
                'query' => $query,
                'results_count' => count($results)
            ]);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Perform the actual search.
     *
     * @param string $query The search query
     * @param array $options Search options
     * @return array Search results
     */
    private function performSearch(string $query, array $options): array
    {
        $maxResults = $options['max_results'] ?? $this->config['max_results'];
        $query = trim($query);

        if (empty($query)) {
            return [];
        }

        // Basic search implementation
        $sql = "SELECT id, title, content, slug, created_at, updated_at 
                FROM pages 
                WHERE title LIKE :query 
                   OR content LIKE :query 
                   OR slug LIKE :query 
                ORDER BY updated_at DESC 
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->bindValue(':limit', $maxResults, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate relevance scores
        foreach ($results as &$result) {
            $result['score'] = $this->calculateRelevance($query, $result);
        }

        // Sort by relevance score
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $results;
    }

    /**
     * Calculate relevance score for a result.
     *
     * @param string $query The search query
     * @param array $result The search result
     * @return float Relevance score
     */
    private function calculateRelevance(string $query, array $result): float
    {
        $score = 0.0;
        $query = strtolower($query);
        $title = strtolower($result['title']);
        $content = strtolower($result['content']);
        $slug = strtolower($result['slug']);

        // Title matches get highest score
        if (strpos($title, $query) !== false) {
            $score += 10.0;
        }

        // Slug matches get high score
        if (strpos($slug, $query) !== false) {
            $score += 8.0;
        }

        // Content matches get medium score
        if (strpos($content, $query) !== false) {
            $score += 5.0;
        }

        // Partial matches
        $queryWords = explode(' ', $query);
        foreach ($queryWords as $word) {
            if (strlen($word) > 2) {
                if (strpos($title, $word) !== false) {
                    $score += 2.0;
                }
                if (strpos($content, $word) !== false) {
                    $score += 1.0;
                }
            }
        }

        // Boost recent content
        if (isset($result['updated_at'])) {
            $daysOld = (time() - strtotime($result['updated_at'])) / 86400;
            if ($daysOld < 30) {
                $score += 1.0;
            }
        }

        return $score;
    }

    /**
     * Index a page for search.
     *
     * @param array $pageData Page data to index
     * @return bool Success status
     */
    public function indexPage(array $pageData): bool
    {
        if (!$this->config['index_pages']) {
            return true;
        }

        try {
            // This would typically update a search index
            // For now, we'll just log the indexing
            $this->logger->info('Page indexed for search', [
                'page_id' => $pageData['id'] ?? 'unknown',
                'title' => $pageData['title'] ?? 'unknown'
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Page indexing failed', [
                'page_data' => $pageData,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get search configuration.
     *
     * @return array Search configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Update search configuration.
     *
     * @param array $config New configuration
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->logger->info('Search configuration updated', $config);
    }
} 