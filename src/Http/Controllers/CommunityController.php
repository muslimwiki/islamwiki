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
use IslamWiki\Core\Container;
use IslamWiki\Core\Logging\Logger;
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
    private Logger $logger;

    /**
     * Create a new community controller instance.
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->db = $container->get(Connection::class);
        $this->logger = $container->get(Logger::class);
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

            return $this->render('community/index.twig', [
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
     * Display the contribution form.
     */
    public function contribute(): Response
    {
        try {
            $categories = $this->getContributionCategories();
            $contributionTypes = $this->getContributionTypes();

            return $this->render('community/contribute.twig', [
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

            return $this->render('community/my-contributions.twig', [
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

            return $this->render('community/moderation.twig', [
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

            return $this->render('community/discussions.twig', [
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

            return $this->render('community/create-discussion.twig', [
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

            return $this->render('community/profile.twig', [
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
    private function errorResponse(string $message, int $status = 500): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'text/html'],
            "<h1>Error {$status}</h1><p>{$message}</p>"
        );
    }
} 