<?php

/**
 * Migration: Add Draft System and Collaborative Editing
 * 
 * This migration adds the necessary database structure for:
 * - Draft management system
 * - Collaborative editing sessions
 * - Enhanced page revision tracking
 * 
 * @package IslamWiki\Database\Migrations
 */

declare(strict_types=1);

use IslamWiki\Core\Database\Migration;

class AddDraftAndCollaborativeSystem extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        // Add draft-related columns to pages table
        $this->addDraftColumnsToPages();
        
        // Create editing_sessions table for collaborative editing
        $this->createEditingSessionsTable();
        
        // Enhance page_revisions table
        $this->enhancePageRevisionsTable();
        
        // Add indexes for better performance
        $this->addPerformanceIndexes();
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        // Remove draft columns from pages table
        $this->removeDraftColumnsFromPages();
        
        // Drop editing_sessions table
        $this->dropEditingSessionsTable();
        
        // Revert page_revisions table enhancements
        $this->revertPageRevisionsTable();
        
        // Remove performance indexes
        $this->removePerformanceIndexes();
    }

    /**
     * Add draft-related columns to pages table.
     *
     * @return void
     */
    private function addDraftColumnsToPages(): void
    {
        $sql = "
            ALTER TABLE pages 
            ADD COLUMN draft_status ENUM('draft', 'pending', 'published', 'rejected') DEFAULT 'published' AFTER content,
            ADD COLUMN draft_notes TEXT NULL AFTER draft_status,
            ADD COLUMN published_at TIMESTAMP NULL AFTER draft_notes,
            ADD COLUMN published_by INT NULL AFTER published_at,
            ADD COLUMN rejected_at TIMESTAMP NULL AFTER published_by,
            ADD COLUMN rejected_by INT NULL AFTER rejected_at,
            ADD COLUMN rejection_reason TEXT NULL AFTER rejected_by,
            ADD COLUMN auto_save_content TEXT NULL AFTER rejection_reason,
            ADD COLUMN last_auto_save TIMESTAMP NULL AFTER auto_save_content
        ";
        
        $this->db->getPdo()->exec($sql);
        
        // Add foreign key constraints
        $this->db->getPdo()->exec("
            ALTER TABLE pages 
            ADD CONSTRAINT fk_pages_published_by 
            FOREIGN KEY (published_by) REFERENCES users(id) ON DELETE SET NULL
        ");
        
        $this->db->getPdo()->exec("
            ALTER TABLE pages 
            ADD CONSTRAINT fk_pages_rejected_by 
            FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL
        ");
    }

    /**
     * Create editing_sessions table for collaborative editing.
     *
     * @return void
     */
    private function createEditingSessionsTable(): void
    {
        $sql = "
            CREATE TABLE editing_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                page_id INT NOT NULL,
                user_id INT NOT NULL,
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
        
        $this->db->getPdo()->exec($sql);
    }

    /**
     * Enhance page_revisions table with additional fields.
     *
     * @return void
     */
    private function enhancePageRevisionsTable(): void
    {
        $sql = "
            ALTER TABLE page_revisions 
            ADD COLUMN revision_type ENUM('minor', 'major', 'rollback', 'merge') DEFAULT 'major' AFTER edit_summary,
            ADD COLUMN parent_revision_id INT NULL AFTER revision_type,
            ADD COLUMN merge_source_revision_id INT NULL AFTER parent_revision_id,
            ADD COLUMN conflict_resolved BOOLEAN DEFAULT FALSE AFTER merge_source_revision_id,
            ADD COLUMN conflict_resolution_method VARCHAR(50) NULL AFTER conflict_resolved,
            ADD COLUMN edit_duration_seconds INT NULL AFTER conflict_resolution_method,
            ADD COLUMN characters_added INT NULL AFTER edit_duration_seconds,
            ADD COLUMN characters_removed INT NULL AFTER characters_added,
            ADD COLUMN lines_added INT NULL AFTER characters_removed,
            ADD COLUMN lines_removed INT NULL AFTER lines_added
        ";
        
        $this->db->getPdo()->exec($sql);
        
        // Add foreign key for parent revision
        $this->db->getPdo()->exec("
            ALTER TABLE page_revisions 
            ADD CONSTRAINT fk_revisions_parent 
            FOREIGN KEY (parent_revision_id) REFERENCES page_revisions(id) ON DELETE SET NULL
        ");
        
        // Add foreign key for merge source revision
        $this->db->getPdo()->exec("
            ALTER TABLE page_revisions 
            ADD CONSTRAINT fk_revisions_merge_source 
            FOREIGN KEY (merge_source_revision_id) REFERENCES page_revisions(id) ON DELETE SET NULL
        ");
    }

    /**
     * Add performance indexes.
     *
     * @return void
     */
    private function addPerformanceIndexes(): void
    {
        // Add indexes for better query performance
        $this->db->getPdo()->exec("
            ALTER TABLE pages 
            ADD INDEX idx_draft_status (draft_status),
            ADD INDEX idx_published_at (published_at),
            ADD INDEX idx_rejected_at (rejected_at),
            ADD INDEX idx_last_auto_save (last_auto_save)
        ");
        
        $this->db->getPdo()->exec("
            ALTER TABLE page_revisions 
            ADD INDEX idx_revision_type (revision_type),
            ADD INDEX idx_parent_revision (parent_revision_id),
            ADD INDEX idx_conflict_resolved (conflict_resolved),
            ADD INDEX idx_edit_duration (edit_duration_seconds)
        ");
    }

    /**
     * Remove draft columns from pages table.
     *
     * @return void
     */
    private function removeDraftColumnsFromPages(): void
    {
        // Remove foreign key constraints first
        $this->db->getPdo()->exec("
            ALTER TABLE pages 
            DROP FOREIGN KEY fk_pages_published_by
        ");
        
        $this->db->getPdo()->exec("
            ALTER TABLE pages 
            DROP FOREIGN KEY fk_pages_rejected_by
        ");
        
        // Remove columns
        $sql = "
            ALTER TABLE pages 
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
        
        $this->db->getPdo()->exec($sql);
    }

    /**
     * Drop editing_sessions table.
     *
     * @return void
     */
    private function dropEditingSessionsTable(): void
    {
        $this->db->getPdo()->exec("DROP TABLE IF EXISTS editing_sessions");
    }

    /**
     * Revert page_revisions table enhancements.
     *
     * @return void
     */
    private function revertPageRevisionsTable(): void
    {
        // Remove foreign key constraints first
        $this->db->getPdo()->exec("
            ALTER TABLE page_revisions 
            DROP FOREIGN KEY fk_revisions_parent
        ");
        
        $this->db->getPdo()->exec("
            ALTER TABLE page_revisions 
            DROP FOREIGN KEY fk_revisions_merge_source
        ");
        
        // Remove columns
        $sql = "
            ALTER TABLE page_revisions 
            DROP COLUMN revision_type,
            DROP COLUMN parent_revision_id,
            DROP COLUMN merge_source_revision_id,
            DROP COLUMN conflict_resolved,
            DROP COLUMN conflict_resolution_method,
            DROP COLUMN edit_duration_seconds,
            DROP COLUMN characters_added,
            DROP COLUMN characters_removed,
            DROP COLUMN lines_added,
            DROP COLUMN lines_removed
        ";
        
        $this->db->getPdo()->exec($sql);
    }

    /**
     * Remove performance indexes.
     *
     * @return void
     */
    private function removePerformanceIndexes(): void
    {
        $this->db->getPdo()->exec("
            ALTER TABLE pages 
            DROP INDEX idx_draft_status,
            DROP INDEX idx_published_at,
            DROP INDEX idx_rejected_at,
            DROP INDEX idx_last_auto_save
        ");
        
        $this->db->getPdo()->exec("
            ALTER TABLE page_revisions 
            DROP INDEX idx_revision_type,
            DROP INDEX idx_parent_revision,
            DROP INDEX idx_conflict_resolved,
            DROP INDEX idx_edit_duration
        ");
    }
} 