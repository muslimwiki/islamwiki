<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Controllers;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Search Controller - Wiki search functionality
 * 
 * @package IslamWiki\Extensions\WikiExtension\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SearchController extends Controller
{
    private $wikiPageModel;
    private $wikiCategoryModel;

    public function __construct(\IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($container->get('database'), $container);
        
        // Initialize models
        $this->wikiPageModel = $container->get('wiki.page.model');
        $this->wikiCategoryModel = $container->get('wiki.category.model');
    }

    /**
     * Display search form
     */
    public function index(Request $request): Response
    {
        try {
            $data = [
                'title' => 'Search Wiki - IslamWiki',
                'categories' => $this->wikiCategoryModel->getAll(),
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/search', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading search page: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Perform search
     */
    public function search(Request $request): Response
    {
        try {
            $query = $request->getQueryParams();
            
            $searchTerm = trim($query['q'] ?? '');
            $category = $query['category'] ?? '';
            $type = $query['type'] ?? 'all';
            $sort = $query['sort'] ?? 'relevance';
            $page = max(1, (int)($query['page'] ?? 1));
            $perPage = min(50, max(10, (int)($query['per_page'] ?? 20)));

            if (empty($searchTerm)) {
                return $this->redirect('/wiki/search');
            }

            // Perform search
            $searchResults = $this->performSearch($searchTerm, $category, $type, $sort, $page, $perPage);
            
            // Get search suggestions
            $suggestions = $this->getSearchSuggestions($searchTerm);
            
            // Get related categories
            $relatedCategories = $this->getRelatedCategories($searchTerm);

            $data = [
                'title' => 'Search Results: ' . htmlspecialchars($searchTerm) . ' - Wiki - IslamWiki',
                'searchTerm' => $searchTerm,
                'category' => $category,
                'type' => $type,
                'sort' => $sort,
                'page' => $page,
                'perPage' => $perPage,
                'results' => $searchResults['results'],
                'totalResults' => $searchResults['total'],
                'totalPages' => $searchResults['totalPages'],
                'suggestions' => $suggestions,
                'relatedCategories' => $relatedCategories,
                'categories' => $this->wikiCategoryModel->getAll(),
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/search-results', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error performing search: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Advanced search
     */
    public function advanced(Request $request): Response
    {
        try {
            $data = [
                'title' => 'Advanced Search - Wiki - IslamWiki',
                'categories' => $this->wikiCategoryModel->getAll(),
                'searchTypes' => $this->getSearchTypes(),
                'sortOptions' => $this->getSortOptions(),
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/advanced-search', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading advanced search: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Search suggestions (AJAX)
     */
    public function suggestions(Request $request): Response
    {
        try {
            $query = $request->getQueryParams();
            $term = trim($query['q'] ?? '');
            
            if (empty($term) || strlen($term) < 2) {
                return $this->json(['suggestions' => []]);
            }

            $suggestions = $this->getSearchSuggestions($term, 10);
            
            return $this->json(['suggestions' => $suggestions]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error getting suggestions'], 500);
        }
    }

    /**
     * Search statistics
     */
    public function stats(Request $request): Response
    {
        try {
            $data = [
                'title' => 'Search Statistics - Wiki - IslamWiki',
                'searchStats' => $this->getSearchStatistics(),
                'popularSearches' => $this->getPopularSearches(),
                'recentSearches' => $this->getRecentSearches(),
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/search-stats', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading search statistics: ' . $e->getMessage()], 500);
        }
    }

    // Helper methods
    private function performSearch(string $searchTerm, string $category, string $type, string $sort, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Build search query
        $whereConditions = [];
        $params = [];
        
        // Search term
        if (!empty($searchTerm)) {
            $whereConditions[] = "(title LIKE ? OR content LIKE ? OR meta_description LIKE ?)";
            $searchPattern = '%' . $searchTerm . '%';
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $params[] = $searchPattern;
        }
        
        // Category filter
        if (!empty($category)) {
            $whereConditions[] = "category_slug = ?";
            $params[] = $category;
        }
        
        // Type filter
        if ($type !== 'all') {
            $whereConditions[] = "content_type = ?";
            $params[] = $type;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Count total results
        $countQuery = "SELECT COUNT(*) as total FROM wiki_pages " . $whereClause;
        $total = $this->db->query($countQuery, $params)->fetch()['total'] ?? 0;
        
        // Get results
        $orderBy = $this->getOrderByClause($sort);
        $query = "SELECT * FROM wiki_pages " . $whereClause . " " . $orderBy . " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $results = $this->db->query($query, $params)->fetchAll();
        
        // Calculate total pages
        $totalPages = ceil($total / $perPage);
        
        return [
            'results' => $results,
            'total' => $total,
            'totalPages' => $totalPages
        ];
    }

    private function getSearchSuggestions(string $term, int $limit = 5): array
    {
        $query = "SELECT title, slug FROM wiki_pages 
                  WHERE title LIKE ? OR content LIKE ? 
                  ORDER BY 
                    CASE WHEN title LIKE ? THEN 1 ELSE 2 END,
                    LENGTH(title) ASC
                  LIMIT ?";
        
        $searchPattern = '%' . $term . '%';
        $exactPattern = $term . '%';
        
        $results = $this->db->query($query, [$searchPattern, $searchPattern, $exactPattern, $limit])->fetchAll();
        
        return array_map(function($row) {
            return [
                'text' => $row['title'],
                'url' => '/wiki/' . $row['slug']
            ];
        }, $results);
    }

    private function getRelatedCategories(string $searchTerm): array
    {
        $query = "SELECT c.name, c.slug, c.description, COUNT(p.id) as page_count
                  FROM wiki_categories c
                  LEFT JOIN wiki_pages p ON c.id = p.category_id
                  WHERE c.name LIKE ? OR c.description LIKE ?
                  GROUP BY c.id
                  ORDER BY page_count DESC
                  LIMIT 5";
        
        $searchPattern = '%' . $searchTerm . '%';
        $results = $this->db->query($query, [$searchPattern, $searchPattern])->fetchAll();
        
        return $results;
    }

    private function getSearchTypes(): array
    {
        return [
            'all' => 'All Content',
            'pages' => 'Wiki Pages',
            'articles' => 'Articles',
            'guides' => 'Guides',
            'references' => 'References'
        ];
    }

    private function getSortOptions(): array
    {
        return [
            'relevance' => 'Relevance',
            'title' => 'Title A-Z',
            'title_desc' => 'Title Z-A',
            'date' => 'Newest First',
            'date_old' => 'Oldest First',
            'views' => 'Most Viewed',
            'rating' => 'Highest Rated'
        ];
    }

    private function getOrderByClause(string $sort): string
    {
        switch ($sort) {
            case 'title':
                return 'ORDER BY title ASC';
            case 'title_desc':
                return 'ORDER BY title DESC';
            case 'date':
                return 'ORDER BY created_at DESC';
            case 'date_old':
                return 'ORDER BY created_at ASC';
            case 'views':
                return 'ORDER BY view_count DESC';
            case 'rating':
                return 'ORDER BY rating DESC';
            case 'relevance':
            default:
                return 'ORDER BY 
                    CASE WHEN title LIKE ? THEN 1 
                         WHEN title LIKE ? THEN 2 
                         ELSE 3 END,
                    LENGTH(title) ASC';
        }
    }

    private function getSearchStatistics(): array
    {
        // Mock implementation - replace with actual statistics
        return [
            'totalSearches' => 1250,
            'uniqueSearchers' => 450,
            'averageResultsPerSearch' => 8.5,
            'mostSearchedTerm' => 'salah',
            'searchSuccessRate' => 94.2
        ];
    }

    private function getPopularSearches(): array
    {
        // Mock implementation - replace with actual popular searches
        return [
            ['term' => 'salah', 'count' => 156],
            ['term' => 'quran', 'count' => 142],
            ['term' => 'hadith', 'count' => 98],
            ['term' => 'ramadan', 'count' => 87],
            ['term' => 'dua', 'count' => 76]
        ];
    }

    private function getRecentSearches(): array
    {
        // Mock implementation - replace with actual recent searches
        return [
            ['term' => 'eid al-fitr', 'timestamp' => '2025-01-20 14:30:00'],
            ['term' => 'tahajjud', 'timestamp' => '2025-01-20 13:15:00'],
            ['term' => 'dhikr', 'timestamp' => '2025-01-20 12:45:00'],
            ['term' => 'wudu', 'timestamp' => '2025-01-20 11:20:00'],
            ['term' => 'sadaqah', 'timestamp' => '2025-01-20 10:55:00']
        ];
    }

    private function getCurrentUser(): ?array
    {
        // Mock implementation - replace with actual user system
        return [
            'id' => 1,
            'username' => 'admin',
            'role' => 'admin'
        ];
    }
} 