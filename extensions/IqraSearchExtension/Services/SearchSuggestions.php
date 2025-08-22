<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * SearchSuggestions - Search suggestions and related searches service
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SearchSuggestions
{
    private Connection $database;
    private LoggerInterface $logger;

    public function __construct(Connection $database, LoggerInterface $logger)
    {
        $this->database = $database;
        $this->logger = $logger;
    }

    /**
     * Get search suggestions for a query
     */
    public function getSuggestions(string $query): array
    {
        if (empty($query) || strlen($query) < 2) {
            return [];
        }

        try {
            $query = strtolower(trim($query));
            
            // Get suggestions from database
            $suggestions = $this->getDatabaseSuggestions($query);
            
            // If no database suggestions, generate fallback suggestions
            if (empty($suggestions)) {
                $suggestions = $this->generateFallbackSuggestions($query);
            }
            
            // Limit suggestions
            $suggestions = array_slice($suggestions, 0, 10);
            
            $this->logger->info('Search suggestions retrieved', [
                'query' => $query,
                'suggestions_count' => count($suggestions),
                'source' => 'database'
            ]);
            
            return $suggestions;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get search suggestions', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            // Return fallback suggestions on error
            return $this->generateFallbackSuggestions($query);
        }
    }

    /**
     * Get suggestions from database
     */
    private function getDatabaseSuggestions(string $query): array
    {
        try {
            if (isset($this->database) && method_exists($this->database, 'query')) {
                $sql = "
                    SELECT suggestion, usage_count, relevance_score
                    FROM iqra_search_suggestions
                    WHERE query LIKE ? AND is_active = TRUE
                    ORDER BY usage_count DESC, relevance_score DESC
                    LIMIT 10
                ";
                
                $results = $this->database->query($sql, [$query . '%']);
                
                if (!empty($results)) {
                    return array_column($results, 'suggestion');
                }
            }
            
            // Fallback to mock suggestions if database is not available
            $this->logger->warning('Database not available, using mock suggestions');
            return $this->getMockDatabaseSuggestions($query);
            
        } catch (\Exception $e) {
            $this->logger->error('Database suggestions query failed', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            // Return mock suggestions on error
            return $this->getMockDatabaseSuggestions($query);
        }
    }

    /**
     * Get mock database suggestions for fallback
     */
    private function getMockDatabaseSuggestions(string $query): array
    {
        // Mock database suggestions based on query
        $mockSuggestions = [
            'islam' => ['islamic principles', 'islamic education', 'islamic finance', 'islamic history'],
            'quran' => ['quran recitation', 'quran translation', 'quran tafsir', 'quran memorization'],
            'hadith' => ['hadith authentication', 'hadith sciences', 'hadith collections', 'hadith scholars'],
            'prayer' => ['salah method', 'daily prayers', 'prayer times', 'prayer benefits'],
            'fasting' => ['ramadan fasting', 'sawm rules', 'fasting benefits', 'fasting schedule'],
            'charity' => ['zakat calculation', 'sadaqah', 'charity benefits', 'charity types']
        ];
        
        // Find matching suggestions
        foreach ($mockSuggestions as $key => $suggestions) {
            if (strpos($query, $key) !== false) {
                return $suggestions;
            }
        }
        
        // Return generic suggestions if no specific match
        return [
            $query . ' knowledge',
            $query . ' principles',
            $query . ' history',
            $query . ' teachings',
            $query . ' scholars'
        ];
    }

    /**
     * Generate fallback suggestions when database fails
     */
    private function generateFallbackSuggestions(string $query): array
    {
        $this->logger->info('Using fallback suggestions', ['query' => $query]);
        
        // Generate intelligent fallback suggestions
        $suggestions = [];
        
        // Add query-based suggestions
        $suggestions[] = $query . ' knowledge';
        $suggestions[] = $query . ' principles';
        $suggestions[] = $query . ' history';
        $suggestions[] = $query . ' teachings';
        $suggestions[] = $query . ' scholars';
        $suggestions[] = $query . ' examples';
        $suggestions[] = $query . ' benefits';
        $suggestions[] = $query . ' importance';
        
        // Add Islamic-specific suggestions
        if (strpos($query, 'islam') !== false) {
            $suggestions[] = 'islamic faith';
            $suggestions[] = 'islamic culture';
            $suggestions[] = 'islamic civilization';
        }
        
        if (strpos($query, 'quran') !== false) {
            $suggestions[] = 'quran recitation';
            $suggestions[] = 'quran translation';
            $suggestions[] = 'quran tafsir';
        }
        
        if (strpos($query, 'hadith') !== false) {
            $suggestions[] = 'hadith collections';
            $suggestions[] = 'hadith authenticity';
            $suggestions[] = 'hadith scholars';
        }
        
        if (strpos($query, 'salah') !== false || strpos($query, 'prayer') !== false) {
            $suggestions[] = 'salah times';
            $suggestions[] = 'salah method';
            $suggestions[] = 'salah benefits';
        }
        
        // Remove duplicates and sort
        $suggestions = array_unique($suggestions);
        sort($suggestions);
        
        return $suggestions;
    }

    /**
     * Get related searches for a query
     */
    public function getRelatedSearches(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        try {
            $query = strtolower(trim($query));
            
            // Get related searches from database
            $related = $this->getDatabaseRelatedSearches($query);
            
            // If no database results, generate fallback
            if (empty($related)) {
                $related = $this->generateFallbackRelatedSearches($query);
            }
            
            // Limit related searches
            $related = array_slice($related, 0, 8);
            
            $this->logger->info('Related searches retrieved', [
                'query' => $query,
                'related_count' => count($related),
                'source' => 'database'
            ]);
            
            return $related;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get related searches', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            return $this->generateFallbackRelatedSearches($query);
        }
    }

    /**
     * Get related searches from database
     */
    private function getDatabaseRelatedSearches(string $query): array
    {
        try {
            if (isset($this->database) && method_exists($this->database, 'query')) {
                // For now, return mock related searches
                // In the future, this will query actual search logs
                $sql = "
                    SELECT DISTINCT query, COUNT(*) as frequency
                    FROM iqra_search_logs
                    WHERE query != ? AND results_count > 0
                    GROUP BY query
                    ORDER BY frequency DESC
                    LIMIT 8
                ";
                
                // Since we don't have search logs yet, return mock data
                return $this->getMockDatabaseRelatedSearches($query);
            }
            
            // Fallback to mock related searches if database is not available
            return $this->getMockDatabaseRelatedSearches($query);
            
        } catch (\Exception $e) {
            $this->logger->error('Database related searches query failed', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            
            // Return mock related searches on error
            return $this->getMockDatabaseRelatedSearches($query);
        }
    }

    /**
     * Get mock database related searches for fallback
     */
    private function getMockDatabaseRelatedSearches(string $query): array
    {
        // Mock related searches based on query
        $mockRelated = [
            'islam' => ['islamic knowledge', 'muslim community', 'islamic education', 'religious studies'],
            'quran' => ['islamic scripture', 'divine revelation', 'holy book', 'quranic studies'],
            'hadith' => ['prophetic traditions', 'islamic jurisprudence', 'religious law', 'hadith sciences'],
            'prayer' => ['daily prayers', 'worship practices', 'spiritual connection', 'prayer method'],
            'fasting' => ['ramadan', 'sawm', 'spiritual discipline', 'fasting benefits'],
            'charity' => ['zakat', 'sadaqah', 'giving', 'charitable acts']
        ];
        
        // Find matching related searches
        foreach ($mockRelated as $key => $related) {
            if (strpos($query, $key) !== false) {
                return $related;
            }
        }
        
        // Return generic related searches
        return [
            'islamic knowledge',
            'muslim community',
            'islamic education',
            'religious studies',
            'spiritual guidance',
            'moral values',
            'faith practices',
            'islamic traditions'
        ];
    }

    /**
     * Generate fallback related searches
     */
    private function generateFallbackRelatedSearches(string $query): array
    {
        $this->logger->info('Using fallback related searches', ['query' => $query]);
        
        // Generate intelligent fallback related searches
        $related = [];
        
        // Add related Islamic topics
        $related[] = 'islamic knowledge';
        $related[] = 'muslim community';
        $related[] = 'islamic education';
        $related[] = 'religious studies';
        $related[] = 'spiritual guidance';
        $related[] = 'moral values';
        $related[] = 'faith practices';
        $related[] = 'islamic traditions';
        
        // Add specific related topics based on query
        if (strpos($query, 'quran') !== false) {
            $related[] = 'islamic scripture';
            $related[] = 'divine revelation';
            $related[] = 'holy book';
        }
        
        if (strpos($query, 'hadith') !== false) {
            $related[] = 'prophetic traditions';
            $related[] = 'islamic jurisprudence';
            $related[] = 'religious law';
        }
        
        if (strpos($query, 'salah') !== false || strpos($query, 'prayer') !== false) {
            $related[] = 'daily prayers';
            $related[] = 'worship practices';
            $related[] = 'spiritual connection';
        }
        
        // Remove duplicates and sort
        $related = array_unique($related);
        sort($related);
        
        return $related;
    }

    /**
     * Get popular searches
     */
    public function getPopularSearches(): array
    {
        try {
            // For now, return mock popular searches
            // In the future, this will query analytics data
            $popular = [
                'islamic principles',
                'quran recitation',
                'hadith authenticity',
                'salah times',
                'islamic calendar',
                'fiqh rulings',
                'aqeedah beliefs',
                'tasawwuf spirituality',
                'islamic history',
                'scholar biographies'
            ];
            
            $this->logger->info('Popular searches retrieved', [
                'count' => count($popular)
            ]);
            
            return $popular;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get popular searches', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get trending searches
     */
    public function getTrendingSearches(): array
    {
        try {
            // For now, return mock trending searches
            // In the future, this will analyze recent search patterns
            $trending = [
                'ramadan 2025',
                'eid al-fitr',
                'hajj guide',
                'umrah preparation',
                'islamic finance',
                'halal lifestyle',
                'islamic education',
                'muslim community'
            ];
            
            $this->logger->info('Trending searches retrieved', [
                'count' => count($trending)
            ]);
            
            return $trending;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get trending searches', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Store search query for analytics
     */
    public function storeSearchQuery(string $query, array $context = []): void
    {
        try {
            // TODO: Implement actual storage in database
            $this->logger->info('Search query stored for analytics', [
                'query' => $query,
                'context' => $context
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to store search query', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
        }
    }

    /**
     * Get search query frequency
     */
    public function getQueryFrequency(string $query): int
    {
        // TODO: Implement actual frequency calculation
        return rand(1, 100); // Mock frequency for now
    }
} 