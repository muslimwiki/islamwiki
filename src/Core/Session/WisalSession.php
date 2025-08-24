<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
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
 *
 * @category  Core
 * @package   IslamWiki\Core\Session
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Session;

use IslamWiki\Core\Logging\ShahidLogger;
use Exception;

/**
 * WisalSession (وصل) - Session Management System
 *
 * Wisal means "Connection" in Arabic. This class provides comprehensive
 * session management including user session tracking, multi-device support,
 * session security, analytics, and lifecycle management for the IslamWiki
 * application.
 *
 * This system is part of the Application Layer and manages all user
 * connections and session data throughout the application.
 *
 * @category  Core
 * @package   IslamWiki\Core\Session
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class WisalSession
{
    /**
     * The logging system.
     */
    protected ShahidLogger $logger;

    /**
     * Session configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Active sessions.
     *
     * @var array<string, array>
     */
    protected array $sessions = [];

    /**
     * Session statistics.
     *
     * @var array<string, mixed>
     */
    protected array $statistics = [];

    /**
     * Session encryption key.
     */
    protected string $encryptionKey;

    /**
     * Constructor.
     *
     * @param ShahidLogger $logger The logging system
     * @param array        $config Session configuration
     */
    public function __construct(ShahidLogger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeSession();
    }

    /**
     * Initialize session system.
     *
     * @return self
     */
    protected function initializeSession(): self
    {
        $this->initializeStatistics();
        $this->encryptionKey = $this->config['encryption_key'] ?? $this->generateEncryptionKey();
        
        // Start PHP session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            $this->startPhpSession();
        }

        $this->logger->info('WisalSession system initialized');

        return $this;
    }

    /**
     * Initialize session statistics.
     *
     * @return self
     */
    protected function initializeStatistics(): self
    {
        $this->statistics = [
            'sessions' => [
                'total_created' => 0,
                'total_destroyed' => 0,
                'active_sessions' => 0,
                'expired_sessions' => 0
            ],
            'users' => [
                'unique_users' => 0,
                'concurrent_users' => 0,
                'multi_device_users' => 0
            ],
            'security' => [
                'sessions_encrypted' => 0,
                'suspicious_activities' => 0,
                'session_hijacking_attempts' => 0
            ],
            'performance' => [
                'session_creation_time' => 0.0,
                'session_validation_time' => 0.0,
                'average_session_duration' => 0.0
            ]
        ];

        return $this;
    }

    /**
     * Start PHP session.
     *
     * @return self
     */
    protected function startPhpSession(): self
    {
        $sessionConfig = $this->config['php_session'] ?? [];
        
        // Set session configuration
        if (isset($sessionConfig['lifetime'])) {
            ini_set('session.gc_maxlifetime', $sessionConfig['lifetime']);
        }
        
        if (isset($sessionConfig['cookie_lifetime'])) {
            ini_set('session.cookie_lifetime', $sessionConfig['cookie_lifetime']);
        }
        
        if (isset($sessionConfig['cookie_secure'])) {
            ini_set('session.cookie_secure', $sessionConfig['cookie_secure'] ? '1' : '0');
        }
        
        if (isset($sessionConfig['cookie_httponly'])) {
            ini_set('session.cookie_httponly', $sessionConfig['cookie_httponly'] ? '1' : '0');
        }

        session_start();

        return $this;
    }

    /**
     * Generate encryption key.
     *
     * @return string
     */
    protected function generateEncryptionKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Create a new session.
     *
     * @param int    $userId     User ID
     * @param array  $userData   User data
     * @param array  $options    Session options
     * @return array<string, mixed>
     */
    public function createSession(int $userId, array $userData, array $options = []): array
    {
        $startTime = microtime(true);

        try {
            $sessionId = $this->generateSessionId();
            $expiresAt = time() + ($options['lifetime'] ?? $this->config['default_lifetime'] ?? 3600);

            $session = [
                'id' => $sessionId,
                'user_id' => $userId,
                'user_data' => $userData,
                'created_at' => time(),
                'expires_at' => $expiresAt,
                'last_activity' => time(),
                'ip_address' => $this->getClientIp(),
                'user_agent' => $this->getUserAgent(),
                'device_id' => $options['device_id'] ?? $this->generateDeviceId(),
                'is_encrypted' => $options['encrypt'] ?? $this->config['encrypt_sessions'] ?? true,
                'metadata' => $options['metadata'] ?? []
            ];

            // Encrypt session data if enabled
            if ($session['is_encrypted']) {
                $session['user_data'] = $this->encryptData($session['user_data']);
                $this->updateStatistics('security', 'sessions_encrypted', 1);
            }

            $this->sessions[$sessionId] = $session;
            $this->updateStatistics('sessions', 'total_created', 1);
            $this->updateStatistics('sessions', 'active_sessions', 1);

            // Update unique users count
            $this->updateUniqueUsersCount($userId);

            $creationTime = microtime(true) - $startTime;
            $this->updateStatistics('performance', 'session_creation_time', $creationTime);

            $this->logger->info("Session created for user {$userId}: {$sessionId}");

            return [
                'session_id' => $sessionId,
                'expires_at' => $expiresAt,
                'device_id' => $session['device_id']
            ];

        } catch (Exception $e) {
            $this->logger->error("Failed to create session for user {$userId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate unique session ID.
     *
     * @return string
     */
    protected function generateSessionId(): string
    {
        do {
            $sessionId = bin2hex(random_bytes(16));
        } while (isset($this->sessions[$sessionId]));

        return $sessionId;
    }

    /**
     * Generate device ID.
     *
     * @return string
     */
    protected function generateDeviceId(): string
    {
        $userAgent = $this->getUserAgent();
        $ipAddress = $this->getClientIp();
        
        return hash('sha256', $userAgent . $ipAddress . time());
    }

    /**
     * Get client IP address.
     *
     * @return string
     */
    protected function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Get user agent.
     *
     * @return string
     */
    protected function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    /**
     * Encrypt data.
     *
     * @param mixed $data Data to encrypt
     * @return string
     */
    protected function encryptData($data): string
    {
        $jsonData = json_encode($data);
        $iv = random_bytes(16);
        
        $encrypted = openssl_encrypt(
            $jsonData,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data.
     *
     * @param string $encryptedData Encrypted data
     * @return mixed
     */
    protected function decryptData(string $encryptedData)
    {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        return json_decode($decrypted, true);
    }

    /**
     * Validate session.
     *
     * @param string $sessionId Session ID
     * @return array<string, mixed>|null
     */
    public function validateSession(string $sessionId): ?array
    {
        $startTime = microtime(true);

        if (!isset($this->sessions[$sessionId])) {
            return null;
        }

        $session = $this->sessions[$sessionId];

        // Check if session has expired
        if (time() > $session['expires_at']) {
            $this->destroySession($sessionId);
            $this->updateStatistics('sessions', 'expired_sessions', 1);
            return null;
        }

        // Check for suspicious activity
        if ($this->isSuspiciousActivity($session)) {
            $this->updateStatistics('security', 'suspicious_activities', 1);
            $this->logger->warning("Suspicious activity detected in session: {$sessionId}");
        }

        // Update last activity
        $session['last_activity'] = time();
        $this->sessions[$sessionId] = $session;

        $validationTime = microtime(true) - $startTime;
        $this->updateStatistics('performance', 'session_validation_time', $validationTime);

        // Return session data
        $result = [
            'user_id' => $session['user_id'],
            'device_id' => $session['device_id'],
            'last_activity' => $session['last_activity'],
            'metadata' => $session['metadata']
        ];

        // Decrypt user data if encrypted
        if ($session['is_encrypted']) {
            $result['user_data'] = $this->decryptData($session['user_data']);
        } else {
            $result['user_data'] = $session['user_data'];
        }

        return $result;
    }

    /**
     * Check for suspicious activity.
     *
     * @param array $session Session data
     * @return bool
     */
    protected function isSuspiciousActivity(array $session): bool
    {
        $currentIp = $this->getClientIp();
        $currentUserAgent = $this->getUserAgent();

        // Check if IP address changed
        if ($session['ip_address'] !== $currentIp) {
            return true;
        }

        // Check if user agent changed
        if ($session['user_agent'] !== $currentUserAgent) {
            return true;
        }

        // Check for rapid activity
        $timeSinceLastActivity = time() - $session['last_activity'];
        if ($timeSinceLastActivity < 1) { // Less than 1 second
            return true;
        }

        return false;
    }

    /**
     * Destroy session.
     *
     * @param string $sessionId Session ID
     * @return bool
     */
    public function destroySession(string $sessionId): bool
    {
        if (!isset($this->sessions[$sessionId])) {
            return false;
        }

        $session = $this->sessions[$sessionId];
        unset($this->sessions[$sessionId]);

        $this->updateStatistics('sessions', 'total_destroyed', 1);
        $this->updateStatistics('sessions', 'active_sessions', -1);

        $this->logger->info("Session destroyed: {$sessionId}");

        return true;
    }

    /**
     * Get user sessions.
     *
     * @param int $userId User ID
     * @return array<string, array>
     */
    public function getUserSessions(int $userId): array
    {
        $userSessions = [];

        foreach ($this->sessions as $sessionId => $session) {
            if ($session['user_id'] === $userId) {
                $userSessions[$sessionId] = [
                    'device_id' => $session['device_id'],
                    'created_at' => $session['created_at'],
                    'last_activity' => $session['last_activity'],
                    'ip_address' => $session['ip_address'],
                    'user_agent' => $session['user_agent']
                ];
            }
        }

        return $userSessions;
    }

    /**
     * Destroy all user sessions.
     *
     * @param int $userId User ID
     * @return int Number of sessions destroyed
     */
    public function destroyUserSessions(int $userId): int
    {
        $destroyedCount = 0;

        foreach ($this->sessions as $sessionId => $session) {
            if ($session['user_id'] === $userId) {
                $this->destroySession($sessionId);
                $destroyedCount++;
            }
        }

        $this->logger->info("Destroyed {$destroyedCount} sessions for user {$userId}");

        return $destroyedCount;
    }

    /**
     * Clean up expired sessions.
     *
     * @return int Number of sessions cleaned up
     */
    public function cleanupExpiredSessions(): int
    {
        $cleanedCount = 0;
        $currentTime = time();

        foreach ($this->sessions as $sessionId => $session) {
            if ($currentTime > $session['expires_at']) {
                $this->destroySession($sessionId);
                $cleanedCount++;
            }
        }

        if ($cleanedCount > 0) {
            $this->logger->info("Cleaned up {$cleanedCount} expired sessions");
        }

        return $cleanedCount;
    }

    /**
     * Update session metadata.
     *
     * @param string $sessionId Session ID
     * @param array  $metadata  New metadata
     * @return bool
     */
    public function updateSessionMetadata(string $sessionId, array $metadata): bool
    {
        if (!isset($this->sessions[$sessionId])) {
            return false;
        }

        $this->sessions[$sessionId]['metadata'] = array_merge(
            $this->sessions[$sessionId]['metadata'],
            $metadata
        );

        return true;
    }

    /**
     * Get session analytics.
     *
     * @return array<string, mixed>
     */
    public function getSessionAnalytics(): array
    {
        $currentTime = time();
        $totalSessions = count($this->sessions);
        $activeSessions = 0;
        $totalDuration = 0;

        foreach ($this->sessions as $session) {
            if ($currentTime <= $session['expires_at']) {
                $activeSessions++;
                $duration = $currentTime - $session['created_at'];
                $totalDuration += $duration;
            }
        }

        $averageDuration = $activeSessions > 0 ? $totalDuration / $activeSessions : 0;

        return [
            'total_sessions' => $totalSessions,
            'active_sessions' => $activeSessions,
            'expired_sessions' => $this->statistics['sessions']['expired_sessions'],
            'average_session_duration' => $averageDuration,
            'unique_users' => $this->statistics['users']['unique_users'],
            'concurrent_users' => $activeSessions,
            'sessions_encrypted' => $this->statistics['security']['sessions_encrypted'],
            'suspicious_activities' => $this->statistics['security']['suspicious_activities']
        ];
    }

    /**
     * Update unique users count.
     *
     * @param int $userId User ID
     * @return self
     */
    protected function updateUniqueUsersCount(int $userId): self
    {
        $existingUsers = [];
        
        foreach ($this->sessions as $session) {
            $existingUsers[] = $session['user_id'];
        }

        $uniqueUsers = count(array_unique($existingUsers));
        $this->statistics['users']['unique_users'] = $uniqueUsers;

        // Check for multi-device users
        $userDeviceCounts = [];
        foreach ($this->sessions as $session) {
            $uid = $session['user_id'];
            if (!isset($userDeviceCounts[$uid])) {
                $userDeviceCounts[$uid] = [];
            }
            $userDeviceCounts[$uid][] = $session['device_id'];
        }

        $multiDeviceUsers = 0;
        foreach ($userDeviceCounts as $devices) {
            if (count(array_unique($devices)) > 1) {
                $multiDeviceUsers++;
            }
        }

        $this->statistics['users']['multi_device_users'] = $multiDeviceUsers;

        return $this;
    }

    /**
     * Update statistics.
     *
     * @param string $category Statistics category
     * @param string $metric   Metric name
     * @param mixed  $value    Value to add
     * @return self
     */
    protected function updateStatistics(string $category, string $metric, mixed $value): self
    {
        if (isset($this->statistics[$category][$metric])) {
            if (is_numeric($this->statistics[$category][$metric])) {
                $this->statistics[$category][$metric] += $value;
            } else {
                $this->statistics[$category][$metric] = $value;
            }
        }

        return $this;
    }

    /**
     * Get session statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Get session configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set session configuration.
     *
     * @param array<string, mixed> $config Session configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get all active sessions.
     *
     * @return array<string, array>
     */
    public function getActiveSessions(): array
    {
        return $this->sessions;
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
     * Generate a new CSRF token.
     */
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->put('csrf_token', $token);
        return $token;
    }

    /**
     * Check if a session key exists.
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Get a session value.
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value.
     */
    public function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
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
     * Set user as logged in.
     */
    public function setUserLoggedIn(array $userData): void
    {
        $this->put('user_id', $userData['id']);
        $this->put('username', $userData['username']);
        $this->put('is_admin', $userData['role'] === 'admin' ? 1 : 0);
        $this->put('logged_in_at', time());
    }
    
    /**
     * Set user as logged out by clearing session data.
     */
    public function setUserLoggedOut(): void
    {
        // Remove user-specific session data
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['is_admin']);
        unset($_SESSION['logged_in_at']);
        
        // Optionally regenerate session ID for security
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
    
    /**
     * Validate CSRF token.
     *
     * @param string $token Token to validate
     * @return bool True if token is valid
     */
    public function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Start the session.
     *
     * @return self
     */
    public function start(): self
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->startPhpSession();
        }
        
        return $this;
    }
}
