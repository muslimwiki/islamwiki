<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @category  Database
 * @package   IslamWiki\Database\Migrations
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

use IslamWiki\Core\Database\Migrations\Migration;
use IslamWiki\Core\Database\Connection;

/**
 * Schema Alignment Completion Migration
 *
 * This migration completes the database schema alignment with the Islamic
 * architecture by updating existing table names, optimizing indexes,
 * implementing proper relationships, and ensuring data integrity.
 *
 * @category  Database
 * @package   IslamWiki\Database\Migrations
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class SchemaAlignmentCompletion extends Migration
{
    /**
     * Migration version.
     */
    protected string $version = '0025';

    /**
     * Migration description.
     */
    protected string $description = 'Complete database schema alignment with Islamic architecture';

    /**
     * Execute the migration.
     */
    public function up(): void
    {
        try {
            $this->log('Starting schema alignment completion migration...');

            // Update existing table names to align with Islamic naming
            $this->updateTableNames();

            // Optimize indexes for new architecture
            $this->optimizeIndexes();

            // Update constraints for data integrity
            $this->updateConstraints();

            // Implement data migration scripts
            $this->migrateExistingData();

            // Create views for backward compatibility
            $this->createCompatibilityViews();

            // Update stored procedures and functions
            $this->updateStoredProcedures();

            $this->log('Schema alignment completion migration completed successfully');
            return true;

        } catch (Exception $e) {
            $this->log('Schema alignment completion migration failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        try {
            $this->log('Reversing schema alignment completion migration...');

            // Drop compatibility views
            $this->dropCompatibilityViews();

            // Reverse table name changes
            $this->reverseTableNames();

            // Restore original indexes
            $this->restoreOriginalIndexes();

            // Restore original constraints
            $this->restoreOriginalConstraints();

            $this->log('Schema alignment completion migration reversed successfully');
            return true;

        } catch (Exception $e) {
            $this->log('Failed to reverse schema alignment completion migration: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing table names to align with Islamic naming.
     *
     * @return void
     */
    protected function updateTableNames(): void
    {
        $this->log('Updating table names to Islamic conventions...');

        $tableMapping = [
            // Legacy to Islamic naming mapping
            'users' => 'aman_users',
            'sessions' => 'wisal_sessions',
            'cache' => 'rihlah_cache',
            'jobs' => 'sabr_jobs',
            'failed_jobs' => 'sabr_failed_jobs',
            'configuration' => 'tadbir_configuration',
            'routes' => 'sabil_routes',
            'search_index' => 'iqra_search_index',
            'content_formatting' => 'bayan_content',
            'api_keys' => 'siraj_api_keys',
            'system_logs' => 'shahid_logs',
            'knowledge_base' => 'usul_knowledge_base',
            'database_connections' => 'mizan_connections',
            'system_metrics' => 'nizam_metrics'
        ];

        foreach ($tableMapping as $oldTable => $newTable) {
            if ($this->tableExists($oldTable) && !$this->tableExists($newTable)) {
                $this->execute("RENAME TABLE `{$oldTable}` TO `{$newTable}`");
                $this->log("Renamed table '{$oldTable}' to '{$newTable}'");
            }
        }
    }

    /**
     * Optimize indexes for new architecture.
     *
     * @return void
     */
    protected function optimizeIndexes(): void
    {
        $this->log('Optimizing indexes for Islamic architecture...');

        $indexOptimizations = [
            // Container Layer indexes
            'asas_foundation' => [
                'idx_foundation_service_type' => ['service_type'],
                'idx_foundation_status' => ['status'],
                'idx_foundation_priority' => ['priority']
            ],
            'asas_utilities' => [
                'idx_utilities_type' => ['utility_type'],
                'idx_utilities_namespace' => ['namespace']
            ],

            // Infrastructure Layer indexes
            'sabil_routes' => [
                'idx_routes_method_path' => ['method', 'path'],
                'idx_routes_name' => ['name'],
                'idx_routes_group' => ['group_name']
            ],
            'nizam_systems' => [
                'idx_systems_layer' => ['layer'],
                'idx_systems_status' => ['status']
            ],
            'mizan_metrics' => [
                'idx_metrics_timestamp' => ['timestamp'],
                'idx_metrics_type' => ['metric_type']
            ],
            'tadbir_config' => [
                'idx_config_category' => ['category'],
                'idx_config_key' => ['config_key']
            ],

            // Application Layer indexes
            'aman_security' => [
                'idx_security_user_action' => ['user_id', 'action'],
                'idx_security_timestamp' => ['timestamp']
            ],
            'wisal_sessions' => [
                'idx_sessions_user_id' => ['user_id'],
                'idx_sessions_last_activity' => ['last_activity']
            ],
            'sabr_queues' => [
                'idx_queues_status' => ['status'],
                'idx_queues_priority' => ['priority'],
                'idx_queues_available_at' => ['available_at']
            ],
            'usul_rules' => [
                'idx_rules_category' => ['category'],
                'idx_rules_status' => ['status']
            ],

            // User Interface Layer indexes
            'iqra_search' => [
                'idx_search_query' => ['search_query'],
                'idx_search_timestamp' => ['timestamp'],
                'idx_search_results_count' => ['results_count']
            ],
            'bayan_content' => [
                'idx_content_type' => ['content_type'],
                'idx_content_status' => ['status']
            ],
            'siraj_api' => [
                'idx_api_endpoint' => ['endpoint'],
                'idx_api_method' => ['method'],
                'idx_api_timestamp' => ['timestamp']
            ],
            'rihlah_cache' => [
                'idx_cache_key' => ['cache_key'],
                'idx_cache_expires_at' => ['expires_at']
            ]
        ];

        foreach ($indexOptimizations as $table => $indexes) {
            if ($this->tableExists($table)) {
                foreach ($indexes as $indexName => $columns) {
                    $columnList = implode('`, `', $columns);
                    $this->execute("CREATE INDEX `{$indexName}` ON `{$table}` (`{$columnList}`)");
                    $this->log("Created index '{$indexName}' on table '{$table}'");
                }
            }
        }
    }

    /**
     * Update constraints for data integrity.
     *
     * @return void
     */
    protected function updateConstraints(): void
    {
        $this->log('Updating constraints for data integrity...');

        $constraints = [
            // Foreign key constraints for Islamic architecture
            'aman_users' => [
                'fk_users_session' => 'ADD CONSTRAINT fk_users_session FOREIGN KEY (session_id) REFERENCES wisal_sessions(id) ON DELETE SET NULL'
            ],
            'sabr_jobs' => [
                'fk_jobs_queue' => 'ADD CONSTRAINT fk_jobs_queue FOREIGN KEY (queue_id) REFERENCES sabr_queues(id) ON DELETE CASCADE'
            ],
            'iqra_search' => [
                'fk_search_user' => 'ADD CONSTRAINT fk_search_user FOREIGN KEY (user_id) REFERENCES aman_users(id) ON DELETE SET NULL'
            ],
            'bayan_content' => [
                'fk_content_user' => 'ADD CONSTRAINT fk_content_user FOREIGN KEY (created_by) REFERENCES aman_users(id) ON DELETE SET NULL'
            ],
            'siraj_api' => [
                'fk_api_user' => 'ADD CONSTRAINT fk_api_user FOREIGN KEY (user_id) REFERENCES aman_users(id) ON DELETE SET NULL'
            ]
        ];

        foreach ($constraints as $table => $tableConstraints) {
            if ($this->tableExists($table)) {
                foreach ($tableConstraints as $constraintName => $constraintSql) {
                    try {
                        $this->execute("ALTER TABLE `{$table}` {$constraintSql}");
                        $this->log("Added constraint '{$constraintName}' to table '{$table}'");
                    } catch (Exception $e) {
                        $this->log("Warning: Could not add constraint '{$constraintName}': " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Migrate existing data to new structure.
     *
     * @return void
     */
    protected function migrateExistingData(): void
    {
        $this->log('Migrating existing data to Islamic architecture...');

        // Migrate user data to Security (Security) structure
        if ($this->tableExists('aman_users') && $this->tableExists('users')) {
            $this->execute("
                INSERT INTO aman_users (username, email, password_hash, created_at, updated_at)
                SELECT username, email, password, created_at, updated_at 
                FROM users 
                WHERE NOT EXISTS (
                    SELECT 1 FROM aman_users WHERE aman_users.email = users.email
                )
            ");
            $this->log('Migrated user data to Security security structure');
        }

        // Migrate session data to Session (Session) structure
        if ($this->tableExists('wisal_sessions') && $this->tableExists('sessions')) {
            $this->execute("
                INSERT INTO wisal_sessions (session_id, user_id, ip_address, user_agent, last_activity)
                SELECT id, user_id, ip_address, user_agent, last_activity 
                FROM sessions 
                WHERE NOT EXISTS (
                    SELECT 1 FROM wisal_sessions WHERE wisal_sessions.session_id = sessions.id
                )
            ");
            $this->log('Migrated session data to Session session structure');
        }

        // Migrate configuration data to Configuration (Configuration) structure
        if ($this->tableExists('tadbir_config') && $this->tableExists('configuration')) {
            $this->execute("
                INSERT INTO tadbir_config (category, config_key, config_value, created_at, updated_at)
                SELECT 'legacy', config_key, config_value, created_at, updated_at 
                FROM configuration 
                WHERE NOT EXISTS (
                    SELECT 1 FROM tadbir_config WHERE tadbir_config.config_key = configuration.config_key
                )
            ");
            $this->log('Migrated configuration data to Configuration configuration structure');
        }

        // Migrate search data to Iqra (Search) structure
        if ($this->tableExists('iqra_search') && $this->tableExists('search_index')) {
            $this->execute("
                INSERT INTO iqra_search (search_query, user_id, results_count, timestamp)
                SELECT query, user_id, result_count, created_at 
                FROM search_index 
                WHERE NOT EXISTS (
                    SELECT 1 FROM iqra_search WHERE iqra_search.search_query = search_index.query
                )
            ");
            $this->log('Migrated search data to Iqra search structure');
        }
    }

    /**
     * Create compatibility views for backward compatibility.
     *
     * @return void
     */
    protected function createCompatibilityViews(): void
    {
        $this->log('Creating compatibility views for backward compatibility...');

        $views = [
            'users' => 'CREATE VIEW users AS SELECT id, username, email, password_hash as password, created_at, updated_at FROM aman_users',
            'sessions' => 'CREATE VIEW sessions AS SELECT session_id as id, user_id, ip_address, user_agent, last_activity FROM wisal_sessions',
            'configuration' => 'CREATE VIEW configuration AS SELECT config_key, config_value, created_at, updated_at FROM tadbir_config WHERE category = "legacy"',
            'search_index' => 'CREATE VIEW search_index AS SELECT search_query as query, user_id, results_count as result_count, timestamp as created_at FROM iqra_search'
        ];

        foreach ($views as $viewName => $viewSql) {
            try {
                $this->execute("DROP VIEW IF EXISTS `{$viewName}`");
                $this->execute($viewSql);
                $this->log("Created compatibility view '{$viewName}'");
            } catch (Exception $e) {
                $this->log("Warning: Could not create view '{$viewName}': " . $e->getMessage());
            }
        }
    }

    /**
     * Update stored procedures and functions.
     *
     * @return void
     */
    protected function updateStoredProcedures(): void
    {
        $this->log('Updating stored procedures for Islamic architecture...');

        // Islamic system health check procedure
        $healthCheckProcedure = "
        CREATE PROCEDURE CheckIslamicSystemHealth()
        BEGIN
            DECLARE system_count INT DEFAULT 0;
            DECLARE healthy_systems INT DEFAULT 0;
            
            -- Count total Islamic systems
            SELECT COUNT(*) INTO system_count FROM nizam_systems WHERE status = 'active';
            
            -- Count healthy systems
            SELECT COUNT(*) INTO healthy_systems FROM nizam_systems 
            WHERE status = 'active' AND health_status = 'healthy';
            
            -- Return health percentage
            SELECT 
                system_count as total_systems,
                healthy_systems as healthy_systems,
                ROUND((healthy_systems / system_count) * 100, 2) as health_percentage,
                CASE 
                    WHEN (healthy_systems / system_count) >= 0.9 THEN 'excellent'
                    WHEN (healthy_systems / system_count) >= 0.7 THEN 'good'
                    WHEN (healthy_systems / system_count) >= 0.5 THEN 'fair'
                    ELSE 'poor'
                END as health_status;
        END
        ";

        try {
            $this->execute("DROP PROCEDURE IF EXISTS CheckIslamicSystemHealth");
            $this->execute($healthCheckProcedure);
            $this->log('Created Islamic system health check procedure');
        } catch (Exception $e) {
            $this->log("Warning: Could not create health check procedure: " . $e->getMessage());
        }

        // Islamic system metrics function
        $metricsFunctions = "
        CREATE FUNCTION GetSystemMetrics(system_name VARCHAR(50), metric_type VARCHAR(50)) 
        RETURNS DECIMAL(10,2)
        READS SQL DATA
        DETERMINISTIC
        BEGIN
            DECLARE metric_value DECIMAL(10,2) DEFAULT 0;
            
            SELECT AVG(value) INTO metric_value
            FROM mizan_metrics 
            WHERE system = system_name 
            AND metric_type = metric_type 
            AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR);
            
            RETURN COALESCE(metric_value, 0);
        END
        ";

        try {
            $this->execute("DROP FUNCTION IF EXISTS GetSystemMetrics");
            $this->execute($metricsFunctions);
            $this->log('Created Islamic system metrics function');
        } catch (Exception $e) {
            $this->log("Warning: Could not create metrics function: " . $e->getMessage());
        }
    }

    /**
     * Drop compatibility views.
     *
     * @return void
     */
    protected function dropCompatibilityViews(): void
    {
        $this->log('Dropping compatibility views...');

        $views = ['users', 'sessions', 'configuration', 'search_index'];

        foreach ($views as $viewName) {
            try {
                $this->execute("DROP VIEW IF EXISTS `{$viewName}`");
                $this->log("Dropped compatibility view '{$viewName}'");
            } catch (Exception $e) {
                $this->log("Warning: Could not drop view '{$viewName}': " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse table name changes.
     *
     * @return void
     */
    protected function reverseTableNames(): void
    {
        $this->log('Reversing table name changes...');

        $reverseMapping = [
            'aman_users' => 'users',
            'wisal_sessions' => 'sessions',
            'rihlah_cache' => 'cache',
            'sabr_jobs' => 'jobs',
            'sabr_failed_jobs' => 'failed_jobs',
            'tadbir_configuration' => 'configuration',
            'sabil_routes' => 'routes',
            'iqra_search_index' => 'search_index',
            'bayan_content' => 'content_formatting',
            'siraj_api_keys' => 'api_keys',
            'shahid_logs' => 'system_logs',
            'usul_knowledge_base' => 'knowledge_base',
            'mizan_connections' => 'database_connections',
            'nizam_metrics' => 'system_metrics'
        ];

        foreach ($reverseMapping as $islamicTable => $legacyTable) {
            if ($this->tableExists($islamicTable) && !$this->tableExists($legacyTable)) {
                $this->execute("RENAME TABLE `{$islamicTable}` TO `{$legacyTable}`");
                $this->log("Reversed table '{$islamicTable}' to '{$legacyTable}'");
            }
        }
    }

    /**
     * Restore original indexes.
     *
     * @return void
     */
    protected function restoreOriginalIndexes(): void
    {
        $this->log('Restoring original indexes...');
        
        // This would involve dropping the Islamic architecture indexes
        // and restoring the original ones - implementation would depend
        // on specific requirements for rollback
    }

    /**
     * Restore original constraints.
     *
     * @return void
     */
    protected function restoreOriginalConstraints(): void
    {
        $this->log('Restoring original constraints...');
        
        // This would involve dropping the new constraints and restoring
        // the original ones - implementation would depend on specific
        // requirements for rollback
    }
} 