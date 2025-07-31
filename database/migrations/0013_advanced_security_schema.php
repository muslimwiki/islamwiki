<?php
declare(strict_types=1);

/**
 * Migration: Advanced Security Schema
 * 
 * This migration creates the database schema for advanced security features
 * including configuration encryption, access control, approval workflows,
 * and security audit logging.
 * 
 * @package IslamWiki
 * @version 0.0.21
 * @license AGPL-3.0-only
 */

use IslamWiki\Core\Database\Migrations\Migration;

class Migration_0013_AdvancedSecuritySchema extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // User roles table
        $this->createTable('user_roles', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('User ID');
            $table->string('role', 50)->comment('Role name (admin, config_manager, security_admin, viewer)');
            $table->boolean('is_active')->default(true)->comment('Whether this role is active');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('role');
            $table->index('is_active');
        });

        // Configuration approvals table
        $this->createTable('configuration_approvals', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('User who requested the change');
            $table->string('category', 50)->comment('Configuration category');
            $table->string('key_name', 100)->comment('Configuration key name');
            $table->text('new_value')->comment('New configuration value');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Approval status');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('User who approved the change');
            $table->unsignedBigInteger('rejected_by')->nullable()->comment('User who rejected the change');
            $table->text('rejection_reason')->nullable()->comment('Reason for rejection');
            $table->timestamp('approved_at')->nullable()->comment('When the change was approved');
            $table->timestamp('rejected_at')->nullable()->comment('When the change was rejected');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index(['category', 'key_name']);
            $table->index('status');
            $table->index('created_at');
        });

        // Configuration security log table
        $this->createTable('configuration_security_log', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('User who performed the action');
            $table->string('category', 50)->comment('Configuration category');
            $table->string('key_name', 100)->comment('Configuration key name');
            $table->string('action', 50)->comment('Action performed (sensitive_access, encryption_change, etc.)');
            $table->string('ip_address', 45)->nullable()->comment('IP address of the user');
            $table->text('user_agent')->nullable()->comment('User agent string');
            $table->json('details')->nullable()->comment('Additional details about the action');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index(['category', 'key_name']);
            $table->index('action');
            $table->index('created_at');
        });

        // Encryption keys table
        $this->createTable('encryption_keys', function ($table) {
            $table->id();
            $table->string('key_id', 64)->unique()->comment('Unique key identifier');
            $table->text('key_data')->comment('Encrypted key data');
            $table->string('algorithm', 20)->default('aes-256-gcm')->comment('Encryption algorithm');
            $table->boolean('is_active')->default(true)->comment('Whether this key is active');
            $table->timestamp('created_at')->comment('When the key was created');
            $table->timestamp('expires_at')->nullable()->comment('When the key expires');
            $table->timestamps();
            
            $table->index('key_id');
            $table->index('is_active');
            $table->index('expires_at');
        });

        // Security audit log table
        $this->createTable('security_audit_log', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('User who performed the action');
            $table->string('action', 100)->comment('Security action performed');
            $table->string('resource', 100)->nullable()->comment('Resource affected');
            $table->string('ip_address', 45)->nullable()->comment('IP address of the user');
            $table->text('user_agent')->nullable()->comment('User agent string');
            $table->json('details')->nullable()->comment('Additional details about the action');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->comment('Severity level');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('action');
            $table->index('severity');
            $table->index('created_at');
        });

        // Add requires_approval column to configuration table
        $this->addColumn('configuration', 'requires_approval', function ($table) {
            $table->boolean('requires_approval')->default(false)->comment('Whether this configuration requires approval');
        });

        // Add encryption_enabled column to configuration table
        $this->addColumn('configuration', 'encryption_enabled', function ($table) {
            $table->boolean('encryption_enabled')->default(false)->comment('Whether this configuration value is encrypted');
        });

        // Insert default user roles
        $this->insertDefaultUserRoles();
        
        // Insert default encryption key
        $this->insertDefaultEncryptionKey();
        
        // Update sensitive configuration to require approval
        $this->updateSensitiveConfiguration();
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->dropTable('security_audit_log');
        $this->dropTable('encryption_keys');
        $this->dropTable('configuration_security_log');
        $this->dropTable('configuration_approvals');
        $this->dropTable('user_roles');
        
        $this->dropColumn('configuration', 'requires_approval');
        $this->dropColumn('configuration', 'encryption_enabled');
    }

    /**
     * Insert default user roles.
     */
    private function insertDefaultUserRoles(): void
    {
        $roles = [
            [
                'user_id' => 1, // Assuming user ID 1 is the admin
                'role' => 'admin',
                'is_active' => true
            ]
        ];

        foreach ($roles as $role) {
            $this->insert('user_roles', $role);
        }
    }

    /**
     * Insert default encryption key.
     */
    private function insertDefaultEncryptionKey(): void
    {
        $keyId = bin2hex(random_bytes(16));
        $keyData = base64_encode(random_bytes(32));
        
        $this->insert('encryption_keys', [
            'key_id' => $keyId,
            'key_data' => $keyData,
            'algorithm' => 'aes-256-gcm',
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year'))
        ]);
    }

    /**
     * Update sensitive configuration to require approval.
     */
    private function updateSensitiveConfiguration(): void
    {
        // Update security-related configuration to require approval
        $securityConfigs = [
            'security.session_lifetime',
            'security.csrf_protection',
            'security.rate_limiting',
            'security.encryption_enabled',
            'security.key_rotation_interval'
        ];

        foreach ($securityConfigs as $configKey) {
            $parts = explode('.', $configKey);
            $category = $parts[0];
            $keyName = $parts[1];
            
            $this->db->table('configuration')
                ->where('category', $category)
                ->where('key_name', $keyName)
                ->update([
                    'requires_approval' => true,
                    'encryption_enabled' => true
                ]);
        }
    }
} 