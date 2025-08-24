<?php

/**
 * Security Controller
 *
 * Advanced security management controller for encryption, access control,
 * audit logging, and security monitoring.
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Container;

/**
 * Security Controller - Handles Security Management Functionality
 */
class SecurityController extends Controller
{
    /**
     * Display the security dashboard.
     */
    public function index(Request $request): Response
    {
        try {
            // Check if user is admin
            $session = $this->container->get('session');
            if (!$session->isLoggedIn() || !$session->isAdmin()) {
                return new Response(403, [], 'Access Denied');
            }

            $securityStats = $this->getSecurityStats();
            $recentAuditLogs = $this->getRecentAuditLogs();
            $pendingApprovals = $this->getPendingApprovals();

            return $this->view('security/index', [
                'security_stats' => $securityStats,
                'recent_audit_logs' => $recentAuditLogs,
                'pending_approvals' => $pendingApprovals,
                'title' => 'Security Dashboard - IslamWiki'
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display the audit log.
     */
    public function auditLog(Request $request): Response
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn() || !$session->isAdmin()) {
                return new Response(403, [], 'Access Denied');
            }

            $limit = (int)($request->getQueryParams()['limit'] ?? 100);
            $offset = (int)($request->getQueryParams()['offset'] ?? 0);
            $severity = $request->getQueryParams()['severity'] ?? null;
            $action = $request->getQueryParams()['action'] ?? null;

            $auditLogs = $this->getAuditLogs($limit, $offset, $severity, $action);
            $totalLogs = $this->getTotalAuditLogs($severity, $action);

            return $this->view('security/audit-log', [
                'audit_logs' => $auditLogs,
                'total_logs' => $totalLogs,
                'current_page' => ceil($offset / $limit) + 1,
                'total_pages' => ceil($totalLogs / $limit),
                'title' => 'Security Audit Log - IslamWiki'
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Display access control settings.
     */
    public function accessControl(Request $request): Response
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn() || !$session->isAdmin()) {
                return new Response(403, [], 'Access Denied');
            }

            $roles = $this->getSecurityRoles();
            $permissions = $this->getSecurityPermissions();

            return $this->view('security/access-control', [
                'roles' => $roles,
                'permissions' => $permissions,
                'title' => 'Access Control - Security - IslamWiki'
            ], 200);
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Update access control settings.
     */
    public function updateAccessControl(Request $request): Response
    {
        try {
            $session = $this->container->get('session');
            if (!$session->isLoggedIn() || !$session->isAdmin()) {
                return new Response(403, [], 'Access Denied');
            }

            $data = $request->getParsedBody();
            $success = $this->updateSecuritySettings($data);

            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => 'Security settings updated successfully'
                ]);
            } else {
                return new Response(500, [], 'Failed to update security settings');
            }
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Get security statistics.
     */
    private function getSecurityStats(): array
    {
        try {
            $sql = "SELECT COUNT(*) as total_incidents FROM security_incidents WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            $totalIncidents = (int)($result['total_incidents'] ?? 0);
            
            return [
                'total_incidents' => $totalIncidents,
                'failed_logins' => 0,
                'suspicious_activities' => 0,
                'last_incident' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'total_incidents' => 0,
                'failed_logins' => 0,
                'suspicious_activities' => 0,
                'last_incident' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Get recent audit logs.
     */
    private function getRecentAuditLogs(): array
    {
        try {
            $sql = "SELECT id, action, user_id, ip_address, created_at, severity FROM security_audit_log 
                    ORDER BY created_at DESC LIMIT 20";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get pending approvals.
     */
    private function getPendingApprovals(): array
    {
        try {
            $sql = "SELECT id, user_id, action, created_at FROM security_approvals 
                    WHERE status = 'pending' ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get audit logs with filters.
     */
    private function getAuditLogs(int $limit, int $offset, ?string $severity, ?string $action): array
    {
        try {
            $sql = "SELECT id, action, user_id, ip_address, created_at, severity FROM security_audit_log WHERE 1=1";
            $params = [];
            
            if ($severity) {
                $sql .= " AND severity = ?";
                $params[] = $severity;
            }
            
            if ($action) {
                $sql .= " AND action = ?";
                $params[] = $action;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get total audit logs count.
     */
    private function getTotalAuditLogs(?string $severity, ?string $action): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM security_audit_log WHERE 1=1";
            $params = [];
            
            if ($severity) {
                $sql .= " AND severity = ?";
                $params[] = $severity;
            }
            
            if ($action) {
                $sql .= " AND action = ?";
                $params[] = $action;
            }
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get security roles.
     */
    private function getSecurityRoles(): array
    {
        return [
            'admin' => 'Administrator',
            'moderator' => 'Moderator',
            'user' => 'Regular User',
            'guest' => 'Guest'
        ];
    }

    /**
     * Get security permissions.
     */
    private function getSecurityPermissions(): array
    {
        return [
            'view_audit_log' => 'View Audit Log',
            'manage_users' => 'Manage Users',
            'manage_security' => 'Manage Security',
            'view_reports' => 'View Security Reports'
        ];
    }

    /**
     * Update security settings.
     */
    private function updateSecuritySettings(array $data): bool
    {
        // TODO: Implement actual security settings update
        return true;
    }
}
