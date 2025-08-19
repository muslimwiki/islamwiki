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
 * @package   IslamWiki\Core\Security
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Security;

use IslamWiki\Core\Logging\ShahidLogger;
use Exception;

/**
 * AmanSecurity (أمان) - Security and Authentication System
 *
 * Aman means "Security" or "Safety" in Arabic. This class provides
 * comprehensive security features including authentication, authorization,
 * Islamic content validation, threat detection, and security policy
 * management for the IslamWiki application.
 *
 * This system is part of the Application Layer and ensures the security
 * and integrity of all Islamic content and user interactions.
 *
 * @category  Core
 * @package   IslamWiki\Core\Security
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class AmanSecurity
{
    /**
     * The logging system.
     */
    protected ShahidLogger $logger;

    /**
     * Security configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Security policies.
     *
     * @var array<string, array>
     */
    protected array $policies = [];

    /**
     * Active security threats.
     *
     * @var array<string, array>
     */
    protected array $threats = [];

    /**
     * Security statistics.
     *
     * @var array<string, mixed>
     */
    protected array $statistics = [];

    /**
     * Constructor.
     *
     * @param ShahidLogger $logger The logging system
     * @param array        $config Security configuration
     */
    public function __construct(ShahidLogger $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->initializeSecurity();
    }

    /**
     * Initialize security system.
     *
     * @return self
     */
    protected function initializeSecurity(): self
    {
        $this->initializePolicies();
        $this->initializeStatistics();
        $this->logger->info('AmanSecurity system initialized');

        return $this;
    }

    /**
     * Initialize security policies.
     *
     * @return self
     */
    protected function initializePolicies(): self
    {
        $this->policies = [
            'authentication' => [
                'password_min_length' => $this->config['password_min_length'] ?? 8,
                'password_complexity' => $this->config['password_complexity'] ?? true,
                'max_login_attempts' => $this->config['max_login_attempts'] ?? 5,
                'lockout_duration' => $this->config['lockout_duration'] ?? 900,
                'session_lifetime' => $this->config['session_lifetime'] ?? 3600,
                'require_2fa' => $this->config['require_2fa'] ?? false,
            ],
            'authorization' => [
                'role_based_access' => $this->config['role_based_access'] ?? true,
                'permission_inheritance' => $this->config['permission_inheritance'] ?? true,
                'resource_protection' => $this->config['resource_protection'] ?? true,
                'api_rate_limiting' => $this->config['api_rate_limiting'] ?? true,
            ],
            'content_security' => [
                'islamic_content_validation' => $this->config['islamic_content_validation'] ?? true,
                'source_verification' => $this->config['source_verification'] ?? true,
                'content_moderation' => $this->config['content_moderation'] ?? true,
                'xss_protection' => $this->config['xss_protection'] ?? true,
                'csrf_protection' => $this->config['csrf_protection'] ?? true,
            ],
            'threat_detection' => [
                'suspicious_activity_monitoring' => $this->config['suspicious_activity_monitoring'] ?? true,
                'ip_blacklisting' => $this->config['ip_blacklisting'] ?? true,
                'behavior_analysis' => $this->config['behavior_analysis'] ?? true,
                'real_time_alerts' => $this->config['real_time_alerts'] ?? true,
            ],
            'encryption' => [
                'data_encryption' => $this->config['data_encryption'] ?? true,
                'session_encryption' => $this->config['session_encryption'] ?? true,
                'api_encryption' => $this->config['api_encryption'] ?? true,
                'key_rotation' => $this->config['key_rotation'] ?? true,
            ]
        ];

        return $this;
    }

    /**
     * Initialize security statistics.
     *
     * @return self
     */
    protected function initializeStatistics(): self
    {
        $this->statistics = [
            'authentication' => [
                'successful_logins' => 0,
                'failed_logins' => 0,
                'locked_accounts' => 0,
                'password_resets' => 0,
                '2fa_verifications' => 0
            ],
            'authorization' => [
                'access_granted' => 0,
                'access_denied' => 0,
                'permission_checks' => 0,
                'role_changes' => 0
            ],
            'content_security' => [
                'content_validated' => 0,
                'content_rejected' => 0,
                'source_verified' => 0,
                'moderation_actions' => 0
            ],
            'threat_detection' => [
                'threats_detected' => 0,
                'threats_blocked' => 0,
                'suspicious_activities' => 0,
                'security_alerts' => 0
            ],
            'encryption' => [
                'data_encrypted' => 0,
                'sessions_secured' => 0,
                'keys_rotated' => 0
            ]
        ];

        return $this;
    }

    /**
     * Authenticate a user.
     *
     * @param string $username Username
     * @param string $password Password
     * @param array  $options  Authentication options
     * @return array<string, mixed>
     * @throws Exception If authentication fails
     */
    public function authenticate(string $username, string $password, array $options = []): array
    {
        try {
            // Validate input
            $this->validateAuthenticationInput($username, $password);

            // Check for account lockout
            if ($this->isAccountLocked($username)) {
                $this->updateStatistics('authentication', 'failed_logins', 1);
                throw new Exception('Account is temporarily locked due to multiple failed login attempts');
            }

            // Perform authentication
            $result = $this->performAuthentication($username, $password, $options);

            if ($result['success']) {
                $this->updateStatistics('authentication', 'successful_logins', 1);
                $this->logger->info("User authenticated successfully: {$username}");
            } else {
                $this->updateStatistics('authentication', 'failed_logins', 1);
                $this->handleFailedLogin($username);
            }

            return $result;

        } catch (Exception $e) {
            $this->logger->warning("Authentication failed for user: {$username} - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate authentication input.
     *
     * @param string $username Username
     * @param string $password Password
     * @return self
     * @throws Exception If validation fails
     */
    protected function validateAuthenticationInput(string $username, string $password): self
    {
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password are required');
        }

        if (strlen($username) < 3) {
            throw new Exception('Username must be at least 3 characters long');
        }

        $minLength = $this->policies['authentication']['password_min_length'];
        if (strlen($password) < $minLength) {
            throw new Exception("Password must be at least {$minLength} characters long");
        }

        return $this;
    }

    /**
     * Check if account is locked.
     *
     * @param string $username Username
     * @return bool
     */
    protected function isAccountLocked(string $username): bool
    {
        // TODO: Implement account lockout checking
        // This would typically check against a database or cache
        return false;
    }

    /**
     * Perform authentication.
     *
     * @param string $username Username
     * @param string $password Password
     * @param array  $options  Authentication options
     * @return array<string, mixed>
     */
    protected function performAuthentication(string $username, string $password, array $options): array
    {
        // TODO: Implement actual authentication logic
        // This would typically check against a database
        // For now, return a mock result
        return [
            'success' => true,
            'user_id' => 1,
            'username' => $username,
            'roles' => ['user'],
            'permissions' => ['read', 'comment'],
            'session_token' => $this->generateSessionToken(),
            'expires_at' => time() + $this->policies['authentication']['session_lifetime']
        ];
    }

    /**
     * Handle failed login attempt.
     *
     * @param string $username Username
     * @return self
     */
    protected function handleFailedLogin(string $username): self
    {
        // TODO: Implement failed login handling
        // This would typically increment failed attempts and potentially lock the account
        $this->logger->warning("Failed login attempt for user: {$username}");

        return $this;
    }

    /**
     * Generate session token.
     *
     * @return string
     */
    protected function generateSessionToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Authorize user access to resource.
     *
     * @param int    $userId     User ID
     * @param string $resource   Resource identifier
     * @param string $action     Action to perform
     * @param array  $context    Additional context
     * @return bool
     */
    public function authorize(int $userId, string $resource, string $action, array $context = []): bool
    {
        try {
            $this->updateStatistics('authorization', 'permission_checks', 1);

            // Check if user exists and is active
            if (!$this->isUserActive($userId)) {
                $this->updateStatistics('authorization', 'access_denied', 1);
                return false;
            }

            // Get user permissions
            $permissions = $this->getUserPermissions($userId);
            
            // Check resource access
            $hasAccess = $this->checkResourceAccess($permissions, $resource, $action, $context);

            if ($hasAccess) {
                $this->updateStatistics('authorization', 'access_granted', 1);
                $this->logger->debug("Access granted: User {$userId} to {$resource} {$action}");
            } else {
                $this->updateStatistics('authorization', 'access_denied', 1);
                $this->logger->warning("Access denied: User {$userId} to {$resource} {$action}");
            }

            return $hasAccess;

        } catch (Exception $e) {
            $this->logger->error("Authorization check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is active.
     *
     * @param int $userId User ID
     * @return bool
     */
    protected function isUserActive(int $userId): bool
    {
        // TODO: Implement user status checking
        return true;
    }

    /**
     * Get user permissions.
     *
     * @param int $userId User ID
     * @return array<string>
     */
    protected function getUserPermissions(int $userId): array
    {
        // TODO: Implement permission retrieval from database
        // For now, return basic permissions
        return ['read', 'comment', 'edit_own'];
    }

    /**
     * Check resource access.
     *
     * @param array  $permissions User permissions
     * @param string $resource    Resource identifier
     * @param string $action      Action to perform
     * @param array  $context     Additional context
     * @return bool
     */
    protected function checkResourceAccess(array $permissions, string $resource, string $action, array $context): bool
    {
        // Check if user has the required permission
        $requiredPermission = "{$action}_{$resource}";
        
        if (in_array($requiredPermission, $permissions)) {
            return true;
        }

        // Check wildcard permissions
        if (in_array('admin', $permissions) || in_array('superuser', $permissions)) {
            return true;
        }

        // Check action-specific permissions
        if (in_array($action, $permissions)) {
            return true;
        }

        return false;
    }

    /**
     * Validate Islamic content.
     *
     * @param string $content Content to validate
     * @param array  $options Validation options
     * @return array<string, mixed>
     */
    public function validateIslamicContent(string $content, array $options = []): array
    {
        try {
            $this->updateStatistics('content_security', 'content_validated', 1);

            $validationResult = [
                'valid' => true,
                'issues' => [],
                'recommendations' => []
            ];

            // Check content length
            if (strlen($content) < 10) {
                $validationResult['valid'] = false;
                $validationResult['issues'][] = 'Content is too short';
            }

            // Check for inappropriate content
            if ($this->containsInappropriateContent($content)) {
                $validationResult['valid'] = false;
                $validationResult['issues'][] = 'Content contains inappropriate material';
            }

            // Check Islamic terminology
            $islamicValidation = $this->validateIslamicTerminology($content);
            if (!$islamicValidation['valid']) {
                $validationResult['issues'] = array_merge($validationResult['issues'], $islamicValidation['issues']);
            }

            // Update statistics
            if ($validationResult['valid']) {
                $this->updateStatistics('content_security', 'content_validated', 1);
            } else {
                $this->updateStatistics('content_security', 'content_rejected', 1);
            }

            return $validationResult;

        } catch (Exception $e) {
            $this->logger->error("Content validation failed: " . $e->getMessage());
            return [
                'valid' => false,
                'issues' => ['Validation system error'],
                'recommendations' => ['Please try again later']
            ];
        }
    }

    /**
     * Check for inappropriate content.
     *
     * @param string $content Content to check
     * @return bool
     */
    protected function containsInappropriateContent(string $content): bool
    {
        // TODO: Implement content filtering
        // This would typically check against a list of inappropriate terms
        $inappropriateTerms = ['spam', 'offensive', 'inappropriate'];
        
        foreach ($inappropriateTerms as $term) {
            if (stripos($content, $term) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate Islamic terminology.
     *
     * @param string $content Content to validate
     * @return array<string, mixed>
     */
    protected function validateIslamicTerminology(string $content): array
    {
        $result = [
            'valid' => true,
            'issues' => []
        ];

        // Check for proper Islamic terms
        $islamicTerms = ['Allah', 'Muhammad', 'Quran', 'Hadith', 'Salah', 'Zakat'];
        $foundTerms = [];

        foreach ($islamicTerms as $term) {
            if (stripos($content, $term) !== false) {
                $foundTerms[] = $term;
            }
        }

        // If Islamic content is mentioned, ensure proper respect
        if (!empty($foundTerms) && !$this->showsProperRespect($content)) {
            $result['valid'] = false;
            $result['issues'][] = 'Islamic content should be treated with proper respect';
        }

        return $result;
    }

    /**
     * Check if content shows proper respect.
     *
     * @param string $content Content to check
     * @return bool
     */
    protected function showsProperRespect(string $content): bool
    {
        // TODO: Implement respect checking logic
        // This would typically check for proper formatting and respectful language
        return true;
    }

    /**
     * Detect security threats.
     *
     * @param array $activity User activity data
     * @return array<string, mixed>
     */
    public function detectThreats(array $activity): array
    {
        try {
            $this->updateStatistics('threat_detection', 'suspicious_activities', 1);

            $threats = [];

            // Check for suspicious login patterns
            if ($this->isSuspiciousLogin($activity)) {
                $threats[] = [
                    'type' => 'suspicious_login',
                    'severity' => 'medium',
                    'description' => 'Unusual login pattern detected',
                    'timestamp' => time()
                ];
            }

            // Check for rate limiting violations
            if ($this->isRateLimitViolation($activity)) {
                $threats[] = [
                    'type' => 'rate_limit_violation',
                    'severity' => 'high',
                    'description' => 'Rate limit exceeded',
                    'timestamp' => time()
                ];
            }

            // Check for content manipulation attempts
            if ($this->isContentManipulationAttempt($activity)) {
                $threats[] = [
                    'type' => 'content_manipulation',
                    'severity' => 'high',
                    'description' => 'Suspicious content modification detected',
                    'timestamp' => time()
                ];
            }

            // Update threats list
            foreach ($threats as $threat) {
                $this->threats[] = $threat;
            }

            // Update statistics
            if (!empty($threats)) {
                $this->updateStatistics('threat_detection', 'threats_detected', count($threats));
                $this->logger->warning("Security threats detected: " . count($threats));
            }

            return [
                'threats_detected' => count($threats),
                'threats' => $threats,
                'recommendations' => $this->getThreatRecommendations($threats)
            ];

        } catch (Exception $e) {
            $this->logger->error("Threat detection failed: " . $e->getMessage());
            return [
                'threats_detected' => 0,
                'threats' => [],
                'recommendations' => ['Threat detection system error']
            ];
        }
    }

    /**
     * Check for suspicious login patterns.
     *
     * @param array $activity Activity data
     * @return bool
     */
    protected function isSuspiciousLogin(array $activity): bool
    {
        // TODO: Implement suspicious login detection
        // This would typically check for unusual IP addresses, times, or patterns
        return false;
    }

    /**
     * Check for rate limit violations.
     *
     * @param array $activity Activity data
     * @return bool
     */
    protected function isRateLimitViolation(array $activity): bool
    {
        // TODO: Implement rate limit checking
        // This would typically check against configured rate limits
        return false;
    }

    /**
     * Check for content manipulation attempts.
     *
     * @param array $activity Activity data
     * @return bool
     {
        // TODO: Implement content manipulation detection
        // This would typically check for unusual content changes
        return false;
    }

    /**
     * Get threat recommendations.
     *
     * @param array $threats Detected threats
     * @return array<string>
     */
    protected function getThreatRecommendations(array $threats): array
    {
        $recommendations = [];

        foreach ($threats as $threat) {
            switch ($threat['type']) {
                case 'suspicious_login':
                    $recommendations[] = 'Review login activity and consider enabling 2FA';
                    break;
                case 'rate_limit_violation':
                    $recommendations[] = 'Implement stricter rate limiting and monitoring';
                    break;
                case 'content_manipulation':
                    $recommendations[] = 'Review content changes and user permissions';
                    break;
            }
        }

        return $recommendations;
    }

    /**
     * Update security statistics.
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
     * Get security statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Get security policies.
     *
     * @return array<string, array>
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * Get active threats.
     *
     * @return array<string, array>
     */
    public function getThreats(): array
    {
        return $this->threats;
    }

    /**
     * Get security configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set security configuration.
     *
     * @param array<string, mixed> $config Security configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        $this->initializePolicies();
        return $this;
    }
} 