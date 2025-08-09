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
     * Display the wiki index page.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function index(Request $request): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Wiki index requested', [
                    'query' => $request->getQueryParams(),
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
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

            // Use pages/index template for now until wiki templates are created
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
     * Show the wiki page.
     *
     * @param Request $request The HTTP request
     * @param string $slug The page slug
     * @return Response
     */
    public function show(Request $request, string $slug): Response
    {
        try {
            if ($this->logger) {
                $this->logger->info('Wiki page view requested', [
                    'slug' => $slug,
                    'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]);
            }

            // Namespace-aware dispatch for MediaWiki-style prefixes inside /wiki/{slug}
            [$ns, $titlePart] = NamespaceManager::parseTitle($slug);
            if (NamespaceManager::isSpecial($ns)) {
                return $this->redirect('/Special:' . $titlePart, 302);
            }
            if ($ns === 'Quran') {
                return $this->redirect('/quran/search?q=' . urlencode($titlePart), 302);
            }
            if ($ns === 'Hadith') {
                return $this->redirect('/hadith/search?q=' . urlencode($titlePart), 302);
            }

            // First try to find the page with wiki namespace
            $wikiSlug = self::WIKI_NAMESPACE . ':' . $slug;
            $page = Page::findBySlug($wikiSlug, $this->db);

            // If not found, try without namespace (for regular pages)
            if (!$page) {
                $page = Page::findBySlug($slug, $this->db);

                // If still not found, try with Main namespace
                if (!$page) {
                    $mainSlug = 'Main:' . $slug;
                    $page = Page::findBySlug($mainSlug, $this->db);
                }
            }

            if (!$page) {
                // Check if user has permission to create pages
                $canCreate = $this->canCreatePage($request);

                if ($canCreate) {
                    return $this->redirect("/wiki/create?title=" . urlencode($slug), 302);
                }

                throw new HttpException(404, 'The requested page was not found.');
            }

            // Check if the page is locked and user has permission to view
            if ($page->isLocked()) {
                $user = $this->user($request);
                $isAdmin = $user ? $this->isAdmin($request) : false;

                if (!$isAdmin) {
                    if ($this->logger) {
                        $this->logger->warning('Attempt to access locked page', [
                            'page_id' => $page->getAttribute('id'),
                            'slug' => $slug,
                            'user_id' => $user ? $user['id'] : 'guest',
                        ]);
                    }

                    return $this->view('errors.403', [
                        'message' => 'This page is currently locked and cannot be viewed.',
                        'title' => 'Access Denied',
                        'show_login' => !$user,
                        'can_request_access' => $user !== null,
                    ], 403);
                }
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
                    if ($this->logger) {
                        $this->logger->error('Failed to update page view count', [
                            'page_id' => $page->getAttribute('id'),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Get page revisions and attach author name for latest revision
            $revisions = $page->revisions();
            $latestRevision = $revisions[0] ?? null;
            if ($latestRevision) {
                $authorUsername = null;
                try {
                    $stmt = $this->db->getPdo()->prepare('SELECT username FROM users WHERE id = ? LIMIT 1');
                    $stmt->execute([$latestRevision->getAttribute('user_id')]);
                    $authorUsername = $stmt->fetchColumn();
                } catch (\Exception $e) {
                    // ignore, will fallback to Unknown
                }
                $latestRevision->setAttribute('author_name', $authorUsername ?: 'Unknown');
            }

            // Parse wiki text content
            $content = $this->parseWikiText($page->getAttribute('content'));

            if ($this->logger) {
                $this->logger->info('Wiki page displayed successfully', [
                    'page_id' => $page->getAttribute('id'),
                    'title' => $page->getAttribute('title'),
                    'namespace' => $page->getAttribute('namespace'),
                    'revision_count' => count($revisions),
                    'view_count' => $page->getAttribute('view_count') + 1,
                ]);
            }

            return $this->displayWikiPage($request, $page);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to display wiki page', [
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            throw new HttpException(500, 'An error occurred while loading the page. Please try again later.');
        }
    }

    /**
     * Show the wiki page creation form.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $this->logger->info('Wiki page creation form requested', [
            'query' => $request->getQueryParams(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
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

                    return $this->redirect("/wiki/{$title}/edit")
                        ->with('info', 'This wiki page already exists. You are now editing the existing page.');
                }
            }

            $this->logger->debug('Wiki page creation form displayed', [
                'title' => $title,
                'namespace' => self::WIKI_NAMESPACE,
            ]);

            // Use pages/edit template for now until wiki templates are created
            return $this->view('pages/edit', [
                'title' => $title,
                'namespace' => self::WIKI_NAMESPACE,
                'content' => '',
                'isNew' => true,
                'canEdit' => true,
                'canDelete' => false,
                'canLock' => $this->isAdmin($request),
                'user' => $this->user($request),
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
    public function store(Request $request): Response
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
    public function edit(Request $request, string $slug): Response
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
    public function update(Request $request, string $slug): Response
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
    public function history(Request $request, string $slug): Response
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
    public function destroy(Request $request, string $slug): Response
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
    public function watch(Request $request, string $slug): Response
    {
        // Placeholder watch logic (no-op)
        return $this->redirect("/wiki/{$slug}")
            ->with('success', 'Page added to your watchlist.');
    }

    public function unwatch(Request $request, string $slug): Response
    {
        // Placeholder unwatch logic (no-op)
        return $this->redirect("/wiki/{$slug}")
            ->with('success', 'Page removed from your watchlist.');
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

        // Update view count for wiki pages
        $this->updatePageViewCount($page, $request, $userId);

        // Get page revisions
        $revisions = $page->revisions();
        $latestRevision = $revisions[0] ?? null;

        // Parse wiki text content
        $content = $this->parseWikiText($page->getAttribute('content'));

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
            'title' => $page->getAttribute('title') . ' - Wiki - IslamWiki',
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
        // Skip view count increment for AJAX requests
        $skipViewCount = $request->isXmlHttpRequest() ||
                        $request->hasHeader('X-PJAX') ||
                        $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        if (!$skipViewCount) {
            try {
                $this->db->beginTransaction();

                $currentPage = $this->db->table('pages')
                    ->where('id', '=', $page->getAttribute('id'))
                    ->first(['view_count']);

                $newViewCount = ($currentPage['view_count'] ?? 0) + 1;

                $this->db->table('pages')
                    ->where('id', '=', $page->getAttribute('id'))
                    ->update([
                        'view_count' => $newViewCount,
                        'last_viewed_at' => date('Y-m-d H:i:s'),
                        'last_viewed_by' => $userId,
                    ]);

                $this->db->commit();

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
    public function apiIndex(Request $request): Response
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

    public function apiShow(Request $request, string $slug): Response
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

    public function apiStore(Request $request): Response
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

    public function apiUpdate(Request $request, string $slug): Response
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

    public function apiDestroy(Request $request, string $slug): Response
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
}
