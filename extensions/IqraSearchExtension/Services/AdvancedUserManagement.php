<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Services;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Advanced User Management Service
 * Provides enterprise-grade user preference and behavior management
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Services
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class AdvancedUserManagement
{
    private Connection $db;
    private LoggerInterface $logger;
    private array $userRoles;
    private array $permissionMatrix;

    public function __construct(
        Connection $db,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->initializeUserRoles();
        $this->initializePermissionMatrix();
    }

    /**
     * Initialize user roles
     */
    private function initializeUserRoles(): void
    {
        $this->userRoles = [
            'admin' => [
                'name' => 'Administrator',
                'permissions' => ['all'],
                'search_limits' => -1, // Unlimited
                'analytics_access' => true,
                'content_moderation' => true
            ],
            'scholar' => [
                'name' => 'Islamic Scholar',
                'permissions' => ['search', 'content_creation', 'content_moderation', 'limited_analytics'],
                'search_limits' => 1000,
                'analytics_access' => true,
                'content_moderation' => true
            ],
            'contributor' => [
                'name' => 'Content Contributor',
                'permissions' => ['search', 'content_creation', 'limited_analytics'],
                'search_limits' => 500,
                'analytics_access' => true,
                'content_moderation' => false
            ],
            'user' => [
                'name' => 'Regular User',
                'permissions' => ['search', 'basic_analytics'],
                'search_limits' => 100,
                'analytics_access' => false,
                'content_moderation' => false
            ],
            'guest' => [
                'name' => 'Guest User',
                'permissions' => ['search'],
                'search_limits' => 10,
                'analytics_access' => false,
                'content_moderation' => false
            ]
        ];
    }

    /**
     * Initialize permission matrix
     */
    private function initializePermissionMatrix(): void
    {
        $this->permissionMatrix = [
            'search' => [
                'basic_search' => ['user', 'contributor', 'scholar', 'admin'],
                'advanced_search' => ['contributor', 'scholar', 'admin'],
                'ai_recommendations' => ['contributor', 'scholar', 'admin'],
                'multilingual_search' => ['user', 'contributor', 'scholar', 'admin']
            ],
            'analytics' => [
                'basic_analytics' => ['user', 'contributor', 'scholar', 'admin'],
                'advanced_analytics' => ['contributor', 'scholar', 'admin'],
                'user_analytics' => ['scholar', 'admin'],
                'system_analytics' => ['admin']
            ],
            'content' => [
                'content_view' => ['user', 'contributor', 'scholar', 'admin'],
                'content_creation' => ['contributor', 'scholar', 'admin'],
                'content_modification' => ['contributor', 'scholar', 'admin'],
                'content_deletion' => ['scholar', 'admin'],
                'content_moderation' => ['scholar', 'admin']
            ],
            'preferences' => [
                'basic_preferences' => ['user', 'contributor', 'scholar', 'admin'],
                'advanced_preferences' => ['contributor', 'scholar', 'admin'],
                'search_preferences' => ['user', 'contributor', 'scholar', 'admin'],
                'language_preferences' => ['user', 'contributor', 'scholar', 'admin']
            ]
        ];
    }

    /**
     * Get comprehensive user profile
     */
    public function getUserProfile(int $userId): array
    {
        try {
            $this->logger->info("Getting comprehensive user profile for user {$userId}");

            // Get basic user information
            $basicInfo = $this->getBasicUserInfo($userId);
            
            // Get user preferences
            $preferences = $this->getUserPreferences($userId);
            
            // Get user behavior analytics
            $behavior = $this->getUserBehaviorAnalytics($userId);
            
            // Get user permissions
            $permissions = $this->getUserPermissions($userId);
            
            // Get user search history
            $searchHistory = $this->getUserSearchHistory($userId);
            
            // Get user content contributions
            $contributions = $this->getUserContributions($userId);

            $profile = array_merge($basicInfo, [
                'preferences' => $preferences,
                'behavior' => $behavior,
                'permissions' => $permissions,
                'search_history' => $searchHistory,
                'contributions' => $contributions
            ]);

            $this->logger->info("User profile retrieved successfully for user {$userId}");

            return $profile;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user profile for user {$userId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get basic user information
     */
    private function getBasicUserInfo(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    id, username, email, role, created_at, last_login,
                    is_active, email_verified, profile_completed
                FROM users 
                WHERE id = ?
            ";

            $user = $this->db->queryOne($sql, [$userId]);

            if (!$user) {
                return [];
            }

            // Get role information
            $roleInfo = $this->userRoles[$user['role']] ?? $this->userRoles['user'];

            return array_merge($user, [
                'role_name' => $roleInfo['name'],
                'role_permissions' => $roleInfo['permissions'],
                'search_limits' => $roleInfo['search_limits'],
                'analytics_access' => $roleInfo['analytics_access'],
                'content_moderation' => $roleInfo['content_moderation']
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get basic user info: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user preferences
     */
    private function getUserPreferences(int $userId): array
    {
        try {
            $sql = "
                SELECT preference_key, preference_value, created_at, updated_at
                FROM iqra_search_preferences 
                WHERE user_id = ?
                ORDER BY updated_at DESC
            ";

            $preferences = $this->db->query($sql, [$userId]);

            $formattedPreferences = [];
            foreach ($preferences as $pref) {
                $formattedPreferences[$pref['preference_key']] = [
                    'value' => json_decode($pref['preference_value'], true),
                    'created_at' => $pref['created_at'],
                    'updated_at' => $pref['updated_at']
                ];
            }

            return $formattedPreferences;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user preferences: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user behavior analytics
     */
    private function getUserBehaviorAnalytics(int $userId): array
    {
        try {
            // Get search behavior
            $searchBehavior = $this->getUserSearchBehavior($userId);
            
            // Get content interaction behavior
            $contentBehavior = $this->getUserContentBehavior($userId);
            
            // Get preference evolution
            $preferenceEvolution = $this->getUserPreferenceEvolution($userId);
            
            // Get engagement metrics
            $engagementMetrics = $this->getUserEngagementMetrics($userId);

            return [
                'search_behavior' => $searchBehavior,
                'content_behavior' => $contentBehavior,
                'preference_evolution' => $preferenceEvolution,
                'engagement_metrics' => $engagementMetrics
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user behavior analytics: " . $e->getMessage());
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
                    AVG(response_time) as avg_response_time,
                    AVG(results_count) as avg_results,
                    MIN(search_timestamp) as first_search,
                    MAX(search_timestamp) as last_search,
                    COUNT(DISTINCT DATE(search_timestamp)) as active_days
                FROM iqra_search_logs 
                WHERE user_id = ?
                GROUP BY content_type
                ORDER BY search_count DESC
            ";

            $behavior = $this->db->query($sql, [$userId]);

            // Calculate search patterns
            $searchPatterns = $this->analyzeSearchPatterns($userId);

            return [
                'content_type_usage' => $behavior,
                'search_patterns' => $searchPatterns,
                'total_searches' => array_sum(array_column($behavior, 'search_count')),
                'avg_response_time' => array_sum(array_column($behavior, 'avg_response_time')) / count($behavior)
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user search behavior: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user content behavior
     */
    private function getUserContentBehavior(int $userId): array
    {
        try {
            // Get content interaction patterns
            $sql = "
                SELECT 
                    content_type,
                    COUNT(*) as interaction_count,
                    AVG(click_timestamp - search_timestamp) as avg_engagement_time
                FROM iqra_search_clicks sc
                JOIN iqra_search_logs sl ON sc.search_log_id = sl.id
                WHERE sl.user_id = ?
                GROUP BY content_type
                ORDER BY interaction_count DESC
            ";

            $interactions = $this->db->query($sql, [$userId]);

            // Get content preferences
            $contentPreferences = $this->getUserContentPreferences($userId);

            return [
                'interactions' => $interactions,
                'preferences' => $contentPreferences,
                'total_interactions' => array_sum(array_column($interactions, 'interaction_count'))
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user content behavior: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user preference evolution
     */
    private function getUserPreferenceEvolution(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    preference_key,
                    preference_value,
                    created_at,
                    updated_at
                FROM iqra_search_preferences 
                WHERE user_id = ?
                ORDER BY created_at ASC
            ";

            $preferences = $this->db->query($sql, [$userId]);

            $evolution = [];
            foreach ($preferences as $pref) {
                if (!isset($evolution[$pref['preference_key']])) {
                    $evolution[$pref['preference_key']] = [];
                }
                
                $evolution[$pref['preference_key']][] = [
                    'value' => json_decode($pref['preference_value'], true),
                    'timestamp' => $pref['created_at']
                ];
            }

            return $evolution;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user preference evolution: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user engagement metrics
     */
    private function getUserEngagementMetrics(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    COUNT(DISTINCT DATE(search_timestamp)) as active_days,
                    COUNT(DISTINCT session_id) as total_sessions,
                    AVG(searches_per_session) as avg_searches_per_session,
                    MAX(search_timestamp) as last_activity
                FROM (
                    SELECT 
                        DATE(search_timestamp) as search_date,
                        session_id,
                        COUNT(*) as searches_per_session,
                        search_timestamp
                    FROM iqra_search_logs 
                    WHERE user_id = ?
                    GROUP BY DATE(search_timestamp), session_id
                ) as session_stats
            ";

            $metrics = $this->db->queryOne($sql, [$userId]);

            if (!$metrics) {
                return [];
            }

            // Calculate engagement score
            $engagementScore = $this->calculateEngagementScore($metrics);

            return array_merge($metrics, [
                'engagement_score' => $engagementScore,
                'engagement_level' => $this->getEngagementLevel($engagementScore)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user engagement metrics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions(int $userId): array
    {
        try {
            $basicInfo = $this->getBasicUserInfo($userId);
            $userRole = $basicInfo['role'] ?? 'user';

            $permissions = [];
            foreach ($this->permissionMatrix as $category => $categoryPermissions) {
                $permissions[$category] = [];
                foreach ($categoryPermissions as $permission => $allowedRoles) {
                    $permissions[$category][$permission] = in_array($userRole, $allowedRoles);
                }
            }

            return $permissions;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user permissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user search history
     */
    private function getUserSearchHistory(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    query, content_type, results_count, response_time,
                    search_timestamp, sort_by, sort_order
                FROM iqra_search_logs 
                WHERE user_id = ?
                ORDER BY search_timestamp DESC
                LIMIT 50
            ";

            return $this->db->query($sql, [$userId]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user search history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user contributions
     */
    private function getUserContributions(int $userId): array
    {
        try {
            // Mock user contributions for now
            return [
                'wiki_pages' => 0,
                'articles' => 0,
                'content_reviews' => 0,
                'translations' => 0,
                'total_contributions' => 0
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user contributions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Analyze search patterns
     */
    private function analyzeSearchPatterns(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    HOUR(search_timestamp) as hour_of_day,
                    DAYOFWEEK(search_timestamp) as day_of_week,
                    COUNT(*) as search_count
                FROM iqra_search_logs 
                WHERE user_id = ?
                GROUP BY HOUR(search_timestamp), DAYOFWEEK(search_timestamp)
                ORDER BY search_count DESC
            ";

            $patterns = $this->db->query($sql, [$userId]);

            // Calculate peak usage times
            $peakHours = [];
            $peakDays = [];
            
            foreach ($patterns as $pattern) {
                if ($pattern['search_count'] > 1) {
                    $peakHours[] = $pattern['hour_of_day'];
                    $peakDays[] = $pattern['day_of_week'];
                }
            }

            return [
                'hourly_patterns' => $patterns,
                'peak_hours' => array_unique($peakHours),
                'peak_days' => array_unique($peakDays)
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to analyze search patterns: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user content preferences
     */
    private function getUserContentPreferences(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    content_type,
                    COUNT(*) as preference_strength
                FROM iqra_search_logs 
                WHERE user_id = ?
                GROUP BY content_type
                ORDER BY preference_strength DESC
            ";

            $preferences = $this->db->query($sql, [$userId]);

            $totalSearches = array_sum(array_column($preferences, 'preference_strength'));
            
            foreach ($preferences as &$pref) {
                $pref['percentage'] = $totalSearches > 0 ? ($pref['preference_strength'] / $totalSearches) * 100 : 0;
            }

            return $preferences;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user content preferences: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate engagement score
     */
    private function calculateEngagementScore(array $metrics): float
    {
        $score = 0;
        
        // Active days (max 30 days)
        $activeDays = min($metrics['active_days'] ?? 0, 30);
        $score += ($activeDays / 30) * 40;
        
        // Sessions (max 100 sessions)
        $sessions = min($metrics['total_sessions'] ?? 0, 100);
        $score += ($sessions / 100) * 30;
        
        // Searches per session (max 10 searches)
        $searchesPerSession = min($metrics['avg_searches_per_session'] ?? 0, 10);
        $score += ($searchesPerSession / 10) * 30;
        
        return min($score, 100);
    }

    /**
     * Get engagement level
     */
    private function getEngagementLevel(float $score): string
    {
        if ($score >= 80) return 'Very High';
        if ($score >= 60) return 'High';
        if ($score >= 40) return 'Medium';
        if ($score >= 20) return 'Low';
        return 'Very Low';
    }

    /**
     * Update user preferences
     */
    public function updateUserPreferences(int $userId, array $preferences): bool
    {
        try {
            $this->logger->info("Updating user preferences for user {$userId}");

            foreach ($preferences as $key => $value) {
                $sql = "
                    INSERT INTO iqra_search_preferences (user_id, preference_key, preference_value)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        preference_value = VALUES(preference_value),
                        updated_at = CURRENT_TIMESTAMP
                ";

                $this->db->execute($sql, [
                    $userId,
                    $key,
                    json_encode($value)
                ]);
            }

            $this->logger->info("User preferences updated successfully for user {$userId}");
            return true;

        } catch (\Exception $e) {
            $this->logger->error("Failed to update user preferences: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user recommendations
     */
    public function getUserRecommendations(int $userId, int $limit = 10): array
    {
        try {
            $userProfile = $this->getUserProfile($userId);
            
            if (empty($userProfile)) {
                return [];
            }

            // Get personalized recommendations based on user profile
            $recommendations = $this->generatePersonalizedRecommendations($userProfile, $limit);
            
            // Filter recommendations based on user permissions
            $filteredRecommendations = $this->filterRecommendationsByPermissions($recommendations, $userProfile['permissions']);

            return $filteredRecommendations;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user recommendations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate personalized recommendations
     */
    private function generatePersonalizedRecommendations(array $userProfile, int $limit): array
    {
        // Mock personalized recommendations based on user profile
        $recommendations = [];
        
        if (isset($userProfile['behavior']['search_behavior']['content_type_usage'])) {
            foreach ($userProfile['behavior']['search_behavior']['content_type_usage'] as $contentType) {
                $recommendations[] = [
                    'type' => 'content_suggestion',
                    'title' => "More {$contentType['content_type']} content",
                    'description' => "Based on your interest in {$contentType['content_type']}",
                    'score' => $contentType['search_count'] / 10,
                    'action' => "Explore {$contentType['content_type']} content"
                ];
            }
        }

        // Add preference-based recommendations
        if (isset($userProfile['preferences']['preferred_content_types'])) {
            $recommendations[] = [
                'type' => 'preference_update',
                'title' => 'Update your content preferences',
                'description' => 'Customize your search experience',
                'score' => 0.8,
                'action' => 'Update preferences'
            ];
        }

        // Sort by score and limit
        usort($recommendations, function($a, $b) {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Filter recommendations by permissions
     */
    private function filterRecommendationsByPermissions(array $recommendations, array $permissions): array
    {
        return array_filter($recommendations, function($rec) use ($permissions) {
            // Check if user has permission for this recommendation type
            switch ($rec['type']) {
                case 'content_suggestion':
                    return $permissions['content']['content_view'] ?? false;
                case 'preference_update':
                    return $permissions['preferences']['basic_preferences'] ?? false;
                default:
                    return true;
            }
        });
    }

    /**
     * Check user permission
     */
    public function hasPermission(int $userId, string $category, string $permission): bool
    {
        try {
            $userPermissions = $this->getUserPermissions($userId);
            return $userPermissions[$category][$permission] ?? false;

        } catch (\Exception $e) {
            $this->logger->error("Failed to check user permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user role information
     */
    public function getUserRole(int $userId): array
    {
        try {
            $basicInfo = $this->getBasicUserInfo($userId);
            $role = $basicInfo['role'] ?? 'user';
            
            return $this->userRoles[$role] ?? $this->userRoles['user'];

        } catch (\Exception $e) {
            $this->logger->error("Failed to get user role: " . $e->getMessage());
            return $this->userRoles['user'];
        }
    }

    /**
     * Get all user roles
     */
    public function getAllUserRoles(): array
    {
        return $this->userRoles;
    }

    /**
     * Get permission matrix
     */
    public function getPermissionMatrix(): array
    {
        return $this->permissionMatrix;
    }
} 