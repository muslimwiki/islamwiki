<?php
declare(strict_types=1);

/**
 * Dashboard Controller
 * 
 * Handles the main dashboard functionality with comprehensive user data.
 * 
 * @package IslamWiki\Http\Controllers
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Session\SessionManager;
use IslamWiki\Core\Container;
use IslamWiki\Core\Database\Connection;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends Controller
{
    private SessionManager $session;

    /**
     * Create a new controller instance.
     */
    public function __construct(Connection $db, Container $container)
    {
        parent::__construct($db, $container);
        $this->session = $container->get('session');
    }

    /**
     * Show the application dashboard.
     */
    public function index(Request $request): Response
    {
        // Get current user from session
        $user = null;
        $userStats = [];
        $recentActivity = [];
        $watchlist = [];
        $quickStats = [];
        $siteStats = [];
        
        try {
            if ($this->session->isLoggedIn()) {
                $userId = $this->session->getUserId();
                $user = \IslamWiki\Models\User::find($userId, $this->db);
                
                if ($user) {
                    // Get user statistics
                    $userStats = $this->getUserStatistics($userId);
                    
                    // Get recent activity
                    $recentActivity = $this->getRecentActivity($userId);
                    
                    // Get watchlist
                    $watchlist = $this->getWatchlist($userId);
                    
                    // Get quick stats
                    $quickStats = $this->getQuickStats($userId);
                }
            }
            
            // Get site statistics (available for all users)
            $siteStats = $this->getSiteStatistics();
        } catch (\Exception $e) {
            error_log('DashboardController: Error loading dashboard data: ' . $e->getMessage());
        }
        
        // Load LocalSettings.php to get active skin
        $localSettingsPath = __DIR__ . '/../../../LocalSettings.php';
        if (file_exists($localSettingsPath)) {
            require_once $localSettingsPath;
        }
        
        global $wgActiveSkin;
        $activeSkinName = $wgActiveSkin ?? 'Bismillah';
        
        $data = [
            'title' => 'Dashboard - IslamWiki',
            'user' => $user,
            'userStats' => $userStats,
            'recentActivity' => $recentActivity,
            'watchlist' => $watchlist,
            'quickStats' => $quickStats,
            'siteStats' => $siteStats,
            'activeSkin' => $activeSkinName,
            'currentTime' => date('Y-m-d H:i:s'),
            'isLoggedIn' => $this->session->isLoggedIn()
        ];

        return $this->view('dashboard/index', $data);
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics(int $userId): array
    {
        try {
            // Get page contributions
            $pageContributions = $this->db->select(
                'SELECT COUNT(*) as count FROM pages WHERE created_by = ?',
                [$userId]
            );
            
            // Get recent edits
            $recentEdits = $this->db->select(
                'SELECT COUNT(*) as count FROM page_history WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
                [$userId]
            );
            
            // Get watchlist count
            $watchlistCount = $this->db->select(
                'SELECT COUNT(*) as count FROM user_watchlist WHERE user_id = ?',
                [$userId]
            );
            
            // Get total edits
            $totalEdits = $this->db->select(
                'SELECT COUNT(*) as count FROM page_history WHERE user_id = ?',
                [$userId]
            );
            
            // Get member since date
            $userData = $this->db->select(
                'SELECT created_at FROM users WHERE id = ?',
                [$userId]
            );
            
            $memberSince = $userData[0]['created_at'] ?? date('Y-m-d');
            $lastActive = date('Y-m-d H:i:s', strtotime('-2 hours')); // Mock data for now
            
            return [
                'total_pages' => $pageContributions[0]['count'] ?? 0,
                'recent_edits' => $recentEdits[0]['count'] ?? 0,
                'watchlist_items' => $watchlistCount[0]['count'] ?? 0,
                'total_edits' => $totalEdits[0]['count'] ?? 0,
                'member_since' => $memberSince,
                'last_active' => $lastActive,
                'contribution_score' => ($pageContributions[0]['count'] ?? 0) * 10 + ($totalEdits[0]['count'] ?? 0) * 2
            ];
        } catch (\Exception $e) {
            return [
                'total_pages' => 0,
                'recent_edits' => 0,
                'watchlist_items' => 0,
                'total_edits' => 0,
                'member_since' => date('Y-m-d'),
                'last_active' => date('Y-m-d H:i:s'),
                'contribution_score' => 0
            ];
        }
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(int $userId): array
    {
        try {
            // Get recent page edits
            $recentEdits = $this->db->select(
                'SELECT ph.*, p.title, p.slug, p.created_at as page_created 
                 FROM page_history ph 
                 JOIN pages p ON ph.page_id = p.id 
                 WHERE ph.user_id = ? 
                 ORDER BY ph.created_at DESC 
                 LIMIT 10',
                [$userId]
            );
            
            // Get recent page creations
            $recentCreations = $this->db->select(
                'SELECT p.*, "create" as action_type 
                 FROM pages p 
                 WHERE p.created_by = ? 
                 ORDER BY p.created_at DESC 
                 LIMIT 5',
                [$userId]
            );
            
            // Combine and sort activities
            $activities = [];
            
            foreach ($recentEdits as $edit) {
                $activities[] = [
                    'type' => 'edit',
                    'page_title' => $edit['title'],
                    'page_slug' => $edit['slug'],
                    'timestamp' => $edit['created_at'],
                    'action' => 'Edited page'
                ];
            }
            
            foreach ($recentCreations as $creation) {
                $activities[] = [
                    'type' => 'create',
                    'page_title' => $creation['title'],
                    'page_slug' => $creation['slug'],
                    'timestamp' => $creation['created_at'],
                    'action' => 'Created page'
                ];
            }
            
            // Sort by timestamp and take top 10
            usort($activities, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            
            return array_slice($activities, 0, 10);
            
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get user watchlist
     */
    private function getWatchlist(int $userId): array
    {
        try {
            $watchlist = $this->db->select(
                'SELECT p.*, uw.created_at as watch_date 
                 FROM user_watchlist uw 
                 JOIN pages p ON uw.page_id = p.id 
                 WHERE uw.user_id = ? 
                 ORDER BY uw.created_at DESC 
                 LIMIT 10',
                [$userId]
            );
            
            return $watchlist;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get quick stats for dashboard
     */
    private function getQuickStats(int $userId): array
    {
        try {
            // Get today's activity
            $todayActivity = $this->db->select(
                'SELECT COUNT(*) as count FROM page_history WHERE user_id = ? AND DATE(created_at) = CURDATE()',
                [$userId]
            );
            
            // Get this week's activity
            $weekActivity = $this->db->select(
                'SELECT COUNT(*) as count FROM page_history WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
                [$userId]
            );
            
            // Get this month's activity
            $monthActivity = $this->db->select(
                'SELECT COUNT(*) as count FROM page_history WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
                [$userId]
            );
            
            // Get total pages count
            $totalPages = $this->db->select(
                'SELECT COUNT(*) as count FROM pages',
                []
            );
            
            return [
                'today_activity' => $todayActivity[0]['count'] ?? 0,
                'week_activity' => $weekActivity[0]['count'] ?? 0,
                'month_activity' => $monthActivity[0]['count'] ?? 0,
                'total_pages_site' => $totalPages[0]['count'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'today_activity' => 0,
                'week_activity' => 0,
                'month_activity' => 0,
                'total_pages_site' => 0
            ];
        }
    }

    /**
     * Get site-wide statistics
     */
    private function getSiteStatistics(): array
    {
        try {
            // Get total pages count
            $totalPages = $this->db->select(
                'SELECT COUNT(*) as count FROM pages',
                []
            );
            
            // Get total users count
            $totalUsers = $this->db->select(
                'SELECT COUNT(*) as count FROM users',
                []
            );
            
            // Get total edits count
            $totalEdits = $this->db->select(
                'SELECT COUNT(*) as count FROM page_history',
                []
            );
            
            // Get total categories count
            $totalCategories = $this->db->select(
                'SELECT COUNT(DISTINCT namespace) as count FROM pages WHERE namespace != "main"',
                []
            );
            
            return [
                'total_pages' => $totalPages[0]['count'] ?? 0,
                'total_users' => $totalUsers[0]['count'] ?? 0,
                'total_edits' => $totalEdits[0]['count'] ?? 0,
                'total_categories' => $totalCategories[0]['count'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'total_pages' => 0,
                'total_users' => 0,
                'total_edits' => 0,
                'total_categories' => 0
            ];
        }
    }
}
