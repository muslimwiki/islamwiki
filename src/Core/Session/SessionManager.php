<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Core\Session;

/**
 * Wisal (وصال) - Connection Manager
 *
 * Handles secure session management and user connection state.
 * Wisal means "connection" or "link" in Arabic, representing the
 * persistent connection between users and the application.
 */
class Wisal
{
    /**
     * @var string Session name
     */
    private string $sessionName = 'islamwiki_session';

    /**
     * @var int Session lifetime in seconds (24 hours)
     */
    private int $sessionLifetime = 86400;

    /**
     * @var string Session cookie path
     */
    private string $cookiePath = '/';

    /**
     * @var bool Whether to use secure cookies
     */
    private bool $secure = false;

    /**
     * @var bool Whether to use HTTP only cookies
     */
    private bool $httpOnly = true;

    /**
     * @var string SameSite cookie attribute
     */
    private string $sameSite = 'Lax';

    /**
     * Create a new Wisal connection manager instance.
     */
    public function __construct(array $config = [])
    {
        $this->sessionName = $config['name'] ?? $this->sessionName;
        $this->sessionLifetime = $config['lifetime'] ?? $this->sessionLifetime;
        $this->cookiePath = $config['path'] ?? $this->cookiePath;
        $this->secure = $config['secure'] ?? $this->secure;
        $this->httpOnly = $config['http_only'] ?? $this->httpOnly;
        $this->sameSite = $config['same_site'] ?? $this->sameSite;
    }

    /**
     * Start the session with secure configuration.
     */
    public function start(): void
    {
        // Set secure session configuration
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', $this->httpOnly ? '1' : '0');
        ini_set('session.cookie_secure', $this->secure ? '1' : '0');
        ini_set('session.cookie_samesite', $this->sameSite);
        ini_set('session.cookie_path', $this->cookiePath);
        ini_set('session.gc_maxlifetime', (string) $this->sessionLifetime);
        ini_set('session.cookie_lifetime', (string) $this->sessionLifetime);

        // Set session name
        session_name($this->sessionName);

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Only regenerate session ID if this is a completely new session
        // Don't regenerate if we have any session data or if there's a session cookie
        if (empty($_SESSION) && !$this->has('last_regeneration') && !isset($_COOKIE[$this->sessionName])) {
            $this->regenerate();
        } elseif ($this->has('last_regeneration') && (time() - $this->get('last_regeneration', 0) > 1800)) { // 30 minutes instead of 5
            $this->regenerate();
        }
    }

    /**
     * Regenerate session ID while preserving data.
     */
    public function regenerate(): void
    {
        // Store current session data
        $sessionData = $_SESSION;
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Restore session data
        $_SESSION = $sessionData;
        
        $this->put('last_regeneration', time());
    }

    /**
     * Get a value from the session.
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a value in the session.
     */
    public function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a key exists in the session.
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a value from the session.
     */
    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data.
     */
    public function clear(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Set user authentication data.
     */
    public function login(int $userId, string $username, bool $isAdmin = false): void
    {
        error_log("SessionManager::login - Setting user data: ID=$userId, Username=$username, IsAdmin=" . ($isAdmin ? 'true' : 'false'));
        
        // Set user data first
        $this->put('user_id', $userId);
        $this->put('username', $username);
        $this->put('is_admin', $isAdmin);
        $this->put('logged_in_at', time());
        
        error_log("SessionManager::login - User data set, session data: " . print_r($_SESSION, true));
        
        // Then regenerate session ID for security
        $this->regenerate();
        
        error_log("SessionManager::login - Session regenerated, final session data: " . print_r($_SESSION, true));
    }

    /**
     * Log out the current user.
     */
    public function logout(): void
    {
        $this->forget('user_id');
        $this->forget('username');
        $this->forget('is_admin');
        $this->forget('logged_in_at');
        $this->regenerate();
    }

    /**
     * Check if user is logged in.
     */
    public function isLoggedIn(): bool
    {
        return $this->has('user_id') && $this->has('username');
    }

    /**
     * Get the current user ID.
     */
    public function getUserId(): ?int
    {
        return $this->get('user_id');
    }

    /**
     * Get the current username.
     */
    public function getUsername(): ?string
    {
        return $this->get('username');
    }

    /**
     * Check if current user is admin.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->get('is_admin', false);
    }

    /**
     * Generate a CSRF token.
     */
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->put('csrf_token', $token);
        return $token;
    }

    /**
     * Get the current CSRF token.
     */
    public function getCsrfToken(): string
    {
        if (!$this->has('csrf_token')) {
            return $this->generateCsrfToken();
        }
        return $this->get('csrf_token');
    }

    /**
     * Verify a CSRF token.
     */
    public function verifyCsrfToken(string $token): bool
    {
        return hash_equals($this->getCsrfToken(), $token);
    }

    /**
     * Set remember me token.
     */
    public function setRememberToken(string $token): void
    {
        $this->put('remember_token', $token);
    }

    /**
     * Get remember me token.
     */
    public function getRememberToken(): ?string
    {
        return $this->get('remember_token');
    }
}
