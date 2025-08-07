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
            error_log("[Migration] 0005_islamic_user_fields up() called");

            // Add Islamic user fields to users table using direct SQL
            $sql = "
                ALTER TABLE users 
                ADD COLUMN scholar_id BIGINT UNSIGNED NULL,
                ADD COLUMN islamic_role VARCHAR(50) NOT NULL DEFAULT 'user',
                ADD COLUMN qualification_level VARCHAR(50) NULL,
                ADD COLUMN madhab VARCHAR(50) NULL,
                ADD COLUMN specialization VARCHAR(100) NULL,
                ADD COLUMN verification_status VARCHAR(20) NOT NULL DEFAULT 'pending',
                ADD COLUMN verified_by BIGINT UNSIGNED NULL,
                ADD COLUMN verified_at TIMESTAMP NULL,
                ADD COLUMN verification_notes TEXT NULL,
                ADD COLUMN islamic_bio TEXT NULL,
                ADD COLUMN arabic_name VARCHAR(100) NULL,
                ADD COLUMN kunyah VARCHAR(50) NULL,
                ADD COLUMN laqab VARCHAR(50) NULL,
                ADD COLUMN nasab VARCHAR(100) NULL,
                ADD COLUMN birth_place VARCHAR(100) NULL,
                ADD COLUMN birth_year INT UNSIGNED NULL,
                ADD COLUMN death_year INT UNSIGNED NULL,
                ADD COLUMN era VARCHAR(50) NULL,
                ADD COLUMN is_sahabi BOOLEAN NOT NULL DEFAULT 0,
                ADD COLUMN is_scholar BOOLEAN NOT NULL DEFAULT 0,
                ADD COLUMN is_verified_scholar BOOLEAN NOT NULL DEFAULT 0,
                ADD COLUMN islamic_credentials JSON NULL,
                ADD COLUMN islamic_works JSON NULL,
                ADD COLUMN islamic_contributions JSON NULL
            ";

            $this->connection->statement($sql);

            // Add indexes
            $indexes = [
                "CREATE INDEX users_islamic_role_is_active_index ON users (islamic_role, is_active)",
                "CREATE INDEX users_verification_status_is_active_index ON users (verification_status, is_active)",
                "CREATE INDEX users_is_scholar_is_verified_scholar_index ON users (is_scholar, is_verified_scholar)",
                "CREATE INDEX users_scholar_id_index ON users (scholar_id)",
                "CREATE INDEX users_madhab_index ON users (madhab)",
                "CREATE INDEX users_specialization_index ON users (specialization)"
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
                "DROP INDEX IF EXISTS users_islamic_role_is_active_index ON users",
                "DROP INDEX IF EXISTS users_verification_status_is_active_index ON users",
                "DROP INDEX IF EXISTS users_is_scholar_is_verified_scholar_index ON users",
                "DROP INDEX IF EXISTS users_scholar_id_index ON users",
                "DROP INDEX IF EXISTS users_madhab_index ON users",
                "DROP INDEX IF EXISTS users_specialization_index ON users"
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
                ALTER TABLE users 
                DROP COLUMN IF EXISTS scholar_id,
                DROP COLUMN IF EXISTS islamic_role,
                DROP COLUMN IF EXISTS qualification_level,
                DROP COLUMN IF EXISTS madhab,
                DROP COLUMN IF EXISTS specialization,
                DROP COLUMN IF EXISTS verification_status,
                DROP COLUMN IF EXISTS verified_by,
                DROP COLUMN IF EXISTS verified_at,
                DROP COLUMN IF EXISTS verification_notes,
                DROP COLUMN IF EXISTS islamic_bio,
                DROP COLUMN IF EXISTS arabic_name,
                DROP COLUMN IF EXISTS kunyah,
                DROP COLUMN IF EXISTS laqab,
                DROP COLUMN IF EXISTS nasab,
                DROP COLUMN IF EXISTS birth_place,
                DROP COLUMN IF EXISTS birth_year,
                DROP COLUMN IF EXISTS death_year,
                DROP COLUMN IF EXISTS era,
                DROP COLUMN IF EXISTS is_sahabi,
                DROP COLUMN IF EXISTS is_scholar,
                DROP COLUMN IF EXISTS is_verified_scholar,
                DROP COLUMN IF EXISTS islamic_credentials,
                DROP COLUMN IF EXISTS islamic_works,
                DROP COLUMN IF EXISTS islamic_contributions
            ";

            $this->connection->statement($sql);
        }
    };
};
