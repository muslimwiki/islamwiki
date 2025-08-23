<?php

/**
 * User Management Service
 *
 * Advanced user management capabilities for the AmanSecurity extension.
 *
 * @package IslamWiki\Extensions\AmanSecurity\Services
 * @version 0.0.1.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Extensions\AmanSecurity\Services;

use IslamWiki\Core\Database\Connection;

class UserManagementService
{
    private Connection $db;
    private array $config;

    public function __construct(Connection $db, array $config = [])
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Get all users with pagination and filtering.
     */
    public function getUsers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $whereClause = 'WHERE 1=1';
        $params = [];

        // Apply filters
        if (!empty($filters['search'])) {
            $whereClause .= ' AND (username LIKE ? OR email LIKE ? OR display_name LIKE ?)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if (isset($filters['is_active'])) {
            $whereClause .= ' AND is_active = ?';
            $params[] = $filters['is_active'];
        }

        if (isset($filters['is_admin'])) {
            $whereClause .= ' AND is_admin = ?';
            $params[] = $filters['is_admin'];
        }

        if (!empty($filters['role'])) {
            $whereClause .= ' AND role = ?';
            $params[] = $filters['role'];
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM users {$whereClause}";
        $countResult = $this->db->select($countSql, $params);
        $total = $countResult[0]['total'] ?? 0;

        // Get users
        $sql = "SELECT id, username, email, display_name, is_active, is_admin, role, 
                       created_at, last_login_at, last_login_ip 
                FROM users {$whereClause} 
                ORDER BY created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $users = $this->db->select($sql, $params);

        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get user statistics.
     */
    public function getUserStatistics(): array
    {
        $stats = [];

        // Total users
        $totalUsers = $this->db->select('SELECT COUNT(*) as count FROM users');
        $stats['total_users'] = $totalUsers[0]['count'] ?? 0;

        // Active users
        $activeUsers = $this->db->select('SELECT COUNT(*) as count FROM users WHERE is_active = 1');
        $stats['active_users'] = $activeUsers[0]['count'] ?? 0;

        // Admin users
        $adminUsers = $this->db->select('SELECT COUNT(*) as count FROM users WHERE is_admin = 1');
        $stats['admin_users'] = $adminUsers[0]['count'] ?? 0;

        // Users registered this month
        $thisMonth = $this->db->select("SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $stats['users_this_month'] = $thisMonth[0]['count'] ?? 0;

        // Users who logged in today
        $todayLogin = $this->db->select("SELECT COUNT(*) as count FROM users WHERE DATE(last_login_at) = CURRENT_DATE()");
        $stats['users_logged_in_today'] = $todayLogin[0]['count'] ?? 0;

        // Users by role
        $usersByRole = $this->db->select('SELECT role, COUNT(*) as count FROM users GROUP BY role');
        $stats['users_by_role'] = $usersByRole;

        return $stats;
    }

    /**
     * Update user status.
     */
    public function updateUserStatus(int $userId, array $updates): bool
    {
        try {
            $allowedFields = ['is_active', 'is_admin', 'role', 'display_name', 'email'];
            $updateFields = [];
            $params = [];

            foreach ($updates as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $value;
                }
            }

            if (empty($updateFields)) {
                return false;
            }

            $updateFields[] = 'updated_at = CURRENT_TIMESTAMP';
            $params[] = $userId;

            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $this->db->update($sql, $params);

            return true;
        } catch (\Exception $e) {
            error_log('UserManagementService::updateUserStatus error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user account.
     */
    public function deleteUser(int $userId): bool
    {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Delete user preferences
            $this->db->delete('DELETE FROM user_preferences WHERE user_id = ?', [$userId]);

            // Delete user sessions
            $this->db->delete('DELETE FROM user_sessions WHERE user_id = ?', [$userId]);

            // Delete user
            $this->db->delete('DELETE FROM users WHERE id = ?', [$userId]);

            // Commit transaction
            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            error_log('UserManagementService::deleteUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user activity log.
     */
    public function getUserActivity(int $userId, int $limit = 50): array
    {
        try {
            $sql = "SELECT action, ip_address, user_agent, created_at, metadata 
                    FROM user_activity_log 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            
            return $this->db->select($sql, [$userId, $limit]);
        } catch (\Exception $e) {
            error_log('UserManagementService::getUserActivity error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Bulk user operations.
     */
    public function bulkUserOperation(array $userIds, string $operation, array $data = []): array
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($userIds as $userId) {
            try {
                switch ($operation) {
                    case 'activate':
                        $this->updateUserStatus($userId, ['is_active' => 1]);
                        $results['success'][] = $userId;
                        break;
                    case 'deactivate':
                        $this->updateUserStatus($userId, ['is_active' => 0]);
                        $results['success'][] = $userId;
                        break;
                    case 'make_admin':
                        $this->updateUserStatus($userId, ['is_admin' => 1, 'role' => 'admin']);
                        $results['success'][] = $userId;
                        break;
                    case 'remove_admin':
                        $this->updateUserStatus($userId, ['is_admin' => 0, 'role' => 'user']);
                        $results['success'][] = $userId;
                        break;
                    case 'delete':
                        if ($this->deleteUser($userId)) {
                            $results['success'][] = $userId;
                        } else {
                            $results['failed'][] = $userId;
                        }
                        break;
                    default:
                        $results['failed'][] = $userId;
                }
            } catch (\Exception $e) {
                $results['failed'][] = $userId;
                error_log("Bulk operation failed for user {$userId}: " . $e->getMessage());
            }
        }

        return $results;
    }
} 