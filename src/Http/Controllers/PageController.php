<?php
declare(strict_types=1);

/**
 * Page Controller
 * 
 * This controller handles all operations related to wiki pages including:
 * - Viewing pages and their history
 * - Creating, editing, and deleting pages
 * - Managing page revisions and rollbacks
 * - Handling page permissions and locks
 * - Processing wiki text and formatting
 * 
 * @package IslamWiki\Http\Controllers
 */



namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use IslamWiki\Models\Page;
use IslamWiki\Models\Revision;
use IslamWiki\Models\User;
use Psr\Log\LoggerInterface;

class PageController extends Controller
{
    /**
     * @var LoggerInterface Logger instance
     */
    private $logger;
    
    /**
     * Create a new controller instance.
     *
     * @param \IslamWiki\Core\Database\Connection $db Database connection
     * @param \IslamWiki\Core\Container $container The dependency injection container
     */
    public function __construct(
        \IslamWiki\Core\Database\Connection $db,
        \IslamWiki\Core\Container $container
    ) {
        parent::__construct($db, $container);
        $this->logger = $container->get(LoggerInterface::class);
    }
    /**
     * Show the about page.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function about(Request $request): Response
    {
        return $this->render('pages/about.twig', [
            'title' => 'About - IslamWiki',
            'user' => $this->getUser($request)
        ]);
    }

    /**
     * Show a paginated list of all wiki pages.
     *
     * This method handles the following query parameters:
     * - page: Page number for pagination (default: 1)
     * - per_page: Number of items per page (default: 20, max: 100)
     * - namespace: Filter pages by namespace
     * - q: Search query to filter pages by title or content
     * - sort: Field to sort by (title, updated_at, views, default: title)
     * - order: Sort order (asc, desc, default: asc)
     *
     * @param Request $request The HTTP request
     * @return Response
     * 
     * @throws \Exception If there's an error retrieving pages
     */
    public function index(Request $request): Response
    {
        $this->logger->info('Page list requested', [
            'query' => $request->getQueryParams(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
        ]);
        
        try {
            // Get sort parameters
            $sort = in_array(strtolower($request->getQueryParam('sort', 'title')), 
                           ['title', 'updated_at', 'view_count']) 
                   ? strtolower($request->getQueryParam('sort', 'title')) 
                   : 'title';
            
            $order = strtolower($request->getQueryParam('order', 'asc')) === 'desc' ? 'desc' : 'asc';
            
            // Get filter parameters
            $namespace = $request->getQueryParam('namespace');
            $search = $request->getQueryParam('q');
            
            // Build the base query - simplified for now
            $pages = $this->db->table('pages')
                ->select([
                    'id',
                    'title',
                    'slug',
                    'namespace',
                    'updated_at',
                    'view_count',
                    'created_at'
                ])
                ->orderBy($sort, $order)
                ->get();
            
            // Apply filters if provided
            if ($namespace) {
                $pages = $this->db->table('pages')
                    ->select([
                        'id',
                        'title',
                        'slug',
                        'namespace',
                        'updated_at',
                        'view_count',
                        'created_at'
                    ])
                    ->where('namespace', '=', $namespace)
                    ->orderBy($sort, $order)
                    ->get();
            } elseif ($search) {
                $pages = $this->db->table('pages')
                    ->select([
                        'id',
                        'title',
                        'slug',
                        'namespace',
                        'updated_at',
                        'view_count',
                        'created_at'
                    ])
                    ->where('title', 'LIKE', "%{$search}%")
                    ->orderBy($sort, $order)
                    ->get();
            }
            
            // Add revision count and author info (simplified)
            foreach ($pages as &$page) {
                $page['revision_count'] = 1; // Default for now
                $page['author_name'] = 'Unknown'; // Default for now
            }
            
            // Log successful page list retrieval
            $this->logger->info('Page list retrieved successfully', [
                'count' => count($pages),
                'filters' => [
                    'namespace' => $namespace,
                    'search' => $search,
                    'sort' => $sort,
                    'order' => $order
                ]
            ]);

            return $this->view('pages/index', [
                'pages' => $pages,
                'pagination' => [
                    'total' => count($pages),
                    'per_page' => count($pages),
                    'current_page' => 1,
                    'last_page' => 1,
                    'from' => 1,
                    'to' => count($pages),
                ],
                'filters' => [
                    'namespace' => $namespace,
                    'search' => $search,
                    'sort' => $sort,
                    'order' => $order
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve page list', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => $request->getQueryParams(),
            ]);
            
            throw $e; // Re-throw to be handled by the global error handler
        }
    }

    /**
     * Display the specified wiki page.
     *
     * This method handles the display of a wiki page by its slug. It performs the following actions:
     * 1. Attempts to find the page by the exact slug
     * 2. If not found, checks for a page with the same name in the 'Main' namespace
     * 3. If still not found, redirects to the edit page with the title pre-filled
     * 4. Checks page view permissions and page locks
     * 5. Increments the view count
     * 6. Renders the page with appropriate template variables
     *
     * @param Request $request The HTTP request
     * @param string $slug The URL-friendly identifier for the page
     * @return Response
     *
     * @throws HttpException If the page is locked and user doesn't have permission to view it
     * @throws \Exception If an unexpected error occurs while retrieving the page
     */
    public function show(Request $request, string $slug): Response
    {
        $startTime = microtime(true);
        $this->logger->info('Page view requested', [
            'slug' => $slug,
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'referer' => $request->getHeaderLine('Referer'),
            'method' => $request->getMethod(),
            'is_ajax' => $request->isXmlHttpRequest(),
        ]);
        
        try {
            // Try to find the page by the exact slug first
            $page = Page::findBySlug($slug, $this->db);
            
            // If not found, try with 'Main' namespace
            if (!$page) {
                $this->logger->debug('Page not found, trying with Main namespace', ['slug' => $slug]);
                $page = Page::findBySlug("Main:{$slug}", $this->db);
                
                if ($page) {
                    $this->logger->info('Redirecting to main namespace page', [
                        'original_slug' => $slug,
                        'resolved_slug' => $page->getSlug(),
                    ]);
                    return $this->redirect($page->getUrl(), 301); // 301 for permanent redirect
                }
                
                // Check if user has permission to create pages
                $canCreate = $this->canCreatePage($request);
                $this->logger->info('Page not found', [
                    'slug' => $slug,
                    'user_can_create' => $canCreate,
                ]);
                
                if ($canCreate) {
                    return $this->redirect("/edit?title=" . urlencode($slug), 302);
                }
                
                throw new HttpException(404, 'The requested page was not found.');
            }

            // Check if the page is locked and user has permission to view
            if ($page->isLocked()) {
                $user = $this->user($request);
                $isAdmin = $user ? $this->isAdmin($request) : false;
                
                if (!$isAdmin) {
                    $this->logger->warning('Attempt to access locked page', [
                        'page_id' => $page->getAttribute('id'),
                        'slug' => $slug,
                        'user_id' => $user ? $user['id'] : 'guest',
                        'is_authenticated' => $user !== null,
                        'is_admin' => false,
                    ]);
                    
                    return $this->view('errors.403', [
                        'message' => 'This page is currently locked and cannot be viewed.',
                        'title' => 'Access Denied',
                        'show_login' => !$user,
                        'can_request_access' => $user !== null,
                    ], 403);
                }
                
                $this->logger->info('Admin viewing locked page', [
                    'page_id' => $page->getAttribute('id'),
                    'admin_id' => $user['id'],
                ]);
            }

            // Get user information for permission checks and tracking
            $user = $this->user($request);
            $isAdmin = $user ? $this->isAdmin($request) : false;
            $userId = $user ? $user['id'] : null;
            
            // Skip view count increment for certain conditions
            $skipViewCount = $request->isXmlHttpRequest() || 
                            $request->hasHeader('X-PJAX') ||
                            $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
            
            // Track page view if not skipped
            $viewCountUpdated = false;
            if (!$skipViewCount) {
                try {
                    $this->db->beginTransaction();
                    
                    // Get current view count and increment it
                    $currentPage = $this->db->table('pages')
                        ->where('id', '=', $page->getAttribute('id'))
                        ->first(['view_count']);
                    
                    $newViewCount = ($currentPage['view_count'] ?? 0) + 1;
                    
                    $result = $this->db->table('pages')
                        ->where('id', '=', $page->getAttribute('id'))
                        ->update([
                            'view_count' => $newViewCount,
                            'last_viewed_at' => date('Y-m-d H:i:s'),
                            'last_viewed_by' => $userId,
                        ]);
                    
                    $this->db->commit();
                    $viewCountUpdated = (bool)$result;
                    
                    // Log the page view for analytics
                    if ($viewCountUpdated) {
                        $this->logPageView($page, $request);
                    }
                } catch (\Exception $e) {
                    $this->db->rollBack();
                    $this->logger->error('Failed to update page view count', [
                        'page_id' => $page->getAttribute('id'),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Continue execution even if view count update fails
                }
            }

            // Get page revisions
            $revisions = $page->revisions();
            $latestRevision = $revisions[0] ?? null;
            
            // Parse wiki text content
            $content = $this->parseWikiText($page->getAttribute('content'));
            
            // Log successful page view
            $this->logger->info('Page displayed successfully', [
                'page_id' => $page->getAttribute('id'),
                'title' => $page->getAttribute('title'),
                'namespace' => $page->getAttribute('namespace'),
                'revision_count' => count($revisions),
                'view_count' => $page->getAttribute('view_count') + 1, // +1 for this view
            ]);

            return $this->view('pages/show', [
                'page' => $page,
                'latestRevision' => $latestRevision,
                'content' => $content,
                'canEdit' => $this->canEditPage($page, $request),
                'canDelete' => $this->canDeletePage($page, $request),
                'canLock' => $this->isAdmin($request),
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to display page', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($e instanceof HttpException) {
                throw $e;
            }
            
            throw new HttpException(500, 'An error occurred while loading the page. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new wiki page.
     *
     * This method displays the page creation form with optional pre-filled values
     * for title and namespace from the query parameters. It also checks if the
     * user has permission to create pages in the specified namespace.
     *
     * @param Request $request The HTTP request
     * @return Response
     *
     * @throws HttpException If the user doesn't have permission to create pages
     */
    public function create(Request $request): Response
    {
        $this->logger->info('Page creation form requested', [
            'query_params' => $request->getQueryParams(),
            'user_id' => $this->user($request)['id'] ?? 'guest',
        ]);
        
        try {
            // Check if user is authenticated
            if (!$this->isAuthenticated($request)) {
                $this->logger->notice('Unauthenticated user attempted to access page creation form');
                return $this->redirect('/login?redirect=' . urlencode($request->getUri()->getPath() . '?' . $request->getUri()->getQuery()));
            }
            
            // Check if user has permission to create pages
            if (!$this->canCreatePage($request)) {
                $this->logger->warning('User does not have permission to create pages', [
                    'user_id' => $this->user($request)['id'],
                ]);
                throw new HttpException(403, 'You do not have permission to create pages.');
            }
            
            // Get title and namespace from query parameters
            $title = trim($request->getQueryParam('title', ''));
            $namespace = trim($request->getQueryParam('namespace', ''));
            
            // If title is provided but namespace isn't, try to extract it
            if ($title && !$namespace && strpos($title, ':') !== false) {
                list($namespace, $title) = explode(':', $title, 2);
                $title = trim($title);
                $namespace = trim($namespace);
            }
            
            // Check if the page already exists
            if ($title) {
                $slug = $this->generateSlug($namespace, $title);
                $existingPage = Page::findBySlug($slug, $this->db);
                
                if ($existingPage) {
                    $this->logger->info('Attempted to create existing page, redirecting to edit', [
                        'slug' => $slug,
                        'existing_page_id' => $existingPage->getAttribute('id'),
                    ]);
                    
                    return $this->redirect($existingPage->getEditUrl())
                        ->with('info', 'This page already exists. You are now editing the existing page.');
                }
            }
            
            // Log successful form access
            $this->logger->debug('Page creation form displayed', [
                'title' => $title,
                'namespace' => $namespace,
                'user_id' => $this->user($request)['id'],
            ]);
            
            return $this->view('pages.edit', [
                'title' => $title,
                'namespace' => $namespace,
                'content' => '',
                'isNew' => true,
                'canEdit' => true,
                'canDelete' => false,
                'canLock' => $this->isAdmin($request),
            ]);
            
        } catch (HttpException $e) {
            throw $e; // Re-throw HTTP exceptions
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to display page creation form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query_params' => $request->getQueryParams(),
            ]);
            
            throw new HttpException(500, 'An error occurred while loading the page creation form. Please try again later.');
        }
    }

    /**
     * Store a newly created page in storage.
     *
     * This method handles the submission of the page creation form. It validates the input,
     * checks permissions, creates the page and its initial revision, and handles any errors
     * that may occur during the process.
     *
     * @param Request $request The HTTP request containing the page data
     * @return Response
     *
     * @throws HttpException If the user is not authenticated, lacks permissions, or validation fails
     * @throws \Exception If an unexpected error occurs during page creation
     */
    public function store(Request $request): Response
    {
        $startTime = microtime(true);
        $this->logger->info('Page creation request received', [
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'content_type' => $request->getHeaderLine('Content-Type'),
        ]);
        
        try {
            // Verify CSRF token
            $this->verifyCsrfToken($request);
            
            // Check authentication
            $user = $this->user($request);
            if (!$user) {
                $this->logger->warning('Unauthenticated user attempted to create a page');
                throw new HttpException(403, 'You must be logged in to create pages.');
            }
            
            // Check permissions
            if (!$this->canCreatePage($request)) {
                $this->logger->warning('User does not have permission to create pages', [
                    'user_id' => $user['id'],
                ]);
                throw new HttpException(403, 'You do not have permission to create pages.');
            }
            
            // Get and validate input
            $data = $request->getParsedBody();
            $title = trim($data['title'] ?? '');
            $namespace = trim($data['namespace'] ?? '');
            $content = $data['content'] ?? '';
            $comment = trim($data['comment'] ?? 'Created page');
            $isMinorEdit = isset($data['is_minor_edit']);
            $watchPage = isset($data['watch']);
            
            // Validate required fields
            $errors = [];
            if (empty($title)) {
                $errors['title'] = 'Page title is required';
            }
            if (empty($content)) {
                $errors['content'] = 'Page content cannot be empty';
            }
            
            // Validate title format
            if (!preg_match('/^[^#<>\[\]|{}_\x00-\x20\x7F]+$/u', $title)) {
                $errors['title'] = 'Invalid characters in page title';
            }
            
            // Validate namespace
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $namespace)) {
                $errors['namespace'] = 'Invalid namespace format';
            }
            
            // If there are validation errors, return to the form
            if (!empty($errors)) {
                $this->logger->info('Page creation validation failed', [
                    'user_id' => $user['id'],
                    'errors' => $errors,
                ]);
                
                return $this->view('pages.edit', [
                    'title' => $title,
                    'namespace' => $namespace,
                    'content' => $content,
                    'isNew' => true,
                    'errors' => $errors,
                    'old' => $data,
                ]);
            }
            
            // Generate slug and check for existing page
            $slug = $this->generateSlug($namespace, $title);
            $existingPage = Page::findBySlug($slug, $this->db);
            
            if ($existingPage) {
                $this->logger->info('Attempted to create existing page', [
                    'user_id' => $user['id'],
                    'existing_page_id' => $existingPage->getAttribute('id'),
                    'slug' => $slug,
                ]);
                
                return $this->redirect($existingPage->getEditUrl())
                    ->with('info', 'This page already exists. You are now editing the existing page.');
            }

            // Create the page
            $page = new Page($this->db, [
                'title' => $title,
                'slug' => $slug,
                'namespace' => $namespace,
                'content' => $data['content'] ?? '',
                'content_format' => $data['content_format'] ?? 'markdown',
            ]);
            
            $page->save();
            
            // Create the initial revision
            $revision = $page->createRevision(
                $user['id'],
                $data['comment'] ?? 'Created page'
            );

            // Log successful page creation
            $this->logger->info('Page created successfully', [
                'page_id' => $page->getAttribute('id'),
                'title' => $title,
                'namespace' => $namespace,
                'user_id' => $user['id'],
                'processing_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            ]);

            return $this->redirect($page->getUrl())
                ->with('success', 'Page created successfully.');
            
        } catch (HttpException $e) {
            throw $e; // Re-throw HTTP exceptions
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user['id'] ?? null,
                'title' => $title,
                'namespace' => $namespace,
            ]);
            
            throw new HttpException(500, 'An error occurred while creating the page. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Request $request, string $slug): Response
    {
        $page = Page::findBySlug($slug, $this->db);
        
        if (!$page) {
            $this->abort(404, 'Page not found');
        }
        
        // Check if page is locked and user has permission to edit
        if ($page->isLocked() && !$this->isAdmin($request)) {
            $this->abort(403, 'This page is locked and cannot be edited');
        }

        return $this->view('pages.edit', [
            'page' => $page,
            'title' => $page->getAttribute('title'),
            'namespace' => $page->getAttribute('namespace'),
            'content' => $page->getAttribute('content'),
            'isNew' => false,
        ]);
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, string $slug): Response
    {
        $user = $this->user($request);
        if (!$user) {
            $this->abort(403, 'You must be logged in to edit pages');
        }

        $page = Page::findBySlug($slug, $this->db);
        if (!$page) {
            $this->abort(404, 'Page not found');
        }
        
        // Check if page is locked and user has permission to edit
        if ($page->isLocked() && !$this->isAdmin($request)) {
            $this->abort(403, 'This page is locked and cannot be edited');
        }

        $data = $request->getParsedBody();
        
        // Update page content
        $page->setAttribute('content', $data['content'] ?? '');
        $page->setAttribute('content_format', $data['content_format'] ?? 'markdown');
        $page->save();
        
        // Create a new revision
        $revision = $page->createRevision(
            $user['id'],
            $data['comment'] ?? 'Edited page'
        );

        return $this->redirect($page->getUrl())
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Show the page history.
     */
    public function history(Request $request, string $slug): Response
    {
        $page = Page::findBySlug($slug, $this->db);
        
        if (!$page) {
            $this->abort(404, 'Page not found');
        }

        $revisions = $page->revisions();
        
        return $this->view('pages.history', [
            'page' => $page,
            'revisions' => $revisions,
        ]);
    }

    /**
     * Show a specific revision of a page.
     */
    public function showRevision(Request $request, string $slug, int $revisionId): Response
    {
        $page = Page::findBySlug($slug, $this->db);
        
        if (!$page) {
            $this->abort(404, 'Page not found');
        }
        
        $revision = null;
        foreach ($page->revisions() as $rev) {
            if ($rev->getAttribute('id') == $revisionId) {
                $revision = $rev;
                break;
            }
        }
        
        if (!$revision) {
            $this->abort(404, 'Revision not found');
        }

        return $this->view('pages.revision', [
            'page' => $page,
            'revision' => $revision,
            'content' => $this->parseWikiText($revision->getAttribute('content')),
        ]);
    }

    /**
     * Revert to a specific revision.
     */
    public function revert(Request $request, string $slug, int $revisionId): Response
    {
        $user = $this->user($request);
        if (!$user) {
            $this->abort(403, 'You must be logged in to revert pages');
        }

        $page = Page::findBySlug($slug, $this->db);
        if (!$page) {
            $this->abort(404, 'Page not found');
        }
        
        // Check if page is locked and user has permission to edit
        if ($page->isLocked() && !$this->isAdmin($request)) {
            $this->abort(403, 'This page is locked and cannot be edited');
        }
        
        // Find the revision to revert to
        $targetRevision = null;
        foreach ($page->revisions() as $revision) {
            if ($revision->getAttribute('id') == $revisionId) {
                $targetRevision = $revision;
                break;
            }
        }
        
        if (!$targetRevision) {
            $this->abort(404, 'Revision not found');
        }
        
        // Update page content to the target revision
        $page->setAttribute('content', $targetRevision->getAttribute('content'));
        $page->save();
        
        // Create a new revision for the revert
        $revision = $page->createRevision(
            $user['id'],
            sprintf('Reverted to revision #%d', $revisionId)
        );

        return $this->redirect($page->getUrl())
            ->with('success', 'Page reverted to the selected revision.');
    }

    /**
     * Lock a page to prevent further edits.
     */
    public function lock(Request $request, string $slug): Response
    {
        if (!$this->isAdmin($request)) {
            $this->abort(403, 'Only administrators can lock pages');
        }

        $page = Page::findBySlug($slug, $this->db);
        if (!$page) {
            $this->abort(404, 'Page not found');
        }

        $page->lock();

        return $this->redirect($page->getUrl())
            ->with('success', 'Page has been locked.');
    }

    /**
     * Unlock a page to allow edits.
     */
    public function unlock(Request $request, string $slug): Response
    {
        if (!$this->isAdmin($request)) {
            $this->abort(403, 'Only administrators can unlock pages');
        }

        $page = Page::findBySlug($slug, $this->db);
        if (!$page) {
            $this->abort(404, 'Page not found');
        }

        $page->unlock();

        return $this->redirect($page->getUrl())
            ->with('success', 'Page has been unlocked.');
    }

    /**
     * Generate a URL-friendly slug from a title.
     */
    protected function generateSlug(string $namespace, string $title): string
    {
        $slug = $title;
        
        // Convert to lowercase
        $slug = mb_strtolower($slug, 'UTF-8');
        
        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $slug);
        
        // Remove all characters except letters, numbers, and hyphens
        $slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $slug);
        
        // Replace multiple hyphens with a single one
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Trim hyphens from the beginning and end
        $slug = trim($slug, '-');
        
        // Add namespace prefix if provided
        if (!empty($namespace)) {
            $slug = $namespace . ':' . $slug;
        }
        
        return $slug;
    }

    /**
     * Parse wiki text into HTML.
     */
    /**
     * Log detailed information about a page view for analytics.
     *
     * This method records detailed information about a page view, including:
     * - Page information (ID, title, namespace)
     * - User information (if authenticated)
     * - Request details (user agent, IP, referrer)
     * - View timestamp and duration
     *
     * @param Page $page The page being viewed
     * @param Request $request The HTTP request
     * @return void
     */
    protected function logPageView(Page $page, Request $request): void
    {
        try {
            $user = $this->user($request);
            $serverParams = $request->getServerParams();
            
            $this->db->table('page_views')->insert([
                'page_id' => $page->getAttribute('id'),
                'user_id' => $user ? $user['id'] : null,
                'ip_address' => $serverParams['REMOTE_ADDR'] ?? null,
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'referrer' => $request->getHeaderLine('Referer'),
                'created_at' => date('Y-m-d H:i:s'),
                'metadata' => json_encode([
                    'is_ajax' => $request->isXmlHttpRequest(),
                    'is_secure' => $request->isSecure(),
                    'method' => $request->getMethod(),
                    'content_type' => $request->getHeaderLine('Content-Type'),
                    'accept_language' => $request->getHeaderLine('Accept-Language'),
                ]),
            ]);
            
            // Update the page's last_viewed_at timestamp
            $this->db->table('pages')
                ->where('id', $page->getAttribute('id'))
                ->update(['last_viewed_at' => date('Y-m-d H:i:s')]);
                
        } catch (\Exception $e) {
            // Log the error but don't interrupt the page view
            $this->logger->error('Failed to log page view', [
                'page_id' => $page->getAttribute('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    /**
     * Check if the current user is an administrator.
     *
     * This method verifies if the currently authenticated user has administrator privileges.
     * For now, it's a simple check that can be extended with more complex role-based
     * permission systems in the future.
     *
     * @param Request $request The HTTP request
     * @return bool True if the user is an administrator, false otherwise
     */
    protected function isAdmin(Request $request): bool
    {
        $user = $this->user($request);
        
        // For now, check if the user is authenticated and has an 'is_admin' flag set to true
        // In a real application, you would check against a proper role/permission system
        return $user && !empty($user['is_admin']) && $user['is_admin'] === true;
    }
    
    /**
     * Check if the current user has permission to edit the specified page.
     *
     * This method determines if the authenticated user has the necessary permissions
     * to edit the given page. By default, users can edit pages they've created,
     * and administrators can edit any page.
     *
     * @param Page $page The page to check edit permissions for
     * @param Request $request The HTTP request
     * @return bool True if the user can edit the page, false otherwise
     */
    protected function canEditPage(Page $page, Request $request): bool
    {
        $user = $this->user($request);
        
        // If user is not authenticated, they can't edit
        if (!$user) {
            return false;
        }
        
        // Admins can edit any page
        if ($this->isAdmin($request)) {
            return true;
        }
        
        // Page creator can edit their own pages
        $latestRevision = $page->revisions()[0] ?? null;
        if ($latestRevision && $latestRevision->getAttribute('user_id') == $user['id']) {
            return true;
        }
        
        // Check if the page is locked
        if ($page->isLocked()) {
            return false;
        }
        
        // Default to allowing edit for authenticated users (can be restricted further)
        return true;
    }
    
    /**
     * Check if the current user has permission to delete the specified page.
     *
     * This method determines if the authenticated user has the necessary permissions
     * to delete the given page. By default, only administrators can delete pages.
     *
     * @param Page $page The page to check delete permissions for
     * @param Request $request The HTTP request
     * @return bool True if the user can delete the page, false otherwise
     */
    protected function canDeletePage(Page $page, Request $request): bool
    {
        // Only admins can delete pages
        return $this->isAdmin($request);
    }
    
    /**
     * Check if the current user has permission to create pages.
     *
     * This method determines if the authenticated user has the necessary permissions
     * to create new pages in the wiki. By default, only authenticated users can create pages,
     * but this can be extended to include more granular permissions.
     *
     * @param Request $request The HTTP request
     * @return bool True if the user can create pages, false otherwise
     */
    protected function canCreatePage(Request $request): bool
    {
        // Check if user is authenticated
        $user = $this->user($request);
        if (!$user) {
            return false;
        }
        
        // For now, any authenticated user can create pages
        // In the future, this can be extended to check specific permissions
        return true;
    }
    
    /**
     * Parse wiki text into HTML with markdown support and code highlighting.
     *
     * @param string $text The wiki text to parse
     * @return string The parsed HTML
     */
    protected function parseWikiText(string $text): string
    {
        // First, escape any existing HTML to prevent XSS
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Process code blocks first (before other markdown)
        $text = $this->parseCodeBlocks($text);
        
        // Process headers
        $text = $this->parseHeaders($text);
        
        // Process emphasis (bold and italic)
        $text = $this->parseEmphasis($text);
        
        // Process links
        $text = $this->parseLinks($text);
        
        // Process lists
        $text = $this->parseLists($text);
        
        // Process blockquotes
        $text = $this->parseBlockquotes($text);
        
        // Process horizontal rules
        $text = $this->parseHorizontalRules($text);
        
        // Convert line breaks to <br> tags
        $text = nl2br($text);
        
        return $text;
    }
    
    /**
     * Parse code blocks with syntax highlighting.
     */
    protected function parseCodeBlocks(string $text): string
    {
        // Match code blocks with language specification
        $text = preg_replace_callback(
            '/```(\w+)?\n(.*?)\n```/s',
            function($matches) {
                $language = $matches[1] ?? 'text';
                $code = htmlspecialchars($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
                return sprintf(
                    '<pre class="code-block language-%s"><code class="language-%s">%s</code></pre>',
                    $language,
                    $language,
                    $code
                );
            },
            $text
        );
        
        // Match inline code
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        
        return $text;
    }
    
    /**
     * Parse markdown headers.
     */
    protected function parseHeaders(string $text): string
    {
        // Process headers (h1-h6)
        $text = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $text);
        
        return $text;
    }
    
    /**
     * Parse emphasis (bold and italic).
     */
    protected function parseEmphasis(string $text): string
    {
        // Bold text
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        
        // Italic text
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
        
        return $text;
    }
    
    /**
     * Parse links.
     */
    protected function parseLinks(string $text): string
    {
        // Markdown-style links: [text](url)
        $text = preg_replace(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>',
            $text
        );
        
        // Auto-link URLs
        $text = preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $text
        );
        
        return $text;
    }
    
    /**
     * Parse lists (ordered and unordered).
     */
    protected function parseLists(string $text): string
    {
        // Unordered lists
        $text = preg_replace_callback(
            '/(^[ \t]*[-*+][ \t]+.*(?:\n[ \t]*[-*+][ \t]+.*)*)/m',
            function($matches) {
                $items = preg_split('/\n/', $matches[1]);
                $html = '<ul>';
                foreach ($items as $item) {
                    if (preg_match('/^[ \t]*[-*+][ \t]+(.+)$/', $item, $itemMatch)) {
                        $html .= '<li>' . trim($itemMatch[1]) . '</li>';
                    }
                }
                $html .= '</ul>';
                return $html;
            },
            $text
        );
        
        // Ordered lists
        $text = preg_replace_callback(
            '/(^[ \t]*\d+\.[ \t]+.*(?:\n[ \t]*\d+\.[ \t]+.*)*)/m',
            function($matches) {
                $items = preg_split('/\n/', $matches[1]);
                $html = '<ol>';
                foreach ($items as $item) {
                    if (preg_match('/^[ \t]*\d+\.[ \t]+(.+)$/', $item, $itemMatch)) {
                        $html .= '<li>' . trim($itemMatch[1]) . '</li>';
                    }
                }
                $html .= '</ol>';
                return $html;
            },
            $text
        );
        
        return $text;
    }
    
    /**
     * Parse blockquotes.
     */
    protected function parseBlockquotes(string $text): string
    {
        // Process blockquotes after other markdown but before line breaks
        $text = preg_replace_callback(
            '/(^[ \t]*>[ \t]+.*(?:\n[ \t]*>[ \t]+.*)*)/m',
            function($matches) {
                $lines = preg_split('/\n/', $matches[1]);
                $html = '<blockquote>';
                foreach ($lines as $line) {
                    if (preg_match('/^[ \t]*>[ \t]+(.+)$/', $line, $lineMatch)) {
                        $html .= '<p>' . trim($lineMatch[1]) . '</p>';
                    }
                }
                $html .= '</blockquote>';
                return $html;
            },
            $text
        );
        
        return $text;
    }
    
    /**
     * Parse horizontal rules.
     */
    protected function parseHorizontalRules(string $text): string
    {
        $text = preg_replace('/^[ \t]*[-*_]{3,}[ \t]*$/m', '<hr>', $text);
        return $text;
    }
}
