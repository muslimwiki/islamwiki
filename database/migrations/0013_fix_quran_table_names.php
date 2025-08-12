<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Schema\Blueprint;
use IslamWiki\Core\Database\Connection;

return function (Connection $connection) {
    return new class ($connection) extends Migration
    {
        public function up(): void
        {
            error_log("[Migration] 0013_fix_quran_table_names up() called");

            // Ensure we're using ayah* naming convention
            // Check if tables exist with old names and rename them

            // Check if verses table exists and rename to ayahs
            if ($this->schema()->hasTable('verses')) {
                $this->schema()->rename('verses', 'ayahs');
                error_log("[Migration] Renamed 'verses' table to 'ayahs'");
            }

            // Check if verse_translations table exists and rename to ayah_translations
            if ($this->schema()->hasTable('verse_translations')) {
                $this->schema()->rename('verse_translations', 'ayah_translations');
                error_log("[Migration] Renamed 'verse_translations' table to 'ayah_translations'");
            }
            
            // Ensure ayahs table has correct column names
            if ($this->schema()->hasTable('ayahs')) {
                // Check if ayah_number column exists, if not rename from verse_number
                $columns = $this->connection->query("SHOW COLUMNS FROM ayahs LIKE 'verse_number'")->fetchAll();
                if (!empty($columns)) {
                    $this->connection->exec("ALTER TABLE ayahs CHANGE COLUMN verse_number ayah_number INT(10) UNSIGNED NOT NULL");
                    error_log("[Migration] Renamed 'verse_number' column to 'ayah_number' in ayahs table");
                }
            }
            
            // Ensure ayah_translations table has correct column names
            if ($this->schema()->hasTable('ayah_translations')) {
                // Check if ayah_id column exists, if not rename from verse_id
                $columns = $this->connection->query("SHOW COLUMNS FROM ayah_translations LIKE 'verse_id'")->fetchAll();
                if (!empty($columns)) {
                    $this->connection->exec("ALTER TABLE ayah_translations CHANGE COLUMN verse_id ayah_id BIGINT(20) UNSIGNED NOT NULL");
                    error_log("[Migration] Renamed 'verse_id' column to 'ayah_id' in ayah_translations table");
                }
            }
        }

        public function down(): void
        {
            error_log("[Migration] 0013_fix_quran_table_names down() called");

            // Revert to verse* naming convention if needed
            if ($this->schema()->hasTable('ayahs')) {
                $this->schema()->rename('ayahs', 'verses');
                error_log("[Migration] Reverted 'ayahs' table back to 'verses'");
            }
            
            if ($this->schema()->hasTable('ayah_translations')) {
                $this->schema()->rename('ayah_translations', 'verse_translations');
                error_log("[Migration] Reverted 'ayah_translations' table back to 'verse_translations'");
            }
            
            // Revert column names if needed
            if ($this->schema()->hasTable('verses')) {
                $columns = $this->connection->query("SHOW COLUMNS FROM verses LIKE 'ayah_number'")->fetchAll();
                if (!empty($columns)) {
                    $this->connection->exec("ALTER TABLE verses CHANGE COLUMN ayah_number verse_number INT(10) UNSIGNED NOT NULL");
                    error_log("[Migration] Reverted 'ayah_number' column back to 'verse_number' in verses table");
                }
            }
            
            if ($this->schema()->hasTable('verse_translations')) {
                $columns = $this->connection->query("SHOW COLUMNS FROM verse_translations LIKE 'ayah_id'")->fetchAll();
                if (!empty($columns)) {
                    $this->connection->exec("ALTER TABLE verse_translations CHANGE COLUMN ayah_id verse_id BIGINT(20) UNSIGNED NOT NULL");
                    error_log("[Migration] Reverted 'ayah_id' column back to 'verse_id' in verse_translations table");
                }
            }
        }
    };
};
