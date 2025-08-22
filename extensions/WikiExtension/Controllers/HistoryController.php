<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Controllers;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * History Controller - Wiki page history and revision management
 * 
 * @package IslamWiki\Extensions\WikiExtension\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class HistoryController extends Controller
{
    private $wikiPageModel;
    private $wikiRevisionModel;

    public function __construct(\IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($container->get('database'), $container);
        
        // Initialize models
        $this->wikiPageModel = $container->get('wiki.page.model');
        $this->wikiRevisionModel = $container->get('wiki.revision.model');
    }

    /**
     * Display page history
     */
    public function index(Request $request, string $slug): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->view('errors/404', ['message' => 'Page not found'], 404);
            }

            $revisions = $this->wikiRevisionModel->getPageRevisions($page['id']);
            
            $data = [
                'title' => 'History: ' . $page['title'] . ' - Wiki - IslamWiki',
                'page' => $page,
                'revisions' => $revisions,
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/history', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading page history: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display specific revision
     */
    public function show(Request $request, string $slug, int $revisionId): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->view('errors/404', ['message' => 'Page not found'], 404);
            }

            $revision = $this->wikiRevisionModel->getById($revisionId);
            
            if (!$revision || $revision['page_id'] != $page['id']) {
                return $this->view('errors/404', ['message' => 'Revision not found'], 404);
            }

            $data = [
                'title' => 'Revision ' . $revisionId . ': ' . $page['title'] . ' - Wiki - IslamWiki',
                'page' => $page,
                'revision' => $revision,
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/revision', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading revision: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Compare two revisions
     */
    public function compare(Request $request, string $slug, int $revision1, int $revision2): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->view('errors/404', ['message' => 'Page not found'], 404);
            }

            $rev1 = $this->wikiRevisionModel->getById($revision1);
            $rev2 = $this->wikiRevisionModel->getById($revision2);
            
            if (!$rev1 || !$rev2 || $rev1['page_id'] != $page['id'] || $rev2['page_id'] != $page['id']) {
                return $this->view('errors/404', ['message' => 'One or both revisions not found'], 404);
            }

            // Generate diff
            $diff = $this->generateDiff($rev1['content'], $rev2['content']);
            
            $data = [
                'title' => 'Compare Revisions: ' . $page['title'] . ' - Wiki - IslamWiki',
                'page' => $page,
                'revision1' => $rev1,
                'revision2' => $rev2,
                'diff' => $diff,
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/compare', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error comparing revisions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Revert to specific revision
     */
    public function revert(Request $request, string $slug, int $revisionId): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->view('errors/404', ['message' => 'Page not found'], 404);
            }

            // Check permissions
            if (!$this->canEditPage($page)) {
                return $this->view('errors/error', ['message' => 'You do not have permission to revert this page'], 403);
            }

            $revision = $this->wikiRevisionModel->getById($revisionId);
            
            if (!$revision || $revision['page_id'] != $page['id']) {
                return $this->view('errors/404', ['message' => 'Revision not found'], 404);
            }

            // Create new revision with old content
            $newRevisionData = [
                'page_id' => $page['id'],
                'content' => $revision['content'],
                'title' => $revision['title'],
                'meta_description' => $revision['meta_description'],
                'category_id' => $revision['category_id'],
                'tags' => $revision['tags'],
                'user_id' => $this->getCurrentUser()['id'],
                'comment' => 'Reverted to revision ' . $revisionId,
                'revision_type' => 'revert'
            ];

            $newRevisionId = $this->wikiRevisionModel->create($newRevisionData);
            
            if ($newRevisionId) {
                // Update page with reverted content
                $pageData = [
                    'content' => $revision['content'],
                    'title' => $revision['title'],
                    'meta_description' => $revision['meta_description'],
                    'category_id' => $revision['category_id'],
                    'tags' => $revision['tags'],
                    'current_revision_id' => $newRevisionId,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->wikiPageModel->update($page['id'], $pageData);
                
                return $this->redirect('/wiki/' . $slug . '?reverted=1');
            } else {
                return $this->view('errors/error', ['message' => 'Failed to revert page'], 500);
            }
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error reverting page: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete revision (admin only)
     */
    public function deleteRevision(Request $request, string $slug, int $revisionId): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->view('errors/404', ['message' => 'Page not found'], 404);
            }

            // Check admin permissions
            if (!$this->canDeleteRevision()) {
                return $this->view('errors/error', ['message' => 'You do not have permission to delete revisions'], 403);
            }

            $revision = $this->wikiRevisionModel->getById($revisionId);
            
            if (!$revision || $revision['page_id'] != $page['id']) {
                return $this->view('errors/404', ['message' => 'Revision not found'], 404);
            }

            // Check if this is the current revision
            if ($page['current_revision_id'] == $revisionId) {
                return $this->view('errors/error', ['message' => 'Cannot delete the current revision'], 400);
            }

            // Delete revision
            $deleted = $this->wikiRevisionModel->delete($revisionId);
            
            if ($deleted) {
                return $this->redirect('/wiki/' . $slug . '/history?deleted=1');
            } else {
                return $this->view('errors/error', ['message' => 'Failed to delete revision'], 500);
            }
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error deleting revision: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get revision statistics
     */
    public function stats(Request $request, string $slug = null): Response
    {
        try {
            if ($slug) {
                // Page-specific revision stats
                $page = $this->wikiPageModel->getBySlug($slug);
                if (!$page) {
                    return $this->view('errors/404', ['message' => 'Page not found'], 404);
                }
                
                $stats = $this->wikiRevisionModel->getPageRevisionStats($page['id']);
                $data = [
                    'title' => 'Revision Statistics: ' . $page['title'] . ' - Wiki - IslamWiki',
                    'page' => $page,
                    'stats' => $stats,
                    'user' => $this->getCurrentUser()
                ];
            } else {
                // Global revision stats
                $stats = $this->wikiRevisionModel->getGlobalRevisionStats();
                $data = [
                    'title' => 'Revision Statistics - Wiki - IslamWiki',
                    'stats' => $stats,
                    'user' => $this->getCurrentUser()
                ];
            }

            return $this->view('wiki/revision-stats', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading revision statistics: ' . $e->getMessage()], 500);
        }
    }

    // Helper methods
    private function canEditPage(array $page): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        // Check if page is locked
        if ($page['is_locked']) {
            return in_array($user['role'], ['admin', 'moderator']);
        }

        // Check user permissions
        return in_array($user['role'], ['admin', 'editor', 'moderator']);
    }

    private function canDeleteRevision(): bool
    {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], ['admin']);
    }

    private function generateDiff(string $oldContent, string $newContent): array
    {
        // Simple diff implementation - replace with more sophisticated diff library
        $oldLines = explode("\n", $oldContent);
        $newLines = explode("\n", $newContent);
        
        $diff = [];
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';
            
            if ($oldLine !== $newLine) {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $oldLine,
                    'new' => $newLine,
                    'type' => 'changed'
                ];
            } else {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $oldLine,
                    'new' => $newLine,
                    'type' => 'unchanged'
                ];
            }
        }
        
        return $diff;
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