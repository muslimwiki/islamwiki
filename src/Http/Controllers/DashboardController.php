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
use IslamWiki\Skins\SkinManager;
use Psr\Http\Message\ServerRequestInterface as Request;

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
    private WisalSession $_session;

    /**
     * Create a new controller instance.
     *
     * @param Connection $db        The database connection
     * @param AsasContainer $container The dependency injection container
     */
    public function __construct(Connection $db, \IslamWiki\Core\Container\AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->_session = $container->get('session');
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request The incoming request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Get current user from Aman
        $user = null;
        $userStats = [];
        $recentActivity = [];
        $watchlist = [];
        $quickStats = [];
        $siteStats = [];

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
                $fallbackUserId = $this->_session->getUserId();
                $fallbackUsername = $this->_session->getUsername() ?? 'User';
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
                        'is_admin' => $this->_session->isAdmin(),
                    ];
                } else {
                    error_log("DashboardController::index - No user found");
                }
            }

            // Get site statistics (available for all users)
            $siteStats = $this->getSiteStatistics();
        } catch (\Exception $e) {
            error_log('DashboardController: Error loading dashboard data: ' . $e->getMessage());
        }

        // Get active skin using standardized skin manager
        $app = $this->container->get('app');
        $activeSkinName = SkinManager::getActiveSkinNameStatic($app);

        $data = [
            'title' => 'Dashboard - IslamWiki',
            'userStats' => $userStats,
            'recentActivity' => $recentActivity,
            'watchlist' => $watchlist,
            'quickStats' => $quickStats,
            'siteStats' => $siteStats,
            'activeSkin' => $activeSkinName,
            'currentTime' => date('Y-m-d H:i:s'),
            'isLoggedIn' => $this->_session->isLoggedIn(),
            // Provide user to view when available (either from auth or fallback)
            'user' => $user
        ];

        return $this->view('dashboard/index', $data);
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

            // Get watchlist count (placeholder for now). No params to avoid HY093.
            $watchlistQuery = "
                SELECT 0 as watchlist_count
            ";
            $watchlist = $this->db->query($watchlistQuery)->fetch();

            $result = [
                'total_pages' => $contributions->total_pages ?? $contributions['total_pages'] ?? 0,
                'total_edits' => $edits->total_edits ?? $edits['total_edits'] ?? 0,
                'recent_activity' => $recent->recent_activity ?? $recent['recent_activity'] ?? 0,
                'watchlist_count' => $watchlist->watchlist_count ?? $watchlist['watchlist_count'] ?? 0,
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
                    'id' => $activity->id ?? $activity['id'],
                    'page_id' => $activity->page_id ?? $activity['page_id'],
                    'page_title' => $activity->page_title ?? $activity['page_title'],
                    'page_slug' => $displaySlug,
                    'version_number' => $activity->version_number ?? $activity['version_number'],
                    'change_summary' => $activity->change_summary ?? $activity['change_summary'],
                    'created_at' => $activity->created_at ?? $activity['created_at'],
                    'timestamp' => $activity->created_at ?? $activity['created_at'],
                    'activity_type' => $activity->activity_type ?? $activity['activity_type'],
                    'type' => $activity->activity_type ?? $activity['activity_type'],
                    'action' => 'EDIT',
                    'time_ago' => $this->getTimeAgo($activity->created_at ?? $activity['created_at'])
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
            // For now, return empty array since we don't have user_watchlist table
            return [];
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
                'today_edits' => $today->today_edits ?? $today['today_edits'] ?? 0,
                'week_edits' => $week->week_edits ?? $week['week_edits'] ?? 0,
                'month_edits' => $month->month_edits ?? $month['month_edits'] ?? 0,
                'month_pages' => $pages->month_pages ?? $pages['month_pages'] ?? 0
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
                'total_pages' => $pages->total_pages ?? $pages['total_pages'] ?? 0,
                'total_edits' => $edits->total_edits ?? $edits['total_edits'] ?? 0,
                'total_users' => $users->total_users ?? $users['total_users'] ?? 0,
                'recent_activity' => $recent->recent_activity ?? $recent['recent_activity'] ?? 0
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
            return $user->created_at ?? $user['created_at'] ?? null;
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
}
