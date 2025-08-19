<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use Psr\Log\LoggerInterface;

/**
 * Discussion Controller
 * 
 * Handles MediaWiki-style discussion pages and talk functionality.
 * Provides threaded discussions, signatures, and moderation features.
 */
class DiscussionController extends BaseController
{
    /**
     * Show discussion page for a wiki page
     */
    public function show(Request $request, string $pageSlug): Response
    {
        try {
            // Get the main page
            $page = $this->getPage($pageSlug);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            
            // Get discussion thread
            $discussion = $this->getDiscussion($page['id']);
            
            // Get replies
            $replies = $this->getReplies($discussion['id'] ?? 0);
            
            return $this->view('discussion/show', [
                'title' => 'Discussion: ' . $page['title'] . ' - IslamWiki',
                'user' => $this->getCurrentUser(),
                'page' => $page,
                'discussion' => $discussion,
                'replies' => $replies
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show discussion page', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug
                ]);
            }
            throw new HttpException(500, 'Failed to load discussion page');
        }
    }
    
    /**
     * Add a new discussion/reply
     */
    public function store(Request $request, string $pageSlug): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $page = $this->getPage($pageSlug);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            
            $data = $request->getParsedBody();
            $user = $this->getCurrentUser();
            
            // Validate input
            if (empty($data['content']) || strlen(trim($data['content'])) < 10) {
                throw new HttpException(400, 'Discussion content must be at least 10 characters');
            }
            
            $discussionData = [
                'page_id' => $page['id'],
                'user_id' => $user['id'],
                'content' => trim($data['content']),
                'parent_id' => $data['parent_id'] ?? null,
                'signature' => $this->generateSignature($user),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $discussionId = $this->createDiscussion($discussionData);
            
            // Redirect back to discussion page
            return $this->redirect("/discussion/{$pageSlug}#discussion-{$discussionId}", 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to create discussion', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug,
                    'user_id' => $this->getCurrentUser()['id'] ?? null
                ]);
            }
            throw new HttpException(500, 'Failed to create discussion');
        }
    }
    
    /**
     * Edit a discussion/reply
     */
    public function edit(Request $request, string $pageSlug, int $discussionId): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $page = $this->getPage($pageSlug);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            
            $discussion = $this->getDiscussionById($discussionId);
            if (!$discussion) {
                throw new HttpException(404, 'Discussion not found');
            }
            
            // Check permissions
            if (!$this->canEditDiscussion($discussion)) {
                throw new HttpException(403, 'Permission denied');
            }
            
            if ($request->getMethod() === 'POST') {
                return $this->update($request, $pageSlug, $discussionId);
            }
            
            return $this->view('discussion/edit', [
                'title' => 'Edit Discussion - ' . $page['title'] . ' - IslamWiki',
                'user' => $this->getCurrentUser(),
                'page' => $page,
                'discussion' => $discussion
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show discussion edit form', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug,
                    'discussion_id' => $discussionId
                ]);
            }
            throw new HttpException(500, 'Failed to load edit form');
        }
    }
    
    /**
     * Update a discussion/reply
     */
    public function update(Request $request, string $pageSlug, int $discussionId): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $discussion = $this->getDiscussionById($discussionId);
            if (!$discussion) {
                throw new HttpException(404, 'Discussion not found');
            }
            
            // Check permissions
            if (!$this->canEditDiscussion($discussion)) {
                throw new HttpException(403, 'Permission denied');
            }
            
            $data = $request->getParsedBody();
            
            // Validate input
            if (empty($data['content']) || strlen(trim($data['content'])) < 10) {
                throw new HttpException(400, 'Discussion content must be at least 10 characters');
            }
            
            $updateData = [
                'content' => trim($data['content']),
                'edited_at' => date('Y-m-d H:i:s'),
                'edit_count' => $discussion['edit_count'] + 1
            ];
            
            $this->updateDiscussion($discussionId, $updateData);
            
            // Redirect back to discussion page
            return $this->redirect("/discussion/{$pageSlug}#discussion-{$discussionId}", 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to update discussion', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug,
                    'discussion_id' => $discussionId
                ]);
            }
            throw new HttpException(500, 'Failed to update discussion');
        }
    }
    
    /**
     * Delete a discussion/reply
     */
    public function destroy(Request $request, string $pageSlug, int $discussionId): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $discussion = $this->getDiscussionById($discussionId);
            if (!$discussion) {
                throw new HttpException(404, 'Discussion not found');
            }
            
            // Check permissions
            if (!$this->canDeleteDiscussion($discussion)) {
                throw new HttpException(403, 'Permission denied');
            }
            
            $this->deleteDiscussion($discussionId);
            
            if ($request->getHeaderLine('Accept') === 'application/json') {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'message' => 'Discussion deleted successfully'
                ]));
            }
            
            return $this->redirect("/discussion/{$pageSlug}", 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to delete discussion', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug,
                    'discussion_id' => $discussionId
                ]);
            }
            throw new HttpException(500, 'Failed to delete discussion');
        }
    }
    
    /**
     * Show discussion history
     */
    public function history(Request $request, string $pageSlug, int $discussionId): Response
    {
        try {
            $page = $this->getPage($pageSlug);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            
            $discussion = $this->getDiscussionById($discussionId);
            if (!$discussion) {
                throw new HttpException(404, 'Discussion not found');
            }
            
            $history = $this->getDiscussionHistory($discussionId);
            
            return $this->view('discussion/history', [
                'title' => 'Discussion History - ' . $page['title'] . ' - IslamWiki',
                'user' => $this->getCurrentUser(),
                'page' => $page,
                'discussion' => $discussion,
                'history' => $history
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show discussion history', [
                    'error' => $e->getMessage(),
                    'page_slug' => $pageSlug,
                    'discussion_id' => $discussionId
                ]);
            }
            throw new HttpException(500, 'Failed to load discussion history');
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
     * Get discussion for a page
     */
    private function getDiscussion(int $pageId): ?array
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT d.*, u.username, u.role
            FROM discussions d
            JOIN users u ON d.user_id = u.id
            WHERE d.page_id = ? AND d.parent_id IS NULL AND d.deleted_at IS NULL
            ORDER BY d.created_at ASC
        ');
        $stmt->execute([$pageId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Get replies for a discussion
     */
    private function getReplies(int $discussionId): array
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT d.*, u.username, u.role
            FROM discussions d
            JOIN users u ON d.user_id = u.id
            WHERE d.parent_id = ? AND d.deleted_at IS NULL
            ORDER BY d.created_at ASC
        ');
        $stmt->execute([$discussionId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get discussion by ID
     */
    private function getDiscussionById(int $discussionId): ?array
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT d.*, u.username, u.role
            FROM discussions d
            JOIN users u ON d.user_id = u.id
            WHERE d.id = ? AND d.deleted_at IS NULL
        ');
        $stmt->execute([$discussionId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Get discussion history
     */
    private function getDiscussionHistory(int $discussionId): array
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT dh.*, u.username
            FROM discussion_history dh
            JOIN users u ON dh.user_id = u.id
            WHERE dh.discussion_id = ?
            ORDER BY dh.created_at DESC
        ');
        $stmt->execute([$discussionId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new discussion
     */
    private function createDiscussion(array $data): int
    {
        $stmt = $this->db->getPdo()->prepare('
            INSERT INTO discussions (
                page_id, user_id, content, parent_id, signature, created_at
            ) VALUES (?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $data['page_id'],
            $data['user_id'],
            $data['content'],
            $data['parent_id'],
            $data['signature'],
            $data['created_at']
        ]);
        
        return (int) $this->db->getPdo()->lastInsertId();
    }
    
    /**
     * Update discussion
     */
    private function updateDiscussion(int $discussionId, array $data): bool
    {
        $updateFields = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $updateFields[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $params[] = $discussionId;
        
        $sql = "UPDATE discussions SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $this->db->getPdo()->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    /**
     * Delete discussion (soft delete)
     */
    private function deleteDiscussion(int $discussionId): bool
    {
        $stmt = $this->db->getPdo()->prepare('
            UPDATE discussions SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?
        ');
        return $stmt->execute([$discussionId]);
    }
    
    /**
     * Generate user signature
     */
    private function generateSignature(array $user): string
    {
        $signature = "[[User:{$user['username']}|{$user['username']}]]";
        
        if (isset($user['role']) && in_array($user['role'], ['admin', 'editor', 'scholar'])) {
            $signature .= " ([[User:{$user['username']}/Role|{$user['role']}]])";
        }
        
        $signature .= " " . date('H:i, d M Y');
        
        return $signature;
    }
    
    /**
     * Check if user can edit discussion
     */
    private function canEditDiscussion(array $discussion): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Discussion author can edit
        if ($discussion['user_id'] == $user['id']) {
            return true;
        }
        
        // Admins and editors can edit
        if (in_array($user['role'], ['admin', 'editor'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can delete discussion
     */
    private function canDeleteDiscussion(array $discussion): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Discussion author can delete
        if ($discussion['user_id'] == $user['id']) {
            return true;
        }
        
        // Only admins and moderators can delete others' discussions
        if (in_array($user['role'], ['admin', 'moderator'])) {
            return true;
        }
        
        return false;
    }
} 