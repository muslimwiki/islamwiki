<?php

/**
 * Sciences Controller
 *
 * Handles HTTP requests for Islamic sciences content and categories.
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Container;

/**
 * Sciences Controller - Handles Islamic Sciences Functionality
 */
class SciencesController extends Controller
{
    /**
     * Display the Islamic Sciences home page
     */
    public function index(Request $request): Response
    {
        try {
            return $this->view('sciences/index', [
                'title' => 'Islamic Sciences - IslamWiki',
                'description' => 'Explore the comprehensive field of Islamic sciences including Fiqh, Aqeedah, Usul al-Fiqh, and more.',
                'sciences' => $this->getIslamicSciences()
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display a specific science category
     */
    public function category(Request $request, string $category): Response
    {
        try {
            $sciences = $this->getIslamicSciences();
            
            if (!isset($sciences[$category])) {
                return new Response(404, [], 'Science category not found');
            }

            $scienceInfo = $sciences[$category];
            $articles = $this->getScienceArticles($category);

            return $this->view('sciences/category', [
                'title' => "{$scienceInfo['title']} - Islamic Sciences - IslamWiki",
                'science' => $scienceInfo,
                'articles' => $articles
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display a specific science article
     */
    public function article(Request $request, string $category, string $slug): Response
    {
        try {
            $article = $this->getScienceArticle($category, $slug);
            
            if (!$article) {
                return new Response(404, [], 'Article not found');
            }

            $relatedArticles = $this->getRelatedArticles($category, $slug);

            return $this->view('sciences/article', [
                'title' => "{$article['title']} - Islamic Sciences - IslamWiki",
                'article' => $article,
                'related_articles' => $relatedArticles
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Search science articles
     */
    public function search(Request $request): Response
    {
        try {
            $query = $request->getQueryParams()['q'] ?? '';
            $category = $request->getQueryParams()['category'] ?? '';
            $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
            $limit = 20;

            if (empty($query)) {
                return $this->view('sciences/search', [
                    'query' => '',
                    'results' => [],
                    'title' => 'Search Islamic Sciences - IslamWiki'
                ], 200);
            }

            $results = $this->searchScienceArticles($query, $category, $page, $limit);
            $totalResults = $this->getTotalSearchResults($query, $category);

            return $this->view('sciences/search', [
                'query' => $query,
                'category' => $category,
                'results' => $results,
                'totalResults' => $totalResults,
                'currentPage' => $page,
                'totalPages' => ceil($totalResults / $limit),
                'title' => "Search: {$query} - Islamic Sciences - IslamWiki"
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Get Islamic sciences categories
     */
    private function getIslamicSciences(): array
    {
        return [
            'fiqh' => [
                'title' => 'Fiqh (Islamic Jurisprudence)',
                'description' => 'The study of Islamic law and legal methodology',
                'icon' => '⚖️',
                'url' => '/sciences/fiqh'
            ],
            'aqeedah' => [
                'title' => 'Aqeedah (Islamic Theology)',
                'description' => 'The study of Islamic beliefs and creed',
                'icon' => '🕌',
                'url' => '/sciences/aqeedah'
            ],
            'usul' => [
                'title' => 'Usul al-Fiqh (Principles of Jurisprudence)',
                'description' => 'The methodology and principles of Islamic law',
                'icon' => '📚',
                'url' => '/sciences/usul'
            ],
            'hadith_sciences' => [
                'title' => 'Hadith Sciences',
                'description' => 'The study of hadith methodology and authentication',
                'icon' => '📖',
                'url' => '/sciences/hadith-sciences'
            ],
            'quranic_sciences' => [
                'title' => 'Quranic Sciences',
                'description' => 'The study of Quranic interpretation and sciences',
                'icon' => '📜',
                'url' => '/sciences/quranic-sciences'
            ],
            'arabic' => [
                'title' => 'Arabic Language & Grammar',
                'description' => 'The study of Arabic language, grammar, and rhetoric',
                'icon' => '🔤',
                'url' => '/sciences/arabic'
            ],
            'history' => [
                'title' => 'Islamic History',
                'description' => 'The study of Islamic civilization and history',
                'icon' => '🏛️',
                'url' => '/sciences/history'
            ]
        ];
    }

    /**
     * Get science articles for a category
     */
    private function getScienceArticles(string $category): array
    {
        try {
            $sql = "SELECT id, title, slug, excerpt, created_at FROM science_articles 
                    WHERE category = ? AND is_published = 1 ORDER BY created_at DESC LIMIT 20";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$category]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get a specific science article
     */
    private function getScienceArticle(string $category, string $slug): ?array
    {
        try {
            $sql = "SELECT id, title, slug, content, excerpt, category, created_at, updated_at 
                    FROM science_articles WHERE category = ? AND slug = ? AND is_published = 1";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$category, $slug]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get related articles
     */
    private function getRelatedArticles(string $category, string $slug): array
    {
        try {
            $sql = "SELECT id, title, slug, excerpt FROM science_articles 
                    WHERE category = ? AND slug != ? AND is_published = 1 
                    ORDER BY created_at DESC LIMIT 5";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$category, $slug]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search science articles
     */
    private function searchScienceArticles(string $query, string $category, int $page, int $limit): array
    {
        try {
            $offset = ($page - 1) * $limit;
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT id, title, slug, excerpt, category, created_at FROM science_articles 
                    WHERE is_published = 1 AND (title LIKE ? OR content LIKE ?)";
            
            $params = [$searchTerm, $searchTerm];
            
            if (!empty($category)) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get total search results count
     */
    private function getTotalSearchResults(string $query, string $category): int
    {
        try {
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT COUNT(*) as count FROM science_articles 
                    WHERE is_published = 1 AND (title LIKE ? OR content LIKE ?)";
            
            $params = [$searchTerm, $searchTerm];
            
            if (!empty($category)) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
