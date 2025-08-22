<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension\Controllers;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;

/**
 * Wiki Controller - Main wiki functionality
 * 
 * @package IslamWiki\Extensions\WikiExtension\Controllers
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiController extends Controller
{
    private $renderer;
    private $wikiPageModel;
    private $wikiCategoryModel;

    public function __construct(\IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($container->get('database'), $container);
        $this->renderer = $this->getView();
        
        // Initialize models - use available services or create defaults
        try {
            $this->wikiPageModel = $container->get('wiki.page.model');
        } catch (\Exception $e) {
            $this->wikiPageModel = null;
        }
        
        try {
            $this->wikiCategoryModel = $container->get('wiki.category.model');
        } catch (\Exception $e) {
            $this->wikiCategoryModel = null;
        }
    }

    /**
     * Display wiki homepage
     */
    public function index(Request $request): Response
    {
        try {
            // Get featured pages
            $featuredPages = $this->getFeaturedPages();
            
            // Get recent pages
            $recentPages = $this->getRecentPages();
            
            // Get categories with page counts
            $categories = $this->getCategoriesWithCounts();
            
            // Get wiki statistics
            $stats = $this->getWikiStats();

            $data = [
                'title' => 'Islamic Wiki - Discover Islamic Knowledge - IslamWiki',
                'featured_pages' => $featuredPages,
                'recent_pages' => $recentPages,
                'categories' => $categories,
                'stats' => $stats,
                'user' => $this->getCurrentUser(),
                'search_query' => $request->getQueryParam('q', '')
            ];

            $html = $this->renderer->render('extensions/WikiExtension/templates/index.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            // Fallback to simple template if rendering fails
            try {
                $data = [
                    'title' => 'Islamic Wiki - Discover Islamic Knowledge - IslamWiki',
                    'featured_pages' => [],
                    'recent_pages' => [],
                    'categories' => [],
                    'stats' => ['total_pages' => 0, 'total_categories' => 0, 'total_revisions' => 0, 'total_users' => 0],
                    'user' => null,
                    'search_query' => ''
                ];
                
                $html = $this->renderer->render('extensions/WikiExtension/templates/index.twig', $data);
                return new Response(200, ['Content-Type' => 'text/html'], $html);
            } catch (\Exception $e2) {
                // Final fallback - return simple HTML
                $html = '<!DOCTYPE html><html><head><title>Islamic Wiki - IslamWiki</title></head><body><h1>Islamic Wiki</h1><p>Welcome to the Islamic Wiki system. The system is being updated.</p></body></html>';
                return new Response(200, ['Content-Type' => 'text/html'], $html);
            }
        }
    }

    /**
     * Display individual wiki page
     */
    public function show(Request $request, string $slug): Response
    {
        try {
            $page = $this->wikiPageModel->getBySlug($slug);
            
            if (!$page) {
                return $this->renderNotFound('Wiki page not found');
            }

            // Get page metadata
            $metadata = $this->getPageMetadata($page);
            
            // Get related pages
            $relatedPages = $this->getRelatedPages($page);
            
            // Get page categories
            $pageCategories = $this->getPageCategories($page['id']);

            $data = [
                'title' => $page['title'] . ' - Wiki - IslamWiki',
                'page' => $page,
                'metadata' => $metadata,
                'related_pages' => $relatedPages,
                'categories' => $pageCategories,
                'user' => $this->getCurrentUser(),
                'can_edit' => $this->canEditPage($page)
            ];

            $html = $this->renderer->render('wiki/show.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading wiki page');
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
     * Update wiki page
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
     * Display category browsing
     */
    public function category(Request $request, string $category): Response
    {
        try {
            $categoryInfo = $this->wikiCategoryModel->getByName($category);
            
            if (!$categoryInfo) {
                return $this->renderNotFound('Category not found');
            }

            // Get pages in category
            $pages = $this->getPagesInCategory($category);
            
            // Get subcategories
            $subcategories = $this->getSubcategories($category);

            $data = [
                'title' => 'Category: ' . $categoryInfo['name'] . ' - Wiki - IslamWiki',
                'category' => $categoryInfo,
                'pages' => $pages,
                'subcategories' => $subcategories,
                'user' => $this->getCurrentUser()
            ];

            $html = $this->renderer->render('wiki/category.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading category');
        }
    }

    /**
     * Display search results
     */
    public function search(Request $request): Response
    {
        try {
            $query = $request->getQueryParam('q', '');
            $category = $request->getQueryParam('category', '');
            $page = (int)($request->getQueryParam('page', 1));

            if (empty($query)) {
                // Show search form
                $data = [
                    'title' => 'Search Wiki - IslamWiki',
                    'query' => '',
                    'results' => [],
                    'categories' => $this->getCategories(),
                    'user' => $this->getCurrentUser()
                ];
            } else {
                // Perform search
                $results = $this->performSearch($query, $category, $page);
                
                $data = [
                    'title' => 'Search Results for "' . htmlspecialchars($query) . '" - Wiki - IslamWiki',
                    'query' => $query,
                    'category' => $category,
                    'results' => $results['pages'],
                    'total_results' => $results['total'],
                    'current_page' => $page,
                    'categories' => $this->getCategories(),
                    'user' => $this->getCurrentUser()
                ];
            }

            $html = $this->renderer->render('wiki/search.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error performing search');
        }
    }

    /**
     * Display page history
     */
    public function history(Request $request, string $slug = null): Response
    {
        try {
            if ($slug) {
                // Page-specific history
                $page = $this->wikiPageModel->getBySlug($slug);
                if (!$page) {
                    return $this->renderNotFound('Wiki page not found');
                }
                
                $revisions = $this->getPageRevisions($page['id']);
                $data = [
                    'title' => 'History: ' . $page['title'] . ' - Wiki - IslamWiki',
                    'page' => $page,
                    'revisions' => $revisions,
                    'user' => $this->getCurrentUser()
                ];
            } else {
                // Global history
                $recentChanges = $this->getRecentChanges();
                $data = [
                    'title' => 'Recent Changes - Wiki - IslamWiki',
                    'recent_changes' => $recentChanges,
                    'user' => $this->getCurrentUser()
                ];
            }

            $html = $this->renderer->render('wiki/history.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading history');
        }
    }

    /**
     * Create new wiki page
     */
    public function create(Request $request): Response
    {
        try {
            // Check create permissions
            if (!$this->canCreatePage()) {
                return $this->renderForbidden('You do not have permission to create pages');
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

            $html = $this->renderer->render('wiki/create.twig', $data);
            return new Response(200, ['Content-Type' => 'text/html'], $html);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error loading create form');
        }
    }

    /**
     * Store new wiki page
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

            // Create page
            $pageId = $this->wikiPageModel->create($data);
            
            if ($pageId) {
                // Redirect to new page
                $slug = $data['slug'] ?? $this->generateSlug($data['title']);
                return $this->redirect('/wiki/' . $slug . '?created=1');
            } else {
                return $this->renderError('Failed to create page');
            }
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error creating wiki page');
        }
    }

    // Helper methods
    private function getFeaturedPages(): array
    {
        // Mock data for now - replace with actual database query
        return [
            [
                'title' => 'Prophet Muhammad ﷺ',
                'slug' => 'prophet-muhammad',
                'excerpt' => 'Learn about the life, teachings, and legacy of the final messenger of Allah.',
                'last_updated' => '2025-01-20',
                'views' => 1234
            ],
            [
                'title' => 'Five Pillars of Islam',
                'slug' => 'five-pillars',
                'excerpt' => 'The fundamental acts of worship that form the foundation of Islamic practice.',
                'last_updated' => '2025-01-19',
                'views' => 987
            ]
        ];
    }

    private function getRecentPages(): array
    {
        // Mock data for now - replace with actual database query
        return [
            [
                'title' => 'Salah Times',
                'slug' => 'salah-times',
                'excerpt' => 'Understanding prayer times and their calculation methods.',
                'created' => '2025-01-20',
                'author' => 'Admin'
            ]
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

    private function getWikiStats(): array
    {
        // Mock data for now - replace with actual database query
        return [
            'total_pages' => 150,
            'total_categories' => 25,
            'total_users' => 45,
            'recent_edits' => 23
        ];
    }

    private function getPageMetadata(array $page): array
    {
        return [
            'created' => $page['created_at'] ?? 'Unknown',
            'updated' => $page['updated_at'] ?? 'Unknown',
            'author' => $page['author'] ?? 'Unknown',
            'views' => $page['views'] ?? 0,
            'revisions' => $page['revision_count'] ?? 1
        ];
    }

    private function getRelatedPages(array $page): array
    {
        // Mock data for now - replace with actual database query
        return [];
    }

    private function getPageCategories(int $pageId): array
    {
        // Mock data for now - replace with actual database query
        return [];
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
        
        return false;
    }

    private function canCreatePage(): bool
    {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], ['admin', 'editor', 'contributor']);
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
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
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

    private function getPagesInCategory(string $category): array
    {
        // Mock data for now - replace with actual database query
        return [];
    }

    private function getSubcategories(string $category): array
    {
        // Mock data for now - replace with actual database query
        return [];
    }

    private function performSearch(string $query, string $category, int $page): array
    {
        // Mock data for now - replace with actual database query
        return [
            'pages' => [],
            'total' => 0
        ];
    }

    private function getPageRevisions(int $pageId): array
    {
        // Mock data for now - replace with actual database query
        return [];
    }

    private function getRecentChanges(): array
    {
        // Mock data for now - replace with actual database query
        return [];
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

    /**
     * Get categories with page counts
     */
    private function getCategoriesWithCounts(): array
    {
        try {
            $sql = "SELECT 
                        c.id, c.name, c.slug, c.description,
                        COUNT(pc.page_id) as page_count
                    FROM wiki_categories c
                    LEFT JOIN wiki_page_categories pc ON c.id = pc.category_id
                    WHERE c.is_active = 1
                    GROUP BY c.id, c.name, c.slug, c.description
                    ORDER BY page_count DESC, c.name ASC
                    LIMIT 20";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error getting categories with counts: ' . $e->getMessage());
            return [];
        }
    }
} 