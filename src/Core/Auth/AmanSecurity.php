<?php
declare(strict_types=1);

/**
 * Aman (أمان) - Security Manager
 * 
 * Comprehensive authentication and security system for IslamWiki.
 * Aman means "security" or "safety" in Arabic, representing the protective
 * layer that ensures user authentication and authorization.
 * 
 * @package IslamWiki\Core\Auth
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Core\Auth;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Session\Wisal;
use IslamWiki\Models\User;

class AmanSecurity
{
    private Wisal $session;
    private Connection $db;
    private ?array $currentUser = null;
    
    /**
     * Create a new Aman security manager.
     */
    public function __construct(Wisal $session, Connection $db)
    {
        $this->session = $session;
        $this->db = $db;
    }
    
    /**
     * Attempt to authenticate a user with username/email and password.
     */
    public function attempt(string $username, string $password): bool
    {
        try {
            // Find user by username or email
            $user = $this->db->select(
                'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
                [$username, $username]
            );
            
            if (empty($user)) {
                return false;
            }
            
            $userData = $user[0];
            
            // Verify password
            if (!password_verify($password, $userData['password'])) {
                return false;
            }
            
            // Login the user
            $this->session->login(
                $userData['id'],
                $userData['username'],
                (bool) $userData['is_admin']
            );
            
            // Update last login
            $this->db->update(
                'UPDATE users SET last_login_at = ?, last_login_ip = ? WHERE id = ?',
                [
                    date('Y-m-d H:i:s'),
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $userData['id']
                ]
            );
            
            $this->currentUser = $userData;
            return true;
            
        } catch (\Exception $e) {
            error_log('Aman::attempt error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register a new user.
     */
    public function register(array $userData): ?int
    {
        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'display_name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    throw new \InvalidArgumentException("Missing required field: $field");
                }
            }
            
            // Check if username or email already exists
            $existing = $this->db->select(
                'SELECT id FROM users WHERE username = ? OR email = ?',
                [$userData['username'], $userData['email']]
            );
            
            if (!empty($existing)) {
                throw new \InvalidArgumentException('Username or email already exists');
            }
            
            // Hash password
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Set default values
            $userData['is_active'] = 1;
            $userData['is_admin'] = 0;
            $userData['created_at'] = date('Y-m-d H:i:s');
            $userData['updated_at'] = date('Y-m-d H:i:s');
            
            // Insert user
            $columns = implode(', ', array_keys($userData));
            $placeholders = implode(', ', array_fill(0, count($userData), '?'));
            $sql = "INSERT INTO users ({$columns}) VALUES ({$placeholders})";
            $userId = $this->db->insert($sql, array_values($userData));
            
            // Auto-login after registration
            if ($userId) {
                $this->session->login($userId, $userData['username'], false);
                $this->currentUser = array_merge($userData, ['id' => $userId]);
            }
            
            return $userId;
            
        } catch (\Exception $e) {
            error_log('Aman::register error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        $this->session->logout();
        $this->currentUser = null;
    }
    
    /**
     * Get the current authenticated user.
     */
    public function user(): ?array
    {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }
        
        if (!$this->session->isLoggedIn()) {
            return null;
        }
        
        try {
            $userId = $this->session->getUserId();
            $user = $this->db->select('SELECT * FROM users WHERE id = ?', [$userId]);
            
            if (!empty($user)) {
                $this->currentUser = $user[0];
                return $this->currentUser;
            }
            
            // User not found in database, logout
            $this->logout();
            return null;
            
        } catch (\Exception $e) {
            error_log('Aman::user error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if user is authenticated.
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }
    
    /**
     * Check if user is a guest (not authenticated).
     */
    public function guest(): bool
    {
        return !$this->check();
    }
    
    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        $user = $this->user();
        return $user && !empty($user['is_admin']) && $user['is_admin'] == 1;
    }
    
    /**
     * Get user ID.
     */
    public function id(): ?int
    {
        $user = $this->user();
        return $user ? $user['id'] : null;
    }
    
    /**
     * Get username.
     */
    public function username(): ?string
    {
        $user = $this->user();
        return $user ? $user['username'] : null;
    }
    
    /**
     * Check if user has a specific permission.
     */
    public function can(string $permission): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }
        
        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check specific permissions
        switch ($permission) {
            case 'create_pages':
                return true; // All authenticated users can create pages
            case 'edit_pages':
                return true; // All authenticated users can edit pages
            case 'delete_pages':
                return $this->isAdmin(); // Only admins can delete pages
            case 'manage_users':
                return $this->isAdmin(); // Only admins can manage users
            case 'manage_site':
                return $this->isAdmin(); // Only admins can manage site
            default:
                return false;
        }
    }
    
    /**
     * Require authentication for a route.
     */
    public function requireAuth(): void
    {
        if (!$this->check()) {
            throw new \Exception('Authentication required');
        }
    }
    
    /**
     * Require admin privileges for a route.
     */
    public function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            throw new \Exception('Admin privileges required');
        }
    }
    
    /**
     * Require a specific permission for a route.
     */
    public function requirePermission(string $permission): void
    {
        if (!$this->can($permission)) {
            throw new \Exception("Permission required: $permission");
        }
    }
    
    /**
     * Generate a password reset token.
     */
    public function generatePasswordResetToken(string $email): ?string
    {
        try {
            $user = $this->db->select('SELECT id FROM users WHERE email = ? AND is_active = 1', [$email]);
            
            if (empty($user)) {
                return null;
            }
            
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->db->update('users', [
                'password_reset_token' => $token,
                'password_reset_expires' => $expires
            ], ['id' => $user[0]['id']]);
            
            return $token;
            
        } catch (\Exception $e) {
            error_log('Aman::generatePasswordResetToken error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Reset password using token.
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        try {
            $user = $this->db->select(
                'SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW() AND is_active = 1',
                [$token]
            );
            
            if (empty($user)) {
                return false;
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $this->db->update('users', [
                'password' => $hashedPassword,
                'password_reset_token' => null,
                'password_reset_expires' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $user[0]['id']]);
            
            return true;
            
        } catch (\Exception $e) {
            error_log('Aman::resetPassword error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user profile.
     */
    public function updateProfile(int $userId, array $data): bool
    {
        try {
            // Remove sensitive fields that shouldn't be updated
            unset($data['id'], $data['password'], $data['is_admin'], $data['is_active']);
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $this->db->update('users', $data, ['id' => $userId]);
            
            // Update current user data if it's the same user
            if ($this->id() === $userId) {
                $this->currentUser = null; // Force reload
            }
            
            return true;
            
        } catch (\Exception $e) {
            error_log('Aman::updateProfile error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Change user password.
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        try {
            $user = $this->db->select('SELECT password FROM users WHERE id = ?', [$userId]);
            
            if (empty($user)) {
                return false;
            }
            
            // Verify current password
            if (!password_verify($currentPassword, $user[0]['password'])) {
                return false;
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $this->db->update('users', [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $userId]);
            
            return true;
            
        } catch (\Exception $e) {
            error_log('Aman::changePassword error: ' . $e->getMessage());
            return false;
        }
    }
} 