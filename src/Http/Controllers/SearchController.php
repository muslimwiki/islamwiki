<?php

/**
 * Search Controller
 *
 * Handles search functionality for IslamWiki.
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
 * Search Controller - Handles Search Functionality
 */
class SearchController extends Controller
{
    /**
     * Display the main search page
     */
    public function index(Request $request): Response
    {
        try {
            $query = $request->getQueryParams()['q'] ?? '';
            $type = $request->getQueryParams()['type'] ?? 'all';
            $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
            $limit = 20;

            $results = [];
            $totalResults = 0;
            $searchStats = [];

            if (!empty($query)) {
                $results = $this->performSearch($query, $type, $page, $limit);
                $totalResults = $this->getTotalResults($query, $type);
                $searchStats = $this->getSearchStatistics($query);
            }

            return $this->view('search/index', [
                'query' => $query,
                'type' => $type,
                'results' => $results,
                'totalResults' => $totalResults,
                'currentPage' => $page,
                'totalPages' => ceil($totalResults / $limit),
                'searchStats' => $searchStats,
                'searchTypes' => [
                    'all' => 'All Content',
                    'pages' => 'Wiki Pages',
                    'quran' => 'Quran Ayahs',
                    'hadith' => 'Hadith',
                    'calendar' => 'Calendar Events',
                    'prayer' => 'Prayer Times'
                ]
            ]);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * API search endpoint
     */
    public function apiSearch(Request $request): Response
    {
        try {
            $query = $request->getQueryParams()['q'] ?? '';
            $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
            $perPage = 20;

            if (empty($query)) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Search query is required'
                ]));
            }

            $results = $this->performSearch($query, $page, $perPage);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'query' => $query,
                'results' => $results['data'],
                'pagination' => $results['pagination']
            ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Get live search suggestions
     */
    public function getSuggestions(Request $request): Response
    {
        try {
            $query = $request->getQueryParams()['q'] ?? '';
            
            if (strlen($query) < 2) {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'suggestions' => []
                ]));
            }

            $suggestions = $this->getSearchSuggestions($query);
            
            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'suggestions' => $suggestions
            ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Save search to user's library
     */
    public function saveSearch(Request $request): Response
    {
        try {
            $body = $request->getBody();
            $data = json_decode($body, true);

            if (!$data) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Invalid request data'
                ]));
            }

            if (empty($data['name']) || empty($data['query'])) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Name and query are required'
                ]));
            }

            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]));
            }

            $searchId = $this->saveSearchToDatabase($userId, $data);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message' => 'Search saved successfully',
                'search_id' => $searchId
            ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Get saved searches
     */
    public function getSavedSearches(Request $request): Response
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]));
            }

            $searches = $this->getUserSavedSearches($userId);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'searches' => $searches
            ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Delete saved search
     */
    public function deleteSavedSearch(Request $request, string $id): Response
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]));
            }

            $success = $this->deleteUserSavedSearch($userId, $id);

            if ($success) {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'message' => 'Search deleted successfully'
                ]));
            } else {
                return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Search not found'
                ]));
            }

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Clear search history
     */
    public function clearSearchHistory(Request $request): Response
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]));
            }

            $this->clearUserSearchHistory($userId);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message' => 'Search history cleared successfully'
            ]));

        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Perform search across all content types
     */
    protected function performSearch(string $query, string $type, int $page, int $limit): array
    {
        $results = [];
        $offset = ($page - 1) * $limit;

        switch ($type) {
            case 'pages':
                $results = $this->searchPages($query, $offset, $limit);
                break;
            case 'quran':
                $results = $this->searchQuran($query, $offset, $limit);
                break;
            case 'hadith':
                $results = $this->searchHadith($query, $offset, $limit);
                break;
            case 'calendar':
                $results = $this->searchCalendar($query, $offset, $limit);
                break;
            case 'prayer':
                $results = $this->searchPrayer($query, $offset, $limit);
                break;
            default:
                // Search all types
                $results = array_merge(
                    $this->searchPages($query, $offset, $limit),
                    $this->searchQuran($query, $offset, $limit),
                    $this->searchHadith($query, $offset, $limit)
                );
                break;
        }

        return $results;
    }

    /**
     * Search wiki pages
     */
    protected function searchPages(string $query, int $offset, int $limit): array
    {
        try {
            $sql = "SELECT id, title, slug, content, created_at FROM pages 
                    WHERE title LIKE ? OR content LIKE ? 
                    ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $searchTerm = "%{$query}%";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $limit, $offset]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search Quran content
     */
    protected function searchQuran(string $query, int $offset, int $limit): array
    {
        // TODO: Implement Quran search
        return [];
    }

    /**
     * Search Hadith content
     */
    protected function searchHadith(string $query, int $offset, int $limit): array
    {
        // TODO: Implement Hadith search
        return [];
    }

    /**
     * Search calendar events
     */
    protected function searchCalendar(string $query, int $offset, int $limit): array
    {
        // TODO: Implement calendar search
        return [];
    }

    /**
     * Search prayer times
     */
    protected function searchPrayer(string $query, int $offset, int $limit): array
    {
        // TODO: Implement prayer search
        return [];
    }

    /**
     * Get total results count
     */
    protected function getTotalResults(string $query, string $type): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM pages WHERE title LIKE ? OR content LIKE ?";
            $searchTerm = "%{$query}%";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
            $result = $stmt->fetch();
            
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get search statistics
     */
    protected function getSearchStatistics(string $query): array
    {
        return [
            'query' => $query,
            'timestamp' => date('Y-m-d H:i:s'),
            'total_results' => $this->getTotalResults($query, 'all')
        ];
    }

    /**
     * Get search suggestions
     */
    private function getSearchSuggestions(string $query): array
    {
        try {
            $sql = "SELECT DISTINCT title FROM pages 
                    WHERE title LIKE ? AND status = 'published' 
                    ORDER BY title ASC LIMIT 10";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute(["%{$query}%"]);
            
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Save search to database
     */
    private function saveSearchToDatabase(int $userId, array $data): int
    {
        try {
            $sql = "INSERT INTO user_saved_searches (user_id, name, query, created_at) 
                    VALUES (?, ?, ?, NOW())";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId, $data['name'], $data['query']]);
            
            return (int) $this->db->getPdo()->lastInsertId();
        } catch (\Exception $e) {
            throw new \Exception('Failed to save search');
        }
    }

    /**
     * Get user saved searches
     */
    private function getUserSavedSearches(int $userId): array
    {
        try {
            $sql = "SELECT id, name, query, created_at FROM user_saved_searches 
                    WHERE user_id = ? ORDER BY created_at DESC";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Delete user saved search
     */
    private function deleteUserSavedSearch(int $userId, string $id): bool
    {
        try {
            $sql = "DELETE FROM user_saved_searches WHERE id = ? AND user_id = ?";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$id, $userId]);
            
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear user search history
     */
    private function clearUserSearchHistory(int $userId): void
    {
        try {
            $sql = "DELETE FROM user_search_history WHERE user_id = ?";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$userId]);
        } catch (\Exception $e) {
            // Log error but don't throw
        }
    }

    /**
     * Get current user ID from session
     */
    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
}
