<?php

declare(strict_types=1);

namespace IslamWiki\Core\Auth\Services;

use IslamWiki\Core\Auth\Models\User;
use IslamWiki\Core\Database\MizanDatabase;

/**
 * Aman Authentication Service
 * 
 * Handles user authentication, password verification, and session management.
 * 
 * @package IslamWiki\Core\Auth\Services
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class AmanAuthService
{
    private MizanDatabase $database;
    private ?User $currentUser = null;

    public function __construct(MizanDatabase $database)
    {
        $this->database = $database;
    }

    /**
     * Authenticate user with credentials
     */
    public function authenticate(string $username, string $password): array
    {
        try {
            // Get user from database
            $user = $this->getUserByUsername($username);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ];
            }

            // Verify password
            if (!$this->verifyPassword($password, $user->password_hash)) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ];
            }

            // Check if user is active
            if (!$user->is_active) {
                return [
                    'success' => false,
                    'message' => 'Account is deactivated. Please contact administrator.'
                ];
            }

            // Update last login
            $this->updateLastLogin($user->id);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Authentication successful.'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Authentication failed. Please try again.'
            ];
        }
    }

    /**
     * Login user (set current user)
     */
    public function login(User $user): void
    {
        $this->currentUser = $user;
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $this->currentUser = null;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->currentUser !== null;
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * Verify password against hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash password for storage
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Get user by username
     */
    private function getUserByUsername(string $username): ?User
    {
        $query = "SELECT * FROM mizan_users WHERE username = ? AND deleted_at IS NULL";
        $result = $this->database->query($query, [$username]);
        
        if ($result && count($result) > 0) {
            return new User($result[0]);
        }
        
        return null;
    }

    /**
     * Update user's last login timestamp
     */
    private function updateLastLogin(int $userId): void
    {
        $query = "UPDATE mizan_users SET last_login = NOW() WHERE id = ?";
        $this->database->query($query, [$userId]);
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        // Get user roles and check permissions
        $userRoles = $this->getUserRoles($this->currentUser->id);
        
        foreach ($userRoles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->hasPermission('admin.access');
    }

    /**
     * Get user roles
     */
    private function getUserRoles(int $userId): array
    {
        $query = "
            SELECT r.* FROM mizan_roles r
            JOIN mizan_user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ? AND r.is_active = 1
        ";
        
        $result = $this->database->query($query, [$userId]);
        
        $roles = [];
        foreach ($result as $roleData) {
            $roles[] = new Role($roleData);
        }
        
        return $roles;
    }
} 