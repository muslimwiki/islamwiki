<?php

/**
 * Islamic Content Recommender
 *
 * Advanced content recommendation system with intelligent matching,
 * personalized recommendations, and Islamic content focus.
 *
 * @package IslamWiki\Core\Islamic
 * @version 0.0.22
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Islamic;

use IslamWiki\Core\Database\Connection;
use Logging;\Logger

class IslamicContentRecommender
{
    /**
     * The database connection.
     */
    private Connection $db;

    /**
     * The logger instance.
     */
    private Logging $logger;

    /**
     * Content categories.
     */
    private array $categories = [
        'quran' => 'Quran & Tafsir',
        'hadith' => 'Hadith & Sunnah',
        'fiqh' => 'Islamic Law & Jurisprudence',
        'aqeedah' => 'Islamic Beliefs & Creed',
        'seerah' => 'Prophet Muhammad (PBUH)',
        'history' => 'Islamic History',
        'scholars' => 'Islamic Scholars',
        'prayer' => 'Prayer & Worship',
        'ramadan' => 'Ramadan & Fasting',
        'hajj' => 'Hajj & Umrah',
        'charity' => 'Charity & Zakat',
        'family' => 'Family & Marriage',
        'education' => 'Islamic Education',
        'modern' => 'Modern Islamic Issues'
    ];

    /**
     * Content tags.
     */
    private array $tags = [
        'beginner' => 'Beginner Level',
        'intermediate' => 'Intermediate Level',
        'advanced' => 'Advanced Level',
        'scholarly' => 'Scholarly Content',
        'practical' => 'Practical Guidance',
        'theoretical' => 'Theoretical Knowledge',
        'historical' => 'Historical Context',
        'contemporary' => 'Contemporary Issues',
        'spiritual' => 'Spiritual Development',
        'social' => 'Social Issues',
        'economic' => 'Economic Principles',
        'political' => 'Political Thought',
        'scientific' => 'Scientific Perspective',
        'philosophical' => 'Philosophical Discussion'
    ];

    /**
     * Create a new Islamic content recommender instance.
     */
    public function __construct(Connection $db, Logging $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get personalized recommendations for a user.
     */
    public function getPersonalizedRecommendations(int $userId, int $limit = 10): array
    {
        try {
            // Get user preferences and history
            $userPreferences = $this->getUserPreferences($userId);
            $userHistory = $this->getUserHistory($userId);
            $userLevel = $this->getUserLevel($userId);

            // Build recommendation query
            $recommendations = $this->buildRecommendations($userPreferences, $userHistory, $userLevel, $limit);

            $this->logger->info('Personalized recommendations generated', [
                'user_id' => $userId,
                'count' => count($recommendations)
            ]);

            return $recommendations;
        } catch (\Exception $e) {
            $this->logger->error('Personalized recommendations failed: ' . $e->getMessage());
            return $this->getDefaultRecommendations($limit);
        }
    }

    /**
     * Get recommendations based on current content.
     */
    public function getRelatedContent(int $contentId, int $limit = 5): array
    {
        try {
            $content = $this->getContentById($contentId);
            if (!$content) {
                return [];
            }

            $relatedContent = $this->findRelatedContent($content, $limit);

            $this->logger->info('Related content found', [
                'content_id' => $contentId,
                'count' => count($relatedContent)
            ]);

            return $relatedContent;
        } catch (\Exception $e) {
            $this->logger->error('Related content lookup failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recommendations based on Islamic events and dates.
     */
    public function getEventBasedRecommendations(string $date, int $limit = 10): array
    {
        try {
            $events = $this->getIslamicEventsForDate($date);
            $recommendations = [];

            foreach ($events as $event) {
                $eventContent = $this->getContentByEvent($event, $limit / count($events));
                $recommendations = array_merge($recommendations, $eventContent);
            }

            // Remove duplicates and limit results
            $recommendations = array_slice(array_unique($recommendations, SORT_REGULAR), 0, $limit);

            $this->logger->info('Event-based recommendations generated', [
                'date' => $date,
                'events' => count($events),
                'recommendations' => count($recommendations)
            ]);

            return $recommendations;
        } catch (\Exception $e) {
            $this->logger->error('Event-based recommendations failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recommendations based on user search history.
     */
    public function getSearchBasedRecommendations(array $searchTerms, int $limit = 10): array
    {
        try {
            $recommendations = [];

            foreach ($searchTerms as $term) {
                $termContent = $this->searchContent($term, $limit / count($searchTerms));
                $recommendations = array_merge($recommendations, $termContent);
            }

            // Remove duplicates and limit results
            $recommendations = array_slice(array_unique($recommendations, SORT_REGULAR), 0, $limit);

            $this->logger->info('Search-based recommendations generated', [
                'search_terms' => $searchTerms,
                'recommendations' => count($recommendations)
            ]);

            return $recommendations;
        } catch (\Exception $e) {
            $this->logger->error('Search-based recommendations failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get trending Islamic content.
     */
    public function getTrendingContent(int $limit = 10): array
    {
        try {
            $trendingContent = $this->db->table('islamic_content')
                ->select([
                    'id',
                    'title',
                    'category',
                    'tags',
                    'view_count',
                    'like_count',
                    'share_count',
                    'created_at'
                ])
                ->orderBy('view_count', 'desc')
                ->orderBy('like_count', 'desc')
                ->orderBy('share_count', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();

            $this->logger->info('Trending content retrieved', [
                'count' => count($trendingContent)
            ]);

            return $trendingContent;
        } catch (\Exception $e) {
            $this->logger->error('Trending content retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get content by category.
     */
    public function getContentByCategory(string $category, int $limit = 10): array
    {
        try {
            $content = $this->db->table('islamic_content')
                ->where('category', $category)
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();

            $this->logger->info('Category content retrieved', [
                'category' => $category,
                'count' => count($content)
            ]);

            return $content;
        } catch (\Exception $e) {
            $this->logger->error('Category content retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user preferences.
     */
    private function getUserPreferences(int $userId): array
    {
        try {
            $preferences = $this->db->table('user_preferences')
                ->where('user_id', $userId)
                ->first();

            return $preferences ? json_decode($preferences['preferences'], true) : [];
        } catch (\Exception $e) {
            $this->logger->error('User preferences retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user history.
     */
    private function getUserHistory(int $userId): array
    {
        try {
            return $this->db->table('user_content_history')
                ->where('user_id', $userId)
                ->orderBy('viewed_at', 'desc')
                ->limit(50)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->logger->error('User history retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user level.
     */
    private function getUserLevel(int $userId): string
    {
        try {
            $user = $this->db->table('users')
                ->where('id', $userId)
                ->first();

            if (!$user) {
                return 'beginner';
            }

            // Calculate user level based on activity and engagement
            $activityScore = $this->calculateActivityScore($userId);

            if ($activityScore > 100) {
                return 'advanced';
            } elseif ($activityScore > 50) {
                return 'intermediate';
            } else {
                return 'beginner';
            }
        } catch (\Exception $e) {
            $this->logger->error('User level calculation failed: ' . $e->getMessage());
            return 'beginner';
        }
    }

    /**
     * Calculate user activity score.
     */
    private function calculateActivityScore(int $userId): int
    {
        try {
            $score = 0;

            // Content views
            $views = $this->db->table('user_content_history')
                ->where('user_id', $userId)
                ->count();
            $score += $views;

            // Comments
            $comments = $this->db->table('user_comments')
                ->where('user_id', $userId)
                ->count();
            $score += $comments * 2;

            // Likes
            $likes = $this->db->table('user_likes')
                ->where('user_id', $userId)
                ->count();
            $score += $likes;

            // Shares
            $shares = $this->db->table('user_shares')
                ->where('user_id', $userId)
                ->count();
            $score += $shares * 3;

            return $score;
        } catch (\Exception $e) {
            $this->logger->error('Activity score calculation failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Build recommendations based on user data.
     */
    private function buildRecommendations(array $preferences, array $history, string $level, int $limit): array
    {
        try {
            $recommendations = [];

            // Get content based on user preferences
            if (!empty($preferences['categories'])) {
                foreach ($preferences['categories'] as $category) {
                    $categoryContent = $this->getContentByCategory($category, $limit / 3);
                    $recommendations = array_merge($recommendations, $categoryContent);
                }
            }

            // Get content based on user history
            if (!empty($history)) {
                $historyCategories = array_column($history, 'category');
                $historyCategories = array_count_values($historyCategories);
                arsort($historyCategories);

                foreach (array_keys(array_slice($historyCategories, 0, 3)) as $category) {
                    $categoryContent = $this->getContentByCategory($category, $limit / 3);
                    $recommendations = array_merge($recommendations, $categoryContent);
                }
            }

            // Filter by user level
            $recommendations = $this->filterByLevel($recommendations, $level);

            // Remove duplicates and limit results
            $recommendations = array_slice(array_unique($recommendations, SORT_REGULAR), 0, $limit);

            return $recommendations;
        } catch (\Exception $e) {
            $this->logger->error('Recommendation building failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Filter content by user level.
     */
    private function filterByLevel(array $content, string $level): array
    {
        return array_filter($content, function ($item) use ($level) {
            $tags = json_decode($item['tags'] ?? '[]', true);

            if ($level === 'beginner') {
                return in_array('beginner', $tags);
            } elseif ($level === 'intermediate') {
                return in_array('intermediate', $tags) || in_array('beginner', $tags);
            } else {
                return true; // Advanced users can see all content
            }
        });
    }

    /**
     * Get content by ID.
     */
    private function getContentById(int $contentId): ?array
    {
        try {
            return $this->db->table('islamic_content')
                ->where('id', $contentId)
                ->first();
        } catch (\Exception $e) {
            $this->logger->error('Content retrieval failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find related content.
     */
    private function findRelatedContent(array $content, int $limit): array
    {
        try {
            $category = $content['category'];
            $tags = json_decode($content['tags'] ?? '[]', true);

            $relatedContent = $this->db->table('islamic_content')
                ->where('category', $category)
                ->where('id', '!=', $content['id'])
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();

            return $relatedContent;
        } catch (\Exception $e) {
            $this->logger->error('Related content search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Islamic events for date.
     */
    private function getIslamicEventsForDate(string $date): array
    {
        try {
            $calendar = new AdvancedIslamicCalendar($this->logger);
            $dateParts = explode('-', $date);

            return $calendar->getIslamicEvents(
                (int) $dateParts[0],
                (int) $dateParts[1],
                (int) $dateParts[2]
            );
        } catch (\Exception $e) {
            $this->logger->error('Islamic events lookup failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get content by event.
     */
    private function getContentByEvent(array $event, int $limit): array
    {
        try {
            $eventName = $event['name'];

            return $this->db->table('islamic_content')
                ->where('title', 'LIKE', "%{$eventName}%")
                ->orWhere('content', 'LIKE', "%{$eventName}%")
                ->orWhere('tags', 'LIKE', "%{$eventName}%")
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Event content search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search content.
     */
    private function searchContent(string $term, int $limit): array
    {
        try {
            return $this->db->table('islamic_content')
                ->where('title', 'LIKE', "%{$term}%")
                ->orWhere('content', 'LIKE', "%{$term}%")
                ->orWhere('tags', 'LIKE', "%{$term}%")
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Content search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get default recommendations.
     */
    private function getDefaultRecommendations(int $limit): array
    {
        try {
            return $this->db->table('islamic_content')
                ->where('is_published', true)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Default recommendations failed: ' . $e->getMessage());
            return [];
        }
    }
}
