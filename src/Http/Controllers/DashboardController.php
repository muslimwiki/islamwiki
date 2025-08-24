<?php

/**
 * Dashboard Controller
 *
 * Handles the main dashboard functionality with comprehensive user data.
 *
 * @category  IslamWiki
 * @package   Http\Controllers
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.28
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Request;
use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Extensions\DashboardExtension\DashboardExtension;

/**
 * DashboardController - Main dashboard functionality for IslamWiki
 *
 * @category  IslamWiki
 * @package   Http\Controllers
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.28
 */
class DashboardController extends Controller
{
    private WisalSession $session;

    /**
     * Create a new controller instance.
     *
     * @param Connection $db        The database connection
     * @param AsasContainer $container The dependency injection container
     */
    public function __construct(Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->session = $container->get('session');
    }

    /**
     * Show the application dashboard.
     * 
     * SECURITY: This method requires authentication. Unauthenticated users
     * are redirected to the login page.
     *
     * @param Request $request The incoming request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Check if user is authenticated
        $session = $this->container->get('session');
        if (!$session->isLoggedIn()) {
            // Redirect to login page if not authenticated
            return new \IslamWiki\Core\Http\Response(
                302, 
                [
                    'Location' => '/login',
                    'Content-Type' => 'text/html; charset=UTF-8'
                ], 
                '<!DOCTYPE html>
                <html>
                <head>
                    <title>Authentication Required - IslamWiki</title>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <meta http-equiv="refresh" content="3;url=/login">
                </head>
                <body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
                    <h1>🔒 Authentication Required</h1>
                    <p>You must be logged in to access the dashboard.</p>
                    <p>Redirecting to login page in 3 seconds...</p>
                    <p><a href="/login">Click here to login now</a></p>
                    <p><a href="/wiki/Main_Page">← Back to Main Page</a></p>
                </body>
                </html>'
            );
        }

        // Get current user from Aman
        $user = null;
        $userStats = [];
        $recentActivity = [];
        $watchlist = [];
        $quickStats = [];
        $siteStats = [];
        // Ensure Bayan card always has data structure so it renders
        $dataBayan = [
            'statistics' => [
                'total_nodes' => 0,
                'total_edges' => 0,
                'node_types' => 0,
            ],
            'hub_nodes' => [],
            'recent_nodes' => [],
            'graph' => [ 'nodes' => [], 'edges' => [] ],
        ];

        // Initialize template path with a default value
        $templatePath = 'dashboard/user_dashboard';

        try {
            error_log("DashboardController::index - About to get auth service");
            $auth = $this->container->get(\IslamWiki\Core\Auth\AmanSecurity::class);
            error_log("DashboardController::index - Auth service retrieved: " . get_class($auth));

            // Test session directly
            $session = $this->container->get('session');
            error_log("DashboardController::index - Session status: " . session_status());
            error_log("DashboardController::index - Session data: " . print_r($_SESSION, true));
            error_log("DashboardController::index - Is logged in: " . ($session->isLoggedIn() ? 'true' : 'false'));
            error_log("DashboardController::index - User ID from session: " . $session->getUserId());

            $user = $auth->user();
            error_log("DashboardController::index - User: " . json_encode($user));

            if ($user) {
                $userId = $user['id'];
                error_log("DashboardController::index - User ID: $userId");

                // Get user statistics
                $userStats = $this->getUserStatistics($userId);
                error_log("DashboardController::index - User stats: " . json_encode($userStats));

                // Get recent activity
                $recentActivity = $this->getRecentActivity($userId);

                // Get watchlist
                $watchlist = $this->getWatchlist($userId);

                // Get quick stats
                $quickStats = $this->getQuickStats($userId);
            } else {
                // Fallback: compute stats if we have a session user ID, even if auth is null
                $fallbackUserId = $this->session->getUserId();
                $fallbackUsername = $this->session->get('username') ?? 'User';
                $fallbackIsAdmin = $this->session->get('is_admin') ?? 0;
                error_log("DashboardController::index - No auth user. Session fallback user ID: " . ($fallbackUserId ?? 'null'));

                if ($fallbackUserId) {
                    $userStats = $this->getUserStatistics($fallbackUserId);
                    error_log("DashboardController::index - (fallback) User stats: " . json_encode($userStats));

                    $recentActivity = $this->getRecentActivity($fallbackUserId);
                    $watchlist = $this->getWatchlist($fallbackUserId);
                    $quickStats = $this->getQuickStats($fallbackUserId);
                    // Also expose a minimal user for the view
                    $user = [
                        'id' => $fallbackUserId,
                        'username' => $fallbackUsername,
                        'is_admin' => $fallbackIsAdmin === 1,
                    ];
                } else {
                    error_log("DashboardController::index - No user found");
                }
            }

            // Get site statistics (available for all users)
            $siteStats = $this->getSiteStatistics();

            // Determine user role and select appropriate template
            $userRole = $this->determineUserRole($user);
            $templatePath = $this->getRoleBasedTemplate($userRole);
            
            // Log role detection for debugging
            error_log("DashboardController::index - User role detected: $userRole");
            error_log("DashboardController::index - Template selected: $templatePath");

            // Try to get Bayan (Knowledge Graph) stats
            try {
                // Always compute from QueryManager/NodeManager to avoid manager binding mismatches
                $logger = $this->container->get(\Psr\Log\LoggerInterface::class);
                $queryManager = new \IslamWiki\Core\Formatter\QueryManager($this->db, $logger);
                $nodeManager = new \IslamWiki\Core\Formatter\NodeManager($this->db, $logger);

                $metrics = $queryManager->getGraphMetrics();
                $hub = $queryManager->getHubNodes(5);
                $recent = $nodeManager->search('', [], 5);

                // Build small graph: hub nodes and a few of their neighbors
                $graph = [ 'nodes' => [], 'edges' => [] ];
                $nodeIndexById = [];
                $maxHubs = min(3, count($hub));
                for ($i = 0; $i < $maxHubs; $i++) {
                    $hubNode = $hub[$i];
                    $hubId = (int)($hubNode['id'] ?? 0);
                    if ($hubId === 0) { continue; }
                    if (!isset($nodeIndexById[$hubId])) {
                        $nodeIndexById[$hubId] = true;
                        $graph['nodes'][] = [
                            'id' => $hubId,
                            'label' => $hubNode['title'] ?? $hubNode->title ?? ('Node ' . $hubId),
                            'type' => $hubNode['type'] ?? $hubNode->type ?? 'node',
                            'isHub' => true,
                        ];
                    }
                    $neighbors = $queryManager->getRelatedNodes($hubId, null, 3);
                    foreach ($neighbors as $neighbor) {
                        $neighborId = (int)($neighbor['id'] ?? 0);
                        if ($neighborId === 0) { continue; }
                        if (!isset($nodeIndexById[$neighborId])) {
                            $nodeIndexById[$neighborId] = true;
                            $graph['nodes'][] = [
                                'id' => $neighborId,
                                'label' => $neighbor['title'] ?? $neighbor->title ?? ('Node ' . $neighborId),
                                'type' => $neighbor['type'] ?? $neighbor->type ?? 'node',
                                'isHub' => false,
                            ];
                        }
                        $graph['edges'][] = [ 'source' => $hubId, 'target' => $neighborId ];
                    }
                }

                // Expose card when tables exist and metrics look sane
                if (is_array($metrics) && (isset($metrics['total_nodes']) || isset($metrics['total_edges']))) {
                    $dataBayan = [
                        'statistics' => [
                            'total_nodes' => (int)($metrics['total_nodes'] ?? 0),
                            'total_edges' => (int)($metrics['total_edges'] ?? 0),
                            'node_types' => is_array($metrics['node_types'] ?? null) ? count($metrics['node_types']) : 0,
                        ],
                        'hub_nodes' => $hub,
                        'recent_nodes' => $recent,
                        'graph' => $graph,
                    ];
                }
            } catch (\Throwable $e) {
                error_log('Dashboard: Bayan compute failed - ' . $e->getMessage());
                // keep default empty $dataBayan
            }
        } catch (\Exception $e) {
            error_log('DashboardController: Error loading dashboard data: ' . $e->getMessage());
        }

        // Get active skin using standardized skin manager
        // For now, use a fallback since we don't have the 'app' binding
        $activeSkinName = 'Bismillah'; // Default skin name
        
        // TODO: Once the full application system is implemented, this can be updated to:
        // $app = $this->container->get('app');
        // $activeSkinName = SkinManager::getActiveSkinNameStatic($app);

        // Prepare data for the view
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'userStats' => $userStats,
            'recentActivity' => $recentActivity,
            'watchlist' => $watchlist,
            'quickStats' => $quickStats,
            'siteStats' => $siteStats,
            'activeSkin' => $activeSkinName,
            'currentTime' => date('Y-m-d H:i:s'),
                            'isLoggedIn' => $this->session->isLoggedIn(),
            // Provide user to view when available (either from auth or fallback)
            'user' => $user,
            'bayan' => $dataBayan,
            // Add current language from session
            'current_language' => $_SESSION['language'] ?? 'en'
        ];

        return $this->view($templatePath, $data);
    }

    /**
     * Get user statistics
     *
     * @param int $userId The user ID
     *
     * @return array
     */
    private function getUserStatistics(int $userId): array
    {
        try {
            error_log("DashboardController::getUserStatistics - Starting for user ID: $userId");

            // Get page contributions (pages created by user)
            $contributionsQuery = "
                SELECT COUNT(DISTINCT p.id) as total_pages
                FROM pages p
                JOIN page_revisions pr ON p.id = pr.page_id
                WHERE pr.user_id = ?
                AND pr.id = (
                    SELECT MIN(pr2.id)
                    FROM page_revisions pr2
                    WHERE pr2.page_id = p.id
                )
            ";
            $contributions = $this->db->query($contributionsQuery, [$userId])->fetch();
            error_log("DashboardController::getUserStatistics - Contributions query result: " . json_encode($contributions));

            // Get edit count (total revisions by user)
            $editsQuery = "
                SELECT COUNT(*) as total_edits
                FROM page_revisions
                WHERE user_id = ?
            ";
            $edits = $this->db->query($editsQuery, [$userId])->fetch();
            error_log("DashboardController::getUserStatistics - Edits query result: " . json_encode($edits));

            // Get recent activity count (last 30 days)
            $recentQuery = "
                SELECT COUNT(*) as recent_activity
                FROM page_revisions
                WHERE user_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ";
            $recent = $this->db->query($recentQuery, [$userId])->fetch();
            error_log("DashboardController::getUserStatistics - Recent query result: " . json_encode($recent));

            // Get watchlist count
            $watchlistQuery = "
                SELECT COUNT(*) as watchlist_count
                FROM user_watchlist
                WHERE user_id = ?
            ";
            $watchlist = $this->db->query($watchlistQuery, [$userId])->fetch();

            $result = [
                'total_pages' => $contributions['total_pages'] ?? 0,
                'total_edits' => $edits['total_edits'] ?? 0,
                'recent_activity' => $recent['recent_activity'] ?? 0,
                'watchlist_count' => $watchlist['watchlist_count'] ?? 0,
                'member_since' => $this->getMemberSince($userId)
            ];

            error_log("DashboardController::getUserStatistics - Final result: " . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            error_log('DashboardController::getUserStatistics - Error: ' . $e->getMessage());
            return [
                'total_pages' => 0,
                'total_edits' => 0,
                'recent_activity' => 0,
                'watchlist_count' => 0,
                'member_since' => null
            ];
        }
    }

    /**
     * Get recent activity for a user
     *
     * @param int $userId The user ID
     *
     * @return array
     */
    private function getRecentActivity(int $userId): array
    {
        try {
            $query = "
                SELECT 
                    pr.id,
                    pr.page_id,
                    p.title as page_title,
                    p.slug as page_slug,
                    pr.id as version_number,
                    pr.comment as change_summary,
                    pr.created_at,
                    'edit' as activity_type
                FROM page_revisions pr
                JOIN pages p ON pr.page_id = p.id
                WHERE pr.user_id = ? 
                ORDER BY pr.created_at DESC
                LIMIT 10
            ";

            $activities = $this->db->query($query, [$userId])->fetchAll();

            return array_map(function ($activity) {
                $rawSlug = $activity->page_slug ?? $activity['page_slug'] ?? '';
                $displaySlug = $rawSlug;
                if (is_string($rawSlug) && strpos($rawSlug, ':') !== false) {
                    $parts = explode(':', $rawSlug, 2);
                    $displaySlug = $parts[1];
                }
                return [
                    'id' => $activity['id'],
                    'page_id' => $activity['page_id'],
                    'page_title' => $activity['page_title'],
                    'page_slug' => $displaySlug,
                    'version_number' => $activity['version_number'],
                    'change_summary' => $activity['change_summary'],
                    'created_at' => $activity['created_at'],
                    'timestamp' => $activity['created_at'],
                    'activity_type' => $activity['activity_type'],
                    'type' => $activity['activity_type'],
                    'action' => 'EDIT',
                    'time_ago' => $this->getTimeAgo($activity['created_at'])
                ];
            }, $activities);
        } catch (\Exception $e) {
            error_log('DashboardController::getRecentActivity - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user's watchlist
     *
     * @param int $userId The user ID
     *
     * @return array
     */
    private function getWatchlist(int $userId): array
    {
        try {
            $query = "
                SELECT w.page_id, p.title, p.slug, w.created_at as watch_date
                FROM user_watchlist w
                JOIN pages p ON p.id = w.page_id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
                LIMIT 10
            ";
            $rows = $this->db->query($query, [$userId])->fetchAll();
            return array_map(function ($row) {
                return [
                    'id' => $row['page_id'],
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'watch_date' => $row['watch_date'],
                ];
            }, $rows);
        } catch (\Exception $e) {
            error_log('DashboardController::getWatchlist - Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get quick statistics for a user
     *
     * @param int $userId The user ID
     *
     * @return array
     */
    private function getQuickStats(int $userId): array
    {
        try {
            // Get today's activity
            $todayQuery = "
                SELECT COUNT(*) as today_edits
                FROM page_revisions
                WHERE user_id = ?
                AND DATE(created_at) = CURDATE()
            ";
            $today = $this->db->query($todayQuery, [$userId])->fetch();

            // Get this week's activity
            $weekQuery = "
                SELECT COUNT(*) as week_edits
                FROM page_revisions
                WHERE user_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ";
            $week = $this->db->query($weekQuery, [$userId])->fetch();

            // Get this month's activity
            $monthQuery = "
                SELECT COUNT(*) as month_edits
                FROM page_revisions
                WHERE user_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ";
            $month = $this->db->query($monthQuery, [$userId])->fetch();

            // Get pages created this month (using the same logic as getUserStatistics)
            $pagesQuery = "
                SELECT COUNT(DISTINCT p.id) as month_pages
                FROM pages p
                JOIN page_revisions pr ON p.id = pr.page_id
                WHERE pr.user_id = ?
                AND pr.id = (
                    SELECT MIN(pr2.id)
                    FROM page_revisions pr2
                    WHERE pr2.page_id = p.id
                )
                AND pr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ";
            $pages = $this->db->query($pagesQuery, [$userId])->fetch();

            return [
                'today_edits' => $today['today_edits'] ?? 0,
                'week_edits' => $week['week_edits'] ?? 0,
                'month_edits' => $month['month_edits'] ?? 0,
                'month_pages' => $pages['month_pages'] ?? 0
            ];
        } catch (\Exception $e) {
            error_log('DashboardController::getQuickStats - Error: ' . $e->getMessage());
            return [
                'today_edits' => 0,
                'week_edits' => 0,
                'month_edits' => 0,
                'month_pages' => 0
            ];
        }
    }

    /**
     * Get site-wide statistics
     *
     * @return array
     */
    private function getSiteStatistics(): array
    {
        try {
            // Get total pages
            $pagesQuery = "SELECT COUNT(*) as total_pages FROM pages";
            $pages = $this->db->query($pagesQuery)->fetch();

            // Get total edits
            $editsQuery = "SELECT COUNT(*) as total_edits FROM page_revisions";
            $edits = $this->db->query($editsQuery)->fetch();

            // Get total users
            $usersQuery = "SELECT COUNT(*) as total_users FROM users";
            $users = $this->db->query($usersQuery)->fetch();

            // Get recent activity (last 24 hours)
            $recentQuery = "
                SELECT COUNT(*) as recent_activity
                FROM page_revisions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ";
            $recent = $this->db->query($recentQuery)->fetch();

            return [
                'total_pages' => $pages['total_pages'] ?? 0,
                'total_edits' => $edits['total_edits'] ?? 0,
                'total_users' => $users['total_users'] ?? 0,
                'recent_activity' => $recent['recent_activity'] ?? 0
            ];
        } catch (\Exception $e) {
            error_log('DashboardController::getSiteStatistics - Error: ' . $e->getMessage());
            return [
                'total_pages' => 0,
                'total_edits' => 0,
                'total_users' => 0,
                'recent_activity' => 0
            ];
        }
    }

    /**
     * Get member since date for a user
     *
     * @param int $userId The user ID
     *
     * @return string|null
     */
    private function getMemberSince(int $userId): ?string
    {
        try {
            $query = "SELECT created_at FROM users WHERE id = ?";
            $user = $this->db->query($query, [$userId])->fetch();
            return $user['created_at'] ?? null;
        } catch (\Exception $e) {
            error_log('DashboardController::getMemberSince - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get time ago string from timestamp
     *
     * @param string $timestamp The timestamp
     *
     * @return string
     */
    private function getTimeAgo(string $timestamp): string
    {
        $time = strtotime($timestamp);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        }
    }

    /**
     * Determine user role based on user data and database
     *
     * @param array|null $user User data
     * @return string User role
     */
    private function determineUserRole(?array $user): string
    {
        if (!$user) {
            return 'user';
        }

        $userId = $user['id'] ?? null;
        if (!$userId) {
            return 'user';
        }

        try {
            // Query database for detailed user role information
            $userQuery = "SELECT role, is_admin, islamic_role FROM users WHERE id = ?";
            $userData = $this->db->query($userQuery, [$userId])->fetch();
            
            if ($userData) {
                // Check if user is admin
                if (($userData['is_admin'] ?? false) || ($userData['role'] ?? '') === 'admin') {
                    return 'admin';
                }

                // Check for Islamic roles
                if (isset($userData['islamic_role']) && $userData['islamic_role'] !== 'user') {
                    return $userData['islamic_role'];
                }

                // Check for regular roles
                if (isset($userData['role']) && $userData['role'] !== 'user') {
                    return $userData['role'];
                }
            }
        } catch (\Exception $e) {
            error_log('DashboardController::determineUserRole - Error querying user role: ' . $e->getMessage());
        }

        // Fallback to basic user data
        if (($user['is_admin'] ?? false)) {
            return 'admin';
        }

        if (isset($user['role']) && $user['role'] !== 'user') {
            return $user['role'];
        }

        // Default to user role
        return 'user';
    }

    /**
     * Get role-based template path
     *
     * @param string $role User role
     * @return string Template path
     */
    private function getRoleBasedTemplate(string $role): string
    {
        switch ($role) {
            case 'admin':
                return 'dashboard/admin_dashboard';
            case 'scholar':
                return 'dashboard/scholar_dashboard';
            case 'contributor':
                return 'dashboard/contributor_dashboard';
            default:
                return 'dashboard/user_dashboard';
        }
    }
}
