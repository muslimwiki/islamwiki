<?php
declare(strict_types=1);

/**
 * Community Controller
 * 
 * Comprehensive community management controller for user contributions,
 * discussions, moderation, and community features.
 * 
 * @package IslamWiki\Http\Controllers
 * @version 0.0.23
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Community\CommunityManager;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\Asas;
use IslamWiki\Core\Logging\Shahid;
use IslamWiki\Core\Database\Connection;

class CommunityController extends Controller
{
    /**
     * The community manager instance.
     */
    private CommunityManager $communityManager;

    /**
     * The database connection.
     */
    private Connection $db;

    /**
     * The logger instance.
     */
    private Shahid $logger;

    /**
     * Create a new community controller instance.
     */
    public function __construct(\IslamWiki\Core\Container\Asas $container)
    {
        parent::__construct($container);
        $this->db = $container->get(Connection::class);
        $this->logger = $container->get(Shahid::class);
        $this->communityManager = new CommunityManager($this->db, $this->logger);
    }

    /**
     * Display the community dashboard.
     */
    public function index(): Response
    {
        try {
            $stats = $this->communityManager->getCommunityStats();
            $recentDiscussions = $this->communityManager->getCommunityDiscussions(10);
            $topContributors = $this->getTopContributors();

            return $this->view('community/index', [
                'stats' => $stats,
                'recent_discussions' => $recentDiscussions,
                'top_contributors' => $topContributors,
                'title' => 'Community Dashboard'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Community dashboard error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load community dashboard', 500);
        }
    }

    /**
     * Display the users directory.
     */
    public function users(): Response
    {
        try {
            // Get search and filter parameters
            $search = $_GET['search'] ?? '';
            $sort = $_GET['sort'] ?? 'recent';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            
            // Build query
            $query = $this->db->table('users')
                ->select([
                    'id', 'username', 'display_name', 'bio', 'created_at', 
                    'last_login_at', 'is_active', 'is_admin'
                ])
                ->where('is_active', true);
            
            // Apply search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('username', 'LIKE', "%{$search}%")
                      ->orWhere('display_name', 'LIKE', "%{$search}%")
                      ->orWhere('bio', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply sorting
            switch ($sort) {
                case 'contributions':
                    $query->orderBy('contributions_count', 'desc');
                    break;
                case 'name':
                    $query->orderBy('display_name', 'asc');
                    break;
                case 'joined':
                    $query->orderBy('created_at', 'desc');
                    break;
                default: // recent
                    $query->orderBy('last_login_at', 'desc');
                    break;
            }
            
            // Get total count for pagination
            $totalUsers = $query->count();
            $totalPages = ceil($totalUsers / $perPage);
            
            // Get paginated results
            $users = $query->offset(($page - 1) * $perPage)
                          ->limit($perPage)
                          ->get();
            
            // Enhance user data with additional information
            foreach ($users as &$user) {
                $user['contributions'] = $this->getUserContributionsCount($user['id']);
                $user['is_online'] = $this->isUserOnline($user['id']);
                $user['last_active'] = $this->getUserLastActivity($user['id']);
            }
            
            // Build pagination data
            $pagination = [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_users' => $totalUsers,
                'per_page' => $perPage,
                'pages' => range(max(1, $page - 2), min($totalPages, $page + 2))
            ];
            
            return $this->view('community/users', [
                'users' => $users,
                'pagination' => $pagination,
                'search_query' => $search,
                'sort' => $sort,
                'title' => 'Community Members'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Community users error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load community users', 500);
        }
    }

    /**
     * Display the activity feed.
     */
    public function activity(): Response
    {
        try {
            $activities = $this->communityManager->getCommunityActivity();
            
            return $this->view('community/activity', [
                'activities' => $activities,
                'title' => 'Community Activity'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Community activity error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load community activity', 500);
        }
    }





    /**
     * Show a specific discussion.
     */
    public function showDiscussion(int $id): Response
    {
        try {
            $discussion = $this->communityManager->getDiscussion($id);
            $replies = $this->communityManager->getDiscussionReplies($id);
            
            return $this->view('community/show-discussion', [
                'discussion' => $discussion,
                'replies' => $replies,
                'title' => $discussion['title'] ?? 'Discussion'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Show discussion error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load discussion', 500);
        }
    }

    /**
     * Add a reply to a discussion.
     */
    public function addReply(Request $request, int $id): Response
    {
        try {
            $data = $request->getParsedBody();
            $userId = $this->getCurrentUserId();
            
            if (!$userId) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'You must be logged in to reply'
                ], 401);
            }
            
            if (empty($data['content'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Reply content is required'
                ], 400);
            }
            
            $replyId = $this->communityManager->addDiscussionReply($id, $userId, $data['content']);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Reply added successfully',
                'reply_id' => $replyId
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Add reply error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add reply'
            ], 500);
        }
    }

    /**
     * Display the contribution form.
     */
    public function contribute(): Response
    {
        try {
            $categories = $this->getContributionCategories();
            $contributionTypes = $this->getContributionTypes();

            return $this->view('community/contribute', [
                'categories' => $categories,
                'contribution_types' => $contributionTypes,
                'title' => 'Submit Contribution'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Contribution form error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load contribution form', 500);
        }
    }

    /**
     * Submit a contribution.
     */
    public function submitContribution(Request $request): Response
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $data = $request->getParsedBody();
            
            $contributionData = [
                'type' => $data['type'] ?? '',
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'category' => $data['category'] ?? '',
                'tags' => $data['tags'] ?? []
            ];

            $result = $this->communityManager->submitContribution($userId, $contributionData);

            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            $this->logger->error('Contribution submission error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to submit contribution'
            ], 500);
        }
    }

    /**
     * Display user contributions.
     */
    public function myContributions(): Response
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->errorResponse('Authentication required', 401);
            }

            $contributions = $this->communityManager->getUserContributions($userId);
            $reputation = $this->communityManager->getUserReputation($userId);

            return $this->view('community/my-contributions', [
                'contributions' => $contributions,
                'reputation' => $reputation,
                'title' => 'My Contributions'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('User contributions error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load contributions', 500);
        }
    }

    /**
     * Display moderation panel.
     */
    public function moderation(): Response
    {
        try {
            if (!$this->isModerator()) {
                return $this->errorResponse('Access denied', 403);
            }

            $pendingContributions = $this->communityManager->getPendingContributions();
            $moderationStats = $this->getModerationStats();

            return $this->view('community/moderation', [
                'pending_contributions' => $pendingContributions,
                'moderation_stats' => $moderationStats,
                'title' => 'Moderation Panel'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Moderation panel error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load moderation panel', 500);
        }
    }

    /**
     * Approve a contribution.
     */
    public function approveContribution(Request $request): Response
    {
        try {
            if (!$this->isModerator()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $data = $request->getParsedBody();
            $contributionId = (int) ($data['contribution_id'] ?? 0);
            $notes = $data['notes'] ?? '';
            $moderatorId = $this->getCurrentUserId();

            $result = $this->communityManager->approveContribution($contributionId, $moderatorId, $notes);

            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            $this->logger->error('Contribution approval error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to approve contribution'
            ], 500);
        }
    }

    /**
     * Reject a contribution.
     */
    public function rejectContribution(Request $request): Response
    {
        try {
            if (!$this->isModerator()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $data = $request->getParsedBody();
            $contributionId = (int) ($data['contribution_id'] ?? 0);
            $reason = $data['reason'] ?? '';
            $moderatorId = $this->getCurrentUserId();

            $result = $this->communityManager->rejectContribution($contributionId, $moderatorId, $reason);

            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            $this->logger->error('Contribution rejection error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to reject contribution'
            ], 500);
        }
    }

    /**
     * Display community discussions.
     */
    public function discussions(): Response
    {
        try {
            $discussions = $this->communityManager->getCommunityDiscussions(50);
            $categories = $this->getDiscussionCategories();

            return $this->view('community/discussions', [
                'discussions' => $discussions,
                'categories' => $categories,
                'title' => 'Community Discussions'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Discussions error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load discussions', 500);
        }
    }

    /**
     * Display discussion creation form.
     */
    public function createDiscussion(): Response
    {
        try {
            $categories = $this->getDiscussionCategories();

            return $this->view('community/create-discussion', [
                'categories' => $categories,
                'title' => 'Create Discussion'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Discussion creation form error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load discussion form', 500);
        }
    }

    /**
     * Submit a discussion.
     */
    public function submitDiscussion(Request $request): Response
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $data = $request->getParsedBody();
            
            $discussionData = [
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'category' => $data['category'] ?? '',
                'tags' => $data['tags'] ?? []
            ];

            $result = $this->communityManager->createDiscussion($userId, $discussionData);

            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            $this->logger->error('Discussion submission error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create discussion'
            ], 500);
        }
    }

    /**
     * Display user profile.
     */
    public function profile(int $userId): Response
    {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            $contributions = $this->communityManager->getUserContributions($userId);
            $reputation = $this->communityManager->getUserReputation($userId);
            $userStats = $this->getUserStats($userId);

            return $this->view('community/profile', [
                'user' => $user,
                'contributions' => $contributions,
                'reputation' => $reputation,
                'user_stats' => $userStats,
                'title' => 'User Profile'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('User profile error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load user profile', 500);
        }
    }

    /**
     * Get contribution categories.
     */
    private function getContributionCategories(): array
    {
        return [
            'quran' => 'Quran & Tafsir',
            'hadith' => 'Hadith & Sunnah',
            'fiqh' => 'Islamic Law & Jurisprudence',
            'aqeedah' => 'Islamic Beliefs & Creed',
            'seerah' => 'Prophet Muhammad (PBUH)',
            'history' => 'Islamic History',
            'scholars' => 'Islamic Scholars',
            'prayer' => 'Prayer & Worship',
            'ramadan' => 'Ramadan & Fasting',
            'hajj' => 'Hajj & Umrah',
            'charity' => 'Charity & Zakat',
            'family' => 'Family & Marriage',
            'education' => 'Islamic Education',
            'modern' => 'Modern Islamic Issues'
        ];
    }

    /**
     * Get contribution types.
     */
    private function getContributionTypes(): array
    {
        return [
            'article' => 'Article',
            'hadith' => 'Hadith',
            'quran' => 'Quran Verse',
            'scholar' => 'Scholar Biography',
            'event' => 'Islamic Event'
        ];
    }

    /**
     * Get discussion categories.
     */
    private function getDiscussionCategories(): array
    {
        return [
            'general' => 'General Discussion',
            'quran' => 'Quran Discussion',
            'hadith' => 'Hadith Discussion',
            'fiqh' => 'Islamic Law Discussion',
            'aqeedah' => 'Beliefs Discussion',
            'seerah' => 'Prophet Muhammad (PBUH)',
            'history' => 'Islamic History',
            'scholars' => 'Islamic Scholars',
            'prayer' => 'Prayer & Worship',
            'ramadan' => 'Ramadan & Fasting',
            'hajj' => 'Hajj & Umrah',
            'charity' => 'Charity & Zakat',
            'family' => 'Family & Marriage',
            'education' => 'Islamic Education',
            'modern' => 'Modern Issues'
        ];
    }

    /**
     * Get top contributors.
     */
    private function getTopContributors(): array
    {
        try {
            return $this->db->table('user_contributions')
                ->select([
                    'user_id',
                    $this->db->raw('COUNT(*) as contribution_count')
                ])
                ->where('status', 'approved')
                ->groupBy('user_id')
                ->orderBy('contribution_count', 'desc')
                ->limit(10)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Top contributors retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get moderation stats.
     */
    private function getModerationStats(): array
    {
        try {
            $stats = [];
            
            $stats['pending_count'] = $this->db->table('user_contributions')
                ->where('status', 'pending')
                ->count();
            
            $stats['approved_today'] = $this->db->table('user_contributions')
                ->where('status', 'approved')
                ->where('approved_at', '>=', date('Y-m-d'))
                ->count();
            
            $stats['rejected_today'] = $this->db->table('user_contributions')
                ->where('status', 'rejected')
                ->where('rejected_at', '>=', date('Y-m-d'))
                ->count();
            
            return $stats;
        } catch (\Exception $e) {
            $this->logger->error('Moderation stats retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user by ID.
     */
    private function getUserById(int $userId): ?array
    {
        try {
            return $this->db->table('users')
                ->where('id', $userId)
                ->first();
        } catch (\Exception $e) {
            $this->logger->error('User retrieval failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user stats.
     */
    private function getUserStats(int $userId): array
    {
        try {
            $stats = [];
            
            $stats['total_contributions'] = $this->db->table('user_contributions')
                ->where('user_id', $userId)
                ->count();
            
            $stats['approved_contributions'] = $this->db->table('user_contributions')
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->count();
            
            $stats['pending_contributions'] = $this->db->table('user_contributions')
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->count();
            
            $stats['discussions_created'] = $this->db->table('community_discussions')
                ->where('user_id', $userId)
                ->count();
            
            return $stats;
        } catch (\Exception $e) {
            $this->logger->error('User stats retrieval failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user is moderator.
     */
    private function isModerator(): bool
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) {
                return false;
            }

            $user = $this->db->table('users')
                ->where('id', $userId)
                ->first();

            return $user && in_array($user['role'], ['admin', 'moderator']);
        } catch (\Exception $e) {
            $this->logger->error('Moderator check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current user ID from session.
     */
    private function getCurrentUserId(): ?int
    {
        try {
            $session = $this->container->get('session');
            return $session?->get('user_id');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Render a template with data.
     */
    private function render(string $template, array $data = []): Response
    {
        $renderer = $this->container->get('view');
        $content = $renderer->render($template, $data);
        
        return new Response(200, ['Content-Type' => 'text/html'], $content);
    }

    /**
     * Create JSON response.
     */
    private function jsonResponse(array $data, int $status = 200): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }

    /**
     * Create error response.
     */
    /**
     * Get user contributions count.
     */
    private function getUserContributionsCount(int $userId): int
    {
        try {
            return $this->db->table('user_contributions')
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if user is online.
     */
    private function isUserOnline(int $userId): bool
    {
        try {
            $lastActivity = $this->db->table('user_activity')
                ->select('last_activity')
                ->where('user_id', $userId)
                ->first();
            
            if (!$lastActivity) {
                return false;
            }
            
            // Consider user online if last activity was within 15 minutes
            $lastActivityTime = strtotime($lastActivity['last_activity']);
            return (time() - $lastActivityTime) < 900; // 15 minutes
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get user last activity.
     */
    private function getUserLastActivity(int $userId): ?string
    {
        try {
            $lastActivity = $this->db->table('user_activity')
                ->select('last_activity')
                ->where('user_id', $userId)
                ->first();
            
            return $lastActivity ? $lastActivity['last_activity'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function errorResponse(string $message, int $status = 500): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'text/html'],
            "<h1>Error {$status}</h1><p>{$message}</p>"
        );
    }
} 