<?php
declare(strict_types=1);

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;
use IslamWiki\Core\Database\Connection;

return function(Connection $connection) {
    return new class($connection) extends Migration
    {
        public function up(): void
        {
            error_log("[Migration] 0006_islamic_page_fields up() called");

            // Add Islamic fields to pages table using direct SQL
            $sql = "
                ALTER TABLE pages 
                ADD COLUMN islamic_category VARCHAR(50) NULL,
                ADD COLUMN islamic_tags JSON NULL,
                ADD COLUMN scholar_verified BOOLEAN NOT NULL DEFAULT 0,
                ADD COLUMN verified_by BIGINT UNSIGNED NULL,
                ADD COLUMN verified_at TIMESTAMP NULL,
                ADD COLUMN verification_notes TEXT NULL,
                ADD COLUMN islamic_references JSON NULL,
                ADD COLUMN islamic_citations JSON NULL,
                ADD COLUMN content_quality_score INT UNSIGNED DEFAULT 0,
                ADD COLUMN islamic_template VARCHAR(50) NULL,
                ADD COLUMN arabic_title VARCHAR(255) NULL,
                ADD COLUMN arabic_content TEXT NULL,
                ADD COLUMN islamic_metadata JSON NULL,
                ADD COLUMN islamic_permissions JSON NULL,
                ADD COLUMN moderation_status VARCHAR(20) DEFAULT 'draft',
                ADD COLUMN moderated_by BIGINT UNSIGNED NULL,
                ADD COLUMN moderated_at TIMESTAMP NULL,
                ADD COLUMN moderation_notes TEXT NULL
            ";
            
            $this->connection->statement($sql);
            
            // Add indexes
            $indexes = [
                "CREATE INDEX pages_islamic_category_index ON pages (islamic_category)",
                "CREATE INDEX pages_scholar_verified_index ON pages (scholar_verified)",
                "CREATE INDEX pages_moderation_status_index ON pages (moderation_status)",
                "CREATE INDEX pages_content_quality_score_index ON pages (content_quality_score)",
                "CREATE INDEX pages_islamic_template_index ON pages (islamic_template)",
                "CREATE INDEX pages_verified_by_index ON pages (verified_by)",
                "CREATE INDEX pages_moderated_by_index ON pages (moderated_by)"
            ];
            
            foreach ($indexes as $indexSql) {
                try {
                    $this->connection->statement($indexSql);
                } catch (\Exception $e) {
                    // Index might already exist, continue
                    error_log("Index creation warning: " . $e->getMessage());
                }
            }
        }

        public function down(): void
        {
            // Drop indexes first
            $indexes = [
                "DROP INDEX IF EXISTS pages_islamic_category_index ON pages",
                "DROP INDEX IF EXISTS pages_scholar_verified_index ON pages",
                "DROP INDEX IF EXISTS pages_moderation_status_index ON pages",
                "DROP INDEX IF EXISTS pages_content_quality_score_index ON pages",
                "DROP INDEX IF EXISTS pages_islamic_template_index ON pages",
                "DROP INDEX IF EXISTS pages_verified_by_index ON pages",
                "DROP INDEX IF EXISTS pages_moderated_by_index ON pages"
            ];
            
            foreach ($indexes as $indexSql) {
                try {
                    $this->connection->statement($indexSql);
                } catch (\Exception $e) {
                    // Index might not exist, continue
                    error_log("Index drop warning: " . $e->getMessage());
                }
            }
            
            // Drop columns
            $sql = "
                ALTER TABLE pages 
                DROP COLUMN IF EXISTS islamic_category,
                DROP COLUMN IF EXISTS islamic_tags,
                DROP COLUMN IF EXISTS scholar_verified,
                DROP COLUMN IF EXISTS verified_by,
                DROP COLUMN IF EXISTS verified_at,
                DROP COLUMN IF EXISTS verification_notes,
                DROP COLUMN IF EXISTS islamic_references,
                DROP COLUMN IF EXISTS islamic_citations,
                DROP COLUMN IF EXISTS content_quality_score,
                DROP COLUMN IF EXISTS islamic_template,
                DROP COLUMN IF EXISTS arabic_title,
                DROP COLUMN IF EXISTS arabic_content,
                DROP COLUMN IF EXISTS islamic_metadata,
                DROP COLUMN IF EXISTS islamic_permissions,
                DROP COLUMN IF EXISTS moderation_status,
                DROP COLUMN IF EXISTS moderated_by,
                DROP COLUMN IF EXISTS moderated_at,
                DROP COLUMN IF EXISTS moderation_notes
            ";
            
            $this->connection->statement($sql);
        }
    };
}; 