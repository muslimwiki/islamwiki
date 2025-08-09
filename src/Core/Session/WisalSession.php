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
        // If session is already active, don't reconfigure it
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

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
            // Only set Secure when the request is HTTPS or explicitly configured
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            $useSecure = $this->secure && $isHttps;
            ini_set('session.cookie_secure', $useSecure ? '1' : '0');
            ini_set('session.cookie_samesite', $this->sameSite);
            ini_set('session.cookie_path', $this->cookiePath);
            ini_set('session.cookie_lifetime', (string) $this->sessionLifetime);
        }

        ini_set('session.gc_maxlifetime', (string) $this->sessionLifetime);

        // Set session name BEFORE starting session
        session_name($this->sessionName);

        // Start session
        session_start();

        // Only regenerate session ID if this is a completely new session
        // Don't regenerate if there's a session cookie (indicating an existing session)
        if (
            empty($_SESSION)
            && !isset($_SESSION['last_regeneration'])
            && !isset($_COOKIE[$this->sessionName])
        ) {
            $this->regenerate();
        } elseif (
            isset($_SESSION['last_regeneration'])
            && (time() - $_SESSION['last_regeneration'] > 1800) // 30 minutes
        ) {
            $this->regenerate();
        }

        // Don't write close here as it can clear session data
        // The session will be written when the request ends
    }

    /**
     * Boot the session system.
     */
    public function boot(): void
    {
        // Start the session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }
    }

    /**
     * Shutdown the session system.
     */
    public function shutdown(): void
    {
        // Close the session if it's active
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    /**
     * Regenerate the session ID.
     */
    public function regenerate(): void
    {
        error_log("WisalSession::regenerate - Regenerating session ID");
        error_log("WisalSession::regenerate - Session data before regeneration: " . print_r($_SESSION, true));

        session_regenerate_id(true);
        // Directly set the value to avoid triggering the put() method's write logic
        $_SESSION['last_regeneration'] = time();

        error_log("WisalSession::regenerate - Session data after regeneration: " . print_r($_SESSION, true));
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
        error_log("WisalSession::put - Setting $key = " . print_r($value, true));

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }

        $_SESSION[$key] = $value;
        error_log("WisalSession::put - Session data after setting $key: " . print_r($_SESSION, true));

        // Ensure session data is written immediately for critical operations
        if (in_array($key, ['user_id', 'username', 'is_admin', 'logged_in_at'])) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
                // Don't restart session here as it can cause infinite loops
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
        error_log("WisalSession::login - Starting login with userId: $userId, username: $username, isAdmin: " . ($isAdmin ? 'true' : 'false'));

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }

        $this->put('user_id', $userId);
        $this->put('username', $username);
        $this->put('is_admin', $isAdmin);
        $this->put('logged_in_at', time());

        error_log("WisalSession::login - Session data after setting: " . print_r($_SESSION, true));

        // Ensure session data is written immediately
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            session_start();
        }

        error_log("WisalSession::login - Session data after restart: " . print_r($_SESSION, true));
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
        // Consider logged in if we have a user_id. Username is optional for
        // backward compatibility with controllers that only set user_id.
        return $this->has('user_id');
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
        error_log("WisalSession::generateCsrfToken - Generated token: $token");
        return $token;
    }

    /**
     * Get the current CSRF token.
     */
    public function getCsrfToken(): string
    {
        if (!$this->has('csrf_token')) {
            error_log("WisalSession::getCsrfToken - No token found, generating new one");
            return $this->generateCsrfToken();
        }
        $token = $this->get('csrf_token');
        error_log("WisalSession::getCsrfToken - Retrieved token: $token");
        return $token;
    }

    /**
     * Verify a CSRF token.
     */
    public function verifyCsrfToken(string $token): bool
    {
        $storedToken = $this->getCsrfToken();
        $isValid = hash_equals($storedToken, $token);
        error_log("WisalSession::verifyCsrfToken - Input token: $token");
        error_log("WisalSession::verifyCsrfToken - Stored token: $storedToken");
        error_log("WisalSession::verifyCsrfToken - Is valid: " . ($isValid ? 'true' : 'false'));
        return $isValid;
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
