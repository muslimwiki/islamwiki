<?php

/**
 * Aman (أمان) - Security Manager Extension
 *
 * Comprehensive authentication and security system for IslamWiki.
 * Aman means "security" or "safety" in Arabic, representing the protective
 * layer that ensures user authentication and authorization.
 *
 * @package IslamWiki\Extensions\AmanSecurity
 * @version 0.0.1.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Extensions\AmanSecurity;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Models\User;

class AmanSecurity
{
    private WisalSession $session;
    private Connection $db;
    private ?array $currentUser = null;
    private array $config;

    /**
     * Create a new Aman security manager.
     */
    public function __construct(WisalSession $session, Connection $db, array $config = [])
    {
        $this->session = $session;
        $this->db = $db;
        $this->config = array_merge([
            'session_timeout' => 3600,
            'max_login_attempts' => 5,
            'password_min_length' => 8,
            'require_email_verification' => true,
            'enable_two_factor' => false
        ], $config);
    }

    /**
     * Attempt to authenticate a user with username/email and password.
     */
    public function attempt(string $username, string $password): bool
    {
        try {
            error_log("AmanSecurity::attempt - Starting login attempt for username: $username");
            
            // Find user by username or email
            $user = $this->db->select(
                'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
                [$username, $username]
            );

            if (empty($user)) {
                error_log("AmanSecurity::attempt - User not found: $username");
                return false;
            }

            $userData = $user[0];
            error_log("AmanSecurity::attempt - User found: ID={$userData['id']}, Username={$userData['username']}, IsAdmin={$userData['is_admin']}");

            // Verify password
            if (!password_verify($password, $userData['password'])) {
                error_log("AmanSecurity::attempt - Password verification failed for user: $username");
                return false;
            }

            error_log("AmanSecurity::attempt - Password verified, logging in user");

            // Login the user using new session method
            $this->session->setUserLoggedIn([
                'id' => $userData['id'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'role' => $userData['is_admin'] ? 'admin' : 'user'
            ]);

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
            error_log("AmanSecurity::attempt - Login successful for user: {$userData['username']}");
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

            // Validate password length
            if (strlen($userData['password']) < $this->config['password_min_length']) {
                throw new \InvalidArgumentException("Password must be at least {$this->config['password_min_length']} characters long");
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
                $this->session->setUserLoggedIn([
                    'id' => $userId,
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'role' => 'user'
                ]);
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
        // Clear session data manually since the session class doesn't have a logout method
        $this->session->put('user_id', null);
        $this->session->put('username', null);
        $this->session->put('is_admin', null);
        $this->session->put('logged_in_at', null);
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
            if ($userId === null) {
                return null;
            }
            
            $user = $this->db->select('SELECT * FROM users WHERE id = ?', [(int)$userId]);

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
     * Get extension configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Update extension configuration.
     */
    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
} 