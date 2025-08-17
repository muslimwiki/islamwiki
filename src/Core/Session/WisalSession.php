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

use IslamWiki\Core\Error\ErrorHandler;

/**
 * WisalSession - Session Management System (وصال)
 * 
 * Handles user sessions, authentication state, and session security.
 * 
 * @package IslamWiki\Core\Session
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license MIT
 */
class WisalSession
{
    /**
     * @var array Session configuration
     */
    private array $config;

    /**
     * @var bool Whether the session has been started
     */
    private bool $started = false;

    /**
     * @var array Session data cache
     */
    private array $data = [];

    /**
     * Constructor.
     * 
     * @param array $config Session configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'name' => 'islamwiki_session',
            'lifetime' => 3600, // 1 hour
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
            'save_path' => __DIR__ . '/../../../storage/sessions'
        ], $config);

        // Configure session settings BEFORE any session operations
        $this->configureSession();
    }

    /**
     * Configure session settings.
     * This must be called before any session operations.
     */
    private function configureSession(): void
    {
        // If session is already active, sync with it instead of reconfiguring
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            $this->data = $_SESSION;
            error_log("WisalSession: Session already active, syncing with existing session");
            return;
        }

        // Only configure if session hasn't been started yet
        if (session_status() === PHP_SESSION_NONE) {
            // Set session save path
            if (!is_dir($this->config['save_path'])) {
                mkdir($this->config['save_path'], 0777, true);
            }
            session_save_path($this->config['save_path']);

            // Set session name
            session_name($this->config['name']);

            // Set secure session configuration
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', $this->config['samesite']);
            ini_set('session.gc_maxlifetime', (string) $this->config['lifetime']);
            ini_set('session.cookie_lifetime', '0'); // Session cookie
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cache_limiter', 'nocache');
        }
    }

    /**
     * Start the session.
     */
    public function start(): void
    {
        // If session is already active, sync with it
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            $this->data = $_SESSION;
            error_log("WisalSession: Session already active, syncing with existing session");
            return;
        }

        // Configure session if not already done
        $this->configureSession();

        // Start the session
        if (session_start()) {
            $this->started = true;
            $this->data = $_SESSION;
            
            // Regenerate session ID periodically for security
            if (!isset($this->data['last_regeneration']) || 
                (time() - $this->data['last_regeneration']) > 300) { // 5 minutes
                $this->regenerateId();
            }
        } else {
            throw new \RuntimeException('Failed to start session');
        }
    }

    /**
     * Get session data.
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Session value
     */
    public function get(string $key, $default = null)
    {
        if (!$this->started) {
            $this->start();
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * Set session data.
     * 
     * @param string $key Session key
     * @param mixed $value Session value
     */
    public function set(string $key, $value): void
    {
        if (!$this->started) {
            $this->start();
        }

        $this->data[$key] = $value;
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session has a key.
     * 
     * @param string $key Session key
     * @return bool True if key exists
     */
    public function has(string $key): bool
    {
        if (!$this->started) {
            $this->start();
        }

        return isset($this->data[$key]);
    }

    /**
     * Remove session data.
     * 
     * @param string $key Session key
     */
    public function remove(string $key): void
    {
        if (!$this->started) {
            $this->start();
        }

        unset($this->data[$key]);
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data.
     */
    public function clear(): void
    {
        if (!$this->started) {
            $this->start();
        }

        $this->data = [];
        $_SESSION = [];
    }

    /**
     * Check if user is logged in.
     * 
     * @return bool True if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return $this->has('user_id') && $this->has('user_authenticated');
    }

    /**
     * Get current user ID.
     * 
     * @return int|null User ID or null if not logged in
     */
    public function getUserId(): ?int
    {
        return $this->get('user_id');
    }

    /**
     * Get current user data.
     * 
     * @return array|null User data or null if not logged in
     */
    public function getUserData(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $this->get('user_id'),
            'username' => $this->get('username'),
            'email' => $this->get('email'),
            'role' => $this->get('user_role'),
            'authenticated' => $this->get('user_authenticated')
        ];
    }

    /**
     * Set user as logged in.
     * 
     * @param array $userData User data
     */
    public function setUserLoggedIn(array $userData): void
    {
        // Ensure session is started
        if (!$this->started) {
            $this->start();
        }

        // Set user data in both local cache and global session
        $this->set('user_id', $userData['id']);
        $this->set('username', $userData['username']);
        $this->set('email', $userData['email']);
        $this->set('user_role', $userData['role'] ?? 'user');
        $this->set('user_authenticated', true);
        $this->set('login_time', time());
        $this->set('last_activity', time());

        // Force write to global session array to ensure persistence
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['user_role'] = $userData['role'] ?? 'user';
        $_SESSION['user_authenticated'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        // Force session write to ensure data is persisted
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            // Restart session to ensure data is properly loaded
            session_start();
            $this->data = $_SESSION;
        }

        error_log("WisalSession: User logged in - ID: {$userData['id']}, Username: {$userData['username']}");
        error_log("WisalSession: Session data after login: " . print_r($_SESSION, true));
    }

    /**
     * Set user as logged out.
     */
    public function setUserLoggedOut(): void
    {
        $this->clear();
        $this->regenerateId();
    }

    /**
     * Update last activity time.
     */
    public function updateActivity(): void
    {
        $this->set('last_activity', time());
    }

    /**
     * Check if session has expired.
     * 
     * @return bool True if session has expired
     */
    public function isExpired(): bool
    {
        if (!$this->has('last_activity')) {
            return true;
        }

        $lastActivity = $this->get('last_activity');
        $maxLifetime = $this->config['lifetime'];
        
        return (time() - $lastActivity) > $maxLifetime;
    }

    /**
     * Regenerate session ID.
     */
    public function regenerateId(): void
    {
        if ($this->started) {
            session_regenerate_id(true);
            $this->set('last_regeneration', time());
        }
    }

    /**
     * Destroy the session.
     */
    public function destroy(): void
    {
        if ($this->started) {
            $this->clear();
            session_destroy();
            $this->started = false;
        }
    }

    /**
     * Get session ID.
     * 
     * @return string|null Session ID or null if not started
     */
    public function getId(): ?string
    {
        return session_id() ?: null;
    }

    /**
     * Get session name.
     * 
     * @return string Session name
     */
    public function getName(): string
    {
        return session_name();
    }

    /**
     * Get session status.
     * 
     * @return int Session status constant
     */
    public function getStatus(): int
    {
        return session_status();
    }

    /**
     * Check if session is started.
     * 
     * @return bool True if session is started
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Get all session data.
     * 
     * @return array All session data
     */
    public function getAll(): array
    {
        if (!$this->started) {
            $this->start();
        }

        return $this->data;
    }

    /**
     * Flash a message to the session.
     * 
     * @param string $key Message key
     * @param string $message Message content
     */
    public function flash(string $key, string $message): void
    {
        $this->set("flash_{$key}", $message);
    }

    /**
     * Get and remove a flashed message.
     * 
     * @param string $key Message key
     * @param mixed $default Default value if no message
     * @return mixed Message content or default
     */
    public function getFlash(string $key, $default = null)
    {
        $message = $this->get("flash_{$key}", $default);
        $this->remove("flash_{$key}");
        return $message;
    }

    /**
     * Check if a flashed message exists.
     * 
     * @param string $key Message key
     * @return bool True if message exists
     */
    public function hasFlash(string $key): bool
    {
        return $this->has("flash_{$key}");
    }

    /**
     * Generate a CSRF token.
     * 
     * @return string CSRF token
     */
    public function getCsrfToken(): string
    {
        if (!$this->has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            $this->set('csrf_token', $token);
        }
        return $this->get('csrf_token');
    }

    /**
     * Verify a CSRF token.
     * 
     * @param string $token Token to verify
     * @return bool True if token is valid
     */
    public function verifyCsrfToken(string $token): bool
    {
        $storedToken = $this->getCsrfToken();
        return hash_equals($storedToken, $token);
    }

    /**
     * Validate a CSRF token (alias for verifyCsrfToken).
     * 
     * @param string $token Token to validate
     * @return bool True if token is valid
     */
    public function validateCsrfToken(string $token): bool
    {
        return $this->verifyCsrfToken($token);
    }
}
