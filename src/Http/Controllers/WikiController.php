<?php

/**
 * Wiki Controller
 *
 * This controller handles all operations related to wiki content including:
 * - Viewing wiki pages and their history
 * - Creating, editing, and deleting wiki pages
 * - Managing wiki page revisions and rollbacks
 * - Handling wiki page permissions and locks
 * - Processing wiki text and formatting
 * - Wiki-specific namespace handling
 *
 * @package IslamWiki\Http\Controllers
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use IslamWiki\Models\Page;
use IslamWiki\Core\Wiki\NamespaceManager;
use Psr\Log\LoggerInterface;

/**
 * Wiki Controller
 *
 * Handles wiki-specific content operations, extending PageController
 * to provide wiki namespace functionality and specialized behaviors.
 */
class WikiController extends PageController
{
    private const WIKI_NAMESPACE = 'wiki';

    /**
     * Create a new controller instance.
     *
     * @param \IslamWiki\Core\Database\Connection $db Database connection
     * @param \IslamWiki\Core\Container\AsasContainer $container The dependency injection container
     */
    public function __construct(
        \IslamWiki\Core\Database\Connection $db,
        \IslamWiki\Core\Container\AsasContainer $container
    ) {
        parent::__construct($db, $container);
    }

    /**
     * Display a user profile page.
     *
     * @param Request $request The HTTP request
     * @param string $username The username to display
     * @return Response
     */
    public function showUserProfile(Request $request, string $username): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('User profile requested', [
                    'username' => $username,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Try to get user data from database, fallback to mock data if tables don't exist
            $user = null;
            $contributions = [];
            $stats = [
                'total_edits' => 0,
                'pages_edited' => 0,
                'first_edit' => null,
                'last_edit' => null
            ];

            try {
                // Check if users table exists
                $stmt = $this->db->getPdo()->prepare("SHOW TABLES LIKE 'users'");
                $stmt->execute();
                $tableExists = $stmt->fetch();

                if ($tableExists) {
                    // Get user data
                    $stmt = $this->db->getPdo()->prepare('
                        SELECT id, username, email, display_name, bio, location, website, 
                               created_at, updated_at, last_login, is_admin, edit_count
                        FROM users 
                        WHERE username = ?
                    ');
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($user) {
                        // Try to get contributions if page_revisions table exists
                        try {
                            $stmt = $this->db->getPdo()->prepare("SHOW TABLES LIKE 'page_revisions'");
                            $stmt->execute();
                            if ($stmt->fetch()) {
                                $stmt = $this->db->getPdo()->prepare('
                                    SELECT pr.id, pr.page_id, pr.content, pr.created_at, pr.edit_summary,
                                           p.title as page_title, p.slug as page_slug
                                    FROM page_revisions pr
                                    LEFT JOIN pages p ON pr.page_id = p.id
                                    WHERE pr.user_id = ?
                                    ORDER BY pr.created_at DESC
                                    LIMIT 20
                                ');
                                $stmt->execute([$user['id']]);
                                $contributions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                                // Get user statistics
                                $stmt = $this->db->getPdo()->prepare('
                                    SELECT COUNT(*) as total_edits,
                                           COUNT(DISTINCT page_id) as pages_edited,
                                           MIN(created_at) as first_edit,
                                           MAX(created_at) as last_edit
                                    FROM page_revisions 
                                    WHERE user_id = ?
                                ');
                                $stmt->execute([$user['id']]);
                                $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
                            }
                        } catch (\Exception $e) {
                            // Page revisions table doesn't exist or query failed, use default stats
                            if ($this->logger) {
                                $this->logger->warning('Could not load user contributions', [
                                    'username' => $username,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Database query failed, use mock data
                if ($this->logger) {
                    $this->logger->warning('Database query failed, using mock data', [
                        'username' => $username,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // If no user found in database, create mock user data
            if (!$user) {
                $user = [
                    'username' => $username,
                    'display_name' => $username,
                    'bio' => 'User profile coming soon...',
                    'created_at' => date('Y-m-d H:i:s'),
                    'location' => 'Unknown',
                    'website' => null,
                    'email' => null,
                    'updated_at' => null,
                    'last_login' => null,
                    'is_admin' => false,
                    'edit_count' => 0
                ];
            }

            return $this->view('wiki/user-profile', [
                'user' => $user,
                'contributions' => $contributions,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Error displaying user profile', [
                    'username' => $username,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            throw new HttpException(500, 'Internal server error');
        }
    }

    /**
     * Display the wiki index page.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function index(Request $request, string $locale = 'en'): Response
    {
        // Validate locale parameter
        $validLocales = ['en', 'ar', 'tr', 'ur', 'id', 'ms', 'fa', 'he'];
        if (!in_array($locale, $validLocales)) {
            $locale = 'en'; // Default to English if invalid locale
        }

        try {
            if ($this->logger) {
                $this->logger->info('Wiki index requested', [
                    'query' => $request->getQueryParams(),
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                    'locale' => $locale,
                ]);
            }

            // Get sort parameters
            $sort = in_array(
                strtolower($request->getQueryParam('sort', 'title')),
                ['title', 'updated_at', 'view_count']
            )
                   ? strtolower($request->getQueryParam('sort', 'title'))
                   : 'title';

            $order = strtolower($request->getQueryParam('order', 'asc')) === 'desc' ? 'desc' : 'asc';

            // Get filter parameters
            $search = $request->getQueryParam('q');
            $namespace = $request->getQueryParam('namespace');

            // Build query for all pages (Main and wiki namespaces)
            $stmt = $this->db->getPdo()->prepare('
                SELECT id, title, slug, namespace, updated_at, view_count, created_at
                FROM pages
                WHERE namespace IN (?, ?, ?)
                ORDER BY title ASC
            ');
            $stmt->execute(['Main', 'wiki', '']);
            $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($this->logger) {
                $this->logger->info('Wiki index query executed', [
                    'pages_count' => count($pages),
                    'search' => $search,
                    'namespace' => $namespace,
                    'sort' => $sort,
                    'order' => $order
                ]);
            }

            // Enrich page list with revision count and first author
            foreach ($pages as &$page) {
                // Revision count
                $countStmt = $this->db->getPdo()->prepare('SELECT COUNT(*) FROM page_revisions WHERE page_id = ?');
                $countStmt->execute([$page['id']]);
                $page['revision_count'] = (int) $countStmt->fetchColumn();

                // First author (creator)
                $authorStmt = $this->db->getPdo()->prepare('SELECT u.username
                    FROM page_revisions pr
                    JOIN users u ON u.id = pr.user_id
                    WHERE pr.page_id = ?
                    ORDER BY pr.id ASC
                    LIMIT 1');
                $authorStmt->execute([$page['id']]);
                $author = $authorStmt->fetchColumn();
                $page['author_name'] = $author ?: 'Unknown';

                // Generate URL based on namespace
                if ($page['namespace'] === 'wiki') {
                    $page['url'] = '/wiki/' . str_replace('wiki:', '', $page['slug']);
                } else {
                    $page['url'] = '/wiki/' . str_replace('Main:', '', $page['slug']);
                }
            }

            if ($this->logger) {
                $this->logger->info('Wiki index retrieved successfully', [
                    'count' => count($pages),
                    'search' => $search,
                    'namespace' => $namespace,
                    'sort' => $sort,
                    'order' => $order
                ]);
            }

            // Use wiki/index template with Bismillah skin
            return $this->view('wiki/index', [
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
                    'search' => $search,
                    'namespace' => $namespace,
                    'sort' => $sort,
                    'order' => $order
                ],
                'title' => 'All Pages - IslamWiki',
                'user' => null
            ]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to retrieve wiki index', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'query' => $request->getQueryParams(),
                ]);
            }

            throw new HttpException(500, 'An error occurred while loading the wiki index. Please try again later.');
        }
    }

    /**
     * Display the Main_Page (default landing page).
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function showMainPage(Request $request): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Main_Page requested', [
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Get current date and time
            $currentTime = date('H:i, j F Y');
            $hijriDate = $this->getHijriDate();
            $totalArticles = $this->getTotalArticleCount();
            
            // Get user data for sidebar authentication state
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available, continue without user
                    if ($this->logger) {
                        $this->logger->warning('Auth service not available for Main_Page', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            return $this->view('wiki/main-page', [
                'current_time' => $currentTime,
                'hijri_date' => $hijriDate,
                'total_articles' => $totalArticles,
                'title' => 'Main Page - IslamWiki',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to load Main_Page', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to load main page');
        }
    }

    /**
     * Get Hijri date for current date
     */
    private function getHijriDate(): string
    {
        // For now, return a placeholder
        // TODO: Implement proper Hijri date calculation
        return '28 Safar 1447 AH';
    }

    /**
     * Get total article count
     */
    private function getTotalArticleCount(): int
    {
        try {
            $stmt = $this->db->getPdo()->prepare('SELECT COUNT(*) as count FROM pages WHERE namespace = ?');
            $stmt->execute(['wiki']);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Display the specified wiki page
     */
    public function show(Request $request, string $slug): Response
    {
        // Get locale from request attributes (set by LocaleMiddleware)
        $locale = $request->getAttribute('locale', 'en');
        
        if ($this->logger) {
            $this->logger->info('WikiController::show called', [
                'slug' => $slug,
                'locale' => $locale,
                'isWikiRoute' => $request->getAttribute('isWikiRoute', false)
            ]);
        }

        // First try to find the page with wiki namespace
        $wikiSlug = self::WIKI_NAMESPACE . ':' . $slug;
        if ($this->logger) {
            $this->logger->info('Looking for page with wiki namespace', [
                'slug' => $slug,
                'wikiSlug' => $wikiSlug,
                'namespace' => self::WIKI_NAMESPACE,
            ]);
        }
        $page = Page::findBySlug($wikiSlug, $this->db);
        if ($this->logger) {
            $this->logger->info('Page lookup result', [
                'wikiSlug' => $wikiSlug,
                'pageFound' => $page ? 'yes' : 'no',
                'pageId' => $page ? $page->getAttribute('id') : null,
            ]);
        }

        if (!$page) {
            // Page doesn't exist - offer to create it
            if ($this->logger) {
                $this->logger->info('Page not found, redirecting to create page', ['slug' => $slug]);
            }
            
            // Redirect to the main create page with the title parameter
            return $this->redirect("/wiki/create?title=" . urlencode($slug), 302);
        }

        // Get page content in the user's preferred language
        $content = $this->getLocalizedContent($page, $locale);
        
        // Render the page with language context
        return $this->view('pages/show.twig', [
            'page' => $page,
            'content' => $content,
            'locale' => $locale,
            'displaySlug' => $slug,
            'isWikiRoute' => true,
            'availableLanguages' => $this->getAvailableLanguages($page),
            'currentLanguage' => $locale
        ]);
    }

    /**
     * Show the wiki page creation form.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function create(Request $request, string $locale = 'en'): Response
    {
        // Validate locale parameter
        $validLocales = ['en', 'ar', 'tr', 'ur', 'id', 'ms', 'fa', 'he'];
        if (!in_array($locale, $validLocales)) {
            $locale = 'en'; // Default to English if invalid locale
        }

        $this->logger->info('Wiki page creation form requested', [
            'query' => $request->getQueryParams(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            'locale' => $locale,
        ]);

        try {
            // Get title from query parameters
            $title = trim($request->getQueryParam('title', ''));

            // Check if the wiki page already exists
            if ($title) {
                $slug = $this->generateSlug(self::WIKI_NAMESPACE, $title);
                $existingPage = Page::findBySlug($slug, $this->db);

                if ($existingPage) {
                    $this->logger->info('Attempted to create existing wiki page, redirecting to edit', [
                        'slug' => $slug,
                        'existing_page_id' => $existingPage->getAttribute('id'),
                    ]);

                    // Use locale-aware redirect
                    $redirectPath = $locale === 'en' ? "/wiki/{$title}/edit" : "/{$locale}/wiki/{$title}/edit";
                    return $this->redirect($redirectPath)
                        ->with('info', 'This wiki page already exists. You are now editing the existing page.');
                }
            }

            $this->logger->debug('Wiki page creation form displayed', [
                'title' => $title,
                'namespace' => self::WIKI_NAMESPACE,
                'locale' => $locale,
            ]);

            // Check if WikiMarkupExtension is available and use it
            if (class_exists('\IslamWiki\Extensions\WikiMarkupExtension\WikiMarkupEditor')) {
                try {
                    // Create WikiMarkupEditor instance
                    $parser = new \IslamWiki\Extensions\WikiMarkupExtension\WikiMarkupParser();
                    $editor = new \IslamWiki\Extensions\WikiMarkupExtension\WikiMarkupEditor($parser);
                    
                    // Generate the enhanced edit form
                    $editForm = $editor->generateEditForm($title, '', 'wikimarkup');
                    
                    // Use a dedicated wiki creation template
                    return $this->view('wiki/create', [
                        'title' => $title,
                        'namespace' => self::WIKI_NAMESPACE,
                        'editForm' => $editForm,
                        'isNew' => true,
                        'canEdit' => true,
                        'canDelete' => false,
                        'canLock' => $this->isAdmin($request),
                        'user' => $this->user($request),
                        'locale' => $locale,
                    ]);
                } catch (\Exception $e) {
                    $this->logger->warning('WikiMarkupExtension not available, falling back to basic form', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Fallback to basic form if WikiMarkupExtension is not available
            return $this->view('pages/edit', [
                'title' => $title,
                'namespace' => self::WIKI_NAMESPACE,
                'content' => '',
                'isNew' => true,
                'canEdit' => true,
                'canDelete' => false,
                'canLock' => $this->isAdmin($request),
                'user' => $this->user($request),
                'locale' => $locale,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to display wiki page creation form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query_params' => $request->getQueryParams(),
            ]);

            throw new HttpException(500, 'An error occurred while loading the wiki page creation form.');
        }
    }

    /**
     * Store a newly created wiki page.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function store(Request $request, string $locale = 'en'): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Wiki page creation requested', [
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $request->getHeaderLine('User-Agent'),
                    'content_type' => $request->getHeaderLine('Content-Type'),
                    'method' => $request->getMethod(),
                    'uri' => $request->getUri()->getPath(),
                ]);
            }

            $data = $request->getParsedBody();
            $title = trim($data['title'] ?? '');
            $content = $data['content'] ?? '';
            $comment = trim($data['comment'] ?? 'Created new page');
            $contentFormat = $data['content_format'] ?? 'markdown';
            $namespace = trim($data['namespace'] ?? '');

            // Validate required fields
            $errors = [];
            if (empty($title)) {
                $errors['title'] = 'Page title is required';
            }
            if (empty($content)) {
                $errors['content'] = 'Page content cannot be empty';
            }

            // Validate title format
            if (!empty($title) && !preg_match('/^[^<>\[\]|{}_\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+$/u', $title)) {
                $errors['title'] = 'Invalid characters in page title';
            }

            // If there are validation errors, return to the form
            if (!empty($errors)) {
                return $this->view('pages/edit', [
                    'title' => $title,
                    'namespace' => $namespace,
                    'content' => $content,
                    'content_format' => $contentFormat,
                    'isNew' => true,
                    'errors' => $errors,
                    'old' => $data,
                    'canEdit' => true,
                    'canDelete' => false,
                    'canLock' => $this->isAdmin($request),
                ]);
            }

            // Determine namespace based on context
            if (empty($namespace)) {
                // If no namespace specified, default to Wiki namespace for WikiController
                $namespace = self::WIKI_NAMESPACE;
            }

            // Generate slug
            $slug = $this->generateSlug($namespace, $title);

            // Check if page already exists
            $existingPage = Page::findBySlug($slug, $this->db);
            if ($existingPage) {
                $errors['title'] = 'A page with this title already exists.';
                return $this->view('pages/edit', [
                    'title' => $title,
                    'namespace' => $namespace,
                    'content' => $content,
                    'content_format' => $contentFormat,
                    'isNew' => true,
                    'errors' => $errors,
                    'old' => $data,
                    'canEdit' => true,
                    'canDelete' => false,
                    'canLock' => $this->isAdmin($request),
                ]);
            }

            // Get user safely
            $user = null;
            try {
                $user = $this->user($request);
            } catch (\Exception $e) {
                // User not authenticated, continue without user
            }

            // Create the page
            $pageId = $this->db->table('pages')->insertGetId([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'content_format' => $contentFormat,
                'namespace' => $namespace,
                'is_locked' => false,
                'view_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Create initial revision
            $revisionId = $this->db->table('page_revisions')->insertGetId([
                'page_id' => $pageId,
                'title' => $title,
                'content' => $content,
                'content_format' => $contentFormat,
                'comment' => $comment,
                'user_id' => $user ? $user['id'] : 1, // Default user if not authenticated
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($this->logger) {
                $this->logger->info('Wiki page created successfully', [
                    'page_id' => $pageId,
                    'revision_id' => $revisionId,
                    'title' => $title,
                    'slug' => $slug,
                    'namespace' => $namespace,
                    'user_id' => $user ? $user['id'] : 'guest',
                ]);
            }

            // Extract the slug without namespace for the redirect URL
            $displaySlug = $slug;
            if (strpos($slug, ':') !== false) {
                $displaySlug = substr($slug, strpos($slug, ':') + 1);
            }

            // Get locale from request or default to 'en'
            $locale = $request->getAttribute('locale', 'en');
            if (empty($locale)) {
                $locale = 'en';
            }

                        // Redirect to the new page using the simpler route structure
            return $this->redirect("/wiki/{$displaySlug}")
                            ->with('success', 'Page created successfully.');

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to create wiki page', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $data ?? [],
                ]);
            }

            throw new HttpException(500, 'An error occurred while creating the page. Please try again later.');
        }
    }

    /**
     * Show the wiki page edit form.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function edit(Request $request, string $slug, string $locale = 'en'): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Wiki page edit form requested', [
                    'slug' => $slug,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Try to find the page with different namespace combinations
            $page = null;

            // First try with wiki namespace
            $wikiSlug = self::WIKI_NAMESPACE . ':' . $slug;
            $page = Page::findBySlug($wikiSlug, $this->db);

            // If not found, try with Main namespace
            if (!$page) {
                $mainSlug = 'Main:' . $slug;
                $page = Page::findBySlug($mainSlug, $this->db);
            }

            // If not found, try without namespace (for regular pages)
            if (!$page) {
                $page = Page::findBySlug($slug, $this->db);
            }

            if (!$page) {
                throw new HttpException(404, 'Wiki page not found.');
            }

            // Check if page is locked and user has permission to edit
            if ($page->isLocked() && !$this->isAdmin($request)) {
                throw new HttpException(403, 'This wiki page is locked and cannot be edited.');
            }

            // Get user safely
            $user = null;
            try {
                $user = $this->user($request);
            } catch (\Exception $e) {
                // User not authenticated, continue without user
            }



            // Use pages/edit template for now until wiki templates are created
            return $this->view('pages/edit', [
                'page' => $page,
                'title' => $page->getAttribute('title'),
                'namespace' => self::WIKI_NAMESPACE,
                'content' => $page->getAttribute('content'),
                'isNew' => false,
                'canEdit' => true,
                'canDelete' => $this->canDeletePage($page, $request),
                'canLock' => $this->isAdmin($request),
                'user' => $user,
            ]);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to display wiki page edit form', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            throw new HttpException(500, 'An error occurred while loading the wiki page edit form.');
        }
    }

    /**
     * Update a wiki page.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function update(Request $request, string $slug, string $locale = 'en'): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Wiki page update requested', [
                    'slug' => $slug,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Try to find the page with different namespace combinations
            $page = null;

            // First try with wiki namespace
            $wikiSlug = self::WIKI_NAMESPACE . ':' . $slug;
            $page = Page::findBySlug($wikiSlug, $this->db);

            // If not found, try with Main namespace
            if (!$page) {
                $mainSlug = 'Main:' . $slug;
                $page = Page::findBySlug($mainSlug, $this->db);
            }

            // If not found, try without namespace (for regular pages)
            if (!$page) {
                $page = Page::findBySlug($slug, $this->db);
            }

            if (!$page) {
                throw new HttpException(404, 'Wiki page not found.');
            }

            // Check if page is locked and user has permission to edit
            if ($page->isLocked() && !$this->isAdmin($request)) {
                throw new HttpException(403, 'This wiki page is locked and cannot be edited.');
            }

            $data = $request->getParsedBody();
            $title = trim($data['title'] ?? '');
            $content = $data['content'] ?? '';
            $comment = trim($data['comment'] ?? 'Edited wiki page');
            $contentFormat = $data['content_format'] ?? 'markdown';

            // Validate required fields
            $errors = [];
            if (empty($title)) {
                $errors['title'] = 'Page title is required';
            }
            if (empty($content)) {
                $errors['content'] = 'Page content cannot be empty';
            }

            // Validate title format
            if (!empty($title) && !preg_match('/^[^<>\[\]|{}_\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+$/u', $title)) {
                $errors['title'] = 'Invalid characters in page title';
            }

            // If there are validation errors, return to the form
            if (!empty($errors)) {
                return $this->view('pages/edit', [
                    'page' => $page,
                    'title' => $title,
                    'namespace' => self::WIKI_NAMESPACE,
                    'content' => $content,
                    'content_format' => $contentFormat,
                    'isNew' => false,
                    'errors' => $errors,
                    'old' => $data,
                    'canEdit' => true,
                    'canDelete' => $this->canDeletePage($page, $request),
                    'canLock' => $this->isAdmin($request),
                ]);
            }

            // Update page content
            $page->setAttribute('title', $title);
            $page->setAttribute('content', $content);
            $page->setAttribute('content_format', $contentFormat);
            $page->setAttribute('updated_at', date('Y-m-d H:i:s'));
            $page->save();

            // Create a new revision
            $revisionId = $this->db->table('page_revisions')->insertGetId([
                'page_id' => $page->getAttribute('id'),
                'title' => $title,
                'content' => $content,
                'content_format' => $contentFormat,
                'comment' => $comment,
                'user_id' => 1, // Default user for now
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($this->logger) {
                $this->logger->info('Wiki page updated successfully', [
                    'page_id' => $page->getAttribute('id'),
                    'revision_id' => $revisionId,
                    'title' => $title,
                    'slug' => $slug,
                ]);
            }

            return $this->redirect("/wiki/{$slug}")
                ->with('success', 'Wiki page updated successfully.');

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to update wiki page', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            throw new HttpException(500, 'An error occurred while updating the wiki page. Please try again later.');
        }
    }

    /**
     * Show wiki page history.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function history(Request $request, string $slug, string $locale = 'en'): Response
    {
        $this->logger->info('Wiki page history requested', [
            'slug' => $slug,
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
        ]);

        try {
            // Try to find the page with different namespace combinations
            $page = null;

            // First try with wiki namespace
            $wikiSlug = self::WIKI_NAMESPACE . ':' . $slug;
            $page = Page::findBySlug($wikiSlug, $this->db);

            // If not found, try with Main namespace
            if (!$page) {
                $mainSlug = 'Main:' . $slug;
                $page = Page::findBySlug($mainSlug, $this->db);
            }

            // If not found, try without namespace (for regular pages)
            if (!$page) {
                $page = Page::findBySlug($slug, $this->db);
            }

            if (!$page) {
                throw new HttpException(404, 'Wiki page not found.');
            }

            // Get revisions for the found page
            $revisions = $page->revisions();

            return $this->view('pages/history', [
                'page' => $page,
                'revisions' => $revisions,
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to display wiki page history', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a wiki page.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function destroy(Request $request, string $slug, string $locale = 'en'): Response
    {
        $this->logger->info('Wiki page delete requested', [
            'slug' => $slug,
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
        ]);

        try {
            // Try to find the page with different namespace combinations
            $page = null;

            // First try with wiki namespace
            $wikiSlug = self::WIKI_NAMESPACE . ':' . $slug;
            $page = Page::findBySlug($wikiSlug, $this->db);

            // If not found, try with Main namespace
            if (!$page) {
                $mainSlug = 'Main:' . $slug;
                $page = Page::findBySlug($mainSlug, $this->db);
            }

            // If not found, try without namespace (for regular pages)
            if (!$page) {
                $page = Page::findBySlug($slug, $this->db);
            }

            if (!$page) {
                throw new HttpException(404, 'Wiki page not found.');
            }

            // Soft-delete page (mark as deleted)
            $this->db->table('pages')
                ->where('id', '=', $page->getAttribute('id'))
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            return $this->redirect('/wiki', 302);

        } catch (\Exception $e) {
            $this->logger->error('Failed to delete wiki page', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Watch/unwatch methods - delegate to parent
     */
    public function watch(Request $request, string $slug, string $locale = 'en'): Response
    {
        try {
            $user = $this->user($request);
            if (!$user) {
                return $this->redirect("/login", 302);
            }
            $page = Page::findBySlug(self::WIKI_NAMESPACE . ':' . $slug, $this->db) ?? Page::findBySlug($slug, $this->db);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            $exists = $this->db->table('user_watchlist')
                ->where('user_id', '=', $user['id'])
                ->where('page_id', '=', $page->getAttribute('id'))
                ->first(['id']);
            if (!$exists) {
                $this->db->table('user_watchlist')->insert([
                    'user_id' => $user['id'],
                    'page_id' => $page->getAttribute('id'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
            // Redirect back to dashboard to reflect updated watchlist quickly
            if ($request->getHeaderLine('Referer') && strpos($request->getHeaderLine('Referer'), '/dashboard') !== false) {
                return $this->redirect('/dashboard', 302);
            }
            return $this->redirect("/wiki/{$slug}")
                ->with('success', 'Page added to your watchlist.');
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to watch page', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                ]);
            }
            return $this->redirect("/wiki/{$slug}")
                ->with('error', 'Unable to add to watchlist.');
        }
    }

    public function unwatch(Request $request, string $slug, string $locale = 'en'): Response
    {
        try {
            $user = $this->user($request);
            if (!$user) {
                return $this->redirect("/login", 302);
            }
            $page = Page::findBySlug(self::WIKI_NAMESPACE . ':' . $slug, $this->db) ?? Page::findBySlug($slug, $this->db);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }
            $this->db->table('user_watchlist')
                ->where('user_id', '=', $user['id'])
                ->where('page_id', '=', $page->getAttribute('id'))
                ->delete();
            if ($request->getHeaderLine('Referer') && strpos($request->getHeaderLine('Referer'), '/dashboard') !== false) {
                return $this->redirect('/dashboard', 302);
            }
            return $this->redirect("/wiki/{$slug}")
                ->with('success', 'Page removed from your watchlist.');
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to unwatch page', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                ]);
            }
            return $this->redirect("/wiki/{$slug}")
                ->with('error', 'Unable to remove from watchlist.');
        }
    }

    /**
     * Display wiki page with wiki-specific context.
     *
     * @param Request $request The HTTP request
     * @param Page $page The page to display
     * @return Response
     */
    private function displayWikiPage(Request $request, Page $page): Response
    {
        // Get user information for permission checks
        $user = $this->user($request);
        $isAdmin = $user ? $this->isAdmin($request) : false;
        $userId = $user ? $user['id'] : null;

        // Check if the page is locked
        if ($page->isLocked() && !$isAdmin) {
            return $this->view('errors.403', [
                'message' => 'This wiki page is currently locked and cannot be viewed.',
                'title' => 'Access Denied',
                'show_login' => !$user,
                'can_request_access' => $user !== null,
            ], 403);
        }

        // View count disabled per request

        // Determine watch status for current user
        $isWatched = false;
        if ($userId) {
            try {
                $existing = $this->db->table('user_watchlist')
                    ->where('user_id', '=', $userId)
                    ->where('page_id', '=', $page->getAttribute('id'))
                    ->first(['id']);
                $isWatched = (bool) ($existing['id'] ?? null);
            } catch (\Exception $e) {
                // ignore watch status errors
            }
        }

        // Get page revisions
        $revisions = $page->revisions();
        $latestRevision = $revisions[0] ?? null;
        // Attach author username for latest revision if available
        if ($latestRevision) {
            try {
                $stmt = $this->db->getPdo()->prepare('SELECT username FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$latestRevision->getAttribute('user_id')]);
                $authorUsername = $stmt->fetchColumn();
                $latestRevision->setAttribute('author_name', $authorUsername ?: 'Unknown');
            } catch (\Throwable $e) {
                // ignore author lookup errors
            }
        }

        // Parse wiki text content
        $content = $this->parseWikiText($page->getAttribute('content'));
        // Allow extensions to enhance page HTML (sitewide progress bars, etc.)
        try {
            if ($this->container->has(\IslamWiki\Core\Extensions\ExtensionManager::class)) {
                $extMgr = $this->container->get(\IslamWiki\Core\Extensions\ExtensionManager::class);
                $hook = $extMgr->getHookManager();
                $post = $hook->runLast('ContentPostRender', [$content, 'wiki']);
                if (is_string($post) && $post !== '') {
                    $content = $post;
                }
            }
        } catch (\Throwable $e) {
            // non-fatal
        }

        $this->logger->info('Wiki page displayed successfully', [
            'page_id' => $page->getAttribute('id'),
            'title' => $page->getAttribute('title'),
            'namespace' => $page->getAttribute('namespace'),
            'revision_count' => count($revisions),
            'view_count' => $page->getAttribute('view_count'),
        ]);

        // Use pages/show template for now until wiki templates are created
        return $this->view('pages/show', [
            'page' => $page,
            'latestRevision' => $latestRevision,
            'content' => $content,
            'canEdit' => $this->canEditPage($page, $request),
            'canDelete' => $this->canDeletePage($page, $request),
            'canLock' => $isAdmin,
            'isLocked' => $page->isLocked(),
            'user' => $user,
            'isWatched' => $isWatched,
            'title' => $page->getAttribute('title') . ' - Wiki - IslamWiki',
            'viewCount' => $page->getAttribute('view_count'),
        ]);
    }

    /**
     * Update page view count.
     *
     * @param Page $page The page
     * @param Request $request The request
     * @param int|null $userId The user ID
     */
    private function updatePageViewCount(Page $page, Request $request, ?int $userId): void
    {
        // Always increment on GET /wiki... page requests (server-rendered view)
        $method = strtoupper($request->getMethod());
        $path = method_exists($request->getUri(), 'getPath') ? $request->getUri()->getPath() : '';
        $isWikiPage = is_string($path) && (preg_match('#^/wiki(?:/|$)#', $path) === 1 || preg_match('#^/[^/]+$#', $path) === 1);
        if ($method === 'GET' && $isWikiPage) {
            try {
                $this->db->beginTransaction();

                // Atomic increment using a single UPDATE to avoid race conditions
                $this->db->getPdo()->prepare(
                    'UPDATE pages SET view_count = COALESCE(view_count,0) + 1, last_viewed_at = ?, last_viewed_by = ? WHERE id = ?'
                )->execute([
                    date('Y-m-d H:i:s'),
                    $userId,
                    $page->getAttribute('id'),
                ]);

                $this->db->commit();

                // Keep in-memory model in sync so the template sees the updated count
                try {
                    // Fetch the new value after atomic increment
                    $fresh = $this->db->table('pages')
                        ->where('id', '=', $page->getAttribute('id'))
                        ->first(['view_count']);
                    if (is_array($fresh) && array_key_exists('view_count', $fresh)) {
                        $page->setAttribute('view_count', (int) $fresh['view_count']);
                    }
                } catch (\Throwable $syncErr) {
                    // ignore model sync errors
                }

                // Log the page view for analytics
                $this->logPageView($page, $request);

            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->logger->error('Failed to update wiki page view count', [
                    'page_id' => $page->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * API methods - simple implementations
     */
    public function apiIndex(Request $request, string $locale = 'en'): Response
    {
        try {
            $search = $request->getQueryParam('q');
            $sort = $request->getQueryParam('sort', 'title');
            $order = $request->getQueryParam('order', 'asc');

            $query = $this->db->table('pages')
                ->select(['id', 'title', 'slug', 'namespace', 'updated_at', 'view_count', 'created_at'])
                ->where('namespace', '=', self::WIKI_NAMESPACE);

            if ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'LIKE', "%{$search}%")
                            ->orWhere('content', 'LIKE', "%{$search}%");
                });
            }

            $pages = $query->orderBy($sort, $order)->get();

            return $this->json([
                'success' => true,
                'data' => $pages,
                'total' => count($pages)
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to retrieve wiki pages'], 500);
        }
    }

    public function apiShow(Request $request, string $locale, string $slug): Response
    {
        try {
            $wikiSlug = self::WIKI_NAMESPACE . ':' . $slug;
            $page = Page::findBySlug($wikiSlug, $this->db);

            if (!$page) {
                $page = Page::findBySlug($slug, $this->db);
                if (!$page || $page->getAttribute('namespace') !== self::WIKI_NAMESPACE) {
                    return $this->json(['success' => false, 'message' => 'Wiki page not found'], 404);
                }
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $page->getAttribute('id'),
                    'title' => $page->getAttribute('title'),
                    'slug' => $page->getAttribute('slug'),
                    'content' => $page->getAttribute('content'),
                    'namespace' => $page->getAttribute('namespace'),
                    'view_count' => $page->getAttribute('view_count'),
                    'is_locked' => $page->getAttribute('is_locked'),
                    'created_at' => $page->getAttribute('created_at'),
                    'updated_at' => $page->getAttribute('updated_at'),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to retrieve wiki page'], 500);
        }
    }

    public function apiStore(Request $request, string $locale = 'en'): Response
    {
        try {
            $data = $request->getParsedBody();
            $title = trim($data['title'] ?? '');
            $content = $data['content'] ?? '';
            $namespace = self::WIKI_NAMESPACE;
            if ($title === '' || $content === '') {
                return $this->json(['success' => false, 'message' => 'Title and content are required'], 422);
            }
            $slug = $this->generateSlug($namespace, $title);
            $exists = Page::findBySlug($slug, $this->db);
            if ($exists) {
                return $this->json(['success' => false, 'message' => 'Page already exists'], 409);
            }
            $pageId = $this->db->table('pages')->insertGetId([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'content_format' => $data['content_format'] ?? 'markdown',
                'namespace' => $namespace,
                'is_locked' => false,
                'view_count' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return $this->json(['success' => true, 'id' => $pageId, 'slug' => $slug], 201);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to create wiki page'], 500);
        }
    }

    public function apiUpdate(Request $request, string $locale, string $slug): Response
    {
        try {
            $page = Page::findBySlug(self::WIKI_NAMESPACE . ':' . $slug, $this->db) ?? Page::findBySlug($slug, $this->db);
            if (!$page) {
                return $this->json(['success' => false, 'message' => 'Page not found'], 404);
            }
            $data = $request->getParsedBody();
            if (isset($data['content'])) {
                $page->setAttribute('content', $data['content']);
            }
            if (isset($data['title'])) {
                $page->setAttribute('title', trim($data['title']));
            }
            $page->setAttribute('updated_at', date('Y-m-d H:i:s'));
            $page->save();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to update wiki page'], 500);
        }
    }

    public function apiDestroy(Request $request, string $locale, string $slug): Response
    {
        try {
            $page = Page::findBySlug(self::WIKI_NAMESPACE . ':' . $slug, $this->db) ?? Page::findBySlug($slug, $this->db);
            if (!$page) {
                return $this->json(['success' => false, 'message' => 'Page not found'], 404);
            }
            $this->db->table('pages')->where('id', '=', $page->getAttribute('id'))
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Failed to delete wiki page'], 500);
        }
    }

    /**
     * Display the unified wiki dashboard.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function dashboard(Request $request): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Wiki dashboard requested', [
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    // Use the full class name for more reliable service resolution
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                        
                        // Debug logging for authentication issue
                        error_log("Wiki dashboard auth debug - Auth service available: YES, User method exists: YES, User retrieved: " . ($user !== null ? 'YES' : 'NO'));
                        if ($user) {
                            error_log("Wiki dashboard auth debug - User data: ID=" . ($user['id'] ?? 'NULL') . ", Username=" . ($user['username'] ?? 'NULL') . ", Is Admin=" . ($user['is_admin'] ?? 'NULL') . ", Role=" . ($user['role'] ?? 'NULL'));
                        } else {
                            error_log("Wiki dashboard auth debug - No user data returned from auth service");
                        }
                        

                        
                        if ($this->logger) {
                            $this->logger->info('Wiki dashboard auth debug', [
                                'auth_service_available' => true,
                                'user_method_exists' => true,
                                'user_retrieved' => ($user !== null),
                                'user_data' => $user ? [
                                    'id' => $user['id'] ?? 'NULL',
                                    'username' => $user['username'] ?? 'NULL',
                                    'is_admin' => $user['is_admin'] ?? 'NULL',
                                    'role' => $user['role'] ?? 'NULL'
                                ] : 'NULL'
                            ]);
                        }
                    } else {
                        error_log("Wiki dashboard auth debug - Auth service or user method not available. Auth: " . ($auth !== null ? 'YES' : 'NO') . ", User method: " . ($auth && method_exists($auth, 'user') ? 'YES' : 'NO'));
                        if ($this->logger) {
                            $this->logger->warning('Auth service or user method not available', [
                                'auth_service_available' => ($auth !== null),
                                'user_method_exists' => ($auth && method_exists($auth, 'user'))
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Wiki dashboard auth debug - Exception getting auth service: " . $e->getMessage());
                    if ($this->logger) {
                        $this->logger->warning('Auth service not available for wiki dashboard', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            } else {
                error_log("Wiki dashboard auth debug - Container not available in WikiController");
                if ($this->logger) {
                    $this->logger->warning('Container not available in WikiController');
                }
            }

            // Get wiki statistics
            $stats = $this->getWikiStatistics();
            
            // Get recent changes
            $recentChanges = $this->getRecentChanges();
            
            // Get user contributions (if logged in)
            $userContributions = [];
            if ($user) {
                $userContributions = $this->getUserContributions($user['id']);
            }
            
            // Get category overview
            $categories = $this->getCategoryOverview();
            
            // Get quick actions based on user role
            $quickActions = $this->getQuickActions($user);

            return $this->view('wiki/dashboard', [
                'user' => $user,
                'stats' => $stats,
                'recentChanges' => $recentChanges,
                'userContributions' => $userContributions,
                'categories' => $categories,
                'quickActions' => $quickActions,
                'title' => 'Wiki Dashboard - IslamWiki'
            ]);

        } catch (\Exception $e) {
            // Enhanced error handling with detailed debugging information
            $errorDetails = $this->generateDetailedErrorReport($e, 'Wiki Dashboard');
            
            if ($this->logger) {
                $this->logger->error('Failed to load wiki dashboard', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'debug_info' => $errorDetails
                ]);
            }
            
            // Store debug info in session for error handler to access
            $_SESSION['debug_error_info'] = $errorDetails;
            
            // Throw the original exception to preserve the stack trace
            throw $e;
        }
    }

    /**
     * Get comprehensive wiki statistics.
     *
     * @return array
     */
    private function getWikiStatistics(): array
    {
        try {
            $stats = [
                'total_pages' => 0,
                'total_revisions' => 0,
                'total_users' => 0,
                'pages_this_month' => 0,
                'edits_this_month' => 0,
                'most_viewed_pages' => [],
                'most_edited_pages' => [],
                'recent_activity' => []
            ];

            // Total pages
            $stmt = $this->db->getPdo()->prepare('SELECT COUNT(*) FROM pages WHERE namespace IN (?, ?, ?)');
            $stmt->execute(['Main', 'wiki', '']);
            $stats['total_pages'] = (int) $stmt->fetchColumn();

            // Total revisions
            $stmt = $this->db->getPdo()->prepare('SELECT COUNT(*) FROM page_revisions');
            $stmt->execute();
            $stats['total_revisions'] = (int) $stmt->fetchColumn();

            // Total users
            $stmt = $this->db->getPdo()->prepare('SELECT COUNT(*) FROM users');
            $stmt->execute();
            $stats['total_users'] = (int) $stmt->fetchColumn();

            // Pages created this month
            $stmt = $this->db->getPdo()->prepare('
                SELECT COUNT(*) FROM pages 
                WHERE namespace IN (?, ?, ?) 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ');
            $stmt->execute(['Main', 'wiki', '']);
            $stats['pages_this_month'] = (int) $stmt->fetchColumn();

            // Edits this month
            $stmt = $this->db->getPdo()->prepare('
                SELECT COUNT(*) FROM page_revisions 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ');
            $stmt->execute();
            $stats['edits_this_month'] = (int) $stmt->fetchColumn();

            // Most viewed pages
            $stmt = $this->db->getPdo()->prepare('
                SELECT title, slug, view_count, namespace 
                FROM pages 
                WHERE namespace IN (?, ?, ?) 
                ORDER BY view_count DESC 
                LIMIT 5
            ');
            $stmt->execute(['Main', 'wiki', '']);
            $stats['most_viewed_pages'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Most edited pages
            $stmt = $this->db->getPdo()->prepare('
                SELECT p.title, p.slug, p.namespace, COUNT(pr.id) as edit_count
                FROM pages p
                LEFT JOIN page_revisions pr ON p.id = pr.page_id
                WHERE p.namespace IN (?, ?, ?)
                GROUP BY p.id
                ORDER BY edit_count DESC
                LIMIT 5
            ');
            $stmt->execute(['Main', 'wiki', '']);
            $stats['most_edited_pages'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $stats;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load wiki statistics', [
                    'error' => $e->getMessage()
                ]);
            }
            return [
                'total_pages' => 0,
                'total_revisions' => 0,
                'total_users' => 0,
                'pages_this_month' => 0,
                'edits_this_month' => 0,
                'most_viewed_pages' => [],
                'most_edited_pages' => []
            ];
        }
    }

    /**
     * Get recent changes across all wiki pages.
     *
     * @return array
     */
    private function getRecentChanges(): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT pr.id, pr.page_id, pr.content, pr.created_at, pr.edit_summary,
                       p.title as page_title, p.slug as page_slug, p.namespace,
                       u.username as editor_name
                FROM page_revisions pr
                LEFT JOIN pages p ON pr.page_id = p.id
                LEFT JOIN users u ON pr.user_id = u.id
                ORDER BY pr.created_at DESC
                LIMIT 20
            ');
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load recent changes', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get user contributions for a specific user.
     *
     * @param int $userId
     * @return array
     */
    private function getUserContributions(int $userId): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT pr.id, pr.page_id, pr.content, pr.created_at, pr.edit_summary,
                       p.title as page_title, p.slug as page_slug, p.namespace
                FROM page_revisions pr
                LEFT JOIN pages p ON pr.page_id = p.id
                WHERE pr.user_id = ?
                ORDER BY pr.created_at DESC
                LIMIT 10
            ');
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load user contributions', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get category overview with page counts.
     *
     * @return array
     */
    private function getCategoryOverview(): array
    {
        try {
            // For now, return predefined categories
            // TODO: Implement dynamic category system
            return [
                [
                    'name' => 'Quran',
                    'slug' => 'Category:Quran',
                    'page_count' => 0,
                    'icon' => 'fas fa-book-open',
                    'description' => 'Quranic studies and tafsir'
                ],
                [
                    'name' => 'Hadith',
                    'slug' => 'Category:Hadith',
                    'page_count' => 0,
                    'icon' => 'fas fa-quote-left',
                    'description' => 'Prophetic traditions and narrations'
                ],
                [
                    'name' => 'Islamic Practices',
                    'slug' => 'Category:Islamic_Practices',
                    'page_count' => 0,
                    'icon' => 'fas fa-mosque',
                    'description' => 'Daily Islamic practices and rituals'
                ],
                [
                    'name' => 'Islamic History',
                    'slug' => 'Category:Islamic_History',
                    'page_count' => 0,
                    'icon' => 'fas fa-landmark',
                    'description' => 'Islamic civilization and historical events'
                ],
                [
                    'name' => 'Islamic Sciences',
                    'slug' => 'Category:Islamic_Sciences',
                    'page_count' => 0,
                    'icon' => 'fas fa-graduation-cap',
                    'description' => 'Islamic scholarship and academic disciplines'
                ]
            ];

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load category overview', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get quick actions based on user role and permissions.
     *
     * @param array|null $user
     * @return array
     */
    private function getQuickActions(?array $user): array
    {
        $actions = [
            [
                'title' => 'Create New Page',
                'description' => 'Start a new wiki page',
                'icon' => 'fas fa-plus',
                'url' => '/wiki/create',
                'color' => 'primary',
                'requires_auth' => true
            ],
            [
                'title' => 'Browse Pages',
                'description' => 'Explore existing content',
                'icon' => 'fas fa-list',
                'url' => '/wiki',
                'color' => 'secondary',
                'requires_auth' => false
            ],
            [
                'title' => 'Search Wiki',
                'description' => 'Find specific content',
                'icon' => 'fas fa-search',
                'url' => '/search',
                'color' => 'info',
                'requires_auth' => false
            ],
            [
                'title' => 'Page Templates',
                'description' => 'Use pre-built templates',
                'icon' => 'fas fa-file-alt',
                'url' => '/wiki/templates',
                'color' => 'success',
                'requires_auth' => true
            ]
        ];

        // Add admin-only actions
        if ($user && ($user['is_admin'] || $user['role'] === 'scholar')) {
            $actions[] = [
                'title' => 'Manage Categories',
                'description' => 'Organize wiki content',
                'icon' => 'fas fa-tags',
                'url' => '/wiki/categories',
                'color' => 'warning',
                'requires_auth' => true
            ];
            
            $actions[] = [
                'title' => 'Review Drafts',
                'description' => 'Approve pending content',
                'icon' => 'fas fa-eye',
                'url' => '/wiki/drafts',
                'color' => 'danger',
                'requires_auth' => true
            ];
        }

        return $actions;
    }

    /**
     * Display the template gallery.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function templates(Request $request): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Template gallery requested', [
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Get available templates
            $templates = $this->getAvailableTemplates();

            return $this->view('wiki/templates', [
                'templates' => $templates,
                'title' => 'Page Templates - IslamWiki'
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to load template gallery', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to load template gallery');
        }
    }

    /**
     * Get available page templates.
     *
     * @return array
     */
    private function getAvailableTemplates(): array
    {
        return [
            [
                'name' => 'Article Template',
                'slug' => 'article',
                'description' => 'Standard article format with introduction, sections, and references',
                'icon' => 'fas fa-file-alt',
                'category' => 'Content',
                'preview' => 'Includes title, introduction, main content sections, and references'
            ],
            [
                'name' => 'Guide Template',
                'slug' => 'guide',
                'description' => 'Step-by-step guide or tutorial format',
                'icon' => 'fas fa-book',
                'category' => 'Educational',
                'preview' => 'Includes overview, prerequisites, step-by-step instructions, and tips'
            ],
            [
                'name' => 'Reference Template',
                'slug' => 'reference',
                'description' => 'Reference material with structured information',
                'icon' => 'fas fa-info-circle',
                'category' => 'Reference',
                'preview' => 'Includes definition, details, examples, and related links'
            ],
            [
                'name' => 'Biography Template',
                'slug' => 'biography',
                'description' => 'Biographical information about Islamic figures',
                'icon' => 'fas fa-user',
                'category' => 'Biography',
                'preview' => 'Includes early life, achievements, contributions, and legacy'
            ],
            [
                'name' => 'Quran Template',
                'slug' => 'quran',
                'description' => 'Quranic verse or surah analysis',
                'icon' => 'fas fa-book-open',
                'category' => 'Quran',
                'preview' => 'Includes Arabic text, translation, tafsir, and context'
            ],
            [
                'name' => 'Hadith Template',
                'slug' => 'hadith',
                'description' => 'Hadith narration and analysis',
                'icon' => 'fas fa-quote-left',
                'category' => 'Hadith',
                'preview' => 'Includes Arabic text, translation, chain of narration, and commentary'
            ]
        ];
    }

    /**
     * Display the drafts management page.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function drafts(Request $request): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Drafts page requested', [
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    // Use the full class name for more reliable service resolution
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    if ($this->logger) {
                        $this->logger->warning('Auth service not available for drafts page', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Check if user has permission to view drafts
            if (!$user || (!$user['is_admin'] && $user['role'] !== 'scholar')) {
                throw new HttpException(403, 'Access denied. Admin or Scholar role required.');
            }

            // Get pending drafts
            $pendingDrafts = $this->getPendingDrafts();
            
            // Get user's own drafts
            $userDrafts = [];
            if ($user) {
                $userDrafts = $this->getUserDrafts($user['id']);
            }

            return $this->view('wiki/drafts', [
                'user' => $user,
                'pendingDrafts' => $pendingDrafts,
                'userDrafts' => $userDrafts,
                'title' => 'Draft Management - IslamWiki'
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to load drafts page', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to load drafts page');
        }
    }

    /**
     * Get pending drafts that need approval.
     *
     * @return array
     */
    private function getPendingDrafts(): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT p.id, p.title, p.slug, p.content, p.namespace, p.created_at, p.updated_at,
                       u.username as author_name, u.display_name as author_display_name,
                       p.draft_status, p.draft_notes
                FROM pages p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.draft_status = "pending"
                ORDER BY p.created_at DESC
            ');
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load pending drafts', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get user's own drafts.
     *
     * @param int $userId
     * @return array
     */
    private function getUserDrafts(int $userId): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT p.id, p.title, p.slug, p.content, p.namespace, p.created_at, p.updated_at,
                       p.draft_status, p.draft_notes
                FROM pages p
                WHERE p.user_id = ? AND p.draft_status != "published"
                ORDER BY p.updated_at DESC
            ');
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load user drafts', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Approve a draft for publication.
     *
     * @param Request $request The HTTP request
     * @param int $draftId The draft ID to approve
     * @return Response
     */
    public function approveDraft(Request $request, int $draftId): Response
    {
        try {
            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                }
            }

            // Check permissions
            if (!$user || (!$user['is_admin'] && $user['role'] !== 'scholar')) {
                throw new HttpException(403, 'Access denied. Admin or Scholar role required.');
            }

            // Update draft status
            $stmt = $this->db->getPdo()->prepare('
                UPDATE pages 
                SET draft_status = "published", 
                    published_at = NOW(), 
                    published_by = ?,
                    updated_at = NOW()
                WHERE id = ? AND draft_status = "pending"
            ');
            $result = $stmt->execute([$user['id'], $draftId]);

            if ($result && $stmt->rowCount() > 0) {
                // Log the approval
                if ($this->logger) {
                    $this->logger->info('Draft approved', [
                        'draft_id' => $draftId,
                        'approved_by' => $user['username'],
                        'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                    ]);
                }

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Draft approved successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Draft not found or already processed'
                ], 404);
            }

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to approve draft', [
                    'draft_id' => $draftId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to approve draft');
        }
    }

    /**
     * Reject a draft with feedback.
     *
     * @param Request $request The HTTP request
     * @param int $draftId The draft ID to reject
     * @return Response
     */
    public function rejectDraft(Request $request, int $draftId): Response
    {
        try {
            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                }
            }

            // Check permissions
            if (!$user || (!$user['is_admin'] && $user['role'] !== 'scholar')) {
                throw new HttpException(403, 'Access denied. Admin or Scholar role required.');
            }

            // Get rejection reason from request
            $rejectionReason = $request->getQueryParam('rejection_reason') ?? 'No reason provided';

            // Update draft status
            $stmt = $this->db->getPdo()->prepare('
                UPDATE pages 
                SET draft_status = "rejected", 
                    rejection_reason = ?,
                    rejected_at = NOW(), 
                    rejected_by = ?,
                    updated_at = NOW()
                WHERE id = ? AND draft_status = "pending"
            ');
            $result = $stmt->execute([$rejectionReason, $user['id'], $draftId]);

            if ($result && $stmt->rowCount() > 0) {
                // Log the rejection
                if ($this->logger) {
                    $this->logger->info('Draft rejected', [
                        'draft_id' => $draftId,
                        'rejected_by' => $user['username'],
                        'rejection_reason' => $rejectionReason,
                        'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                    ]);
                }

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Draft rejected successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Draft not found or already processed'
                ], 404);
            }

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to reject draft', [
                    'draft_id' => $draftId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to reject draft');
        }
    }

    /**
     * Get draft statistics for the dashboard.
     *
     * @return array
     */
    private function getDraftStatistics(): array
    {
        try {
            $stats = [
                'total_drafts' => 0,
                'pending_approval' => 0,
                'rejected_drafts' => 0,
                'drafts_this_month' => 0
            ];

            // Total drafts
            $stmt = $this->db->getPdo()->prepare('
                SELECT COUNT(*) FROM pages WHERE draft_status != "published"
            ');
            $stmt->execute();
            $stats['total_drafts'] = (int) $stmt->fetchColumn();

            // Pending approval
            $stmt = $this->db->getPdo()->prepare('
                SELECT COUNT(*) FROM pages WHERE draft_status = "pending"
            ');
            $stmt->execute();
            $stats['pending_approval'] = (int) $stmt->fetchColumn();

            // Rejected drafts
            $stmt = $this->db->getPdo()->prepare('
                SELECT COUNT(*) FROM pages WHERE draft_status = "rejected"
            ');
            $stmt->execute();
            $stats['rejected_drafts'] = (int) $stmt->fetchColumn();

            // Drafts this month
            $stmt = $this->db->getPdo()->prepare('
                SELECT COUNT(*) FROM pages 
                WHERE draft_status != "published" 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ');
            $stmt->execute();
            $stats['drafts_this_month'] = (int) $stmt->fetchColumn();

            return $stats;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load draft statistics', [
                    'error' => $e->getMessage()
                ]);
            }
            return [
                'total_drafts' => 0,
                'pending_approval' => 0,
                'rejected_drafts' => 0,
                'drafts_this_month' => 0
            ];
        }
    }

    /**
     * Helper method to return JSON responses.
     *
     * @param array $data
     * @param int $statusCode
     * @return Response
     */
    private function jsonResponse(array $data, int $statusCode = 200): Response
    {
        return new Response(
            $statusCode,
            ['Content-Type' => 'application/json'],
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Get collaborative editing session for a page.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function getCollaborativeSession(Request $request, string $slug): Response
    {
        try {
            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                }
            }

            if (!$user) {
                throw new HttpException(401, 'Authentication required for collaborative editing');
            }

            // Get page information
            $page = $this->getPageBySlug($slug);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }

            // Get active editing sessions
            $activeSessions = $this->getActiveEditingSessions($page['id']);

            // Get page content with revision history
            $revisions = $this->getPageRevisions($page['id']);

            return $this->jsonResponse([
                'success' => true,
                'page' => $page,
                'active_sessions' => $activeSessions,
                'revisions' => $revisions,
                'current_user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'display_name' => $user['display_name'] ?? $user['username']
                ]
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to get collaborative session', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to get collaborative session');
        }
    }

    /**
     * Join collaborative editing session.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function joinCollaborativeSession(Request $request, string $slug): Response
    {
        try {
            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                }
            }

            if (!$user) {
                throw new HttpException(401, 'Authentication required for collaborative editing');
            }

            // Get page information
            $page = $this->getPageBySlug($slug);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }

            // Create or update editing session
            $sessionId = $this->createEditingSession($page['id'], $user['id']);

            // Log the join action
            if ($this->logger) {
                $this->logger->info('User joined collaborative editing session', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'page_id' => $page['id'],
                    'page_slug' => $slug,
                    'session_id' => $sessionId,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            return $this->jsonResponse([
                'success' => true,
                'session_id' => $sessionId,
                'message' => 'Successfully joined editing session'
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to join collaborative session', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to join collaborative session');
        }
    }

    /**
     * Leave collaborative editing session.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function leaveCollaborativeSession(Request $request, string $slug): Response
    {
        try {
            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                }
            }

            if (!$user) {
                throw new HttpException(401, 'Authentication required');
            }

            // Get page information
            $page = $this->getPageBySlug($slug);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }

            // Remove editing session
            $this->removeEditingSession($page['id'], $user['id']);

            // Log the leave action
            if ($this->logger) {
                $this->logger->info('User left collaborative editing session', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'page_id' => $page['id'],
                    'page_slug' => $slug,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Successfully left editing session'
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to leave collaborative session', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to leave collaborative session');
        }
    }

    /**
     * Save collaborative edit with conflict resolution.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function saveCollaborativeEdit(Request $request, string $slug): Response
    {
        try {
            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                }
            }

            if (!$user) {
                throw new HttpException(401, 'Authentication required for collaborative editing');
            }

            // Get page information
            $page = $this->getPageBySlug($slug);
            if (!$page) {
                throw new HttpException(404, 'Page not found');
            }

            // Get request data
            $content = $request->getQueryParam('content') ?? '';
            $editSummary = $request->getQueryParam('edit_summary') ?? '';
            $baseRevision = $request->getQueryParam('base_revision') ?? '';
            $resolveConflicts = $request->getQueryParam('resolve_conflicts') === 'true';

            if (!$content) {
                throw new HttpException(400, 'Content is required');
            }

            // Check for conflicts
            $conflicts = $this->checkForConflicts($page['id'], $baseRevision, $content);

            if ($conflicts && !$resolveConflicts) {
                return $this->jsonResponse([
                    'success' => false,
                    'conflicts' => true,
                    'conflict_data' => $conflicts,
                    'message' => 'Content conflicts detected. Please resolve conflicts before saving.'
                ], 409);
            }

            // Save the edit
            $revisionId = $this->savePageEdit($page['id'], $user['id'], $content, $editSummary);

            // Remove editing session
            $this->removeEditingSession($page['id'], $user['id']);

            // Log the save action
            if ($this->logger) {
                $this->logger->info('Collaborative edit saved successfully', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'page_id' => $page['id'],
                    'page_slug' => $slug,
                    'revision_id' => $revisionId,
                    'edit_summary' => $editSummary,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            return $this->jsonResponse([
                'success' => true,
                'revision_id' => $revisionId,
                'message' => 'Edit saved successfully'
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to save collaborative edit', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to save collaborative edit');
        }
    }

    /**
     * Get page by slug.
     *
     * @param string $slug
     * @return array|null
     */
    private function getPageBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT id, title, slug, content, namespace, draft_status, created_at, updated_at
                FROM pages 
                WHERE slug = ? AND namespace IN (?, ?, ?)
            ');
            $stmt->execute([$slug, 'Main', 'wiki', '']);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get page by slug', [
                    'slug' => $slug,
                    'error' => $e->getMessage()
                ]);
            }
            return null;
        }
    }

    /**
     * Get active editing sessions for a page.
     *
     * @param int $pageId
     * @return array
     */
    private function getActiveEditingSessions(int $pageId): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT es.id, es.user_id, es.joined_at, es.last_activity,
                       u.username, u.display_name
                FROM editing_sessions es
                LEFT JOIN users u ON es.user_id = u.id
                WHERE es.page_id = ? AND es.last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                ORDER BY es.joined_at ASC
            ');
            $stmt->execute([$pageId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load active editing sessions', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get page revisions.
     *
     * @param int $pageId
     * @return array
     */
    private function getPageRevisions(int $pageId): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT id, content, created_at, edit_summary, user_id
                FROM page_revisions 
                WHERE page_id = ?
                ORDER BY created_at DESC
                LIMIT 10
            ');
            $stmt->execute([$pageId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not load page revisions', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Create or update editing session.
     *
     * @param int $pageId
     * @param int $userId
     * @return int
     */
    private function createEditingSession(int $pageId, int $userId): int
    {
        try {
            // Check if session already exists
            $stmt = $this->db->getPdo()->prepare('
                SELECT id FROM editing_sessions 
                WHERE page_id = ? AND user_id = ?
            ');
            $stmt->execute([$pageId, $userId]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing session
                $stmt = $this->db->getPdo()->prepare('
                    UPDATE editing_sessions 
                    SET last_activity = NOW() 
                    WHERE id = ?
                ');
                $stmt->execute([$existing['id']]);
                return $existing['id'];
            } else {
                // Create new session
                $stmt = $this->db->getPdo()->prepare('
                    INSERT INTO editing_sessions (page_id, user_id, joined_at, last_activity)
                    VALUES (?, ?, NOW(), NOW())
                ');
                $stmt->execute([$pageId, $userId]);
                return (int) $this->db->getPdo()->lastInsertId();
            }

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not create editing session', [
                    'error' => $e->getMessage()
                ]);
            }
            return (int) time(); // Fallback to timestamp if database fails
        }
    }

    /**
     * Remove editing session.
     *
     * @param int $pageId
     * @param int $userId
     * @return bool
     */
    private function removeEditingSession(int $pageId, int $userId): bool
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                DELETE FROM editing_sessions 
                WHERE page_id = ? AND user_id = ?
            ');
            return $stmt->execute([$pageId, $userId]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not remove editing session', [
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }

    /**
     * Check for content conflicts.
     *
     * @param int $pageId
     * @param string $baseRevision
     * @param string $newContent
     * @return array|null
     */
    private function checkForConflicts(int $pageId, string $baseRevision, string $newContent): ?array
    {
        try {
            // Get the latest revision
            $stmt = $this->db->getPdo()->prepare('
                SELECT content, created_at 
                FROM page_revisions 
                WHERE page_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ');
            $stmt->execute([$pageId]);
            $latestRevision = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$latestRevision) {
                return null; // No conflicts if no previous revisions
            }

            // Simple conflict detection (can be enhanced with more sophisticated algorithms)
            if ($latestRevision['content'] !== $baseRevision) {
                return [
                    'base_revision' => $baseRevision,
                    'current_revision' => $latestRevision['content'],
                    'new_content' => $newContent,
                    'conflict_timestamp' => $latestRevision['created_at']
                ];
            }

            return null;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not check for conflicts', [
                    'error' => $e->getMessage()
                ]);
            }
            return null;
        }
    }

    /**
     * Save page edit with revision tracking.
     *
     * @param int $pageId
     * @param int $userId
     * @param string $content
     * @param string $editSummary
     * @return int
     */
    private function savePageEdit(int $pageId, int $userId, string $content, string $editSummary): int
    {
        try {
            // Create new revision
            $stmt = $this->db->getPdo()->prepare('
                INSERT INTO page_revisions (page_id, user_id, content, edit_summary, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$pageId, $userId, $content, $editSummary]);
            $revisionId = $this->db->getPdo()->lastInsertId();
            if ($revisionId === false) {
                throw new \Exception('Failed to get revision ID');
            }

            // Update page content and metadata
            $stmt = $this->db->getPdo()->prepare('
                UPDATE pages 
                SET content = ?, updated_at = NOW(), view_count = view_count + 1
                WHERE id = ?
            ');
            $stmt->execute([$content, $pageId]);

            return $revisionId;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Could not save page edit', [
                    'error' => $e->getMessage()
                ]);
            }
            throw new \Exception('Failed to save page edit');
        }
    }

    /**
     * Handle red links - non-existent pages that users want to create.
     *
     * @param Request $request The HTTP request
     * @param string $slug The requested page slug
     * @return Response
     */
    public function handleRedLink(Request $request, string $slug): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Red link accessed', [
                    'slug' => $slug,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    // Use the full class name for more reliable service resolution
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                    if ($this->logger) {
                        $this->logger->warning('Auth service not available for red link', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Check if user is authenticated
            if (!$user) {
                // Redirect to login with return URL
                $returnUrl = urlencode('/wiki/' . $slug);
                return new Response(
                    302,
                    ['Location' => '/login?return=' . $returnUrl],
                    ''
                );
            }

            // Check if page actually exists (in case it was created between request and response)
            $existingPage = $this->getPageBySlug($slug);
            if ($existingPage) {
                // Page exists now, redirect to it
                return new Response(
                    302,
                    ['Location' => '/wiki/' . $slug],
                    ''
                );
            }

            // Get suggested templates for this type of page
            $suggestedTemplates = $this->getSuggestedTemplates($slug);
            
            // Get related existing pages
            $relatedPages = $this->getRelatedPages($slug);
            
            // Get category suggestions
            $categorySuggestions = $this->getCategorySuggestions($slug);

            return $this->view('wiki/create_from_redlink', [
                'user' => $user,
                'slug' => $slug,
                'title' => $this->slugToTitle($slug),
                'suggestedTemplates' => $suggestedTemplates,
                'relatedPages' => $relatedPages,
                'categorySuggestions' => $categorySuggestions,
                'pageTitle' => 'Create Page - ' . $this->slugToTitle($slug)
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to handle red link', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to handle red link');
        }
    }

    /**
     * Create a new page from a red link.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug to create
     * @return Response
     */
    public function createFromRedLink(Request $request, string $slug): Response
    {
        try {
            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                }
            }

            if (!$user) {
                throw new HttpException(401, 'Authentication required');
            }

            // Get request data
            $title = $request->getQueryParam('title') ?? $this->slugToTitle($slug);
            $content = $request->getQueryParam('content') ?? '';
            $template = $request->getQueryParam('template') ?? '';
            $categories = $request->getQueryParam('categories') ?? [];
            $draftNotes = $request->getQueryParam('draft_notes') ?? '';
            $namespace = $request->getQueryParam('namespace') ?? 'Main';

            if (!$content) {
                throw new HttpException(400, 'Content is required');
            }

            // Check if page already exists
            $existingPage = $this->getPageBySlug($slug);
            if ($existingPage) {
                throw new HttpException(409, 'Page already exists');
            }

            // Determine draft status based on user role
            $draftStatus = 'draft';
            $publishedAt = null;
            $publishedBy = null;

            if ($user['is_admin'] || $user['role'] === 'scholar') {
                $draftStatus = 'published';
                $publishedAt = date('Y-m-d H:i:s');
                $publishedBy = $user['id'];
            }

            // Create the page
            $pageId = $this->createNewPage($slug, $title, $content, $user['id'], $namespace, $draftStatus, $draftNotes, $publishedAt, $publishedBy);

            // Add categories if provided
            if (!empty($categories)) {
                $this->addPageCategories($pageId, $categories);
            }

            // Create initial revision
            $revisionId = $this->createInitialRevision($pageId, $user['id'], $content, 'Page created from red link');

            // Log the creation
            if ($this->logger) {
                $this->logger->info('Page created from red link', [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'page_id' => $pageId,
                    'page_slug' => $slug,
                    'revision_id' => $revisionId,
                    'draft_status' => $draftStatus,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Redirect to the new page
            return new Response(
                302,
                ['Location' => '/wiki/' . $slug],
                ''
            );

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to create page from red link', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to create page from red link');
        }
    }

    /**
     * Get suggested templates for a new page.
     *
     * @param string $slug
     * @return array
     */
    private function getSuggestedTemplates(string $slug): array
    {
        // Analyze the slug to suggest appropriate templates
        $slugLower = strtolower($slug);
        
        $suggestions = [];
        
        if (strpos($slugLower, 'quran') !== false || strpos($slugLower, 'surah') !== false || strpos($slugLower, 'ayah') !== false) {
            $suggestions[] = 'quran';
        }
        
        if (strpos($slugLower, 'hadith') !== false || strpos($slugLower, 'narrated') !== false) {
            $suggestions[] = 'hadith';
        }
        
        if (strpos($slugLower, 'biography') !== false || strpos($slugLower, 'life') !== false || strpos($slugLower, 'story') !== false) {
            $suggestions[] = 'biography';
        }
        
        if (strpos($slugLower, 'guide') !== false || strpos($slugLower, 'how') !== false || strpos($slugLower, 'tutorial') !== false) {
            $suggestions[] = 'guide';
        }
        
        if (strpos($slugLower, 'reference') !== false || strpos($slugLower, 'definition') !== false || strpos($slugLower, 'glossary') !== false) {
            $suggestions[] = 'reference';
        }
        
        // If no specific suggestions, return general templates
        if (empty($suggestions)) {
            $suggestions = ['article', 'guide', 'reference'];
        }
        
        // Get the actual template data
        $allTemplates = $this->getAvailableTemplates();
        $suggestedTemplates = [];
        
        foreach ($suggestions as $suggestion) {
            foreach ($allTemplates as $template) {
                if ($template['slug'] === $suggestion) {
                    $suggestedTemplates[] = $template;
                    break;
                }
            }
        }
        
        return $suggestedTemplates;
    }

    /**
     * Get related existing pages.
     *
     * @param string $slug
     * @return array
     */
    private function getRelatedPages(string $slug): array
    {
        try {
            // Extract keywords from slug
            $keywords = explode('-', $slug);
            $keywords = array_filter($keywords, function($keyword) {
                return strlen($keyword) > 2; // Filter out very short keywords
            });
            
            if (empty($keywords)) {
                return [];
            }
            
            // Search for pages with similar keywords
            $placeholders = str_repeat('?,', count($keywords) - 1) . '?';
            $sql = "
                SELECT id, title, slug, namespace, view_count
                FROM pages 
                WHERE draft_status = 'published'
                AND (
                    " . implode(' OR ', array_fill(0, count($keywords), 'LOWER(title) LIKE ?')) . "
                    OR " . implode(' OR ', array_fill(0, count($keywords), 'LOWER(content) LIKE ?')) . "
                )
                ORDER BY view_count DESC, updated_at DESC
                LIMIT 5
            ";
            
            $params = [];
            foreach ($keywords as $keyword) {
                $params[] = '%' . $keyword . '%';
            }
            foreach ($keywords as $keyword) {
                $params[] = '%' . $keyword . '%';
            }
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get related pages', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get category suggestions for a new page.
     *
     * @param string $slug
     * @return array
     */
    private function getCategorySuggestions(string $slug): array
    {
        try {
            // Extract keywords from slug
            $keywords = explode('-', $slug);
            $keywords = array_filter($keywords, function($keyword) {
                return strlen($keyword) > 2;
            });
            
            if (empty($keywords)) {
                return [];
            }
            
            // Search for categories with similar keywords
            $placeholders = str_repeat('?,', count($keywords) - 1) . '?';
            $sql = "
                SELECT id, name, description, page_count
                FROM categories 
                WHERE (
                    " . implode(' OR ', array_fill(0, count($keywords), 'LOWER(name) LIKE ?')) . "
                    OR " . implode(' OR ', array_fill(0, count($keywords), 'LOWER(description) LIKE ?')) . "
                )
                ORDER BY page_count DESC
                LIMIT 5
            ";
            
            $params = [];
            foreach ($keywords as $keyword) {
                $params[] = '%' . $keyword . '%';
            }
            foreach ($keywords as $keyword) {
                $params[] = '%' . $keyword . '%';
            }
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get category suggestions', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Convert slug to readable title.
     *
     * @param string $slug
     * @return string
     */
    private function slugToTitle(string $slug): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }

    /**
     * Create a new page in the database.
     *
     * @param string $slug
     * @param string $title
     * @param string $content
     * @param int $userId
     * @param string $namespace
     * @param string $draftStatus
     * @param string $draftNotes
     * @param string|null $publishedAt
     * @param int|null $publishedBy
     * @return int
     */
    private function createNewPage(string $slug, string $title, string $content, int $userId, string $namespace, string $draftStatus, string $draftNotes, ?string $publishedAt, ?int $publishedBy): int
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                INSERT INTO pages (slug, title, content, user_id, namespace, draft_status, draft_notes, published_at, published_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([$slug, $title, $content, $userId, $namespace, $draftStatus, $draftNotes, $publishedAt, $publishedBy]);
            
            $pageId = $this->db->getPdo()->lastInsertId();
            if ($pageId === false) {
                throw new \Exception('Failed to get page ID');
            }
            
            return (int) $pageId;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Could not create new page', [
                    'error' => $e->getMessage()
                ]);
            }
            throw new \Exception('Failed to create new page');
        }
    }

    /**
     * Add categories to a page.
     *
     * @param int $pageId
     * @param array $categories
     * @return void
     */
    private function addPageCategories(int $pageId, array $categories): void
    {
        try {
            foreach ($categories as $categoryName) {
                // Get or create category
                $categoryId = $this->getOrCreateCategory($categoryName);
                
                // Add page to category
                $stmt = $this->db->getPdo()->prepare('
                    INSERT INTO page_categories (page_id, category_id) 
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE page_id = page_id
                ');
                $stmt->execute([$pageId, $categoryId]);
            }

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not add page categories', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get or create a category.
     *
     * @param string $categoryName
     * @return int
     */
    private function getOrCreateCategory(string $categoryName): int
    {
        try {
            // Try to get existing category
            $stmt = $this->db->getPdo()->prepare('
                SELECT id FROM categories WHERE name = ?
            ');
            $stmt->execute([$categoryName]);
            $category = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($category) {
                return (int) $category['id'];
            }
            
            // Create new category
            $stmt = $this->db->getPdo()->prepare('
                INSERT INTO categories (name, description, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ');
            $stmt->execute([$categoryName, 'Category for ' . $categoryName]);
            
            $categoryId = $this->db->getPdo()->lastInsertId();
            if ($categoryId === false) {
                throw new \Exception('Failed to get category ID');
            }
            
            return (int) $categoryId;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get or create category', [
                    'error' => $e->getMessage()
                ]);
            }
            throw new \Exception('Failed to get or create category');
        }
    }

    /**
     * Create initial revision for a new page.
     *
     * @param int $pageId
     * @param int $userId
     * @param string $content
     * @param string $editSummary
     * @return int
     */
    private function createInitialRevision(int $pageId, int $userId, string $content, string $editSummary): int
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                INSERT INTO page_revisions (page_id, user_id, title, content, comment, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$pageId, $userId, 'Page created from red link', $content, $editSummary]);
            
            $revisionId = $this->db->getPdo()->lastInsertId();
            if ($revisionId === false) {
                throw new \Exception('Failed to get revision ID');
            }
            
            return (int) $revisionId;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Could not create initial revision', [
                    'error' => $e->getMessage()
                ]);
            }
            throw new \Exception('Failed to create initial revision');
        }
    }

    /**
     * Enhanced search functionality using IqraSearchExtension.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function enhancedSearch(Request $request): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Enhanced search requested', [
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Get search parameters
            $query = $request->getQueryParam('q') ?? '';
            $category = $request->getQueryParam('category') ?? '';
            $namespace = $request->getQueryParam('namespace') ?? '';
            $author = $request->getQueryParam('author') ?? '';
            $dateFrom = $request->getQueryParam('date_from') ?? '';
            $dateTo = $request->getQueryParam('date_to') ?? '';
            $sortBy = $request->getQueryParam('sort_by') ?? 'relevance';
            $page = max(1, (int) ($request->getQueryParam('page') ?? 1));
            $perPage = 20;

            // Get current user for personalized results
            $user = null;
            if (isset($this->container)) {
                try {
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                }
            }

            $searchResults = [];
            $totalResults = 0;
            $searchSuggestions = [];
            $relatedSearches = [];

            if ($query) {
                // Perform enhanced search
                $searchResults = $this->performEnhancedSearch($query, $category, $namespace, $author, $dateFrom, $dateTo, $sortBy, $page, $perPage);
                $totalResults = $this->getSearchResultCount($query, $category, $namespace, $author, $dateFrom, $dateTo);
                
                // Get search suggestions
                $searchSuggestions = $this->getSearchSuggestions($query);
                
                // Get related searches
                $relatedSearches = $this->getRelatedSearches($query);
                
                // Log search query
                if ($this->logger) {
                    $this->logger->info('Search performed', [
                        'query' => $query,
                        'results_count' => count($searchResults),
                        'total_results' => $totalResults,
                        'user_id' => $user ? $user['id'] : null,
                        'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                    ]);
                }
            }

            // Get available categories for search filters
            $availableCategories = $this->getAvailableCategories();
            
            // Get available namespaces
            $availableNamespaces = $this->getAvailableNamespaces();
            
            // Get recent searches for current user
            $recentSearches = [];
            if ($user) {
                $recentSearches = $this->getRecentSearches($user['id']);
            }

            return $this->view('wiki/search', [
                'user' => $user,
                'query' => $query,
                'searchResults' => $searchResults,
                'totalResults' => $totalResults,
                'searchSuggestions' => $searchSuggestions,
                'relatedSearches' => $relatedSearches,
                'availableCategories' => $availableCategories,
                'availableNamespaces' => $availableNamespaces,
                'recentSearches' => $recentSearches,
                'currentFilters' => [
                    'category' => $category,
                    'namespace' => $namespace,
                    'author' => $author,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'sort_by' => $sortBy
                ],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => ceil($totalResults / $perPage)
                ],
                'title' => 'Enhanced Search - IslamWiki'
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to perform enhanced search', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to perform search');
        }
    }

    /**
     * Redirect wiki search to main search functionality.
     *
     * @param Request $request
     * @return Response
     */
    public function redirectToSearch(Request $request): Response
    {
        // Get query parameters from the request URI
        $uri = $request->getServerParams()['REQUEST_URI'] ?? '';
        $queryString = '';
        
        // Extract query string if present
        if (strpos($uri, '?') !== false) {
            $queryString = substr($uri, strpos($uri, '?'));
        }
        
        // Build the redirect URL
        $redirectUrl = '/search' . $queryString;
        
        // Log the redirect for debugging
        if ($this->logger) {
            $this->logger->info('Redirecting wiki search to main search', [
                'original_uri' => $uri,
                'query_string' => $queryString,
                'redirect_url' => $redirectUrl,
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            ]);
        }
        
        return new Response(
            302,
            ['Location' => $redirectUrl],
            ''
        );
    }

    /**
     * Perform enhanced search with multiple filters.
     *
     * @param string $query
     * @param string $category
     * @param string $namespace
     * @param string $author
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $sortBy
     * @param int $page
     * @param int $perPage
     * @return array
     */
    private function performEnhancedSearch(string $query, string $category, string $namespace, string $author, string $dateFrom, string $dateTo, string $sortBy, int $page, int $perPage): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Build the search query
            $sql = "
                SELECT DISTINCT p.id, p.title, p.slug, p.content, p.namespace, p.created_at, p.updated_at,
                       p.view_count, p.draft_status,
                       u.username as author_name, u.display_name as author_display_name,
                       GROUP_CONCAT(DISTINCT c.name) as categories
                FROM pages p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN page_categories pc ON p.id = pc.page_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.draft_status = 'published'
            ";
            
            $params = [];
            $conditions = [];
            
            // Add search query condition
            if ($query) {
                $conditions[] = "(LOWER(p.title) LIKE ? OR LOWER(p.content) LIKE ? OR LOWER(p.slug) LIKE ?)";
                $searchTerm = '%' . strtolower($query) . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Add category filter
            if ($category) {
                $conditions[] = "c.name = ?";
                $params[] = $category;
            }
            
            // Add namespace filter
            if ($namespace) {
                $conditions[] = "p.namespace = ?";
                $params[] = $namespace;
            }
            
            // Add author filter
            if ($author) {
                $conditions[] = "(LOWER(u.username) LIKE ? OR LOWER(u.display_name) LIKE ?)";
                $authorTerm = '%' . strtolower($author) . '%';
                $params[] = $authorTerm;
                $params[] = $authorTerm;
            }
            
            // Add date range filters
            if ($dateFrom) {
                $conditions[] = "p.created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }
            
            if ($dateTo) {
                $conditions[] = "p.created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }
            
            if (!empty($conditions)) {
                $sql .= " AND " . implode(' AND ', $conditions);
            }
            
            $sql .= " GROUP BY p.id";
            
            // Add sorting
            switch ($sortBy) {
                case 'title':
                    $sql .= " ORDER BY p.title ASC";
                    break;
                case 'date':
                    $sql .= " ORDER BY p.created_at DESC";
                    break;
                case 'views':
                    $sql .= " ORDER BY p.view_count DESC";
                    break;
                case 'relevance':
                default:
                    // Relevance scoring based on title match, content match, and view count
                    $sql .= " ORDER BY 
                        CASE 
                            WHEN LOWER(p.title) LIKE ? THEN 100
                            WHEN LOWER(p.slug) LIKE ? THEN 80
                            ELSE 0
                        END DESC,
                        p.view_count DESC,
                        p.created_at DESC";
                    $titleExact = strtolower($query);
                    $params[] = $titleExact;
                    $params[] = $titleExact;
                    break;
            }
            
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Process results to add search highlights and snippets
            foreach ($results as &$result) {
                $result['search_snippet'] = $this->generateSearchSnippet($result['content'], $query);
                $result['search_highlights'] = $this->generateSearchHighlights($result['title'] . ' ' . $result['content'], $query);
                $result['relevance_score'] = $this->calculateRelevanceScore($result, $query);
            }
            
            // Sort by relevance score if relevance sorting is selected
            if ($sortBy === 'relevance') {
                usort($results, function($a, $b) {
                    return $b['relevance_score'] <=> $a['relevance_score'];
                });
            }
            
            return $results;

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not perform enhanced search', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get total count of search results.
     *
     * @param string $query
     * @param string $category
     * @param string $namespace
     * @param string $author
     * @param string $dateFrom
     * @param string $dateTo
     * @return int
     */
    private function getSearchResultCount(string $query, string $category, string $namespace, string $author, string $dateFrom, string $dateTo): int
    {
        try {
            $sql = "
                SELECT COUNT(DISTINCT p.id) as total
                FROM pages p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN page_categories pc ON p.id = pc.page_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.draft_status = 'published'
            ";
            
            $params = [];
            $conditions = [];
            
            if ($query) {
                $conditions[] = "(LOWER(p.title) LIKE ? OR LOWER(p.content) LIKE ? OR LOWER(p.slug) LIKE ?)";
                $searchTerm = '%' . strtolower($query) . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if ($category) {
                $conditions[] = "c.name = ?";
                $params[] = $category;
            }
            
            if ($namespace) {
                $conditions[] = "p.namespace = ?";
                $params[] = $namespace;
            }
            
            if ($author) {
                $conditions[] = "(LOWER(u.username) LIKE ? OR LOWER(u.display_name) LIKE ?)";
                $authorTerm = '%' . strtolower($author) . '%';
                $params[] = $authorTerm;
                $params[] = $authorTerm;
            }
            
            if ($dateFrom) {
                $conditions[] = "p.created_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }
            
            if ($dateTo) {
                $conditions[] = "p.created_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }
            
            if (!empty($conditions)) {
                $sql .= " AND " . implode(' AND ', $conditions);
            }
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return (int) ($result['total'] ?? 0);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get search result count', [
                    'error' => $e->getMessage()
                ]);
            }
            return 0;
        }
    }

    /**
     * Generate search snippet from content.
     *
     * @param string $content
     * @param string $query
     * @return string
     */
    private function generateSearchSnippet(string $content, string $query): string
    {
        $content = strip_tags($content);
        $queryLower = strtolower($query);
        $contentLower = strtolower($content);
        
        $position = strpos($contentLower, $queryLower);
        if ($position === false) {
            // If exact match not found, return first 200 characters
            return substr($content, 0, 200) . '...';
        }
        
        $start = max(0, $position - 100);
        $length = 200;
        
        $snippet = substr($content, $start, $length);
        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($start + $length < strlen($content)) {
            $snippet .= '...';
        }
        
        return $snippet;
    }

    /**
     * Generate search highlights.
     *
     * @param string $text
     * @param string $query
     * @return array
     */
    private function generateSearchHighlights(string $text, string $query): array
    {
        $highlights = [];
        $queryLower = strtolower($query);
        $textLower = strtolower($text);
        
        $position = 0;
        while (($pos = strpos($textLower, $queryLower, $position)) !== false) {
            $highlights[] = [
                'start' => $pos,
                'end' => $pos + strlen($queryLower),
                'text' => substr($text, $pos, strlen($queryLower))
            ];
            $position = $pos + 1;
        }
        
        return $highlights;
    }

    /**
     * Calculate relevance score for search results.
     *
     * @param array $result
     * @param string $query
     * @return float
     */
    private function calculateRelevanceScore(array $result, string $query): float
    {
        $score = 0;
        $queryLower = strtolower($query);
        
        // Title match (highest weight)
        if (strpos(strtolower($result['title']), $queryLower) !== false) {
            $score += 100;
        }
        
        // Slug match
        if (strpos(strtolower($result['slug']), $queryLower) !== false) {
            $score += 80;
        }
        
        // Content match
        $contentMatches = substr_count(strtolower($result['content']), $queryLower);
        $score += $contentMatches * 10;
        
        // View count bonus (small weight)
        $score += min($result['view_count'] / 100, 10);
        
        // Recency bonus (small weight)
        $daysOld = (time() - strtotime($result['created_at'])) / (24 * 60 * 60);
        $score += max(0, 10 - $daysOld / 30);
        
        return $score;
    }

    /**
     * Get search suggestions based on query.
     *
     * @param string $query
     * @return array
     */
    private function getSearchSuggestions(string $query): array
    {
        try {
            $queryLower = strtolower($query);
            
            // Get suggestions from page titles
            $stmt = $this->db->getPdo()->prepare('
                SELECT DISTINCT title, slug
                FROM pages 
                WHERE draft_status = "published" 
                AND LOWER(title) LIKE ?
                ORDER BY view_count DESC
                LIMIT 5
            ');
            $stmt->execute(['%' . $queryLower . '%']);
            $titleSuggestions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get suggestions from categories
            $stmt = $this->db->getPdo()->prepare('
                SELECT DISTINCT name, description
                FROM categories 
                WHERE LOWER(name) LIKE ? OR LOWER(description) LIKE ?
                ORDER BY page_count DESC
                LIMIT 3
            ');
            $stmt->execute(['%' . $queryLower . '%', '%' . $queryLower . '%']);
            $categorySuggestions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'titles' => $titleSuggestions,
                'categories' => $categorySuggestions
            ];

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get search suggestions', [
                    'error' => $e->getMessage()
                ]);
            }
            return ['titles' => [], 'categories' => []];
        }
    }

    /**
     * Get related searches based on query.
     *
     * @param string $query
     * @return array
     */
    private function getRelatedSearches(string $query): array
    {
        try {
            $queryLower = strtolower($query);
            $keywords = explode(' ', $queryLower);
            $keywords = array_filter($keywords, function($keyword) {
                return strlen($keyword) > 2;
            });
            
            if (empty($keywords)) {
                return [];
            }
            
            // Find pages with similar keywords
            $placeholders = str_repeat('?,', count($keywords) - 1) . '?';
            $sql = "
                SELECT DISTINCT title, slug, view_count
                FROM pages 
                WHERE draft_status = 'published'
                AND (
                    " . implode(' OR ', array_fill(0, count($keywords), 'LOWER(title) LIKE ?')) . "
                )
                AND LOWER(title) != ?
                ORDER BY view_count DESC
                LIMIT 5
            ";
            
            $params = [];
            foreach ($keywords as $keyword) {
                $params[] = '%' . $keyword . '%';
            }
            $params[] = $queryLower;
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get related searches', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get available categories for search filters.
     *
     * @return array
     */
    private function getAvailableCategories(): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT c.id, c.name, c.description, COUNT(pc.page_id) as page_count
                FROM categories c
                LEFT JOIN page_categories pc ON c.id = pc.category_id
                LEFT JOIN pages p ON pc.page_id = p.id AND p.draft_status = "published"
                GROUP BY c.id
                HAVING page_count > 0
                ORDER BY page_count DESC
            ');
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get available categories', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get available namespaces for search filters.
     *
     * @return array
     */
    private function getAvailableNamespaces(): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT DISTINCT namespace, COUNT(*) as page_count
                FROM pages 
                WHERE draft_status = "published"
                GROUP BY namespace
                ORDER BY page_count DESC
            ');
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get available namespaces', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get recent searches for a user.
     *
     * @param int $userId
     * @return array
     */
    private function getRecentSearches(int $userId): array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT query, created_at, result_count
                FROM search_history 
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 10
            ');
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->warning('Could not get recent searches', [
                    'error' => $e->getMessage()
                ]);
            }
            return [];
        }
    }

    /**
     * Get localized content for the page
     */
    private function getLocalizedContent(Page $page, string $locale): string
    {
        // For now, return the main content
        // In the future, this could check for translations
        return $page->getAttribute('content') ?? '';
    }

    /**
     * Get available languages for the page
     */
    private function getAvailableLanguages(Page $page): array
    {
        // For now, return supported languages
        // In the future, this could check for actual translations
        return [
            'en' => 'English',
            'ar' => 'العربية'
        ];
    }

    /**
     * Show the create page interface
     */
    public function showCreatePage(Request $request): Response
    {
        try {
            // Get current user
            $user = null;
            if (isset($this->container)) {
                try {
                    // Use the full class name for more reliable service resolution
                    $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                    if ($auth && method_exists($auth, 'user')) {
                        $user = $auth->user();
                    }
                } catch (\Exception $e) {
                    // Auth service not available
                    if ($this->logger) {
                        $this->logger->warning('Auth service not available for create page', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Allow all users to view the create page interface
            // Authentication will be required when they actually try to create a page
            return $this->view('wiki/create_page', [
                'user' => $user,
                'pageTitle' => 'Create New Page - IslamWiki'
            ]);

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show create page interface', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new HttpException(500, 'Failed to show create page interface');
        }
    }

    /**
     * Show a specific revision of a page
     */
    public function showRevision(Request $request, string $slug, int $revisionId): Response
    {
        try {
            $page = \IslamWiki\Models\Page::findBySlug($slug, $this->db);
            if (!$page) {
                throw new \IslamWiki\Core\Http\Exceptions\HttpException(404, 'Page not found');
            }

            // Get the specific revision
            $stmt = $this->db->prepare("
                SELECT r.*, u.username, u.display_name, u.role
                FROM revisions r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.page_id = ? AND r.id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$page->id, $revisionId]);
            $revision = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$revision) {
                throw new \IslamWiki\Core\Http\Exceptions\HttpException(404, 'Revision not found');
            }

            // Get current revision ID for comparison
            $currentRevisionId = $page->getAttribute('revision_id');

            return $this->view('wiki/revision', [
                'page' => $page,
                'revision' => $revision,
                'current_revision_id' => $currentRevisionId
            ]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Error in showRevision method', [
                    'slug' => $slug,
                    'revision_id' => $revisionId,
                    'error' => $e->getMessage()
                ]);
            }
            throw new \IslamWiki\Core\Http\Exceptions\HttpException(500, 'Internal server error while retrieving revision');
        }
    }

    /**
     * Test method to trigger enhanced error handling
     */
    public function testError(Request $request): Response
    {
        try {
            // Intentionally cause a database error to test our enhanced error handling
            // This will trigger our enhanced error handling when an error occurs
            $stmt = $this->db->prepare("SELECT * FROM non_existent_table");
            $stmt->execute();
            
            // If we get here, no error occurred, so let's cause one
            throw new \Exception('Test error from WikiController to demonstrate enhanced error handling');
            
        } catch (\Exception $e) {
            // This will trigger our enhanced error handling
            $errorDetails = $this->generateDetailedErrorReport($e, 'Test Error Method');
            
            // Store debug info in session for error handler to access
            $_SESSION['debug_error_info'] = $errorDetails;
            
            // Throw the exception to trigger the error handler
            throw $e;
        }
    }

    /**
     * Generate a comprehensive error report for debugging
     *
     * @param \Exception $e
     * @param string $context
     * @return array
     */
    private function generateDetailedErrorReport(\Exception $e, string $context): array
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'context' => $context,
            'error_type' => get_class($e),
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString(),
            'request_info' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'http_host' => $_SERVER['HTTP_HOST'] ?? 'UNKNOWN'
            ],
            'session_info' => [
                'session_id' => session_id() ?: 'NO_SESSION',
                'session_status' => session_status(),
                'session_data' => $_SESSION ?? [],
                'session_name' => session_name()
            ],
            'authentication_info' => $this->getAuthenticationDebugInfo(),
            'database_info' => $this->getDatabaseDebugInfo(),
            'container_info' => $this->getContainerDebugInfo(),
            'memory_usage' => [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'memory_limit' => ini_get('memory_limit')
            ],
            'php_info' => [
                'php_version' => PHP_VERSION,
                'extensions' => get_loaded_extensions(),
                'error_reporting' => error_reporting(),
                'display_errors' => ini_get('display_errors'),
                'log_errors' => ini_get('log_errors')
            ]
        ];

        return $report;
    }

    /**
     * Get authentication debugging information
     *
     * @return array
     */
    private function getAuthenticationDebugInfo(): array
    {
        $info = [
            'auth_service_available' => false,
            'user_data' => null,
            'session_user_data' => null
        ];

        try {
            if (isset($this->container)) {
                $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
                $info['auth_service_available'] = ($auth !== null);
                
                if ($auth && method_exists($auth, 'user')) {
                    $user = $auth->user();
                    $info['user_data'] = $user;
                }
            }
        } catch (\Exception $e) {
            $info['auth_error'] = $e->getMessage();
        }

        // Get session user data directly
        $info['session_user_data'] = [
            'user_id' => $_SESSION['user_id'] ?? 'NOT_SET',
            'username' => $_SESSION['username'] ?? 'NOT_SET',
            'is_admin' => $_SESSION['is_admin'] ?? 'NOT_SET',
            'logged_in_at' => $_SESSION['logged_in_at'] ?? 'NOT_SET'
        ];

        return $info;
    }

    /**
     * Get database debugging information
     *
     * @return array
     */
    private function getDatabaseDebugInfo(): array
    {
        $info = [
            'database_available' => false,
            'connection_status' => 'UNKNOWN',
            'tables_exist' => []
        ];

        try {
            if (isset($this->container) && isset($this->db)) {
                $info['database_available'] = true;
                
                // Check if we can connect
                $pdo = $this->db->getPdo();
                $info['connection_status'] = 'CONNECTED';
                
                // Check if key tables exist
                $tables = ['users', 'pages', 'page_revisions', 'sessions'];
                foreach ($tables as $table) {
                    try {
                        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                        $info['tables_exist'][$table] = ($stmt->rowCount() > 0);
                    } catch (\Exception $e) {
                        $info['tables_exist'][$table] = false;
                    }
                }
            }
        } catch (\Exception $e) {
            $info['database_error'] = $e->getMessage();
        }

        return $info;
    }

    /**
     * Get container debugging information
     *
     * @return array
     */
    private function getContainerDebugInfo(): array
    {
        $info = [
            'container_available' => false,
            'services' => [],
            'providers' => []
        ];

        try {
            if (isset($this->container)) {
                $info['container_available'] = true;
                
                // Check if key services are available
                $services = ['auth', 'session', 'db', 'logger', 'view'];
                foreach ($services as $service) {
                    try {
                        $serviceInstance = $this->container->get($service);
                        $info['services'][$service] = [
                            'available' => true,
                            'class' => get_class($serviceInstance)
                        ];
                    } catch (\Exception $e) {
                        $info['services'][$service] = [
                            'available' => false,
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $info['container_error'] = $e->getMessage();
        }

        return $info;
    }
}