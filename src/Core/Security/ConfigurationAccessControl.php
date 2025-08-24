<?php

/**
 * Configuration Access Control
 *
 * Advanced role-based access control system for configuration management
 * with granular permissions, approval workflows, and audit logging.
 *
 * @package IslamWiki\Core\Security
 * @version 0.0.21
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Security;

use IslamWiki\Core\Database\Connection;
use Logging;\Logger

class ConfigurationAccessControl
{
    /**
     * The database connection.
     */
    private Connection $db;

    /**
     * The logger instance.
     */
    private Logging $logger;

    /**
     * The current user ID.
     */
    private ?int $userId;

    /**
     * The current user roles.
     */
    private array $userRoles = [];

    /**
     * Configuration permissions.
     */
    private array $permissions = [
        'admin' => [
            'configuration.view' => true,
            'configuration.edit' => true,
            'configuration.delete' => true,
            'configuration.export' => true,
            'configuration.import' => true,
            'configuration.backup' => true,
            'configuration.restore' => true,
            'configuration.audit' => true,
            'configuration.security' => true,
            'configuration.encryption' => true
        ],
        'config_manager' => [
            'configuration.view' => true,
            'configuration.edit' => true,
            'configuration.delete' => false,
            'configuration.export' => true,
            'configuration.import' => true,
            'configuration.backup' => true,
            'configuration.restore' => false,
            'configuration.audit' => true,
            'configuration.security' => false,
            'configuration.encryption' => false
        ],
        'security_admin' => [
            'configuration.view' => true,
            'configuration.edit' => false,
            'configuration.delete' => false,
            'configuration.export' => false,
            'configuration.import' => false,
            'configuration.backup' => false,
            'configuration.restore' => false,
            'configuration.audit' => true,
            'configuration.security' => true,
            'configuration.encryption' => true
        ],
        'viewer' => [
            'configuration.view' => true,
            'configuration.edit' => false,
            'configuration.delete' => false,
            'configuration.export' => false,
            'configuration.import' => false,
            'configuration.backup' => false,
            'configuration.restore' => false,
            'configuration.audit' => false,
            'configuration.security' => false,
            'configuration.encryption' => false
        ]
    ];

    /**
     * Create a new configuration access control instance.
     */
    public function __construct(Connection $db, Logging $logger, ?int $userId = null)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->userId = $userId;
        $this->loadUserRoles();
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->userId) {
            return false;
        }

        foreach ($this->userRoles as $role) {
            if (isset($this->permissions[$role][$permission]) && $this->permissions[$role][$permission]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can view configuration.
     */
    public function canView(): bool
    {
        return $this->hasPermission('configuration.view');
    }

    /**
     * Check if user can edit configuration.
     */
    public function canEdit(): bool
    {
        return $this->hasPermission('configuration.edit');
    }

    /**
     * Check if user can delete configuration.
     */
    public function canDelete(): bool
    {
        return $this->hasPermission('configuration.delete');
    }

    /**
     * Check if user can export configuration.
     */
    public function canExport(): bool
    {
        return $this->hasPermission('configuration.export');
    }

    /**
     * Check if user can import configuration.
     */
    public function canImport(): bool
    {
        return $this->hasPermission('configuration.import');
    }

    /**
     * Check if user can create backups.
     */
    public function canBackup(): bool
    {
        return $this->hasPermission('configuration.backup');
    }

    /**
     * Check if user can restore backups.
     */
    public function canRestore(): bool
    {
        return $this->hasPermission('configuration.restore');
    }

    /**
     * Check if user can view audit logs.
     */
    public function canViewAudit(): bool
    {
        return $this->hasPermission('configuration.audit');
    }

    /**
     * Check if user can manage security settings.
     */
    public function canManageSecurity(): bool
    {
        return $this->hasPermission('configuration.security');
    }

    /**
     * Check if user can manage encryption.
     */
    public function canManageEncryption(): bool
    {
        return $this->hasPermission('configuration.encryption');
    }

    /**
     * Check if user can access sensitive configuration.
     */
    public function canAccessSensitive(string $category, string $key): bool
    {
        // Only admin and security_admin can access sensitive configuration
        if (!$this->hasPermission('configuration.security')) {
            return false;
        }

        // Log access to sensitive configuration
        $this->logSensitiveAccess($category, $key);

        return true;
    }

    /**
     * Require approval for configuration changes.
     */
    public function requireApproval(string $category, string $key, mixed $newValue): bool
    {
        // Check if this configuration requires approval
        $requiresApproval = $this->isApprovalRequired($category, $key);

        if ($requiresApproval) {
            return $this->createApprovalRequest($category, $key, $newValue);
        }

        return true;
    }

    /**
     * Get user permissions.
     */
    public function getUserPermissions(): array
    {
        $userPermissions = [];

        foreach ($this->userRoles as $role) {
            if (isset($this->permissions[$role])) {
                $userPermissions = array_merge($userPermissions, $this->permissions[$role]);
            }
        }

        return $userPermissions;
    }

    /**
     * Get user roles.
     */
    public function getUserRoles(): array
    {
        return $this->userRoles;
    }

    /**
     * Load user roles from database.
     */
    private function loadUserRoles(): void
    {
        if (!$this->userId) {
            return;
        }

        try {
            $roles = $this->db->table('user_roles')
                ->where('user_id', $this->userId)
                ->get();

            foreach ($roles as $role) {
                $this->userRoles[] = $role['role'];
            }

            // If no roles found, assign default role
            if (empty($this->userRoles)) {
                $this->userRoles[] = 'viewer';
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to load user roles: ' . $e->getMessage());
            $this->userRoles = ['viewer'];
        }
    }

    /**
     * Check if configuration requires approval.
     */
    private function isApprovalRequired(string $category, string $key): bool
    {
        // Security and encryption settings always require approval
        if (in_array($category, ['security', 'encryption'])) {
            return true;
        }

        // Check if this specific configuration requires approval
        try {
            $config = $this->db->table('configuration')
                ->where('category', $category)
                ->where('key_name', $key)
                ->first();

            return $config && $config['requires_approval'] ?? false;
        } catch (\Exception $e) {
            $this->logger->error('Failed to check approval requirement: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create approval request.
     */
    private function createApprovalRequest(string $category, string $key, mixed $newValue): bool
    {
        try {
            $this->db->table('configuration_approvals')->insert([
                'user_id' => $this->userId,
                'category' => $category,
                'key_name' => $key,
                'new_value' => is_array($newValue) ? json_encode($newValue) : (string) $newValue,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info('Configuration approval request created', [
                'user_id' => $this->userId,
                'category' => $category,
                'key_name' => $key
            ]);

            return false; // Return false to indicate approval is required
        } catch (\Exception $e) {
            $this->logger->error('Failed to create approval request: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log access to sensitive configuration.
     */
    private function logSensitiveAccess(string $category, string $key): void
    {
        try {
            $this->db->table('configuration_security_log')->insert([
                'user_id' => $this->userId,
                'category' => $category,
                'key_name' => $key,
                'action' => 'sensitive_access',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->warning('Sensitive configuration accessed', [
                'user_id' => $this->userId,
                'category' => $category,
                'key_name' => $key
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to log sensitive access: ' . $e->getMessage());
        }
    }

    /**
     * Get approval requests for user.
     */
    public function getApprovalRequests(): array
    {
        if (!$this->canManageSecurity()) {
            return [];
        }

        try {
            return $this->db->table('configuration_approvals')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Failed to get approval requests: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Approve configuration change.
     */
    public function approveConfiguration(int $approvalId): bool
    {
        if (!$this->canManageSecurity()) {
            return false;
        }

        try {
            $approval = $this->db->table('configuration_approvals')
                ->where('id', $approvalId)
                ->where('status', 'pending')
                ->first();

            if (!$approval) {
                return false;
            }

            // Update the configuration
            $this->db->table('configuration')
                ->where('category', $approval['category'])
                ->where('key_name', $approval['key_name'])
                ->update([
                    'value' => $approval['new_value'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Mark approval as approved
            $this->db->table('configuration_approvals')
                ->where('id', $approvalId)
                ->update([
                    'status' => 'approved',
                    'approved_by' => $this->userId,
                    'approved_at' => date('Y-m-d H:i:s')
                ]);

            $this->logger->info('Configuration change approved', [
                'approval_id' => $approvalId,
                'approved_by' => $this->userId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to approve configuration: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject configuration change.
     */
    public function rejectConfiguration(int $approvalId, string $reason = ''): bool
    {
        if (!$this->canManageSecurity()) {
            return false;
        }

        try {
            $this->db->table('configuration_approvals')
                ->where('id', $approvalId)
                ->update([
                    'status' => 'rejected',
                    'rejected_by' => $this->userId,
                    'rejection_reason' => $reason,
                    'rejected_at' => date('Y-m-d H:i:s')
                ]);

            $this->logger->info('Configuration change rejected', [
                'approval_id' => $approvalId,
                'rejected_by' => $this->userId,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to reject configuration: ' . $e->getMessage());
            return false;
        }
    }
}
