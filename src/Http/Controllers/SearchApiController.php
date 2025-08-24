<?php

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

class SearchApiController
{
    private Connection $db;
    private ?LoggerInterface $logger;

    public function __construct(Connection $db, ?LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Get live search suggestions
     */
    public function getSuggestions(Request $request): Response
    {
        try {
                    $query = $request->getQueryParams()['q'] ?? $_GET['q'] ?? '';
        
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
            if ($this->logger) {
                $this->logger->error('Error getting search suggestions', [
                    'error' => $e->getMessage(),
                    'query' => $query ?? 'unknown'
                ]);
            }

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

            // Validate required fields
            if (empty($data['name']) || empty($data['query'])) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Name and query are required'
                ]));
            }

            // Get user ID from session (you'll need to implement authentication)
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]));
            }

            $searchId = $this->saveSearchToDatabase($userId, $data);

            if ($this->logger) {
                $this->logger->info('Search saved successfully', [
                    'user_id' => $userId,
                    'search_name' => $data['name'],
                    'search_id' => $searchId
                ]);
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'search_id' => $searchId,
                'message' => 'Search saved successfully'
            ]));

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Error saving search', [
                    'error' => $e->getMessage(),
                    'data' => $data ?? 'unknown'
                ]);
            }

            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Get user's saved searches
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

            $searches = $this->getSavedSearchesFromDatabase($userId);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'searches' => $searches
            ]));

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Error getting saved searches', [
                    'error' => $e->getMessage(),
                    'user_id' => $userId ?? 'unknown'
                ]);
            }

            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Delete saved search
     */
    public function deleteSavedSearch(Request $request, string $searchId): Response
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return new Response(401, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Authentication required'
                ]));
            }

            $deleted = $this->deleteSavedSearchFromDatabase($userId, $searchId);

            if (!$deleted) {
                return new Response(404, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => 'Search not found or access denied'
                ]));
            }

            if ($this->logger) {
                $this->logger->info('Saved search deleted', [
                    'user_id' => $userId,
                    'search_id' => $searchId
                ]);
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message' => 'Search deleted successfully'
            ]));

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Error deleting saved search', [
                    'error' => $e->getMessage(),
                    'search_id' => $searchId,
                    'user_id' => $userId ?? 'unknown'
                ]);
            }

            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Clear user's search history
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

            $this->clearSearchHistoryFromDatabase($userId);

            if ($this->logger) {
                $this->logger->info('Search history cleared', [
                    'user_id' => $userId
                ]);
            }

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'success' => true,
                'message' => 'Search history cleared successfully'
            ]));

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Error clearing search history', [
                    'error' => $e->getMessage(),
                    'user_id' => $userId ?? 'unknown'
                ]);
            }

            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Get search suggestions from database
     */
    private function getSearchSuggestions(string $query): array
    {
        $suggestions = [];
        
        // Search existing suggestions table
        $existingSuggestions = $this->searchExistingSuggestions($query);
        $suggestions = array_merge($suggestions, $existingSuggestions);
        
        // Search in wiki pages
        $wikiSuggestions = $this->searchWikiPages($query);
        $suggestions = array_merge($suggestions, $wikiSuggestions);
        
        // Search in categories
        $categorySuggestions = $this->searchCategories($query);
        $suggestions = array_merge($suggestions, $categorySuggestions);
        
        // Search in users
        $userSuggestions = $this->searchUsers($query);
        $suggestions = array_merge($suggestions, $userSuggestions);
        
        // Sort by relevance and limit results
        usort($suggestions, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });
        
        return array_slice($suggestions, 0, 10);
    }

    /**
     * Search existing suggestions table
     */
    private function searchExistingSuggestions(string $query): array
    {
        $sql = "SELECT suggestion_text as query, suggestion_type as type, click_count as count,
                       (relevance_score * 100) as relevance
                FROM search_suggestions 
                WHERE suggestion_text LIKE ? OR query LIKE ?
                ORDER BY relevance_score DESC, click_count DESC
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = [
                'query' => $row['query'],
                'type' => $row['type'],
                'count' => $row['count'],
                'relevance' => (int) $row['relevance']
            ];
        }
        
        return $results;
    }

    /**
     * Search wiki pages for suggestions
     */
    private function searchWikiPages(string $query): array
    {
        $sql = "SELECT title, slug, 'wiki' as type, 
                       CASE 
                           WHEN title LIKE ? THEN 100
                           WHEN title LIKE ? THEN 80
                           WHEN title LIKE ? THEN 60
                           ELSE 40
                       END as relevance
                FROM pages 
                WHERE title LIKE ? OR content LIKE ?
                ORDER BY relevance DESC, title ASC
                LIMIT 5";
        
        $searchTerm = $query;
        $searchTermStart = $query . '%';
        $searchTermContains = '%' . $query . '%';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTermStart, $searchTermContains, $searchTermContains, $searchTermContains, $searchTermContains]);
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = [
                'query' => $row['title'],
                'type' => $row['type'],
                'count' => 1, // This would be actual result count
                'relevance' => $row['relevance']
            ];
        }
        
        return $results;
    }

    /**
     * Search categories for suggestions
     */
    private function searchCategories(string $query): array
    {
        $sql = "SELECT c.name, 'category' as type, COUNT(pc.page_id) as count
                FROM categories c
                LEFT JOIN page_categories pc ON c.id = pc.category_id
                WHERE c.name LIKE ? OR c.description LIKE ?
                GROUP BY c.id, c.name
                ORDER BY count DESC, c.name ASC
                LIMIT 3";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = [
                'query' => $row['name'],
                'type' => $row['type'],
                'count' => $row['count'],
                'relevance' => 70
            ];
        }
        
        return $results;
    }

    /**
     * Search users for suggestions
     */
    private function searchUsers(string $query): array
    {
        $sql = "SELECT username, 'user' as type
                FROM users 
                WHERE username LIKE ? OR display_name LIKE ?
                ORDER BY username ASC
                LIMIT 2";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = [
                'query' => $row['username'],
                'type' => $row['type'],
                'count' => 1,
                'relevance' => 50
            ];
        }
        
        return $results;
    }

    /**
     * Save search to database
     */
    private function saveSearchToDatabase(int $userId, array $data): int
    {
        $sql = "INSERT INTO saved_searches (user_id, name, description, tags, query_string, is_public, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $data['name'],
            $data['description'] ?? '',
            $data['tags'] ?? '',
            $data['query'],
            $data['public'] ? 1 : 0
        ]);
        
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get saved searches from database
     */
    private function getSavedSearchesFromDatabase(int $userId): array
    {
        $sql = "SELECT id, name, description, tags, query_string, is_public, created_at
                FROM saved_searches 
                WHERE user_id = ? OR is_public = 1
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'tags' => $row['tags'],
                'query' => $row['query_string'],
                'public' => (bool) $row['is_public'],
                'savedAt' => $row['created_at']
            ];
        }
        
        return $results;
    }

    /**
     * Delete saved search from database
     */
    private function deleteSavedSearchFromDatabase(int $userId, string $searchId): bool
    {
        $sql = "DELETE FROM saved_searches 
                WHERE id = ? AND (user_id = ? OR is_public = 1)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchId, $userId]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Clear search history from database
     */
    private function clearSearchHistoryFromDatabase(int $userId): void
    {
        $sql = "DELETE FROM search_history WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
    }

    /**
     * Get current user ID from session
     * This is a placeholder - implement based on your authentication system
     */
    private function getCurrentUserId(): ?int
    {
        // TODO: Implement based on your authentication system
        // For now, return a mock user ID
        return 1;
    }
} 