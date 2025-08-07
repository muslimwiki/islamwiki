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
            $auth = $this->container->get('auth');
            $user = $auth->user();

            if ($user) {
                $userId = $user['id'];
                // Get user statistics
                $userStats = $this->getUserStatistics($userId);

                // Get recent activity
                $recentActivity = $this->getRecentActivity($userId);

                // Get watchlist
                $watchlist = $this->getWatchlist($userId);

                // Get quick stats
                $quickStats = $this->getQuickStats($userId);
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
            'isLoggedIn' => $this->_session->isLoggedIn()
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
            // Get page contributions
            $contributionsQuery = "
                SELECT COUNT(*) as total_pages
                FROM pages 
                WHERE created_by = ? AND deleted_at IS NULL
            ";
            $contributions = $this->db->query($contributionsQuery, [$userId])->fetch();

            // Get edit count
            $editsQuery = "
                SELECT COUNT(*) as total_edits
                FROM page_versions 
                WHERE created_by = ? AND deleted_at IS NULL
            ";
            $edits = $this->db->query($editsQuery, [$userId])->fetch();

            // Get recent activity count (last 30 days)
            $recentQuery = "
                SELECT COUNT(*) as recent_activity
                FROM page_versions 
                WHERE created_by = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND deleted_at IS NULL
            ";
            $recent = $this->db->query($recentQuery, [$userId])->fetch();

            // Get watchlist count
            $watchlistQuery = "
                SELECT COUNT(*) as watchlist_count
                FROM user_watchlist 
                WHERE user_id = ? AND deleted_at IS NULL
            ";
            $watchlist = $this->db->query($watchlistQuery, [$userId])->fetch();

            return [
                'total_pages' => $contributions['total_pages'] ?? 0,
                'total_edits' => $edits['total_edits'] ?? 0,
                'recent_activity' => $recent['recent_activity'] ?? 0,
                'watchlist_count' => $watchlist['watchlist_count'] ?? 0,
                'member_since' => $this->getMemberSince($userId)
            ];
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
                    pv.id,
                    pv.page_id,
                    p.title as page_title,
                    pv.version_number,
                    pv.change_summary,
                    pv.created_at,
                    'edit' as activity_type
                FROM page_versions pv
                JOIN pages p ON pv.page_id = p.id
                WHERE pv.created_by = ? 
                AND pv.deleted_at IS NULL
                AND p.deleted_at IS NULL
                ORDER BY pv.created_at DESC
                LIMIT 10
            ";
            
            $activities = $this->db->query($query, [$userId])->fetchAll();

            return array_map(function ($activity) {
                return [
                    'id' => $activity['id'],
                    'page_id' => $activity['page_id'],
                    'page_title' => $activity['page_title'],
                    'version_number' => $activity['version_number'],
                    'change_summary' => $activity['change_summary'],
                    'created_at' => $activity['created_at'],
                    'activity_type' => $activity['activity_type'],
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
                SELECT 
                    p.id,
                    p.title,
                    p.slug,
                    p.updated_at,
                    pv.version_number,
                    pv.change_summary
                FROM user_watchlist uw
                JOIN pages p ON uw.page_id = p.id
                LEFT JOIN page_versions pv ON p.id = pv.page_id 
                    AND pv.id = (
                        SELECT MAX(id) 
                        FROM page_versions 
                        WHERE page_id = p.id AND deleted_at IS NULL
                    )
                WHERE uw.user_id = ? 
                AND uw.deleted_at IS NULL
                AND p.deleted_at IS NULL
                ORDER BY p.updated_at DESC
                LIMIT 10
            ";
            
            $watchlist = $this->db->query($query, [$userId])->fetchAll();

            return array_map(function($item) {
                return [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'updated_at' => $item['updated_at'],
                    'version_number' => $item['version_number'],
                    'change_summary' => $item['change_summary'],
                    'time_ago' => $this->getTimeAgo($item['updated_at'])
                ];
            }, $watchlist);
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
                FROM page_versions 
                WHERE created_by = ? 
                AND DATE(created_at) = CURDATE()
                AND deleted_at IS NULL
            ";
            $today = $this->db->query($todayQuery, [$userId])->fetch();

            // Get this week's activity
            $weekQuery = "
                SELECT COUNT(*) as week_edits
                FROM page_versions 
                WHERE created_by = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND deleted_at IS NULL
            ";
            $week = $this->db->query($weekQuery, [$userId])->fetch();

            // Get this month's activity
            $monthQuery = "
                SELECT COUNT(*) as month_edits
                FROM page_versions 
                WHERE created_by = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND deleted_at IS NULL
            ";
            $month = $this->db->query($monthQuery, [$userId])->fetch();

            // Get pages created this month
            $pagesQuery = "
                SELECT COUNT(*) as month_pages
                FROM pages 
                WHERE created_by = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND deleted_at IS NULL
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
            $pagesQuery = "SELECT COUNT(*) as total_pages FROM pages WHERE deleted_at IS NULL";
            $pages = $this->db->query($pagesQuery)->fetch();

            // Get total edits
            $editsQuery = "SELECT COUNT(*) as total_edits FROM page_versions WHERE deleted_at IS NULL";
            $edits = $this->db->query($editsQuery)->fetch();

            // Get total users
            $usersQuery = "SELECT COUNT(*) as total_users FROM users WHERE deleted_at IS NULL";
            $users = $this->db->query($usersQuery)->fetch();

            // Get recent activity (last 24 hours)
            $recentQuery = "
                SELECT COUNT(*) as recent_activity
                FROM page_versions 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND deleted_at IS NULL
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
            $query = "SELECT created_at FROM users WHERE id = ? AND deleted_at IS NULL";
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
}
