<?php

declare(strict_types=1);

namespace IslamWiki\Core\Admin;

use IslamWiki\Core\Http\Controllers\Controller;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Auth\Services\AmanAuthService;
use IslamWiki\Core\Auth\Services\AmanUserService;
use IslamWiki\Core\Auth\Services\AmanRoleService;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinManager;

/**
 * Admin Controller
 * 
 * Handles admin-only functionality with role-based access control.
 * 
 * @package IslamWiki\Core\Admin
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class AdminController extends Controller
{
    private AmanAuthService $authService;
    private AmanUserService $userService;
    private AmanRoleService $roleService;
    private SkinManager $skinManager;

    public function __construct(
        AmanAuthService $authService,
        AmanUserService $userService,
        AmanRoleService $roleService,
        SkinManager $skinManager
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->skinManager = $skinManager;
    }

    /**
     * Display admin dashboard
     */
    public function dashboard(): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->redirect('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $stats = $this->getSystemStats();
        $recentUsers = $this->userService->getRecentUsers(10);
        $systemHealth = $this->getSystemHealth();

        return $this->view('admin.dashboard', [
            'page_title' => 'Admin Dashboard - IslamWiki',
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'system_health' => $systemHealth,
            'active_tab' => 'dashboard'
        ]);
    }

    /**
     * Display user management page
     */
    public function users(): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->redirect('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $users = $this->userService->getAllUsers();
        $roles = $this->roleService->getAllRoles();

        return $this->view('admin.users', [
            'page_title' => 'User Management - IslamWiki',
            'users' => $users,
            'roles' => $roles,
            'active_tab' => 'users'
        ]);
    }

    /**
     * Display role management page
     */
    public function roles(): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->redirect('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $roles = $this->roleService->getAllRoles();
        $permissions = $this->roleService->getAllPermissions();

        return $this->view('admin.roles', [
            'page_title' => 'Role Management - IslamWiki',
            'roles' => $roles,
            'permissions' => $permissions,
            'active_tab' => 'roles'
        ]);
    }

    /**
     * Display system settings page
     */
    public function settings(): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->redirect('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $systemSettings = $this->getSystemSettings();
        $extensions = $this->getInstalledExtensions();

        return $this->view('admin.settings', [
            'page_title' => 'System Settings - IslamWiki',
            'system_settings' => $systemSettings,
            'extensions' => $extensions,
            'active_tab' => 'settings'
        ]);
    }

    /**
     * Display skin management page
     */
    public function skins(): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->redirect('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $availableSkins = $this->skinManager->getAvailableSkins();
        $activeSkin = $this->skinManager->getActiveSkin();
        $skinStats = $this->skinManager->getSkinStats();

        return $this->view('admin.skins', [
            'page_title' => 'Skin Management - IslamWiki',
            'available_skins' => $availableSkins,
            'active_skin' => $activeSkin,
            'skin_stats' => $skinStats,
            'active_tab' => 'skins'
        ]);
    }

    /**
     * Display system logs page
     */
    public function logs(): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->redirect('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        $logs = $this->getSystemLogs();
        $logTypes = $this->getLogTypes();

        return $this->view('admin.logs', [
            'page_title' => 'System Logs - IslamWiki',
            'logs' => $logs,
            'log_types' => $logTypes,
            'active_tab' => 'logs'
        ]);
    }

    /**
     * Create new user
     */
    public function createUser(Request $request): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }

        $userData = $request->only(['username', 'email', 'password', 'first_name', 'last_name', 'roles']);
        
        // Validate user data
        $validation = $this->validateUserData($userData);
        if (!$validation['valid']) {
            return $this->jsonResponse(['success' => false, 'message' => $validation['message']], 400);
        }

        // Create user
        $user = $this->userService->createUser($userData);
        
        if ($user) {
            // Assign roles
            if (!empty($userData['roles'])) {
                $this->roleService->assignRolesToUser($user->id, $userData['roles']);
            }
            
            return $this->jsonResponse(['success' => true, 'message' => 'User created successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to create user'], 500);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, int $userId): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }

        $userData = $request->only(['email', 'first_name', 'last_name', 'is_active', 'roles']);
        
        // Update user
        $updated = $this->userService->updateUser($userId, $userData);
        
        if ($updated) {
            // Update roles if provided
            if (isset($userData['roles'])) {
                $this->roleService->updateUserRoles($userId, $userData['roles']);
            }
            
            return $this->jsonResponse(['success' => true, 'message' => 'User updated successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update user'], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(int $userId): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }

        // Prevent admin from deleting themselves
        $currentUser = $this->authService->getCurrentUser();
        if ($currentUser->id === $userId) {
            return $this->jsonResponse(['success' => false, 'message' => 'Cannot delete your own account'], 400);
        }

        $deleted = $this->userService->deleteUser($userId);
        
        if ($deleted) {
            return $this->jsonResponse(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    }

    /**
     * Create new role
     */
    public function createRole(Request $request): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }

        $roleData = $request->only(['name', 'display_name', 'description', 'permissions']);
        
        // Validate role data
        $validation = $this->validateRoleData($roleData);
        if (!$validation['valid']) {
            return $this->jsonResponse(['success' => false, 'message' => $validation['message']], 400);
        }

        // Create role
        $role = $this->roleService->createRole($roleData);
        
        if ($role) {
            return $this->jsonResponse(['success' => true, 'message' => 'Role created successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to create role'], 500);
        }
    }

    /**
     * Update role
     */
    public function updateRole(Request $request, int $roleId): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }

        $roleData = $request->only(['display_name', 'description', 'permissions', 'is_active']);
        
        // Update role
        $updated = $this->roleService->updateRole($roleId, $roleData);
        
        if ($updated) {
            return $this->jsonResponse(['success' => true, 'message' => 'Role updated successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to update role'], 500);
        }
    }

    /**
     * Delete role
     */
    public function deleteRole(int $roleId): Response
    {
        // Check admin access
        if (!$this->authService->isAdmin()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
        }

        // Prevent deletion of system roles
        $role = $this->roleService->getRole($roleId);
        if ($role && $role->is_system) {
            return $this->jsonResponse(['success' => false, 'message' => 'Cannot delete system roles'], 400);
        }

        $deleted = $this->roleService->deleteRole($roleId);
        
        if ($deleted) {
            return $this->jsonResponse(['success' => true, 'message' => 'Role deleted successfully']);
        } else {
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to delete role'], 500);
        }
    }

    /**
     * Get system statistics
     */
    private function getSystemStats(): array
    {
        return [
            'total_users' => $this->userService->getTotalUsers(),
            'active_users' => $this->userService->getActiveUsers(),
            'total_roles' => $this->roleService->getTotalRoles(),
            'total_skins' => count($this->skinManager->getAvailableSkins()),
            'system_uptime' => $this->getSystemUptime(),
            'database_size' => $this->getDatabaseSize()
        ];
    }

    /**
     * Get system health information
     */
    private function getSystemHealth(): array
    {
        return [
            'database_status' => 'healthy',
            'cache_status' => 'healthy',
            'disk_space' => $this->getDiskSpace(),
            'memory_usage' => $this->getMemoryUsage(),
            'php_version' => PHP_VERSION,
            'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ];
    }

    /**
     * Get system settings
     */
    private function getSystemSettings(): array
    {
        return [
            'site_name' => 'IslamWiki',
            'site_description' => 'Islamic Knowledge Platform',
            'maintenance_mode' => false,
            'user_registration' => true,
            'email_verification' => true,
            'max_upload_size' => '10MB'
        ];
    }

    /**
     * Get installed extensions
     */
    private function getInstalledExtensions(): array
    {
        return [
            'SafaSkinExtension' => [
                'name' => 'SafaSkinExtension',
                'version' => '0.0.1',
                'status' => 'active',
                'description' => 'Unified skin management system'
            ]
        ];
    }

    /**
     * Get system logs
     */
    private function getSystemLogs(): array
    {
        // This would retrieve actual system logs
        return [];
    }

    /**
     * Get log types
     */
    private function getLogTypes(): array
    {
        return ['error', 'warning', 'info', 'debug'];
    }

    /**
     * Validate user data
     */
    private function validateUserData(array $userData): array
    {
        if (empty($userData['username']) || strlen($userData['username']) < 3) {
            return ['valid' => false, 'message' => 'Username must be at least 3 characters long.'];
        }
        
        if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Please enter a valid email address.'];
        }
        
        if (empty($userData['password']) || strlen($userData['password']) < 8) {
            return ['valid' => false, 'message' => 'Password must be at least 8 characters long.'];
        }
        
        if (empty($userData['first_name'])) {
            return ['valid' => false, 'message' => 'First name is required.'];
        }
        
        if (empty($userData['last_name'])) {
            return ['valid' => false, 'message' => 'Last name is required.'];
        }
        
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Validate role data
     */
    private function validateRoleData(array $roleData): array
    {
        if (empty($roleData['name']) || strlen($roleData['name']) < 2) {
            return ['valid' => false, 'message' => 'Role name must be at least 2 characters long.'];
        }
        
        if (empty($roleData['display_name'])) {
            return ['valid' => false, 'message' => 'Display name is required.'];
        }
        
        return ['valid' => true, 'message' => ''];
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): string
    {
        // This would get actual system uptime
        return '24 hours';
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        // This would get actual database size
        return '15.2 MB';
    }

    /**
     * Get disk space
     */
    private function getDiskSpace(): array
    {
        // This would get actual disk space
        return [
            'total' => '100 GB',
            'used' => '45 GB',
            'free' => '55 GB',
            'percentage' => 45
        ];
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        // This would get actual memory usage
        return [
            'total' => '8 GB',
            'used' => '2.5 GB',
            'free' => '5.5 GB',
            'percentage' => 31
        ];
    }
} 