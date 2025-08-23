<?php

/**
 * Security Monitoring Service
 *
 * Advanced security monitoring and threat detection for the AmanSecurity extension.
 *
 * @package IslamWiki\Extensions\AmanSecurity\Services
 * @version 0.0.1.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Extensions\AmanSecurity\Services;

use IslamWiki\Core\Database\Connection;

class SecurityMonitoringService
{
    private Connection $db;
    private array $config;
    private array $threatPatterns;

    public function __construct(Connection $db, array $config = [])
    {
        $this->db = $db;
        $this->config = $config;
        $this->threatPatterns = $this->loadThreatPatterns();
    }

    /**
     * Log security event.
     */
    public function logSecurityEvent(string $eventType, array $data, int $severity = 1): bool
    {
        try {
            $sql = "INSERT INTO security_events (event_type, severity, data, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $params = [
                $eventType,
                $severity,
                json_encode($data),
                $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];

            $this->db->insert($sql, $params);
            return true;
        } catch (\Exception $e) {
            error_log('SecurityMonitoringService::logSecurityEvent error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log failed login attempt.
     */
    public function logFailedLogin(string $username, string $ipAddress, string $userAgent): bool
    {
        try {
            $sql = "INSERT INTO login_attempts (username, ip_address, user_agent, success, created_at) 
                    VALUES (?, ?, ?, 0, CURRENT_TIMESTAMP)";
            
            $this->db->insert($sql, [$username, $ipAddress, $userAgent]);

            // Check if this IP should be blocked
            $this->checkIpThreat($ipAddress);

            return true;
        } catch (\Exception $e) {
            error_log('SecurityMonitoringService::logFailedLogin error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log successful login.
     */
    public function logSuccessfulLogin(int $userId, string $ipAddress, string $userAgent): bool
    {
        try {
            $sql = "INSERT INTO login_attempts (user_id, username, ip_address, user_agent, success, created_at) 
                    VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP)";
            
            $user = $this->getUserById($userId);
            $this->db->insert($sql, [$userId, $user['username'] ?? 'unknown', $ipAddress, $userAgent]);

            // Log user activity
            $this->logUserActivity($userId, 'login', $ipAddress, $userAgent);

            return true;
        } catch (\Exception $e) {
            error_log('SecurityMonitoringService::logSuccessfulLogin error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check for suspicious activity.
     */
    public function detectSuspiciousActivity(string $ipAddress, int $userId = null): array
    {
        $threats = [];

        // Check failed login attempts
        $failedLogins = $this->getFailedLoginCount($ipAddress, 900); // 15 minutes
        if ($failedLogins > $this->config['max_login_attempts']) {
            $threats[] = [
                'type' => 'brute_force_attack',
                'severity' => 'high',
                'description' => "Multiple failed login attempts from IP: {$ipAddress}",
                'count' => $failedLogins
            ];
        }

        // Check for unusual login patterns
        if ($userId) {
            $unusualLogins = $this->detectUnusualLoginPatterns($userId, $ipAddress);
            if (!empty($unusualLogins)) {
                $threats = array_merge($threats, $unusualLogins);
            }
        }

        // Check for known malicious patterns
        $maliciousPatterns = $this->detectMaliciousPatterns($ipAddress);
        if (!empty($maliciousPatterns)) {
            $threats = array_merge($threats, $maliciousPatterns);
        }

        return $threats;
    }

    /**
     * Get security statistics.
     */
    public function getSecurityStatistics(): array
    {
        $stats = [];

        // Failed login attempts today
        $failedToday = $this->db->select("SELECT COUNT(*) as count FROM login_attempts WHERE success = 0 AND DATE(created_at) = CURRENT_DATE()");
        $stats['failed_logins_today'] = $failedToday[0]['count'] ?? 0;

        // Successful logins today
        $successToday = $this->db->select("SELECT COUNT(*) as count FROM login_attempts WHERE success = 1 AND DATE(created_at) = CURRENT_DATE()");
        $stats['successful_logins_today'] = $successToday[0]['count'] ?? 0;

        // Security events by severity
        $eventsBySeverity = $this->db->select("SELECT severity, COUNT(*) as count FROM security_events WHERE DATE(created_at) = CURRENT_DATE() GROUP BY severity");
        $stats['security_events_by_severity'] = $eventsBySeverity;

        // Top attacking IPs
        $topAttackingIPs = $this->db->select("SELECT ip_address, COUNT(*) as count FROM login_attempts WHERE success = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) GROUP BY ip_address ORDER BY count DESC LIMIT 10");
        $stats['top_attacking_ips'] = $topAttackingIPs;

        // Account lockouts
        $lockouts = $this->db->select("SELECT COUNT(*) as count FROM security_events WHERE event_type = 'account_locked' AND DATE(created_at) = CURRENT_DATE()");
        $stats['account_lockouts_today'] = $lockouts[0]['count'] ?? 0;

        return $stats;
    }

    /**
     * Block IP address.
     */
    public function blockIP(string $ipAddress, string $reason, int $duration = 3600): bool
    {
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + $duration);
            
            $sql = "INSERT INTO ip_blacklist (ip_address, reason, expires_at, created_at) 
                    VALUES (?, ?, ?, CURRENT_TIMESTAMP) 
                    ON DUPLICATE KEY UPDATE reason = ?, expires_at = ?, updated_at = CURRENT_TIMESTAMP";
            
            $this->db->insert($sql, [$ipAddress, $reason, $expiresAt, $reason, $expiresAt]);

            // Log the blocking event
            $this->logSecurityEvent('ip_blocked', [
                'ip_address' => $ipAddress,
                'reason' => $reason,
                'duration' => $duration,
                'expires_at' => $expiresAt
            ], 2);

            return true;
        } catch (\Exception $e) {
            error_log('SecurityMonitoringService::blockIP error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unblock IP address.
     */
    public function unblockIP(string $ipAddress): bool
    {
        try {
            $sql = "DELETE FROM ip_blacklist WHERE ip_address = ?";
            $this->db->delete($sql, [$ipAddress]);

            // Log the unblocking event
            $this->logSecurityEvent('ip_unblocked', [
                'ip_address' => $ipAddress
            ], 1);

            return true;
        } catch (\Exception $e) {
            error_log('SecurityMonitoringService::unblockIP error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if IP is blocked.
     */
    public function isIPBlocked(string $ipAddress): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM ip_blacklist WHERE ip_address = ? AND expires_at > NOW()";
            $result = $this->db->select($sql, [$ipAddress]);
            
            return ($result[0]['count'] ?? 0) > 0;
        } catch (\Exception $e) {
            error_log('SecurityMonitoringService::isIPBlocked error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent security events.
     */
    public function getRecentSecurityEvents(int $limit = 50): array
    {
        try {
            $sql = "SELECT event_type, severity, data, ip_address, created_at 
                    FROM security_events 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            
            return $this->db->select($sql, [$limit]);
        } catch (\Exception $e) {
            error_log('SecurityMonitoringService::getRecentSecurityEvents error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Private helper methods.
     */
    private function loadThreatPatterns(): array
    {
        return [
            'sql_injection' => [
                'patterns' => ['UNION SELECT', 'DROP TABLE', 'INSERT INTO', 'UPDATE SET'],
                'severity' => 'critical'
            ],
            'xss_attack' => [
                'patterns' => ['<script>', 'javascript:', 'onload=', 'onerror='],
                'severity' => 'high'
            ],
            'path_traversal' => [
                'patterns' => ['../', '..\\', '/etc/passwd', 'C:\\Windows'],
                'severity' => 'high'
            ]
        ];
    }

    private function checkIpThreat(string $ipAddress): void
    {
        $failedCount = $this->getFailedLoginCount($ipAddress, 900);
        
        if ($failedCount >= $this->config['max_login_attempts']) {
            $this->blockIP($ipAddress, 'Too many failed login attempts', 3600);
            
            $this->logSecurityEvent('account_locked', [
                'ip_address' => $ipAddress,
                'reason' => 'Too many failed login attempts',
                'failed_count' => $failedCount
            ], 2);
        }
    }

    private function getFailedLoginCount(string $ipAddress, int $timeWindow): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM login_attempts 
                    WHERE ip_address = ? AND success = 0 
                    AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)";
            
            $result = $this->db->select($sql, [$ipAddress, $timeWindow]);
            return $result[0]['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function detectUnusualLoginPatterns(int $userId, string $ipAddress): array
    {
        $threats = [];
        
        // Check for login from new location
        $previousLogins = $this->db->select("SELECT DISTINCT ip_address FROM login_attempts WHERE user_id = ? AND success = 1 AND ip_address != ? LIMIT 5", [$userId, $ipAddress]);
        
        if (empty($previousLogins)) {
            $threats[] = [
                'type' => 'new_location_login',
                'severity' => 'medium',
                'description' => 'User logged in from new location',
                'ip_address' => $ipAddress
            ];
        }

        return $threats;
    }

    private function detectMaliciousPatterns(string $ipAddress): array
    {
        $threats = [];
        
        // Check against known threat patterns
        foreach ($this->threatPatterns as $threatType => $config) {
            // This would typically involve checking request data against patterns
            // For now, we'll just log the check
            $this->logSecurityEvent('threat_pattern_check', [
                'threat_type' => $threatType,
                'ip_address' => $ipAddress,
                'severity' => $config['severity']
            ], 1);
        }

        return $threats;
    }

    private function getUserById(int $userId): ?array
    {
        try {
            $result = $this->db->select('SELECT * FROM users WHERE id = ?', [$userId]);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function logUserActivity(int $userId, string $action, string $ipAddress, string $userAgent): void
    {
        try {
            $sql = "INSERT INTO user_activity_log (user_id, action, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $this->db->insert($sql, [$userId, $action, $ipAddress, $userAgent]);
        } catch (\Exception $e) {
            error_log('Failed to log user activity: ' . $e->getMessage());
        }
    }
} 