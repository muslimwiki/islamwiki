<?php

/**
 * Community Controller
 *
 * Comprehensive community management controller for user contributions,
 * discussions, moderation, and community features.
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Container;

/**
 * Community Controller - Handles Community Management Functionality
 */
class CommunityController extends Controller
{
    /**
     * Display the community dashboard.
     */
    public function index(Request $request): Response
    {
        try {
            $stats = $this->getCommunityStats();
            $recentDiscussions = $this->getRecentDiscussions(10);
            $topContributors = $this->getTopContributors();

            return $this->view('community/index', [
                'stats' => $stats,
                'recent_discussions' => $recentDiscussions,
                'top_contributors' => $topContributors,
                'title' => 'Community Dashboard - IslamWiki'
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display the users directory.
     */
    public function users(Request $request): Response
    {
        try {
            $search = $request->getQueryParams()['search'] ?? '';
            $sort = $request->getQueryParams()['sort'] ?? 'recent';
            $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
            $perPage = 20;

            $users = $this->getUsers($search, $sort, $page, $perPage);
            $totalUsers = $this->getTotalUsers($search);

            return $this->view('community/users', [
                'users' => $users,
                'search' => $search,
                'sort' => $sort,
                'currentPage' => $page,
                'totalPages' => ceil($totalUsers / $perPage),
                'title' => 'Community Users - IslamWiki'
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display community discussions.
     */
    public function discussions(Request $request): Response
    {
        try {
            $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
            $perPage = 20;

            $discussions = $this->getDiscussions($page, $perPage);
            $totalDiscussions = $this->getTotalDiscussions();

            return $this->view('community/discussions', [
                'discussions' => $discussions,
                'currentPage' => $page,
                'totalPages' => ceil($totalDiscussions / $perPage),
                'title' => 'Community Discussions - IslamWiki'
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Get community statistics.
     */
    private function getCommunityStats(): array
    {
        try {
            $sql = "SELECT COUNT(*) as total_users FROM users WHERE is_active = 1";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            $totalUsers = (int)($result['total_users'] ?? 0);
            
            return [
                'total_users' => $totalUsers,
                'active_users' => $totalUsers,
                'total_discussions' => 0,
                'total_contributions' => 0,
                'last_activity' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'total_users' => 0,
                'active_users' => 0,
                'total_discussions' => 0,
                'total_contributions' => 0,
                'last_activity' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Get recent discussions.
     */
    private function getRecentDiscussions(int $limit): array
    {
        try {
            $sql = "SELECT id, title, content, created_at FROM discussions ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get top contributors.
     */
    private function getTopContributors(): array
    {
        try {
            $sql = "SELECT id, username, display_name, created_at FROM users 
                    WHERE is_active = 1 ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get users with pagination and search.
     */
    private function getUsers(string $search, string $sort, int $page, int $perPage): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $searchTerm = "%{$search}%";
            
            $sql = "SELECT id, username, display_name, bio, created_at, last_login_at, is_active, is_admin 
                    FROM users WHERE is_active = 1";
            
            if (!empty($search)) {
                $sql .= " AND (username LIKE ? OR display_name LIKE ? OR bio LIKE ?)";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            
            if (!empty($search)) {
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);
            } else {
                $stmt->execute([$perPage, $offset]);
            }
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get total users count for pagination.
     */
    private function getTotalUsers(string $search): int
    {
        try {
            $searchTerm = "%{$search}%";
            
            $sql = "SELECT COUNT(*) as count FROM users WHERE is_active = 1";
            
            if (!empty($search)) {
                $sql .= " AND (username LIKE ? OR display_name LIKE ? OR bio LIKE ?)";
                $stmt = $this->db->getPdo()->prepare($sql);
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            } else {
                $stmt = $this->db->getPdo()->prepare($sql);
                $stmt->execute();
            }
            
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get discussions with pagination.
     */
    private function getDiscussions(int $page, int $perPage): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT id, title, content, created_at FROM discussions ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$perPage, $offset]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get total discussions count for pagination.
     */
    private function getTotalDiscussions(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM discussions";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
