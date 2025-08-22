<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Controllers;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Page Controller - Individual page management
 * 
 * @package IslamWiki\Extensions\WikiExtension\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class PageController extends Controller
{
    private $wikiPageModel;
    private $wikiCategoryModel;
    private $renderer;

    public function __construct(\IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($container->get('database'), $container);
        
        // Initialize models
        $this->wikiPageModel = $container->get('wiki.page.model');
        $this->wikiCategoryModel = $container->get('wiki.category.model');
        
        // Initialize renderer
        $this->renderer = $this->getView();
    }

    /**
     * Display page creation form
     */
    public function create(Request $request): Response
    {
        try {
            // Check create permissions
            if (!$this->canCreatePage()) {
                return $this->view('errors/error', ['message' => 'You do not have permission to create pages'], 403);
            }

            // Get available categories
            $categories = $this->getCategories();
            
            // Get page templates
            $templates = $this->getPageTemplates();

            $data = [
                'title' => 'Create New Page - Wiki - IslamWiki',
                'categories' => $categories,
                'templates' => $templates,
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/create', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading create form: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store new page
     */
    public function store(Request $request): Response
    {
        try {
            // Check create permissions
            if (!$this->canCreatePage()) {
                return $this->renderForbidden('You do not have permission to create pages');
            }

            $data = $request->getParsedBody();
            
            // Validate input
            $validation = $this->validatePageData($data);
            if (!$validation['valid']) {
                return $this->renderError('Validation failed: ' . implode(', ', $validation['errors']));
            }

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['title']);
            }

            // Set author
            $user = $this->getCurrentUser();
            $data['author_id'] = $user['id'];
            $data['author'] = $user['username'];

            // Create page
            $pageId = $this->wikiPageModel->create($data);
            
            if ($pageId) {
                // Redirect to new page
                return $this->redirect('/wiki/' . $data['slug'] . '?created=1');
            } else {
                return $this->renderError('Failed to create page');
            }
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error creating wiki page');
        }
    }

    /**
     * Display page editing form
     */
    public function edit(Request $request, string $slug): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->renderNotFound('Wiki page not found');
            }

            // Check edit permissions
            if (!$this->canEditPage($page)) {
                return $this->renderForbidden('You do not have permission to edit this page');
            }

            // Get available categories
            $categories = $this->getCategories();
            
            // Get page templates
            $templates = $this->getPageTemplates();

            $data = [
                'title' => 'Edit: ' . $page['title'] . ' - Wiki - IslamWiki',
                'page' => $page,
                'categories' => $categories,
                'templates' => $templates,
                'user' => $this->getCurrentUser()
            ];

            $html = $this->renderer->render('wiki/edit.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading edit form');
        }
    }

    /**
     * Update existing page
     */
    public function update(Request $request, string $slug): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->renderNotFound('Wiki page not found');
            }

            // Check edit permissions
            if (!$this->canEditPage($page)) {
                return $this->renderForbidden('You do not have permission to edit this page');
            }

            $data = $request->getParsedBody();
            
            // Validate input
            $validation = $this->validatePageData($data);
            if (!$validation['valid']) {
                return $this->renderError('Validation failed: ' . implode(', ', $validation['errors']));
            }

            // Set editor
            $user = $this->getCurrentUser();
            $data['editor_id'] = $user['id'];
            $data['editor'] = $user['username'];

            // Update page
            $updated = $this->wikiPageModel->update($page['id'], $data);
            
            if ($updated) {
                // Redirect to updated page
                return $this->redirect('/wiki/' . $slug . '?updated=1');
            } else {
                return $this->renderError('Failed to update page');
            }
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating wiki page');
        }
    }

    /**
     * Delete page
     */
    public function delete(Request $request, string $slug): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->renderNotFound('Wiki page not found');
            }

            // Check delete permissions
            if (!$this->canDeletePage($page)) {
                return $this->renderForbidden('You do not have permission to delete this page');
            }

            // Delete page
            $deleted = $this->wikiPageModel->delete($page['id']);
            
            if ($deleted) {
                // Redirect to wiki homepage
                return $this->redirect('/wiki?deleted=1');
            } else {
                return $this->renderError('Failed to delete page');
            }
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting wiki page');
        }
    }

    /**
     * Lock page (prevent editing)
     */
    public function lock(Request $request, string $slug): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->renderNotFound('Wiki page not found');
            }

            // Check lock permissions
            if (!$this->canLockPage($page)) {
                return $this->renderForbidden('You do not have permission to lock this page');
            }

            // Lock page
            $locked = $this->wikiPageModel->lock($page['id']);
            
            if ($locked) {
                return $this->redirect('/wiki/' . $slug . '?locked=1');
            } else {
                return $this->renderError('Failed to lock page');
            }
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error locking wiki page');
        }
    }

    /**
     * Unlock page (allow editing)
     */
    public function unlock(Request $request, string $slug): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->renderNotFound('Wiki page not found');
            }

            // Check unlock permissions
            if (!$this->canUnlockPage($page)) {
                return $this->renderForbidden('You do not have permission to unlock this page');
            }

            // Unlock page
            $unlocked = $this->wikiPageModel->unlock($page['id']);
            
            if ($unlocked) {
                return $this->redirect('/wiki/' . $slug . '?unlocked=1');
            } else {
                return $this->renderError('Failed to unlock page');
            }
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error unlocking wiki page');
        }
    }

    /**
     * Show page revision
     */
    public function showRevision(Request $request, string $slug, int $revisionId): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->renderNotFound('Wiki page not found');
            }

            $revision = $this->wikiPageModel->getRevision($page['id'], $revisionId);
            
            if (!$revision) {
                return $this->renderNotFound('Revision not found');
            }

            $data = [
                'title' => 'Revision ' . $revisionId . ': ' . $page['title'] . ' - Wiki - IslamWiki',
                'page' => $page,
                'revision' => $revision,
                'user' => $this->getCurrentUser()
            ];

            $html = $this->renderer->render('wiki/revision.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading revision');
        }
    }

    /**
     * Revert page to specific revision
     */
    public function revert(Request $request, string $slug, int $revisionId): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->renderNotFound('Wiki page not found');
            }

            // Check revert permissions
            if (!$this->canRevertPage($page)) {
                return $this->renderForbidden('You do not have permission to revert this page');
            }

            // Revert page
            $reverted = $this->wikiPageModel->revert($page['id'], $revisionId);
            
            if ($reverted) {
                return $this->redirect('/wiki/' . $slug . '?reverted=1');
            } else {
                return $this->renderError('Failed to revert page');
            }
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error reverting wiki page');
        }
    }

    // Helper methods
    private function canCreatePage(): bool
    {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], ['admin', 'editor', 'contributor']);
    }

    private function canEditPage(array $page): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Admin can edit all pages
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Author can edit their own pages
        if ($page['author_id'] == $user['id']) {
            return true;
        }
        
        // Editors can edit any page
        if ($user['role'] === 'editor') {
            return true;
        }
        
        return false;
    }

    private function canDeletePage(array $page): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Only admin can delete pages
        return $user['role'] === 'admin';
    }

    private function canLockPage(array $page): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Admin and editors can lock pages
        return in_array($user['role'], ['admin', 'editor']);
    }

    private function canUnlockPage(array $page): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Admin and editors can unlock pages
        return in_array($user['role'], ['admin', 'editor']);
    }

    private function canRevertPage(array $page): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Admin and editors can revert pages
        return in_array($user['role'], ['admin', 'editor']);
    }

    private function validatePageData(array $data): array
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        if (empty($data['content'])) {
            $errors[] = 'Content is required';
        }
        
        if (strlen($data['title']) > 255) {
            $errors[] = 'Title must be less than 255 characters';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function getCategories(): array
    {
        // Mock data for now - replace with actual database query
        return [
            'islamic-history',
            'quran-studies',
            'hadith-sciences',
            'islamic-law',
            'islamic-philosophy',
            'sufism',
            'islamic-art',
            'islamic-architecture'
        ];
    }

    private function getPageTemplates(): array
    {
        return [
            'standard' => 'Standard Page',
            'article' => 'Article',
            'guide' => 'Guide',
            'reference' => 'Reference'
        ];
    }

    private function generateSlug(string $title): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }

    private function getCurrentUser(): ?array
    {
        // Mock data for now - replace with actual user system
        return [
            'id' => 1,
            'username' => 'admin',
            'role' => 'admin'
        ];
    }
} 