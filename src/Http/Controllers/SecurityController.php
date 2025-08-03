<?php
declare(strict_types=1);

/**
 * Security Controller
 * 
 * Advanced security management controller for encryption, access control,
 * audit logging, and security monitoring.
 * 
 * @package IslamWiki\Http\Controllers
 * @version 0.0.21
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Security\ConfigurationEncryption;
use IslamWiki\Core\Security\ConfigurationAccessControl;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Asas;
use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Database\Connection;

class SecurityController extends Controller
{
    /**
     * The configuration encryption instance.
     */
    private ConfigurationEncryption $encryption;

    /**
     * The access control instance.
     */
    private ConfigurationAccessControl $accessControl;

    /**
     * The database connection.
     */
    private Connection $db;

    /**
     * The logger instance.
     */
    private Shahid $shahid;

    /**
     * Create a new security controller instance.
     */
    public function __construct(Asas $asas)
    {
        parent::__construct($asas);
        $this->db = $asas->get(Connection::class);
        $this->shahid = $asas->get(Shahid::class);
        $this->encryption = new ConfigurationEncryption($this->shahid);
        $this->accessControl = new ConfigurationAccessControl($this->db, $this->shahid, $this->getCurrentUserId());
    }

    /**
     * Display the security dashboard.
     */
    public function index(): Response
    {
        try {
            if (!$this->accessControl->canManageSecurity()) {
                return $this->errorResponse('Access denied', 403);
            }

            $securityStats = $this->getSecurityStats();
            $recentAuditLogs = $this->getRecentAuditLogs();
            $pendingApprovals = $this->accessControl->getApprovalRequests();
            $encryptionInfo = $this->encryption->getKeyInfo();

            return $this->view('security/index', [
                'security_stats' => $securityStats,
                'recent_audit_logs' => $recentAuditLogs,
                'pending_approvals' => $pendingApprovals,
                'encryption_info' => $encryptionInfo,
                'title' => 'Security Dashboard'
            ]);
        } catch (\Exception $e) {
            $this->shahid->error('Security dashboard error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load security dashboard', 500);
        }
    }

    /**
     * Display the audit log.
     */
    public function auditLog(Request $request): Response
    {
        try {
            if (!$this->accessControl->canViewAudit()) {
                return $this->errorResponse('Access denied', 403);
            }

            $limit = (int) ($request->getQueryParams()['limit'] ?? 100);
            $offset = (int) ($request->getQueryParams()['offset'] ?? 0);
            $severity = $request->getQueryParams()['severity'] ?? null;
            $action = $request->getQueryParams()['action'] ?? null;

            $auditLogs = $this->getAuditLogs($limit, $offset, $severity, $action);
            $totalCount = $this->getAuditLogCount($severity, $action);

            return $this->view('security/audit-log', [
                'audit_logs' => $auditLogs,
                'total_count' => $totalCount,
                'limit' => $limit,
                'offset' => $offset,
                'severity' => $severity,
                'action' => $action,
                'title' => 'Security Audit Log'
            ]);
        } catch (\Exception $e) {
            $this->shahid->error('Audit log error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load audit log', 500);
        }
    }

    /**
     * Display approval requests.
     */
    public function approvals(): Response
    {
        try {
            if (!$this->accessControl->canManageSecurity()) {
                return $this->errorResponse('Access denied', 403);
            }

            $approvals = $this->accessControl->getApprovalRequests();

            return $this->view('security/approvals', [
                'approvals' => $approvals,
                'title' => 'Configuration Approvals'
            ]);
        } catch (\Exception $e) {
            $this->shahid->error('Approvals error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load approvals', 500);
        }
    }

    /**
     * Approve a configuration change.
     */
    public function approve(Request $request): Response
    {
        try {
            if (!$this->accessControl->canManageSecurity()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $data = $request->getParsedBody();
            
            if (!isset($data['approval_id'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Approval ID is required'
                ], 400);
            }

            $approvalId = (int) $data['approval_id'];

            if ($this->accessControl->approveConfiguration($approvalId)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Configuration change approved successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to approve configuration change'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->shahid->error('Configuration approval error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Reject a configuration change.
     */
    public function reject(Request $request): Response
    {
        try {
            if (!$this->accessControl->canManageSecurity()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $data = $request->getParsedBody();
            
            if (!isset($data['approval_id'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Approval ID is required'
                ], 400);
            }

            $approvalId = (int) $data['approval_id'];
            $reason = $data['reason'] ?? '';

            if ($this->accessControl->rejectConfiguration($approvalId, $reason)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Configuration change rejected successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to reject configuration change'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->shahid->error('Configuration rejection error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Rotate encryption key.
     */
    public function rotateKey(): Response
    {
        try {
            if (!$this->accessControl->canManageEncryption()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            if ($this->encryption->rotateKey()) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Encryption key rotated successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to rotate encryption key'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->shahid->error('Key rotation error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get encryption information.
     */
    public function encryptionInfo(): Response
    {
        try {
            if (!$this->accessControl->canManageEncryption()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $keyInfo = $this->encryption->getKeyInfo();

            return $this->jsonResponse([
                'success' => true,
                'encryption_info' => $keyInfo
            ]);
        } catch (\Exception $e) {
            $this->shahid->error('Encryption info error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get security statistics.
     */
    public function securityStats(): Response
    {
        try {
            if (!$this->accessControl->canManageSecurity()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $stats = $this->getSecurityStats();

            return $this->jsonResponse([
                'success' => true,
                'security_stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->shahid->error('Security stats error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get security statistics.
     */
    private function getSecurityStats(): array
    {
        try {
            $stats = [];

            // Total audit log entries
            $stats['total_audit_entries'] = $this->db->table('security_audit_log')->count();

            // Recent audit entries (last 24 hours)
            $stats['recent_audit_entries'] = $this->db->table('security_audit_log')
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->count();

            // Pending approvals
            $stats['pending_approvals'] = $this->db->table('configuration_approvals')
                ->where('status', 'pending')
                ->count();

            // High severity events
            $stats['high_severity_events'] = $this->db->table('security_audit_log')
                ->where('severity', 'high')
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->count();

            // Critical severity events
            $stats['critical_severity_events'] = $this->db->table('security_audit_log')
                ->where('severity', 'critical')
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->count();

            // Active encryption keys
            $stats['active_encryption_keys'] = $this->db->table('encryption_keys')
                ->where('is_active', true)
                ->count();

            return $stats;
        } catch (\Exception $e) {
            $this->shahid->error('Failed to get security stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent audit logs.
     */
    private function getRecentAuditLogs(int $limit = 10): array
    {
        try {
            return $this->db->table('security_audit_log')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->shahid->error('Failed to get recent audit logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get audit logs with filtering.
     */
    private function getAuditLogs(int $limit, int $offset, ?string $severity = null, ?string $action = null): array
    {
        try {
            $query = $this->db->table('security_audit_log');

            if ($severity) {
                $query->where('severity', $severity);
            }

            if ($action) {
                $query->where('action', $action);
            }

            return $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->shahid->error('Failed to get audit logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get audit log count.
     */
    private function getAuditLogCount(?string $severity = null, ?string $action = null): int
    {
        try {
            $query = $this->db->table('security_audit_log');

            if ($severity) {
                $query->where('severity', $severity);
            }

            if ($action) {
                $query->where('action', $action);
            }

            return $query->count();
        } catch (\Exception $e) {
            $this->shahid->error('Failed to get audit log count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get current user ID from session.
     */
    private function getCurrentUserId(): ?int
    {
        try {
            $session = $this->container->get('session');
            return $session?->get('user_id');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Render a template with data.
     */
    private function render(string $template, array $data = []): Response
    {
        $renderer = $this->container->get('view');
        $content = $renderer->render($template, $data);
        
        return new Response(200, ['Content-Type' => 'text/html'], $content);
    }

    /**
     * Create JSON response.
     */
    private function jsonResponse(array $data, int $status = 200): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }

    /**
     * Create error response.
     */
    private function errorResponse(string $message, int $status = 500): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'text/html'],
            "<h1>Error {$status}</h1><p>{$message}</p>"
        );
    }
} 