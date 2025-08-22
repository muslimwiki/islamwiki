<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * AI-Powered Recommendation Engine
 * Uses machine learning techniques for intelligent content suggestions
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class AIRecommendationEngine
{
    private Connection $db;
    private LoggerInterface $logger;
    private array $mlModels;
    private array $recommendationCache;

    public function __construct(
        Connection $db,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->recommendationCache = [];
        $this->initializeMLModels();
    }

    /**
     * Initialize machine learning models
     */
    private function initializeMLModels(): void
    {
        $this->mlModels = [
            'collaborative_filtering' => [
                'enabled' => true,
                'algorithm' => 'user_based_cf',
                'min_interactions' => 5,
                'similarity_threshold' => 0.3
            ],
            'content_based_filtering' => [
                'enabled' => true,
                'algorithm' => 'tf_idf_similarity',
                'feature_extraction' => 'keyword_based',
                'similarity_threshold' => 0.4
            ],
            'hybrid_recommendation' => [
                'enabled' => true,
                'algorithm' => 'weighted_combination',
                'collaborative_weight' => 0.6,
                'content_weight' => 0.4
            ]
        ];
    }

    /**
     * Get AI-powered recommendations for a user
     */
    public function getAIRecommendations(int $userId, string $context = '', int $limit = 10): array
    {
        try {
            $cacheKey = "ai_recs_{$userId}_{$context}_{$limit}";
            
            // Check cache first
            if (isset($this->recommendationCache[$cacheKey])) {
                $this->logger->info("Returning cached AI recommendations for user {$userId}");
                return $this->recommendationCache[$cacheKey];
            }
            
            $this->logger->info("Generating AI recommendations for user {$userId}");
            
            // Get user profile and preferences
            $userProfile = $this->getUserProfile($userId);
            $userPreferences = $this->getUserPreferences($userId);
            $userBehavior = $this->getUserBehavior($userId);
            
            // Generate recommendations using different ML models
            $recommendations = [];
            
            if ($this->mlModels['collaborative_filtering']['enabled']) {
                $collaborativeRecs = $this->getCollaborativeFilteringRecommendations($userId, $userProfile, $limit);
                $recommendations = array_merge($recommendations, $collaborativeRecs);
            }
            
            if ($this->mlModels['content_based_filtering']['enabled']) {
                $contentBasedRecs = $this->getContentBasedRecommendations($userId, $userPreferences, $context, $limit);
                $recommendations = array_merge($recommendations, $contentBasedRecs);
            }
            
            if ($this->mlModels['hybrid_recommendation']['enabled']) {
                $hybridRecs = $this->getHybridRecommendations($userId, $userBehavior, $context, $limit);
                $recommendations = array_merge($recommendations, $hybridRecs);
            }
            
            // Rank and deduplicate recommendations
            $rankedRecommendations = $this->rankRecommendations($recommendations, $userProfile, $context);
            $finalRecommendations = array_slice($rankedRecommendations, 0, $limit);
            
            // Cache the results
            $this->recommendationCache[$cacheKey] = $finalRecommendations;
            
            $this->logger->info("Generated " . count($finalRecommendations) . " AI recommendations for user {$userId}");
            
            return $finalRecommendations;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to generate AI recommendations for user {$userId}: " . $e->getMessage());
            
            // Return fallback recommendations
            return $this->getFallbackRecommendations($limit);
        }
    }

    /**
     * Get collaborative filtering recommendations
     */
    private function getCollaborativeFilteringRecommendations(int $userId, array $userProfile, int $limit): array
    {
        try {
            // Find similar users based on search behavior
            $similarUsers = $this->findSimilarUsers($userId, $userProfile);
            
            if (empty($similarUsers)) {
                return [];
            }
            
            // Get content that similar users found useful
            $recommendations = [];
            foreach ($similarUsers as $similarUser) {
                $userRecs = $this->getUserRecommendations($similarUser['user_id'], $limit / 2);
                $recommendations = array_merge($recommendations, $userRecs);
            }
            
            // Remove duplicates and limit results
            $uniqueRecs = array_unique(array_column($recommendations, 'content_id'));
            $filteredRecs = [];
            
            foreach ($recommendations as $rec) {
                if (in_array($rec['content_id'], $uniqueRecs)) {
                    $filteredRecs[] = $rec;
                    $uniqueRecs = array_diff($uniqueRecs, [$rec['content_id']]);
                }
                
                if (count($filteredRecs) >= $limit) {
                    break;
                }
            }
            
            return $filteredRecs;
            
        } catch (\Exception $e) {
            $this->logger->error("Collaborative filtering failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get content-based recommendations
     */
    private function getContentBasedRecommendations(int $userId, array $userPreferences, string $context, int $limit): array
    {
        try {
            // Extract user interests from preferences and behavior
            $userInterests = $this->extractUserInterests($userPreferences);
            
            // Find content similar to user interests
            $recommendations = [];
            
            foreach ($userInterests as $interest) {
                $similarContent = $this->findSimilarContent($interest, $context, $limit / count($userInterests));
                $recommendations = array_merge($recommendations, $similarContent);
            }
            
            // Rank by relevance to user interests
            usort($recommendations, function($a, $b) use ($userInterests) {
                $scoreA = $this->calculateInterestRelevance($a, $userInterests);
                $scoreB = $this->calculateInterestRelevance($b, $userInterests);
                return $scoreB <=> $scoreA;
            });
            
            return array_slice($recommendations, 0, $limit);
            
        } catch (\Exception $e) {
            $this->logger->error("Content-based filtering failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get hybrid recommendations
     */
    private function getHybridRecommendations(int $userId, array $userBehavior, string $context, int $limit): array
    {
        try {
            $collaborativeWeight = $this->mlModels['hybrid_recommendation']['collaborative_weight'];
            $contentWeight = $this->mlModels['hybrid_recommendation']['content_weight'];
            
            // Get recommendations from both models
            $collaborativeRecs = $this->getCollaborativeFilteringRecommendations($userId, [], $limit);
            $contentBasedRecs = $this->getContentBasedRecommendations($userId, [], $context, $limit);
            
            // Combine and weight recommendations
            $hybridRecs = [];
            
            foreach ($collaborativeRecs as $rec) {
                $rec['score'] = ($rec['score'] ?? 0) * $collaborativeWeight;
                $hybridRecs[] = $rec;
            }
            
            foreach ($contentBasedRecs as $rec) {
                $rec['score'] = ($rec['score'] ?? 0) * $contentWeight;
                $hybridRecs[] = $rec;
            }
            
            // Group by content and combine scores
            $combinedRecs = [];
            foreach ($hybridRecs as $rec) {
                $contentId = $rec['content_id'];
                
                if (!isset($combinedRecs[$contentId])) {
                    $combinedRecs[$contentId] = $rec;
                } else {
                    $combinedRecs[$contentId]['score'] += $rec['score'];
                }
            }
            
            // Sort by combined score
            usort($combinedRecs, function($a, $b) {
                return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
            });
            
            return array_slice($combinedRecs, 0, $limit);
            
        } catch (\Exception $e) {
            $this->logger->error("Hybrid recommendations failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find similar users based on search behavior
     */
    private function findSimilarUsers(int $userId, array $userProfile): array
    {
        try {
            // Get user's search behavior
            $userBehavior = $this->getUserSearchBehavior($userId);
            
            if (empty($userBehavior)) {
                return [];
            }
            
            // Find users with similar search patterns
            $sql = "
                SELECT 
                    user_id,
                    COUNT(*) as common_searches,
                    AVG(ABS(response_time - ?)) as avg_time_diff
                FROM iqra_search_logs 
                WHERE user_id != ? 
                AND user_id IS NOT NULL
                AND content_type IN (" . implode(',', array_fill(0, count($userBehavior['content_types']), '?')) . ")
                GROUP BY user_id
                HAVING common_searches >= ?
                ORDER BY common_searches DESC, avg_time_diff ASC
                LIMIT 10
            ";
            
            $params = array_merge(
                [$userBehavior['avg_response_time'], $userId],
                $userBehavior['content_types'],
                [$this->mlModels['collaborative_filtering']['min_interactions']]
            );
            
            $similarUsers = $this->db->query($sql, $params);
            
            // Calculate similarity scores
            foreach ($similarUsers as &$user) {
                $user['similarity_score'] = $this->calculateUserSimilarity($userBehavior, $user);
            }
            
            // Filter by similarity threshold
            $similarUsers = array_filter($similarUsers, function($user) {
                return $user['similarity_score'] >= $this->mlModels['collaborative_filtering']['similarity_threshold'];
            });
            
            // Sort by similarity score
            usort($similarUsers, function($a, $b) {
                return ($b['similarity_score'] ?? 0) <=> ($a['similarity_score'] ?? 0);
            });
            
            return $similarUsers;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to find similar users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find similar content based on interests
     */
    private function findSimilarContent(string $interest, string $context, int $limit): array
    {
        try {
            // Use TF-IDF similarity to find related content
            $sql = "
                SELECT 
                    id, title, content, type, relevance_score,
                    MATCH(title, content) AGAINST(? IN BOOLEAN MODE) as text_similarity
                FROM iqra_search_index 
                WHERE is_active = TRUE
                AND MATCH(title, content) AGAINST(? IN BOOLEAN MODE)
                ORDER BY text_similarity DESC, relevance_score DESC
                LIMIT ?
            ";
            
            $searchTerm = $interest . ' ' . $context;
            $params = [$searchTerm, $searchTerm, $limit];
            
            $similarContent = $this->db->query($sql, $params);
            
            // Calculate content similarity scores
            foreach ($similarContent as &$content) {
                $content['similarity_score'] = $this->calculateContentSimilarity($content, $interest, $context);
            }
            
            // Sort by similarity score
            usort($similarContent, function($a, $b) {
                return ($b['similarity_score'] ?? 0) <=> ($a['similarity_score'] ?? 0);
            });
            
            return $similarContent;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to find similar content: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Rank recommendations by relevance
     */
    private function rankRecommendations(array $recommendations, array $userProfile, string $context): array
    {
        try {
            foreach ($recommendations as &$rec) {
                $rec['final_score'] = $this->calculateFinalScore($rec, $userProfile, $context);
            }
            
            // Sort by final score
            usort($recommendations, function($a, $b) {
                return ($b['final_score'] ?? 0) <=> ($a['final_score'] ?? 0);
            });
            
            return $recommendations;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to rank recommendations: " . $e->getMessage());
            return $recommendations;
        }
    }

    /**
     * Calculate final recommendation score
     */
    private function calculateFinalScore(array $recommendation, array $userProfile, string $context): float
    {
        $score = 0;
        
        // Base score from ML model
        $score += ($recommendation['score'] ?? 0) * 0.4;
        
        // Content relevance
        $score += ($recommendation['relevance_score'] ?? 0) * 0.3;
        
        // User preference alignment
        $score += $this->calculateUserPreferenceAlignment($recommendation, $userProfile) * 0.2;
        
        // Context relevance
        $score += $this->calculateContextRelevance($recommendation, $context) * 0.1;
        
        return $score;
    }

    /**
     * Calculate user preference alignment
     */
    private function calculateUserPreferenceAlignment(array $recommendation, array $userProfile): float
    {
        $alignment = 0;
        
        // Content type preference
        if (isset($userProfile['preferred_content_types']) && 
            in_array($recommendation['type'], $userProfile['preferred_content_types'])) {
            $alignment += 0.5;
        }
        
        // Topic preference
        if (isset($userProfile['preferred_topics'])) {
            foreach ($userProfile['preferred_topics'] as $topic) {
                if (stripos($recommendation['title'], $topic) !== false) {
                    $alignment += 0.3;
                    break;
                }
            }
        }
        
        // Language preference
        if (isset($userProfile['preferred_language']) && 
            $recommendation['language'] === $userProfile['preferred_language']) {
            $alignment += 0.2;
        }
        
        return $alignment;
    }

    /**
     * Calculate context relevance
     */
    private function calculateContextRelevance(array $recommendation, string $context): float
    {
        if (empty($context)) {
            return 0.5; // Neutral score for no context
        }
        
        $contextWords = explode(' ', strtolower($context));
        $titleWords = explode(' ', strtolower($recommendation['title']));
        
        $commonWords = array_intersect($contextWords, $titleWords);
        $relevance = count($commonWords) / max(count($contextWords), 1);
        
        return min($relevance, 1.0);
    }

    /**
     * Get user profile data
     */
    private function getUserProfile(int $userId): array
    {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $user = $this->db->queryOne($sql, [$userId]);
            
            if (!$user) {
                return [];
            }
            
            // Get user preferences
            $preferences = $this->getUserPreferences($userId);
            
            return array_merge($user, $preferences);
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to get user profile: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user preferences
     */
    private function getUserPreferences(int $userId): array
    {
        try {
            $sql = "SELECT preference_key, preference_value FROM iqra_search_preferences WHERE user_id = ?";
            $preferences = $this->db->query($sql, [$userId]);
            
            $formattedPrefs = [];
            foreach ($preferences as $pref) {
                $formattedPrefs[$pref['preference_key']] = json_decode($pref['preference_value'], true);
            }
            
            return $formattedPrefs;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to get user preferences: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user behavior data
     */
    private function getUserBehavior(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_searches,
                    AVG(response_time) as avg_response_time,
                    COUNT(DISTINCT content_type) as content_types_used,
                    MAX(search_timestamp) as last_search
                FROM iqra_search_logs 
                WHERE user_id = ?
            ";
            
            $behavior = $this->db->queryOne($sql, [$userId]);
            
            if (!$behavior) {
                return [];
            }
            
            // Get content type preferences
            $contentTypePrefs = $this->getUserContentTypePreferences($userId);
            $behavior['content_type_preferences'] = $contentTypePrefs;
            
            return $behavior;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to get user behavior: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user search behavior
     */
    private function getUserSearchBehavior(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    content_type,
                    COUNT(*) as search_count,
                    AVG(response_time) as avg_response_time
                FROM iqra_search_logs 
                WHERE user_id = ?
                GROUP BY content_type
                ORDER BY search_count DESC
            ";
            
            $behavior = $this->db->query($sql, [$userId]);
            
            $contentTypes = array_column($behavior, 'content_type');
            $avgResponseTime = array_sum(array_column($behavior, 'avg_response_time')) / count($behavior);
            
            return [
                'content_types' => $contentTypes,
                'avg_response_time' => $avgResponseTime,
                'search_patterns' => $behavior
            ];
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to get user search behavior: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user content type preferences
     */
    private function getUserContentTypePreferences(int $userId): array
    {
        try {
            $sql = "
                SELECT content_type, COUNT(*) as usage_count
                FROM iqra_search_logs 
                WHERE user_id = ?
                GROUP BY content_type
                ORDER BY usage_count DESC
            ";
            
            $preferences = $this->db->query($sql, [$userId]);
            
            return array_column($preferences, 'content_type');
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to get user content type preferences: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract user interests from preferences
     */
    private function extractUserInterests(array $userPreferences): array
    {
        $interests = [];
        
        if (isset($userPreferences['preferred_topics'])) {
            $interests = array_merge($interests, $userPreferences['preferred_topics']);
        }
        
        if (isset($userPreferences['search_history'])) {
            $interests = array_merge($interests, $userPreferences['search_history']);
        }
        
        // Remove duplicates and limit
        $interests = array_unique($interests);
        return array_slice($interests, 0, 10);
    }

    /**
     * Calculate user similarity
     */
    private function calculateUserSimilarity(array $userBehavior, array $similarUser): float
    {
        $similarity = 0;
        
        // Content type similarity
        $commonTypes = array_intersect($userBehavior['content_types'], [$similarUser['content_type']]);
        $similarity += count($commonTypes) / max(count($userBehavior['content_types']), 1) * 0.6;
        
        // Search behavior similarity
        $similarity += (1 / (1 + $similarUser['avg_time_diff'])) * 0.4;
        
        return $similarity;
    }

    /**
     * Calculate content similarity
     */
    private function calculateContentSimilarity(array $content, string $interest, string $context): float
    {
        $similarity = 0;
        
        // Text similarity score
        $similarity += ($content['text_similarity'] ?? 0) * 0.7;
        
        // Relevance score
        $similarity += ($content['relevance_score'] ?? 0) / 100 * 0.3;
        
        return $similarity;
    }

    /**
     * Get user recommendations
     */
    private function getUserRecommendations(int $userId, int $limit): array
    {
        try {
            $sql = "
                SELECT 
                    content_id, content_type, title, url,
                    COUNT(*) as click_count,
                    AVG(response_time) as avg_response_time
                FROM iqra_search_clicks sc
                JOIN iqra_search_logs sl ON sc.search_log_id = sl.id
                WHERE sl.user_id = ?
                GROUP BY content_id, content_type
                ORDER BY click_count DESC, avg_response_time ASC
                LIMIT ?
            ";
            
            return $this->db->query($sql, [$userId, $limit]);
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to get user recommendations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get fallback recommendations
     */
    private function getFallbackRecommendations(int $limit): array
    {
        try {
            $sql = "
                SELECT 
                    id as content_id, title, type, url, relevance_score
                FROM iqra_search_index 
                WHERE is_active = TRUE
                ORDER BY relevance_score DESC, last_updated DESC
                LIMIT ?
            ";
            
            $recommendations = $this->db->query($sql, [$limit]);
            
            foreach ($recommendations as &$rec) {
                $rec['score'] = $rec['relevance_score'] / 100;
                $rec['source'] = 'fallback';
            }
            
            return $recommendations;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to get fallback recommendations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear recommendation cache
     */
    public function clearCache(): void
    {
        $this->recommendationCache = [];
        $this->logger->info('AI recommendation cache cleared');
    }

    /**
     * Get ML model status
     */
    public function getModelStatus(): array
    {
        return [
            'models' => $this->mlModels,
            'cache_size' => count($this->recommendationCache),
            'status' => 'operational'
        ];
    }
} 