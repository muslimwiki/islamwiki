<?php

/**
 * Create Security Tables Migration
 *
 * Creates the necessary database tables for security monitoring and user management.
 *
 * @package IslamWiki\Extensions\AmanSecurity\Database\Migrations
 * @version 0.0.1.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Extensions\AmanSecurity\Database\Migrations;

use IslamWiki\Core\Database\Connection;

class CreateSecurityTables
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Run the migration.
     */
    public function up(): void
    {
        $this->createLoginAttemptsTable();
        $this->createSecurityEventsTable();
        $this->createUserActivityLogTable();
        $this->createIpBlacklistTable();
        $this->createUserSessionsTable();
    }

    /**
     * Rollback the migration.
     */
    public function down(): void
    {
        $this->dropTable('user_sessions');
        $this->dropTable('ip_blacklist');
        $this->dropTable('user_activity_log');
        $this->dropTable('security_events');
        $this->dropTable('login_attempts');
    }

    /**
     * Create login attempts table.
     */
    private function createLoginAttemptsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            username VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NULL,
            success TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_ip_address (ip_address),
            INDEX idx_created_at (created_at),
            INDEX idx_success (success)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->execute($sql);
    }

    /**
     * Create security events table.
     */
    private function createSecurityEventsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS security_events (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(100) NOT NULL,
            severity TINYINT UNSIGNED NOT NULL DEFAULT 1,
            data JSON NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_type (event_type),
            INDEX idx_severity (severity),
            INDEX idx_ip_address (ip_address),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->execute($sql);
    }

    /**
     * Create user activity log table.
     */
    private function createUserActivityLogTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_activity_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(100) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NULL,
            metadata JSON NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->execute($sql);
    }

    /**
     * Create IP blacklist table.
     */
    private function createIpBlacklistTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS ip_blacklist (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL UNIQUE,
            reason TEXT NOT NULL,
            expires_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_ip_address (ip_address),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->execute($sql);
    }

    /**
     * Create user sessions table.
     */
    private function createUserSessionsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            session_id VARCHAR(255) NOT NULL UNIQUE,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NULL,
            device_id VARCHAR(255) NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_session_id (session_id),
            INDEX idx_expires_at (expires_at),
            INDEX idx_last_activity (last_activity),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->db->execute($sql);
    }

    /**
     * Drop a table.
     */
    private function dropTable(string $tableName): void
    {
        $sql = "DROP TABLE IF EXISTS {$tableName}";
        $this->db->execute($sql);
    }
} 