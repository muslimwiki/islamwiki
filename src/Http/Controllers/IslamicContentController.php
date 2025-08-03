<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use IslamWiki\Models\IslamicPage;
use IslamWiki\Models\IslamicUser;
use IslamWiki\Models\Revision;
use Psr\Log\LoggerInterface;

/**
 * Islamic Content Controller
 * 
 * Enhanced content controller with Islamic-specific features:
 * - Islamic content creation and editing
 * - Scholar verification workflow
 * - Content moderation system
 * - Islamic templates and formatting
 * - Quality control and scoring
 */
class IslamicContentController extends PageController
{
    /**
     * Create a new Islamic content controller instance.
     */
    public function __construct(
        \IslamWiki\Core\Database\Connection $db,
        \IslamWiki\Core\Container\Asas $container
    ) {
        parent::__construct($db, $container);
    }

    /**
     * Display the Islamic content index page.
     */
    public function index(): Response
    {
        try {
            // Get search and filter parameters
            $search = $_GET['q'] ?? '';
            $category = $_GET['category'] ?? '';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 12;
            
            // Build query
            $query = $this->db->table('islamic_pages')
                ->select([
                    'id', 'title', 'arabic_title', 'content', 'arabic_content',
                    'islamic_category', 'islamic_template', 'islamic_tags',
                    'moderation_status', 'verification_status', 'created_at',
                    'updated_at', 'author_id', 'view_count', 'quality_score'
                ])
                ->where('moderation_status', 'approved')
                ->where('verification_status', 'verified');
            
            // Apply search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('arabic_title', 'LIKE', "%{$search}%")
                      ->orWhere('content', 'LIKE', "%{$search}%")
                      ->orWhere('arabic_content', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply category filter
            if (!empty($category)) {
                $query->where('islamic_category', $category);
            }
            
            // Get total count for pagination
            $totalContent = $query->count();
            $totalPages = ceil($totalContent / $perPage);
            
            // Get paginated results
            $content = $query->orderBy('created_at', 'desc')
                            ->offset(($page - 1) * $perPage)
                            ->limit($perPage)
                            ->get();
            
            // Enhance content data
            foreach ($content as &$item) {
                $item['excerpt'] = $this->generateExcerpt($item['content']);
                $item['read_time'] = $this->calculateReadTime($item['content']);
                $item['author'] = $this->getAuthorInfo($item['author_id']);
                $item['tags'] = json_decode($item['islamic_tags'] ?? '[]', true);
            }
            
            // Get category statistics
            $categories = $this->getCategoryStats();
            
            // Get featured content
            $featuredContent = $this->getFeaturedContent();
            
            // Get recent articles
            $recentArticles = $this->getRecentArticles(5);
            
            return $this->view('content/index', [
                'content' => $content,
                'categories' => $categories,
                'featured_content' => $featuredContent,
                'recent_articles' => $recentArticles,
                'search_query' => $search,
                'current_category' => $category,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_content' => $totalContent,
                    'per_page' => $perPage
                ],
                'title' => 'Islamic Content - IslamWiki'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Content index error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load Islamic content', 500);
        }
    }

    /**
     * Show Islamic content creation form.
     */
    public function showIslamicCreate(Request $request): Response
    {
        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('create_pages')) {
            return $this->redirect('/islamic-login?error=Please log in to create Islamic content');
        }

        return $this->view('pages/islamic_create', [
            'title' => 'Create Islamic Content - IslamWiki',
            'user' => $user,
            'csrf_token' => $this->session->getCsrfToken(),
            'islamic_categories' => IslamicPage::getIslamicCategories(),
            'islamic_templates' => IslamicPage::getIslamicTemplates(),
            'moderation_statuses' => IslamicPage::getModerationStatuses(),
        ]);
    }

    /**
     * Store new Islamic content.
     */
    public function storeIslamicContent(Request $request): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/islamic-content/create?error=Invalid security token');
        }

        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('create_pages')) {
            return $this->redirect('/islamic-login?error=Please log in to create Islamic content');
        }

        // Get form data
        $title = $request->getPostParam('title');
        $arabicTitle = $request->getPostParam('arabic_title');
        $content = $request->getPostParam('content');
        $arabicContent = $request->getPostParam('arabic_content');
        $islamicCategory = $request->getPostParam('islamic_category');
        $islamicTemplate = $request->getPostParam('islamic_template');
        $islamicTags = $request->getPostParam('islamic_tags', []);
        $moderationStatus = $request->getPostParam('moderation_status', 'draft');
        $revisionComment = $request->getPostParam('revision_comment', '');

        // Validate required fields
        if (empty($title) || empty($content)) {
            return $this->redirect('/islamic-content/create?error=Please fill in all required fields');
        }

        // Create Islamic page
        $page = new IslamicPage($this->db, [
            'title' => $title,
            'arabic_title' => $arabicTitle,
            'content' => $content,
            'arabic_content' => $arabicContent,
            'islamic_category' => $islamicCategory,
            'islamic_template' => $islamicTemplate,
            'islamic_tags' => json_encode($islamicTags),
            'moderation_status' => $moderationStatus,
            'revision_comment' => $revisionComment,
            'namespace' => $request->getPostParam('namespace', 'main'),
            'content_format' => 'markdown',
        ]);

        // Generate slug
        $slug = $this->generateSlug($page->getAttribute('namespace'), $title);
        $page->setAttribute('slug', $slug);

        // Set Islamic permissions based on user role
        $permissions = $this->getIslamicPermissionsForUser($user);
        $page->setIslamicPermissions($permissions);

        // Save page
        if (!$page->save()) {
            return $this->redirect('/islamic-content/create?error=Failed to create Islamic content');
        }

        // Create revision
        $page->createRevision($user->getAttribute('id'), $revisionComment);

        // If scholar, auto-verify
        if ($user->isScholar() && $user->hasIslamicPermission('verify_content')) {
            $page->verifyByScholar($user->getAttribute('id'), 'Auto-verified by scholar');
        }

        return $this->redirect("/islamic-content/{$slug}?success=Islamic content created successfully");
    }

    /**
     * Show Islamic content editing form.
     */
    public function showIslamicEdit(Request $request, string $slug): Response
    {
        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('edit_pages')) {
            return $this->redirect('/islamic-login?error=Please log in to edit Islamic content');
        }

        $page = IslamicPage::findBySlug($slug, $this->db);
        if (!$page) {
            throw new HttpException(404, 'Islamic content not found');
        }

        // Check if user can edit this page
        if (!$this->canEditIslamicPage($page, $user)) {
            return $this->redirect("/islamic-content/{$slug}?error=You do not have permission to edit this content");
        }

        return $this->view('pages/islamic_edit', [
            'title' => "Edit {$page->getAttribute('title')} - IslamWiki",
            'page' => $page,
            'user' => $user,
            'csrf_token' => $this->session->getCsrfToken(),
            'islamic_categories' => IslamicPage::getIslamicCategories(),
            'islamic_templates' => IslamicPage::getIslamicTemplates(),
            'moderation_statuses' => IslamicPage::getModerationStatuses(),
        ]);
    }

    /**
     * Update Islamic content.
     */
    public function updateIslamicContent(Request $request, string $slug): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect("/islamic-content/{$slug}/edit?error=Invalid security token");
        }

        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('edit_pages')) {
            return $this->redirect('/islamic-login?error=Please log in to edit Islamic content');
        }

        $page = IslamicPage::findBySlug($slug, $this->db);
        if (!$page) {
            throw new HttpException(404, 'Islamic content not found');
        }

        // Check if user can edit this page
        if (!$this->canEditIslamicPage($page, $user)) {
            return $this->redirect("/islamic-content/{$slug}?error=You do not have permission to edit this content");
        }

        // Get form data
        $title = $request->getPostParam('title');
        $arabicTitle = $request->getPostParam('arabic_title');
        $content = $request->getPostParam('content');
        $arabicContent = $request->getPostParam('arabic_content');
        $islamicCategory = $request->getPostParam('islamic_category');
        $islamicTemplate = $request->getPostParam('islamic_template');
        $islamicTags = $request->getPostParam('islamic_tags', []);
        $moderationStatus = $request->getPostParam('moderation_status');
        $revisionComment = $request->getPostParam('revision_comment', '');

        // Validate required fields
        if (empty($title) || empty($content)) {
            return $this->redirect("/islamic-content/{$slug}/edit?error=Please fill in all required fields");
        }

        // Update page
        $page->setAttribute('title', $title);
        $page->setAttribute('arabic_title', $arabicTitle);
        $page->setAttribute('content', $content);
        $page->setAttribute('arabic_content', $arabicContent);
        $page->setAttribute('islamic_category', $islamicCategory);
        $page->setAttribute('islamic_template', $islamicTemplate);
        $page->setAttribute('islamic_tags', json_encode($islamicTags));
        
        // Update moderation status if user has permission
        if ($user->hasIslamicPermission('moderate_comments')) {
            $page->setAttribute('moderation_status', $moderationStatus);
        }

        if (!$page->save()) {
            return $this->redirect("/islamic-content/{$slug}/edit?error=Failed to update Islamic content");
        }

        // Create revision
        $page->createRevision($user->getAttribute('id'), $revisionComment);

        return $this->redirect("/islamic-content/{$slug}?success=Islamic content updated successfully");
    }

    /**
     * Show Islamic content moderation queue.
     */
    public function showModerationQueue(Request $request): Response
    {
        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('moderate_comments')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        // Get pages that need moderation
        $pages = $this->db->table('pages')
            ->select(['*'])
            ->where('moderation_status', 'in', ['pending', 'needs_revision'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->view('pages/islamic_moderation_queue', [
            'title' => 'Islamic Content Moderation - IslamWiki',
            'user' => $user,
            'pages' => $pages,
            'csrf_token' => $this->session->getCsrfToken(),
        ]);
    }

    /**
     * Approve Islamic content.
     */
    public function approveIslamicContent(Request $request, string $slug): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/islamic-moderation?error=Invalid security token');
        }

        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('moderate_comments')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        $page = IslamicPage::findBySlug($slug, $this->db);
        if (!$page) {
            return $this->redirect('/islamic-moderation?error=Content not found');
        }

        $notes = $request->getPostParam('notes', '');

        if ($page->approve($user->getAttribute('id'), $notes)) {
            return $this->redirect('/islamic-moderation?success=Content approved successfully');
        } else {
            return $this->redirect('/islamic-moderation?error=Failed to approve content');
        }
    }

    /**
     * Reject Islamic content.
     */
    public function rejectIslamicContent(Request $request, string $slug): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/islamic-moderation?error=Invalid security token');
        }

        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('moderate_comments')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        $page = IslamicPage::findBySlug($slug, $this->db);
        if (!$page) {
            return $this->redirect('/islamic-moderation?error=Content not found');
        }

        $reason = $request->getPostParam('reason');
        if (empty($reason)) {
            return $this->redirect('/islamic-moderation?error=Please provide a reason for rejection');
        }

        if ($page->reject($user->getAttribute('id'), $reason)) {
            return $this->redirect('/islamic-moderation?success=Content rejected successfully');
        } else {
            return $this->redirect('/islamic-moderation?error=Failed to reject content');
        }
    }

    /**
     * Request revision for Islamic content.
     */
    public function requestRevision(Request $request, string $slug): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/islamic-moderation?error=Invalid security token');
        }

        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('moderate_comments')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        $page = IslamicPage::findBySlug($slug, $this->db);
        if (!$page) {
            return $this->redirect('/islamic-moderation?error=Content not found');
        }

        $notes = $request->getPostParam('notes');
        if (empty($notes)) {
            return $this->redirect('/islamic-moderation?error=Please provide revision notes');
        }

        if ($page->requestRevision($user->getAttribute('id'), $notes)) {
            return $this->redirect('/islamic-moderation?success=Revision requested successfully');
        } else {
            return $this->redirect('/islamic-moderation?error=Failed to request revision');
        }
    }

    /**
     * Show Islamic content verification queue.
     */
    public function showVerificationQueue(Request $request): Response
    {
        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('verify_content')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        // Get pages that need verification
        $pages = $this->db->table('pages')
            ->select(['*'])
            ->where('scholar_verified', 0)
            ->where('moderation_status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->view('pages/islamic_verification_queue', [
            'title' => 'Islamic Content Verification - IslamWiki',
            'user' => $user,
            'pages' => $pages,
            'csrf_token' => $this->session->getCsrfToken(),
        ]);
    }

    /**
     * Verify Islamic content by scholar.
     */
    public function verifyIslamicContent(Request $request, string $slug): Response
    {
        // Validate CSRF token
        if (!$this->session->validateCsrfToken($request->getPostParam('csrf_token'))) {
            return $this->redirect('/islamic-verification?error=Invalid security token');
        }

        $user = $this->getCurrentIslamicUser();
        if (!$user || !$user->hasIslamicPermission('verify_content')) {
            return $this->redirect('/dashboard?error=Access denied');
        }

        $page = IslamicPage::findBySlug($slug, $this->db);
        if (!$page) {
            return $this->redirect('/islamic-verification?error=Content not found');
        }

        $notes = $request->getPostParam('notes', '');

        if ($page->verifyByScholar($user->getAttribute('id'), $notes)) {
            return $this->redirect('/islamic-verification?success=Content verified successfully');
        } else {
            return $this->redirect('/islamic-verification?error=Failed to verify content');
        }
    }

    /**
     * Get current Islamic user.
     */
    protected function getCurrentIslamicUser(): ?IslamicUser
    {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            return null;
        }

        return IslamicUser::find($userId, $this->db);
    }

    /**
     * Check if user can edit Islamic page.
     */
    protected function canEditIslamicPage(IslamicPage $page, IslamicUser $user): bool
    {
        // Admins can edit everything
        if ($user->isAdmin()) {
            return true;
        }

        // Scholars can edit their own content and unverified content
        if ($user->isScholar()) {
            return true;
        }

        // Regular users can only edit their own content if it's not verified
        if ($page->getAttribute('created_by') === $user->getAttribute('id')) {
            return !$page->isScholarVerified();
        }

        return false;
    }

    /**
     * Get Islamic permissions for user.
     */
        protected function getIslamicPermissionsForUser(IslamicUser $user): array
    {
        $permissions = [];
        
        if ($user->isAdmin()) {
            $permissions = ['read', 'edit', 'delete', 'moderate', 'verify'];
        } elseif ($user->isScholar()) {
            $permissions = ['read', 'edit', 'verify'];
        } else {
            $permissions = ['read', 'edit'];
        }
        
        return $permissions;
    }

    /**
     * Generate excerpt from content.
     */
    private function generateExcerpt(string $content, int $length = 150): string
    {
        // Remove HTML tags and get plain text
        $plainText = strip_tags($content);
        
        // Truncate to specified length
        if (strlen($plainText) > $length) {
            $plainText = substr($plainText, 0, $length) . '...';
        }
        
        return $plainText;
    }

    /**
     * Calculate read time for content.
     */
    private function calculateReadTime(string $content): int
    {
        // Average reading speed: 200 words per minute
        $wordCount = str_word_count(strip_tags($content));
        $readTime = ceil($wordCount / 200);
        
        return max(1, $readTime);
    }

    /**
     * Get author information.
     */
    private function getAuthorInfo(int $authorId): ?array
    {
        try {
            $author = $this->db->table('users')
                ->select(['id', 'username', 'display_name'])
                ->where('id', $authorId)
                ->first();
            
            return $author;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get category statistics.
     */
    private function getCategoryStats(): array
    {
        try {
            $stats = $this->db->table('islamic_pages')
                ->select([
                    'islamic_category',
                    $this->db->raw('COUNT(*) as count')
                ])
                ->where('moderation_status', 'approved')
                ->where('verification_status', 'verified')
                ->groupBy('islamic_category')
                ->get();
            
            $categories = [];
            foreach ($stats as $stat) {
                $categories[$stat['islamic_category']] = [
                    'count' => $stat['count']
                ];
            }
            
            return $categories;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get featured content.
     */
    private function getFeaturedContent(): array
    {
        try {
            return $this->db->table('islamic_pages')
                ->select([
                    'id', 'title', 'arabic_title', 'islamic_category',
                    'created_at', 'view_count', 'quality_score'
                ])
                ->where('moderation_status', 'approved')
                ->where('verification_status', 'verified')
                ->where('is_featured', true)
                ->orderBy('quality_score', 'desc')
                ->limit(6)
                ->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get recent articles.
     */
    private function getRecentArticles(int $limit = 5): array
    {
        try {
            return $this->db->table('islamic_pages')
                ->select([
                    'id', 'title', 'islamic_category', 'created_at',
                    'view_count', 'author_id'
                ])
                ->where('moderation_status', 'approved')
                ->where('verification_status', 'verified')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            return [];
        }
    }
} 