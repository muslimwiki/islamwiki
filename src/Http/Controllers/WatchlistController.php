<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use Psr\Log\LoggerInterface;

/**
 * Watchlist Controller
 * 
 * Handles MediaWiki-style watchlist functionality for tracking
 * page changes and user notifications.
 */
class WatchlistController extends BaseController
{
    /**
     * Show user's watchlist
     */
    public function index(Request $request): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->redirect('/login?redirect=' . urlencode('/watchlist'), 302);
            }
            
            $user = $this->getCurrentUser();
            $page = (int) ($request->getQueryParam('page', 1));
            $perPage = (int) ($request->getQueryParam('per_page', 25));
            
            $watchlist = $this->getUserWatchlist($user['id'], $page, $perPage);
            
            return $this->view('watchlist/index', [
                'title' => 'My Watchlist - IslamWiki',
                'user' => $user,
                'watchlist' => $watchlist['items'],
                'pagination' => $watchlist['pagination']
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show watchlist', [
                    'error' => $e->getMessage(),
                    'user_id' => $this->getCurrentUser()['id'] ?? null
                ]);
            }
            throw new HttpException(500, 'Failed to load watchlist');
        }
    }
    
    /**
     * Add page to watchlist
     */
    public function watch(Request $request, string $pageSlug): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $user = $this->getCurrentUser();
            $page = $this->getPage($pageSlug);
            
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            
            // Check if already watching
            if ($this->isWatching($user['id'], $page['id'])) {
                if ($request->getHeaderLine('Accept') === 'application/json') {
                    return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                        'success' => true,
                        'message' => 'Already watching this page',
                        'watching' => true
                    ]));
                }
                return $this->redirect("/wiki/{$pageSlug}", 302);
            }
            
            // Add to watchlist
            $this->addToWatchlist($user['id'], $page['id']);
            
            if ($request->getHeaderLine('Accept') === 'application/json') {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'message' => 'Page added to watchlist',
                    'watching' => true
                ]));
            }
            
            return $this->redirect("/wiki/{$pageSlug}", 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to add page to watchlist', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug,
                    'user_id' => $this->getCurrentUser()['id'] ?? null
                ]);
            }
            throw new HttpException(500, 'Failed to add page to watchlist');
        }
    }
    
    /**
     * Remove page from watchlist
     */
    public function unwatch(Request $request, string $pageSlug): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $user = $this->getCurrentUser();
            $page = $this->getPage($pageSlug);
            
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            
            // Check if watching
            if (!$this->isWatching($user['id'], $page['id'])) {
                if ($request->getHeaderLine('Accept') === 'application/json') {
                    return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                        'success' => true,
                        'message' => 'Not watching this page',
                        'watching' => false
                    ]));
                }
                return $this->redirect("/wiki/{$pageSlug}", 302);
            }
            
            // Remove from watchlist
            $this->removeFromWatchlist($user['id'], $page['id']);
            
            if ($request->getHeaderLine('Accept') === 'application/json') {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'message' => 'Page removed from watchlist',
                    'watching' => false
                ]));
            }
            
            return $this->redirect("/wiki/{$pageSlug}", 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to remove page from watchlist', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug,
                    'user_id' => $this->getCurrentUser()['id'] ?? null
                ]);
            }
            throw new HttpException(500, 'Failed to remove page from watchlist');
        }
    }
    
    /**
     * Toggle watch status
     */
    public function toggle(Request $request, string $pageSlug): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $user = $this->getCurrentUser();
            $page = $this->getPage($pageSlug);
            
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            
            $isWatching = $this->isWatching($user['id'], $page['id']);
            
            if ($isWatching) {
                $this->removeFromWatchlist($user['id'], $page['id']);
                $message = 'Page removed from watchlist';
                $watching = false;
            } else {
                $this->addToWatchlist($user['id'], $page['id']);
                $message = 'Page added to watchlist';
                $watching = true;
            }
            
            if ($request->getHeaderLine('Accept') === 'application/json') {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'message' => $message,
                    'watching' => $watching
                ]));
            }
            
            return $this->redirect("/wiki/{$pageSlug}", 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to toggle watch status', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug,
                    'user_id' => $this->getCurrentUser()['id'] ?? null
                ]);
            }
            throw new HttpException(500, 'Failed to toggle watch status');
        }
    }
    
    /**
     * Show watchlist preferences
     */
    public function preferences(Request $request): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->redirect('/login?redirect=' . urlencode('/watchlist/preferences'), 302);
            }
            
            $user = $this->getCurrentUser();
            
            if ($request->getMethod() === 'POST') {
                return $this->updatePreferences($request);
            }
            
            $preferences = $this->getWatchlistPreferences($user['id']);
            
            return $this->view('watchlist/preferences', [
                'title' => 'Watchlist Preferences - IslamWiki',
                'user' => $user,
                'preferences' => $preferences
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show watchlist preferences', [
                    'error' => $e->getMessage(),
                    'user_id' => $this->getCurrentUser()['id'] ?? null
                ]);
            }
            throw new HttpException(500, 'Failed to load watchlist preferences');
        }
    }
    
    /**
     * Update watchlist preferences
     */
    private function updatePreferences(Request $request): Response
    {
        try {
            $user = $this->getCurrentUser();
            $data = $request->getParsedBody();
            
            $preferences = [
                'email_notifications' => isset($data['email_notifications']),
                'browser_notifications' => isset($data['browser_notifications']),
                'digest_frequency' => $data['digest_frequency'] ?? 'daily',
                'include_minor_edits' => isset($data['include_minor_edits']),
                'include_my_edits' => isset($data['include_my_edits'])
            ];
            
            $this->updateWatchlistPreferences($user['id'], $preferences);
            
            return $this->redirect('/watchlist/preferences?updated=1', 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to update watchlist preferences', [
                    'error' => $e->getMessage(),
                    'user_id' => $this->getCurrentUser()['id'] ?? null
                ]);
            }
            throw new HttpException(500, 'Failed to update preferences');
        }
    }
    
    /**
     * Get page by slug
     */
    private function getPage(string $slug): ?array
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT * FROM pages WHERE slug = ? AND deleted_at IS NULL
        ');
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Get user's watchlist
     */
    private function getUserWatchlist(int $userId, int $page = 1, int $perPage = 25): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countStmt = $this->db->getPdo()->prepare('
                SELECT COUNT(*) FROM watchlist WHERE user_id = ?
            ');
            $countStmt->execute([$userId]);
            $total = (int) $countStmt->fetchColumn();
            
            // Get watchlist items
            $stmt = $this->db->getPdo()->prepare('
                SELECT w.*, p.title, p.slug, p.namespace, p.updated_at,
                       u.username as last_editor, pr.comment as last_edit_comment
                FROM watchlist w
                JOIN pages p ON w.page_id = p.id
                LEFT JOIN page_revisions pr ON p.id = pr.page_id
                LEFT JOIN users u ON pr.user_id = u.id
                WHERE w.user_id = ? AND p.deleted_at IS NULL
                AND pr.id = (
                    SELECT MAX(id) FROM page_revisions 
                    WHERE page_id = p.id
                )
                ORDER BY p.updated_at DESC
                LIMIT ? OFFSET ?
            ');
            
            $stmt->execute([$userId, $perPage, $offset]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Enrich items with additional data
            foreach ($items as &$item) {
                $item['url'] = $this->getPageUrl($item['namespace'], $item['slug']);
                $item['time_ago'] = $this->getTimeAgo($item['updated_at']);
                $item['has_unread_changes'] = $this->hasUnreadChanges($userId, $item['page_id']);
            }
            
            return [
                'items' => $items,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user watchlist', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return ['items' => [], 'pagination' => []];
        }
    }
    
    /**
     * Check if user is watching a page
     */
    private function isWatching(int $userId, int $pageId): bool
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT COUNT(*) FROM watchlist WHERE user_id = ? AND page_id = ?
        ');
        $stmt->execute([$userId, $pageId]);
        return (int) $stmt->fetchColumn() > 0;
    }
    
    /**
     * Add page to watchlist
     */
    private function addToWatchlist(int $userId, int $pageId): bool
    {
        $stmt = $this->db->getPdo()->prepare('
            INSERT INTO watchlist (user_id, page_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)
        ');
        return $stmt->execute([$userId, $pageId]);
    }
    
    /**
     * Remove page from watchlist
     */
    private function removeFromWatchlist(int $userId, int $pageId): bool
    {
        $stmt = $this->db->getPdo()->prepare('
            DELETE FROM watchlist WHERE user_id = ? AND page_id = ?
        ');
        return $stmt->execute([$userId, $pageId]);
    }
    
    /**
     * Get watchlist preferences
     */
    private function getWatchlistPreferences(int $userId): array
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT * FROM user_preferences WHERE user_id = ? AND preference_key LIKE "watchlist_%"
        ');
        $stmt->execute([$userId]);
        
        $preferences = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $key = str_replace('watchlist_', '', $row['preference_key']);
            $preferences[$key] = $row['preference_value'];
        }
        
        // Set defaults
        return array_merge([
            'email_notifications' => '1',
            'browser_notifications' => '1',
            'digest_frequency' => 'daily',
            'include_minor_edits' => '0',
            'include_my_edits' => '0'
        ], $preferences);
    }
    
    /**
     * Update watchlist preferences
     */
    private function updateWatchlistPreferences(int $userId, array $preferences): bool
    {
        try {
            $this->db->getPdo()->beginTransaction();
            
            foreach ($preferences as $key => $value) {
                $stmt = $this->db->getPdo()->prepare('
                    INSERT INTO user_preferences (user_id, preference_key, preference_value, updated_at)
                    VALUES (?, ?, ?, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE
                    preference_value = VALUES(preference_value),
                    updated_at = CURRENT_TIMESTAMP
                ');
                
                $stmt->execute([$userId, "watchlist_{$key}", $value]);
            }
            
            $this->db->getPdo()->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->getPdo()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Get page URL
     */
    private function getPageUrl(string $namespace, string $slug): string
    {
        if ($namespace === 'wiki' || $namespace === 'Main' || $namespace === '') {
            return "/wiki/{$slug}";
        }
        
        return "/{$namespace}:{$slug}";
    }
    
    /**
     * Get time ago string
     */
    private function getTimeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "{$minutes} minute" . ($minutes > 1 ? 's' : '') . " ago";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "{$hours} hour" . ($hours > 1 ? 's' : '') . " ago";
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return "{$days} day" . ($days > 1 ? 's' : '') . " ago";
        } else {
            $months = floor($diff / 2592000);
            return "{$months} month" . ($months > 1 ? 's' : '') . " ago";
        }
    }
    
    /**
     * Check if page has unread changes
     */
    private function hasUnreadChanges(int $userId, int $pageId): bool
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT w.last_read_at, p.updated_at
            FROM watchlist w
            JOIN pages p ON w.page_id = p.id
            WHERE w.user_id = ? AND w.page_id = ?
        ');
        $stmt->execute([$userId, $pageId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            return false;
        }
        
        $lastRead = $row['last_read_at'] ? strtotime($row['last_read_at']) : 0;
        $lastUpdated = strtotime($row['updated_at']);
        
        return $lastUpdated > $lastRead;
    }
} 