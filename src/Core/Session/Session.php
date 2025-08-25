<?php

/**
 * Session Manager
 *
 * Comprehensive session management system for IslamWiki.
 * This class provides comprehensive session handling, user authentication
 * state management, and secure session operations.
 *
 * @package IslamWiki\Core\Session
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Session;

use IslamWiki\Core\Logging\Logger;

/**
 * Session Manager - Session Management System
 *
 * This class provides comprehensive session handling, user authentication
 * state management, and secure session operations.
 */
class Session
{
    /**
     * The logging system instance.
     */
    protected Logger $logger;

    /**
     * Session configuration.
     */
    private array $config;

    /**
     * Whether the session has been started.
     */
    private bool $started = false;

    /**
     * Session data storage.
     */
    private array $data = [];

    /**
     * CSRF token storage.
     */
    private string $csrfToken = '';

    /**
     * Create a new session manager instance.
     *
     * @param Logger $logger The logging system
     * @param array $config Session configuration
     */
    public function __construct(Logger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'name' => 'ISLAMWIKI_SESSION',
            'lifetime' => 3600,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ], $config);

        $this->initialize();
    }

    /**
     * Initialize session system.
     */
    private function initialize(): void
    {
        // Start PHP session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            $this->startPhpSession();
        }

        $this->logger->info('Session system initialized');
    }

    /**
     * Start PHP session.
     */
    private function startPhpSession(): void
    {
        // Set session configuration
        ini_set('session.gc_maxlifetime', $this->config['lifetime']);
        ini_set('session.cookie_lifetime', $this->config['lifetime']);
        ini_set('session.cookie_secure', $this->config['secure'] ? '1' : '0');
        ini_set('session.cookie_httponly', $this->config['httponly'] ? '1' : '0');

        if (isset($this->config['samesite'])) {
            ini_set('session.cookie_samesite', $this->config['samesite']);
        }

        session_start();
        $this->started = true;
    }

    /**
     * Check if user is logged in.
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user ID.
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data.
     */
    public function getUserData(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? 'user',
            'is_admin' => $_SESSION['is_admin'] ?? false
        ];
    }

    /**
     * Set user as logged in.
     */
    public function setUserLoggedIn(array $userData): void
    {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'] ?? null;
        $_SESSION['email'] = $userData['email'] ?? null;
        $_SESSION['role'] = $userData['role'] ?? 'user';
        $_SESSION['is_admin'] = $userData['is_admin'] ?? false;
        $_SESSION['login_time'] = time();

        $this->logger->info("User logged in: {$userData['username']}", $userData);
    }

    /**
     * Set user as logged out.
     */
    public function setUserLoggedOut(): void
    {
        $userData = $this->getUserData();
        
        // Clear session data
        session_unset();
        session_destroy();
        
        // Start new session
        session_start();
        
        if ($userData) {
            $this->logger->info("User logged out: {$userData['username']}", $userData);
        }
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $_SESSION['is_admin'] ?? false;
    }

    /**
     * Get session data.
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session data.
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Remove session data.
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Check if session has data.
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Regenerate session ID.
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
        $this->logger->info('Session ID regenerated');
    }

    /**
     * Get session statistics.
     */
    public function getStatistics(): array
    {
        return [
            'started' => $this->started,
            'user_id' => $this->getUserId(),
            'is_logged_in' => $this->isLoggedIn(),
            'is_admin' => $this->isAdmin(),
            'session_id' => session_id(),
            'session_name' => session_name()
        ];
    }

    /**
     * Generate a CSRF token.
     *
     * @return string The generated CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (empty($this->csrfToken)) {
            $this->csrfToken = bin2hex(random_bytes(32));
        }
        return $this->csrfToken;
    }

    /**
     * Get the current CSRF token.
     *
     * @return string The current CSRF token
     */
    public function getCsrfToken(): string
    {
        return $this->generateCsrfToken();
    }

    /**
     * Verify a CSRF token.
     *
     * @param string $token The token to verify
     * @return bool True if token is valid
     */
    public function verifyCsrfToken(string $token): bool
    {
        return hash_equals($this->csrfToken, $token);
    }
} 