<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Controllers;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

/**
 * Category Controller - Wiki category management
 * 
 * @package IslamWiki\Extensions\WikiExtension\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class CategoryController extends Controller
{
    private $wikiCategoryModel;
    private $wikiPageModel;

    public function __construct(\IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($container->get('database'), $container);
        
        // Initialize models
        $this->wikiCategoryModel = $container->get('wiki.category.model');
        $this->wikiPageModel = $container->get('wiki.page.model');
    }

    /**
     * Display category index
     */
    public function index(Request $request): Response
    {
        try {
            $categories = $this->wikiCategoryModel->getAll();
            
            $data = [
                'title' => 'Wiki Categories - IslamWiki',
                'categories' => $categories,
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/categories', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading categories: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display category with its pages
     */
    public function show(Request $request, string $slug): Response
    {
        try {
            $category = $this->wikiCategoryModel->getBySlug($slug);
            
            if (!$category) {
                return $this->view('errors/404', ['message' => 'Category not found'], 404);
            }

            $pages = $this->wikiPageModel->getPagesByCategory($slug);
            $subcategories = $this->wikiCategoryModel->getSubcategories($category['id']);

            $data = [
                'title' => 'Category: ' . $category['name'] . ' - Wiki - IslamWiki',
                'category' => $category,
                'pages' => $pages,
                'subcategories' => $subcategories,
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/category', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create new category
     */
    public function create(Request $request): Response
    {
        try {
            // Check permissions
            if (!$this->canManageCategories()) {
                return $this->view('errors/error', ['message' => 'You do not have permission to create categories'], 403);
            }

            $data = [
                'title' => 'Create New Category - Wiki - IslamWiki',
                'parent_categories' => $this->wikiCategoryModel->getAll(),
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/category-create', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading create form: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store new category
     */
    public function store(Request $request): Response
    {
        try {
            // Check permissions
            if (!$this->canManageCategories()) {
                return $this->view('errors/error', ['message' => 'You do not have permission to create categories'], 403);
            }

            $data = $request->getParsedBody();
            
            // Validate input
            $validation = $this->validateCategoryData($data);
            if (!$validation['valid']) {
                return $this->view('errors/error', ['message' => 'Validation failed: ' . implode(', ', $validation['errors'])], 400);
            }

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['name']);
            }

            // Create category
            $categoryId = $this->wikiCategoryModel->create($data);
            
            if ($categoryId) {
                return $this->redirect('/wiki/category/' . $data['slug'] . '?created=1');
            } else {
                return $this->view('errors/error', ['message' => 'Failed to create category'], 500);
            }
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error creating category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edit category
     */
    public function edit(Request $request, string $slug): Response
    {
        try {
            $category = $this->wikiCategoryModel->getBySlug($slug);
            
            if (!$category) {
                return $this->view('errors/404', ['message' => 'Category not found'], 404);
            }

            // Check permissions
            if (!$this->canManageCategories()) {
                return $this->view('errors/error', ['message' => 'You do not have permission to edit categories'], 403);
            }

            $data = [
                'title' => 'Edit Category: ' . $category['name'] . ' - Wiki - IslamWiki',
                'category' => $category,
                'parent_categories' => $this->wikiCategoryModel->getAll(),
                'user' => $this->getCurrentUser()
            ];

            return $this->view('wiki/category-edit', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading edit form: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update category
     */
    public function update(Request $request, string $slug): Response
    {
        try {
            $category = $this->wikiCategoryModel->getBySlug($slug);
            
            if (!$category) {
                return $this->view('errors/404', ['message' => 'Category not found'], 404);
            }

            // Check permissions
            if (!$this->canManageCategories()) {
                return $this->view('errors/error', ['message' => 'You do not have permission to edit categories'], 403);
            }

            $data = $request->getParsedBody();
            
            // Validate input
            $validation = $this->validateCategoryData($data);
            if (!$validation['valid']) {
                return $this->view('errors/error', ['message' => 'Validation failed: ' . implode(', ', $validation['errors'])], 400);
            }

            // Update category
            $updated = $this->wikiCategoryModel->update($category['id'], $data);
            
            if ($updated) {
                return $this->redirect('/wiki/category/' . $slug . '?updated=1');
            } else {
                return $this->view('errors/error', ['message' => 'Failed to update category'], 500);
            }
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error updating category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete category
     */
    public function delete(Request $request, string $slug): Response
    {
        try {
            $category = $this->wikiCategoryModel->getBySlug($slug);
            
            if (!$category) {
                return $this->view('errors/404', ['message' => 'Category not found'], 404);
            }

            // Check permissions
            if (!$this->canManageCategories()) {
                return $this->view('errors/error', ['message' => 'You do not have permission to delete categories'], 403);
            }

            // Check if category has pages
            $pageCount = $this->wikiPageModel->getPageCountByCategory($category['id']);
            if ($pageCount > 0) {
                return $this->view('errors/error', ['message' => 'Cannot delete category with pages. Move or delete all pages first.'], 400);
            }

            // Delete category
            $deleted = $this->wikiCategoryModel->delete($category['id']);
            
            if ($deleted) {
                return $this->redirect('/wiki/categories?deleted=1');
            } else {
                return $this->view('errors/error', ['message' => 'Failed to delete category'], 500);
            }
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error deleting category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get category statistics
     */
    public function stats(Request $request, string $slug = null): Response
    {
        try {
            if ($slug) {
                // Category-specific stats
                $category = $this->wikiCategoryModel->getBySlug($slug);
                if (!$category) {
                    return $this->view('errors/404', ['message' => 'Category not found'], 404);
                }
                
                $stats = $this->wikiCategoryModel->getCategoryStats($category['id']);
                $data = [
                    'title' => 'Category Statistics: ' . $category['name'] . ' - Wiki - IslamWiki',
                    'category' => $category,
                    'stats' => $stats,
                    'user' => $this->getCurrentUser()
                ];
            } else {
                // Global category stats
                $stats = $this->wikiCategoryModel->getGlobalStats();
                $data = [
                    'title' => 'Category Statistics - Wiki - IslamWiki',
                    'stats' => $stats,
                    'user' => $this->getCurrentUser()
                ];
            }

            return $this->view('wiki/category-stats', $data);
        } catch (\Exception $e) {
            return $this->view('errors/error', ['message' => 'Error loading category statistics: ' . $e->getMessage()], 500);
        }
    }

    // Helper methods
    private function canManageCategories(): bool
    {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], ['admin', 'editor']);
    }

    private function validateCategoryData(array $data): array
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Category name is required';
        }
        
        if (strlen($data['name']) > 100) {
            $errors[] = 'Category name must be less than 100 characters';
        }
        
        if (!empty($data['description']) && strlen($data['description']) > 500) {
            $errors[] = 'Category description must be less than 500 characters';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function generateSlug(string $name): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
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