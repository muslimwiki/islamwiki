<?php

/**
 * Migration: Add Draft System to Pages Table
 *
 * This migration adds the necessary database structure for:
 * - Draft management system
 * - User tracking for pages
 *
 * @package IslamWiki\Database\Migrations
 */

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;

/**
 * Add Draft System to Pages Table
 */
class AddDraftSystemToPages
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        $pdo = $this->db->getPdo();
        
        // Add draft-related columns to pages table
        $this->addDraftColumnsToPages($pdo);

        // Create editing_sessions table for collaborative editing
        $this->createEditingSessionsTable($pdo);

        // Add indexes for better performance
        $this->addPerformanceIndexes($pdo);
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        $pdo = $this->db->getPdo();
        
        // Remove draft columns from pages table
        $this->removeDraftColumnsFromPages($pdo);

        // Drop editing_sessions table
        $this->dropEditingSessionsTable($pdo);

        // Remove performance indexes
        $this->removePerformanceIndexes($pdo);
    }

    /**
     * Add draft-related columns to pages table.
     *
     * @param PDO $pdo
     * @return void
     */
    private function addDraftColumnsToPages(PDO $pdo): void
    {
        $sql = "
            ALTER TABLE pages
            ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER content,
            ADD COLUMN draft_status ENUM('draft', 'pending', 'published', 'rejected') DEFAULT 'published' AFTER user_id,
            ADD COLUMN draft_notes TEXT NULL AFTER draft_status,
            ADD COLUMN published_at TIMESTAMP NULL AFTER draft_notes,
            ADD COLUMN published_by BIGINT UNSIGNED NULL AFTER published_at,
            ADD COLUMN rejected_at TIMESTAMP NULL AFTER published_by,
            ADD COLUMN rejected_by BIGINT UNSIGNED NULL AFTER rejected_at,
            ADD COLUMN rejection_reason TEXT NULL AFTER rejected_by,
            ADD COLUMN auto_save_content TEXT NULL AFTER rejection_reason,
            ADD COLUMN last_auto_save TIMESTAMP NULL AFTER auto_save_content
        ";

        $pdo->exec($sql);

        // Add foreign key constraints
        $pdo->exec("
            ALTER TABLE pages
            ADD CONSTRAINT fk_pages_user_id
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ");

        $pdo->exec("
            ALTER TABLE pages
            ADD CONSTRAINT fk_pages_published_by
            FOREIGN KEY (published_by) REFERENCES users(id) ON DELETE SET NULL
        ");

        $pdo->exec("
            ALTER TABLE pages
            ADD CONSTRAINT fk_pages_rejected_by
            FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL
        ");
    }

    /**
     * Create editing_sessions table for collaborative editing.
     *
     * @param PDO $pdo
     * @return void
     */
    private function createEditingSessionsTable(PDO $pdo): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS editing_sessions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                page_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                session_token VARCHAR(255) NOT NULL,
                joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE,
                user_cursor_position INT DEFAULT 0,
                user_selection_start INT DEFAULT 0,
                user_selection_end INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                INDEX idx_page_id (page_id),
                INDEX idx_user_id (user_id),
                INDEX idx_session_token (session_token),
                INDEX idx_last_activity (last_activity),
                INDEX idx_is_active (is_active),

                FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

                UNIQUE KEY unique_user_page_session (user_id, page_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        $pdo->exec($sql);
    }

    /**
     * Add performance indexes.
     *
     * @param PDO $pdo
     * @return void
     */
    private function addPerformanceIndexes(PDO $pdo): void
    {
        // Add indexes for better query performance
        $pdo->exec("
            ALTER TABLE pages
            ADD INDEX idx_draft_status (draft_status),
            ADD INDEX idx_published_at (published_at),
            ADD INDEX idx_rejected_at (rejected_at),
            ADD INDEX idx_last_auto_save (last_auto_save),
            ADD INDEX idx_user_id (user_id)
        ");
    }

    /**
     * Remove draft columns from pages table.
     *
     * @param PDO $pdo
     * @return void
     */
    private function removeDraftColumnsFromPages(PDO $pdo): void
    {
        // Remove foreign key constraints first
        try {
            $pdo->exec("
                ALTER TABLE pages
                DROP FOREIGN KEY fk_pages_user_id
            ");
        } catch (Exception $e) {
            // Constraint might not exist
        }

        try {
            $pdo->exec("
                ALTER TABLE pages
                DROP FOREIGN KEY fk_pages_published_by
            ");
        } catch (Exception $e) {
            // Constraint might not exist
        }

        try {
            $pdo->exec("
                ALTER TABLE pages
                DROP FOREIGN KEY fk_pages_rejected_by
            ");
        } catch (Exception $e) {
            // Constraint might not exist
        }

        // Remove columns
        $sql = "
            ALTER TABLE pages
            DROP COLUMN user_id,
            DROP COLUMN draft_status,
            DROP COLUMN draft_notes,
            DROP COLUMN published_at,
            DROP COLUMN published_by,
            DROP COLUMN rejected_at,
            DROP COLUMN rejected_by,
            DROP COLUMN rejection_reason,
            DROP COLUMN auto_save_content,
            DROP COLUMN last_auto_save
        ";

        $pdo->exec($sql);
    }

    /**
     * Drop editing_sessions table.
     *
     * @param PDO $pdo
     * @return void
     */
    private function dropEditingSessionsTable(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS editing_sessions");
    }

    /**
     * Remove performance indexes.
     *
     * @param PDO $pdo
     * @return void
     */
    private function removePerformanceIndexes(PDO $pdo): void
    {
        try {
            $pdo->exec("
                ALTER TABLE pages
                DROP INDEX idx_draft_status,
                DROP INDEX idx_published_at,
                DROP INDEX idx_rejected_at,
                DROP INDEX idx_last_auto_save,
                DROP INDEX idx_user_id
            ");
        } catch (Exception $e) {
            // Indexes might not exist
        }
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
    
    // Initialize application
    $app = new \IslamWiki\Core\NizamApplication(__DIR__ . '/../..');
    $container = $app->getContainer();
    
    // Get database connection
    $connection = $container->get('db');
    
    // Create migration instance
    $migration = new AddDraftSystemToPages($connection);
    
    // Run migration
    echo "Running migration: Add Draft System to Pages\n";
    $migration->up();
    echo "Migration completed successfully!\n";
} 