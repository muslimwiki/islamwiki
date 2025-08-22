<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Personalized Search Service
 * Provides user-specific search recommendations and preferences
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class PersonalizedSearch
{
    private Connection $db;
    private LoggerInterface $logger;
    private SearchAnalytics $analytics;

    public function __construct(
        Connection $db,
        LoggerInterface $logger,
        SearchAnalytics $analytics
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->analytics = $analytics;
    }

    /**
     * Get personalized search results
     */
    public function getPersonalizedResults(string $query, array $options = [], ?int $userId = null): array
    {
        try {
            // Get base search results
            $baseResults = $this->getBaseSearchResults($query, $options);
            
            // Apply personalization if user is logged in
            if ($userId) {
                $baseResults = $this->applyPersonalization($baseResults, $userId, $query);
            }
            
            // Add personalized recommendations
            $recommendations = $this->getPersonalizedRecommendations($userId, $query);
            
            return [
                'results' => $baseResults,
                'recommendations' => $recommendations,
                'personalization_applied' => $userId !== null
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get personalized search results', [
                'error' => $e->getMessage(),
                'query' => $query,
                'user_id' => $userId
            ]);
            
            // Return base results on error
            return [
                'results' => $this->getBaseSearchResults($query, $options),
                'recommendations' => [],
                'personalization_applied' => false
            ];
        }
    }

    /**
     * Get base search results
     */
    private function getBaseSearchResults(string $query, array $options): array
    {
        // This would typically call the main search engine
        // For now, return mock results
        return [
            [
                'id' => 1,
                'title' => 'Islamic Principles and Beliefs',
                'snippet' => 'A comprehensive guide to the core tenets of Islam.',
                'url' => '/wiki/islamic-principles',
                'type' => 'wiki',
                'relevance' => 95
            ],
            [
                'id' => 2,
                'title' => 'The Five Pillars of Islam',
                'snippet' => 'Essential practices in Islam: Shahada, Salah, Zakat, Sawm, and Hajj.',
                'url' => '/articles/five-pillars',
                'type' => 'article',
                'relevance' => 92
            ]
        ];
    }

    /**
     * Apply personalization to search results
     */
    private function applyPersonalization(array $results, int $userId, string $query): array
    {
        try {
            // Get user preferences
            $preferences = $this->getUserPreferences($userId);
            
            // Get user search history
            $searchHistory = $this->getUserSearchHistory($userId);
            
            // Apply personalization scoring
            foreach ($results as &$result) {
                $result['personalized_score'] = $this->calculatePersonalizedScore(
                    $result,
                    $preferences,
                    $searchHistory,
                    $query
                );
            }
            
            // Sort by personalized score
            usort($results, function($a, $b) {
                return ($b['personalized_score'] ?? 0) <=> ($a['personalized_score'] ?? 0);
            });
            
            return $results;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to apply personalization', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return $results;
        }
    }

    /**
     * Calculate personalized score for a result
     */
    private function calculatePersonalizedScore(array $result, array $preferences, array $searchHistory, string $query): float
    {
        $score = $result['relevance'] ?? 0;
        
        // Boost based on content type preference
        if (isset($preferences['preferred_content_types'])) {
            $preferredTypes = json_decode($preferences['preferred_content_types'], true) ?? [];
            if (in_array($result['type'], $preferredTypes)) {
                $score += 20;
            }
        }
        
        // Boost based on search history
        foreach ($searchHistory as $history) {
            if (strpos(strtolower($history['query']), strtolower($result['type'])) !== false) {
                $score += 15;
                break;
            }
        }
        
        // Boost based on query similarity
        if (strpos(strtolower($result['title']), strtolower($query)) !== false) {
            $score += 25;
        }
        
        // Boost based on recent interactions
        if (isset($preferences['recent_interactions'])) {
            $recentInteractions = json_decode($preferences['recent_interactions'], true) ?? [];
            if (in_array($result['id'], $recentInteractions)) {
                $score += 30;
            }
        }
        
        return min($score, 100); // Cap at 100
    }

    /**
     * Get personalized search recommendations
     */
    public function getPersonalizedRecommendations(?int $userId, string $query = ''): array
    {
        try {
            if (!$userId) {
                return $this->getGeneralRecommendations($query);
            }
            
            // Get user-specific recommendations
            $userRecommendations = $this->getUserSpecificRecommendations($userId, $query);
            
            // Get trending recommendations
            $trendingRecommendations = $this->getTrendingRecommendations($query);
            
            // Combine and rank recommendations
            $allRecommendations = array_merge($userRecommendations, $trendingRecommendations);
            $rankedRecommendations = $this->rankRecommendations($allRecommendations, $userId);
            
            return array_slice($rankedRecommendations, 0, 10);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get personalized recommendations', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return $this->getGeneralRecommendations($query);
        }
    }

    /**
     * Get user-specific recommendations
     */
    private function getUserSpecificRecommendations(int $userId, string $query): array
    {
        try {
            // Get user's preferred content types
            $preferredTypes = $this->getUserPreferredContentTypes($userId);
            
            // Get content recommendations based on preferences
            $recommendations = [];
            
            foreach ($preferredTypes as $type) {
                $typeRecommendations = $this->getContentTypeRecommendations($type, $query);
                $recommendations = array_merge($recommendations, $typeRecommendations);
            }
            
            return $recommendations;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user-specific recommendations', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [];
        }
    }

    /**
     * Get trending recommendations
     */
    private function getTrendingRecommendations(string $query): array
    {
        try {
            // Get trending searches from analytics
            $trendingSearches = $this->analytics->getTrendingSearches(['all']);
            
            $recommendations = [];
            foreach ($trendingSearches as $trending) {
                $recommendations[] = [
                    'title' => $trending['query'],
                    'url' => "/search?q=" . urlencode($trending['query']),
                    'type' => 'trending',
                    'score' => $trending['search_count'] * 10,
                    'description' => 'Trending search term'
                ];
            }
            
            return $recommendations;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get trending recommendations', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get general recommendations for non-logged-in users
     */
    private function getGeneralRecommendations(string $query): array
    {
        return [
            [
                'title' => 'Islamic Principles',
                'url' => '/search?q=islamic+principles',
                'type' => 'general',
                'score' => 80,
                'description' => 'Learn about core Islamic beliefs'
            ],
            [
                'title' => 'Quran Recitation',
                'url' => '/search?q=quran+recitation',
                'type' => 'general',
                'score' => 75,
                'description' => 'Master Quran recitation techniques'
            ],
            [
                'title' => 'Hadith Sciences',
                'url' => '/search?q=hadith+sciences',
                'type' => 'general',
                'score' => 70,
                'description' => 'Understand hadith authentication'
            ]
        ];
    }

    /**
     * Get content type recommendations
     */
    private function getContentTypeRecommendations(string $contentType, string $query): array
    {
        $recommendations = [
            'wiki' => [
                [
                    'title' => 'Islamic History',
                    'url' => '/search?q=islamic+history&type=wiki',
                    'type' => 'wiki',
                    'score' => 85,
                    'description' => 'Explore Islamic historical events'
                ],
                [
                    'title' => 'Islamic Scholars',
                    'url' => '/search?q=islamic+scholars&type=wiki',
                    'type' => 'wiki',
                    'score' => 80,
                    'description' => 'Learn about great Islamic scholars'
                ]
            ],
            'quran' => [
                [
                    'title' => 'Quran Translation',
                    'url' => '/search?q=quran+translation&type=quran',
                    'type' => 'quran',
                    'score' => 90,
                    'description' => 'Read Quran in multiple languages'
                ],
                [
                    'title' => 'Quran Tafsir',
                    'url' => '/search?q=quran+tafsir&type=quran',
                    'type' => 'quran',
                    'score' => 85,
                    'description' => 'Understand Quran interpretation'
                ]
            ],
            'hadith' => [
                [
                    'title' => 'Hadith Collections',
                    'url' => '/search?q=hadith+collections&type=hadith',
                    'type' => 'hadith',
                    'score' => 88,
                    'description' => 'Access major hadith collections'
                ],
                [
                    'title' => 'Hadith Authentication',
                    'url' => '/search?q=hadith+authentication&type=hadith',
                    'type' => 'hadith',
                    'score' => 85,
                    'description' => 'Learn hadith verification methods'
                ]
            ]
        ];
        
        return $recommendations[$contentType] ?? [];
    }

    /**
     * Rank recommendations by relevance
     */
    private function rankRecommendations(array $recommendations, ?int $userId): array
    {
        // Remove duplicates based on URL
        $uniqueRecommendations = [];
        foreach ($recommendations as $rec) {
            $uniqueRecommendations[$rec['url']] = $rec;
        }
        
        // Sort by score
        usort($uniqueRecommendations, function($a, $b) {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });
        
        return array_values($uniqueRecommendations);
    }

    /**
     * Get user preferences
     */
    private function getUserPreferences(int $userId): array
    {
        try {
            $sql = "SELECT preference_key, preference_value FROM iqra_search_preferences WHERE user_id = ?";
            $results = $this->db->query($sql, [$userId]);
            
            $preferences = [];
            foreach ($results as $result) {
                $preferences[$result['preference_key']] = $result['preference_value'];
            }
            
            return $preferences;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user preferences', [
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
        try {
            $sql = "SELECT query, content_type, search_timestamp FROM iqra_search_logs WHERE user_id = ? ORDER BY search_timestamp DESC LIMIT 20";
            return $this->db->query($sql, [$userId]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user search history', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [];
        }
    }

    /**
     * Get user preferred content types
     */
    private function getUserPreferredContentTypes(int $userId): array
    {
        try {
            $sql = "
                SELECT content_type, COUNT(*) as search_count
                FROM iqra_search_logs 
                WHERE user_id = ? AND content_type != 'all'
                GROUP BY content_type
                ORDER BY search_count DESC
                LIMIT 3
            ";
            
            $results = $this->db->query($sql, [$userId]);
            return array_column($results, 'content_type');
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user preferred content types', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return ['wiki', 'quran', 'hadith'];
        }
    }

    /**
     * Save user search preference
     */
    public function saveUserPreference(int $userId, string $key, string $value): bool
    {
        try {
            $sql = "
                INSERT INTO iqra_search_preferences (user_id, preference_key, preference_value)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE preference_value = VALUES(preference_value)
            ";
            
            $this->db->execute($sql, [$userId, $key, $value]);
            
            $this->logger->info('User preference saved', [
                'user_id' => $userId,
                'key' => $key
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to save user preference', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'key' => $key
            ]);
            
            return false;
        }
    }
} 