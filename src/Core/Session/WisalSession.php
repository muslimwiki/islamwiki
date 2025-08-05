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
class WisalSession
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
        // Set session save path to our custom directory
        $sessionPath = __DIR__ . '/../../../storage/sessions';
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0777, true);
        }
        session_save_path($sessionPath);
        
        // Set secure session configuration
        ini_set('session.use_strict_mode', '1');
        
        // Handle CLI vs web environment
        if (php_sapi_name() === 'cli') {
            // For CLI, disable cookies and enable trans_sid
            ini_set('session.use_cookies', '0');
            ini_set('session.use_only_cookies', '0');
            ini_set('session.use_trans_sid', '1');
        } else {
            // For web, use secure cookie configuration
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', $this->httpOnly ? '1' : '0');
            ini_set('session.cookie_secure', $this->secure ? '1' : '0');
            ini_set('session.cookie_samesite', $this->sameSite);
            ini_set('session.cookie_path', $this->cookiePath);
            ini_set('session.cookie_lifetime', (string) $this->sessionLifetime);
        }
        
        ini_set('session.gc_maxlifetime', (string) $this->sessionLifetime);
        
        // Set session name BEFORE starting session
        session_name($this->sessionName);
        
        // Always start session if not already active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        } elseif (session_name() !== $this->sessionName) {
            // Session name doesn't match, close and restart
            session_write_close();
            session_name($this->sessionName);
            session_start();
        }
        
        // Only regenerate session ID if this is a completely new session
        // Don't regenerate if there's a session cookie (indicating an existing session)
        if (empty($_SESSION) && !$this->has('last_regeneration') && !isset($_COOKIE[$this->sessionName])) {
            $this->regenerate();
        } elseif ($this->has('last_regeneration') && (time() - $this->get('last_regeneration', 0) > 1800)) { // 30 minutes instead of 5
            $this->regenerate();
        }
        
        // Ensure session data is written at the end of start()
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION)) {
            session_write_close();
            session_start();
        }
    }
    
    /**
     * Regenerate the session ID.
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
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
        
        // Ensure session data is written immediately for critical operations
        if (in_array($key, ['user_id', 'username', 'is_admin', 'logged_in_at'])) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
                session_start();
            }
        }
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
        $this->put('user_id', $userId);
        $this->put('username', $username);
        $this->put('is_admin', $isAdmin);
        $this->put('logged_in_at', time());
        
        // Ensure session data is written immediately
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            session_start();
        }
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
        
        // Ensure session data is written immediately
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            session_start();
        }
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