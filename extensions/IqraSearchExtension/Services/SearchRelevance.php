<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * SearchRelevance - Relevance scoring service for search results
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SearchRelevance
{
    private Connection $database;
    private LoggerInterface $logger;

    public function __construct(Connection $database, LoggerInterface $logger)
    {
        $this->database = $database;
        $this->logger = $logger;
    }

    /**
     * Calculate relevance score for search result
     */
    public function calculateRelevance(string $query, array $result): float
    {
        $score = 0.0;
        
        try {
            // Title relevance (highest weight)
            $titleScore = $this->calculateTitleRelevance($query, $result['title'] ?? '');
            $score += $titleScore * 0.4;
            
            // Content relevance
            $contentScore = $this->calculateContentRelevance($query, $result['content'] ?? '');
            $score += $contentScore * 0.3;
            
            // Content type relevance
            $typeScore = $this->calculateTypeRelevance($query, $result['type'] ?? '');
            $score += $typeScore * 0.1;
            
            // Freshness relevance
            $freshnessScore = $this->calculateFreshnessRelevance($result['last_updated'] ?? '');
            $score += $freshnessScore * 0.1;
            
            // Popularity relevance
            $popularityScore = $this->calculatePopularityRelevance($result);
            $score += $popularityScore * 0.1;
            
            // Normalize score to 0-100 range
            $score = min(100, max(0, $score * 100));
            
        } catch (\Exception $e) {
            $this->logger->error('Relevance calculation failed', [
                'error' => $e->getMessage(),
                'query' => $query,
                'result' => $result
            ]);
            $score = 50.0; // Default score
        }
        
        return round($score, 2);
    }

    /**
     * Calculate title relevance score
     */
    private function calculateTitleRelevance(string $query, string $title): float
    {
        if (empty($title)) return 0.0;
        
        $query = strtolower($query);
        $title = strtolower($title);
        
        $score = 0.0;
        
        // Exact title match
        if ($title === $query) {
            $score += 1.0;
        }
        
        // Title starts with query
        if (strpos($title, $query) === 0) {
            $score += 0.8;
        }
        
        // Title contains query
        if (strpos($title, $query) !== false) {
            $score += 0.6;
        }
        
        // Word matches in title
        $queryWords = explode(' ', $query);
        $titleWords = explode(' ', $title);
        
        foreach ($queryWords as $queryWord) {
            if (in_array($queryWord, $titleWords)) {
                $score += 0.3;
            }
        }
        
        return min(1.0, $score);
    }

    /**
     * Calculate content relevance score
     */
    private function calculateContentRelevance(string $query, string $content): float
    {
        if (empty($content)) return 0.0;
        
        $query = strtolower($query);
        $content = strtolower($content);
        
        $score = 0.0;
        
        // Exact content match
        if (strpos($content, $query) !== false) {
            $score += 0.8;
        }
        
        // Word matches in content
        $queryWords = explode(' ', $query);
        $contentWords = explode(' ', $content);
        
        $wordMatches = 0;
        foreach ($queryWords as $queryWord) {
            if (in_array($queryWord, $contentWords)) {
                $wordMatches++;
            }
        }
        
        if ($wordMatches > 0) {
            $score += ($wordMatches / count($queryWords)) * 0.6;
        }
        
        // Content length bonus (longer content gets slight bonus)
        if (strlen($content) > 1000) {
            $score += 0.1;
        }
        
        return min(1.0, $score);
    }

    /**
     * Calculate content type relevance score
     */
    private function calculateTypeRelevance(string $query, string $type): float
    {
        // Islamic content types get higher relevance
        $islamicTypes = ['quran', 'hadith', 'fiqh', 'aqeedah', 'tasawwuf'];
        
        if (in_array(strtolower($type), $islamicTypes)) {
            return 0.8;
        }
        
        // Wiki content gets medium relevance
        if ($type === 'wiki') {
            return 0.6;
        }
        
        // Other content types
        return 0.4;
    }

    /**
     * Calculate freshness relevance score
     */
    private function calculateFreshnessRelevance(string $lastUpdated): float
    {
        if (empty($lastUpdated)) return 0.5;
        
        try {
            $updateDate = new \DateTime($lastUpdated);
            $now = new \DateTime();
            $daysDiff = $now->diff($updateDate)->days;
            
            // Recent content (last 30 days) gets higher score
            if ($daysDiff <= 30) {
                return 0.9;
            }
            
            // Content from last 3 months
            if ($daysDiff <= 90) {
                return 0.7;
            }
            
            // Content from last year
            if ($daysDiff <= 365) {
                return 0.5;
            }
            
            // Older content
            return 0.3;
            
        } catch (\Exception $e) {
            return 0.5; // Default score
        }
    }

    /**
     * Calculate popularity relevance score
     */
    private function calculatePopularityRelevance(array $result): float
    {
        // For now, return default score
        // In the future, this would consider:
        // - View count
        // - Rating/score
        // - Social engagement
        // - Citation count
        
        return 0.5;
    }

    /**
     * Boost search results by content type
     */
    public function boostContentType(string $type, float $boost): void
    {
        // TODO: Implement content type boosting
        $this->logger->info('Content type boost applied', [
            'type' => $type,
            'boost' => $boost
        ]);
    }

    /**
     * Boost recent content
     */
    public function boostRecentContent(float $boost): void
    {
        // TODO: Implement recent content boosting
        $this->logger->info('Recent content boost applied', [
            'boost' => $boost
        ]);
    }

    /**
     * Set relevance algorithm
     */
    public function setRelevanceAlgorithm(string $algorithm): void
    {
        // TODO: Implement different relevance algorithms
        $this->logger->info('Relevance algorithm changed', [
            'algorithm' => $algorithm
        ]);
    }
} 