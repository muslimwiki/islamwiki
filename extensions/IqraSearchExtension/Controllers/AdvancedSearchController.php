<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Extensions\IqraSearchExtension\Services\IqraSearchEngine;
use IslamWiki\Extensions\IqraSearchExtension\Services\SearchAnalytics;
use IslamWiki\Extensions\IqraSearchExtension\Services\PersonalizedSearch;
use Psr\Log\LoggerInterface;

/**
 * Advanced Search Controller
 * Provides enhanced search functionality with analytics and personalization
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class AdvancedSearchController
{
    private IqraSearchEngine $searchEngine;
    private SearchAnalytics $analytics;
    private PersonalizedSearch $personalizedSearch;
    private LoggerInterface $logger;

    public function __construct(
        IqraSearchEngine $searchEngine,
        SearchAnalytics $analytics,
        PersonalizedSearch $personalizedSearch,
        LoggerInterface $logger
    ) {
        $this->searchEngine = $searchEngine;
        $this->analytics = $analytics;
        $this->personalizedSearch = $personalizedSearch;
        $this->logger = $logger;
    }

    /**
     * Advanced search with personalization
     */
    public function advancedSearch(Request $request): Response
    {
        try {
            $query = $request->get('q', '');
            $type = $request->get('type', 'all');
            $sort = $request->get('sort', 'relevance');
            $order = $request->get('order', 'desc');
            $page = (int)($request->get('page', 1));
            $limit = (int)($request->get('limit', 20));
            $userId = $this->getCurrentUserId();
            
            if (empty($query)) {
                return $this->renderAdvancedSearchPage();
            }
            
            // Track search analytics
            $startTime = microtime(true);
            
            // Get personalized search results
            $searchResults = $this->personalizedSearch->getPersonalizedResults($query, [
                'type' => $type,
                'sort' => $sort,
                'order' => $order,
                'page' => $page,
                'limit' => $limit
            ], $userId);
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            
            // Track search in analytics
            $this->analytics->trackSearch($query, [
                'type' => $type,
                'sort' => $sort,
                'order' => $order,
                'results_count' => count($searchResults['results']),
                'response_time' => $responseTime / 1000 // Convert back to seconds
            ], $userId);
            
            // Get search analytics for insights
            $searchInsights = $this->getSearchInsights($query, $type);
            
            return $this->renderAdvancedSearchResults($query, $searchResults, $searchInsights, [
                'type' => $type,
                'sort' => $sort,
                'order' => $order,
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Advanced search failed', [
                'error' => $e->getMessage(),
                'query' => $query ?? 'unknown'
            ]);
            
            return $this->renderError('Search failed', $e->getMessage());
        }
    }

    /**
     * Get search insights and analytics
     */
    public function getSearchInsights(string $query, string $type): array
    {
        try {
            $insights = [];
            
            // Get related searches
            $insights['related_searches'] = $this->getRelatedSearches($query, $type);
            
            // Get trending topics in this content type
            $insights['trending_topics'] = $this->getTrendingTopics($type);
            
            // Get search statistics
            $insights['search_stats'] = $this->getSearchStatistics($query, $type);
            
            // Get content recommendations
            $insights['content_recommendations'] = $this->getContentRecommendations($type);
            
            return $insights;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get search insights', [
                'error' => $e->getMessage(),
                'query' => $query,
                'type' => $type
            ]);
            
            return [];
        }
    }

    /**
     * Get related searches
     */
    private function getRelatedSearches(string $query, string $type): array
    {
        try {
            // This would typically query the analytics database
            // For now, return mock related searches
            $relatedSearches = [
                'islamic principles' => [
                    'islamic beliefs',
                    'core tenets of islam',
                    'islamic faith',
                    'muslim beliefs'
                ],
                'quran recitation' => [
                    'tajweed rules',
                    'quran pronunciation',
                    'quran memorization',
                    'beautiful recitation'
                ],
                'hadith authentication' => [
                    'hadith sciences',
                    'isnad verification',
                    'hadith grading',
                    'authentic hadith'
                ]
            ];
            
            $queryLower = strtolower($query);
            foreach ($relatedSearches as $key => $related) {
                if (strpos($queryLower, $key) !== false) {
                    return $related;
                }
            }
            
            // Default related searches
            return [
                $query . ' guide',
                $query . ' principles',
                $query . ' importance',
                $query . ' in islam'
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get related searches', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get trending topics for content type
     */
    private function getTrendingTopics(string $type): array
    {
        try {
            $trendingTopics = [
                'all' => [
                    'Ramadan 2025',
                    'Islamic Finance',
                    'Digital Islam',
                    'Islamic Education'
                ],
                'wiki' => [
                    'Islamic History',
                    'Islamic Scholars',
                    'Islamic Architecture',
                    'Islamic Art'
                ],
                'quran' => [
                    'Quran Translation',
                    'Quran Tafsir',
                    'Quran Memorization',
                    'Quran Recitation'
                ],
                'hadith' => [
                    'Hadith Collections',
                    'Hadith Sciences',
                    'Authentic Hadith',
                    'Hadith Scholars'
                ],
                'articles' => [
                    'Modern Islamic Issues',
                    'Islamic Technology',
                    'Islamic Ethics',
                    'Islamic Psychology'
                ]
            ];
            
            return $trendingTopics[$type] ?? $trendingTopics['all'];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get trending topics', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);
            
            return [];
        }
    }

    /**
     * Get search statistics
     */
    private function getSearchStatistics(string $query, string $type): array
    {
        try {
            // This would typically query the analytics database
            // For now, return mock statistics
            return [
                'total_results' => rand(50, 200),
                'response_time' => rand(50, 150) / 1000, // Random response time
                'search_popularity' => rand(1, 100), // Random popularity score
                'content_coverage' => '100%',
                'last_updated' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get search statistics', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get content recommendations
     */
    private function getContentRecommendations(string $type): array
    {
        try {
            $recommendations = [
                'all' => [
                    [
                        'title' => 'Islamic Principles Guide',
                        'url' => '/wiki/islamic-principles',
                        'type' => 'wiki',
                        'description' => 'Comprehensive guide to Islamic beliefs'
                    ],
                    [
                        'title' => 'Quran Study Resources',
                        'url' => '/quran/study-resources',
                        'type' => 'quran',
                        'description' => 'Tools and resources for Quran study'
                    ]
                ],
                'wiki' => [
                    [
                        'title' => 'Islamic History Timeline',
                        'url' => '/wiki/islamic-history',
                        'type' => 'wiki',
                        'description' => 'Complete timeline of Islamic history'
                    ]
                ],
                'quran' => [
                    [
                        'title' => 'Quran Translation Comparison',
                        'url' => '/quran/translations',
                        'type' => 'quran',
                        'description' => 'Compare different Quran translations'
                    ]
                ]
            ];
            
            return $recommendations[$type] ?? $recommendations['all'];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get content recommendations', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);
            
            return [];
        }
    }

    /**
     * Render advanced search page
     */
    private function renderAdvancedSearchPage(): Response
    {
        // This would typically render a Twig template
        $html = '
        <div class="advanced-search-page">
            <h1>Advanced Search</h1>
            <p>Use advanced search features to find Islamic content more effectively.</p>
        </div>';
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Render advanced search results
     */
    private function renderAdvancedSearchResults(string $query, array $searchResults, array $insights, array $options): Response
    {
        // This would typically render a Twig template
        $html = '
        <div class="advanced-search-results">
            <h1>Search Results for: ' . htmlspecialchars($query) . '</h1>
            <p>Found ' . count($searchResults['results']) . ' results</p>
            
            <div class="search-results">
                ' . $this->renderSearchResults($searchResults['results']) . '
            </div>
            
            <div class="search-insights">
                ' . $this->renderSearchInsights($insights) . '
            </div>
        </div>';
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Render search results
     */
    private function renderSearchResults(array $results): string
    {
        $html = '';
        foreach ($results as $result) {
            $html .= '
            <div class="search-result">
                <h3><a href="' . htmlspecialchars($result['url']) . '">' . htmlspecialchars($result['title']) . '</a></h3>
                <p>' . htmlspecialchars($result['snippet']) . '</p>
                <div class="result-meta">
                    <span class="type">' . htmlspecialchars($result['type']) . '</span>
                    <span class="relevance">Relevance: ' . ($result['relevance'] ?? 0) . '%</span>
                </div>
            </div>';
        }
        
        return $html;
    }

    /**
     * Render search insights
     */
    private function renderSearchInsights(array $insights): string
    {
        $html = '<div class="search-insights-container">';
        
        if (!empty($insights['related_searches'])) {
            $html .= '
            <div class="insight-section">
                <h3>Related Searches</h3>
                <div class="related-searches">';
            foreach ($insights['related_searches'] as $related) {
                $html .= '<a href="/search?q=' . urlencode($related) . '" class="related-search">' . htmlspecialchars($related) . '</a>';
            }
            $html .= '</div></div>';
        }
        
        if (!empty($insights['trending_topics'])) {
            $html .= '
            <div class="insight-section">
                <h3>Trending Topics</h3>
                <div class="trending-topics">';
            foreach ($insights['trending_topics'] as $topic) {
                $html .= '<a href="/search?q=' . urlencode($topic) . '" class="trending-topic">' . htmlspecialchars($topic) . '</a>';
            }
            $html .= '</div></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render error page
     */
    private function renderError(string $title, string $message): Response
    {
        $html = '
        <div class="search-error">
            <h1>' . htmlspecialchars($title) . '</h1>
            <p>' . htmlspecialchars($message) . '</p>
            <a href="/search" class="back-to-search">Back to Search</a>
        </div>';
        
        return new Response($html, 500, ['Content-Type' => 'text/html']);
    }

    /**
     * Get current user ID from session
     */
    private function getCurrentUserId(): ?int
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }
        
        return null;
    }

    /**
     * API endpoint for search analytics
     */
    public function getAnalytics(Request $request): Response
    {
        try {
            $filters = [
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'content_type' => $request->get('content_type', 'all'),
                'user_id' => $this->getCurrentUserId()
            ];
            
            $analytics = $this->analytics->getSearchAnalytics($filters);
            
            return new Response(
                json_encode($analytics),
                200,
                ['Content-Type' => 'application/json']
            );
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get search analytics', [
                'error' => $e->getMessage()
            ]);
            
            return new Response(
                json_encode(['error' => 'Failed to get analytics']),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }

    /**
     * API endpoint for personalized recommendations
     */
    public function getRecommendations(Request $request): Response
    {
        try {
            $userId = $this->getCurrentUserId();
            $limit = (int)($request->get('limit', 10));
            
            $recommendations = $this->personalizedSearch->getPersonalizedRecommendations($userId, '', $limit);
            
            return new Response(
                json_encode(['recommendations' => $recommendations]),
                200,
                ['Content-Type' => 'application/json']
            );
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get personalized recommendations', [
                'error' => $e->getMessage()
            ]);
            
            return new Response(
                json_encode(['error' => 'Failed to get recommendations']),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }
} 