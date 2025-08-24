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

use IslamWiki\Core\Database\Migration;

/**
 * Islamic Architecture Restructure Migration
 *
 * This migration restructures the database to align with the new Islamic
 * architecture system, implementing the 16 core Islamic systems with
 * proper naming conventions and relationships.
 *
 * @category  Database
 * @package   IslamWiki\Database\Migrations
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class IslamicArchitectureRestructure extends Migration
{
    /**
     * Migration version.
     */
    public const VERSION = '0.0.1.1';

    /**
     * Migration description.
     */
    public const DESCRIPTION = 'Islamic Architecture Restructure - Implementing 16 core Islamic systems';

    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        $this->createContainerLayer();
        $this->createInfrastructureLayer();
        $this->createApplicationLayer();
        $this->createUserInterfaceLayer();
        $this->createIslamicSystemRelationships();
        $this->migrateExistingData();
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        $this->dropIslamicSystemRelationships();
        $this->dropUserInterfaceLayer();
        $this->dropApplicationLayer();
        $this->dropInfrastructureLayer();
        $this->dropContainerLayer();
    }

    /**
     * Create Container Layer (أساس) tables.
     *
     * @return void
     */
    protected function createContainerLayer(): void
    {
        // Container Container Services
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS asas_foundation (
                id INT AUTO_INCREMENT PRIMARY KEY,
                service_name VARCHAR(100) NOT NULL UNIQUE,
                service_class VARCHAR(255) NOT NULL,
                service_description TEXT,
                service_status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
                service_version VARCHAR(20) DEFAULT '0.0.1',
                service_dependencies JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_service_name (service_name),
                INDEX idx_service_status (service_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Container Utilities
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS asas_utilities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                utility_name VARCHAR(100) NOT NULL UNIQUE,
                utility_type ENUM('helper', 'formatter', 'validator', 'converter') NOT NULL,
                utility_class VARCHAR(255) NOT NULL,
                utility_description TEXT,
                utility_config JSON,
                is_enabled BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_utility_name (utility_name),
                INDEX idx_utility_type (utility_type),
                INDEX idx_is_enabled (is_enabled)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insert default foundation services
        $this->db->exec("
            INSERT INTO asas_foundation (service_name, service_class, service_description) VALUES
            ('container', 'IslamWiki\\Core\\Container\\Container 'Dependency injection container'),
            ('foundation', 'IslamWiki\\Core\\Container\\Container 'Core foundation services'),
            ('bootstrap', 'IslamWiki\\Core\\Container\\Container 'Application bootstrap system')
            ON DUPLICATE KEY UPDATE service_class = VALUES(service_class)
        ");
    }

    /**
     * Create Infrastructure Layer tables.
     *
     * @return void
     */
    protected function createInfrastructureLayer(): void
    {
        // Sabil Routes (سبيل - Path)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS sabil_routes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                route_name VARCHAR(100) NOT NULL UNIQUE,
                route_path VARCHAR(255) NOT NULL,
                route_method ENUM('GET', 'POST', 'PUT', 'DELETE', 'PATCH') NOT NULL,
                route_group VARCHAR(50) NOT NULL,
                route_handler VARCHAR(255) NOT NULL,
                route_middleware JSON,
                route_options JSON,
                is_active BOOLEAN DEFAULT TRUE,
                is_cached BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_route_path (route_path),
                INDEX idx_route_group (route_group),
                INDEX idx_route_method (route_method),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Application Systems (نظام - Order)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS nizam_systems (
                id INT AUTO_INCREMENT PRIMARY KEY,
                system_name VARCHAR(100) NOT NULL UNIQUE,
                system_type ENUM('core', 'extension', 'module', 'service') NOT NULL,
                system_class VARCHAR(255) NOT NULL,
                system_description TEXT,
                system_dependencies JSON,
                system_config JSON,
                system_status ENUM('active', 'inactive', 'maintenance', 'deprecated') DEFAULT 'active',
                system_version VARCHAR(20) DEFAULT '0.0.1',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_system_name (system_name),
                INDEX idx_system_type (system_type),
                INDEX idx_system_status (system_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Database Metrics (ميزان - Balance)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS mizan_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_name VARCHAR(100) NOT NULL,
                metric_value DECIMAL(15,4) NOT NULL,
                metric_unit VARCHAR(20),
                metric_category VARCHAR(50) NOT NULL,
                metric_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                metric_metadata JSON,
                INDEX idx_metric_name (metric_name),
                INDEX idx_metric_category (metric_category),
                INDEX idx_metric_timestamp (metric_timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Configuration Configuration (تدبير - Management)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tadbir_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_key VARCHAR(100) NOT NULL UNIQUE,
                config_value TEXT,
                config_type ENUM('string', 'integer', 'boolean', 'json', 'array') DEFAULT 'string',
                config_category VARCHAR(50) NOT NULL,
                config_description TEXT,
                config_default TEXT,
                config_validation JSON,
                is_encrypted BOOLEAN DEFAULT FALSE,
                is_public BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_config_key (config_key),
                INDEX idx_config_category (config_category),
                INDEX idx_is_public (is_public)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Create Application Layer tables.
     *
     * @return void
     */
    protected function createApplicationLayer(): void
    {
        // Security Security (أمان - Security)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS aman_security (
                id INT AUTO_INCREMENT PRIMARY KEY,
                security_policy VARCHAR(100) NOT NULL UNIQUE,
                policy_type ENUM('authentication', 'authorization', 'validation', 'encryption') NOT NULL,
                policy_config JSON NOT NULL,
                policy_rules JSON,
                policy_status ENUM('active', 'inactive', 'testing') DEFAULT 'active',
                policy_priority INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_policy_type (policy_type),
                INDEX idx_policy_status (policy_status),
                INDEX idx_policy_priority (policy_priority)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Session Sessions (وصل - Connection)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS wisal_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(128) NOT NULL UNIQUE,
                user_id INT,
                session_data JSON,
                session_metadata JSON,
                ip_address VARCHAR(45),
                user_agent TEXT,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_session_id (session_id),
                INDEX idx_user_id (user_id),
                INDEX idx_last_activity (last_activity),
                INDEX idx_expires_at (expires_at),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Queue Queues (صبر - Patience)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS sabr_queues (
                id INT AUTO_INCREMENT PRIMARY KEY,
                queue_name VARCHAR(100) NOT NULL,
                job_data JSON NOT NULL,
                job_type VARCHAR(100) NOT NULL,
                job_priority INT DEFAULT 0,
                job_status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
                attempts INT DEFAULT 0,
                max_attempts INT DEFAULT 3,
                error_message TEXT,
                scheduled_at TIMESTAMP NULL,
                started_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_queue_name (queue_name),
                INDEX idx_job_status (job_status),
                INDEX idx_job_priority (job_priority),
                INDEX idx_scheduled_at (scheduled_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Knowledge Rules (أصول - Principles)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS usul_rules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rule_name VARCHAR(100) NOT NULL UNIQUE,
                rule_category VARCHAR(50) NOT NULL,
                rule_type ENUM('validation', 'business', 'security', 'islamic') NOT NULL,
                rule_definition JSON NOT NULL,
                rule_conditions JSON,
                rule_actions JSON,
                rule_priority INT DEFAULT 0,
                rule_status ENUM('active', 'inactive', 'testing') DEFAULT 'active',
                rule_description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_rule_name (rule_name),
                INDEX idx_rule_category (rule_category),
                INDEX idx_rule_type (rule_type),
                INDEX idx_rule_status (rule_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Create User Interface Layer tables.
     *
     * @return void
     */
    protected function createUserInterfaceLayer(): void
    {
        // Iqra Search (إقرأ - Read)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS iqra_search (
                id INT AUTO_INCREMENT PRIMARY KEY,
                search_query VARCHAR(255) NOT NULL,
                search_type ENUM('content', 'user', 'file', 'islamic') NOT NULL,
                search_filters JSON,
                search_results JSON,
                result_count INT DEFAULT 0,
                search_time_ms INT DEFAULT 0,
                user_id INT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_search_query (search_query),
                INDEX idx_search_type (search_type),
                INDEX idx_user_id (user_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Bayan Content (بيان - Explanation)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS bayan_content (
                id INT AUTO_INCREMENT PRIMARY KEY,
                content_type VARCHAR(50) NOT NULL,
                content_title VARCHAR(255) NOT NULL,
                content_body LONGTEXT,
                content_format ENUM('markdown', 'html', 'text', 'islamic') DEFAULT 'markdown',
                content_metadata JSON,
                content_tags JSON,
                content_status ENUM('draft', 'published', 'archived', 'deleted') DEFAULT 'draft',
                author_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                published_at TIMESTAMP NULL,
                INDEX idx_content_type (content_type),
                INDEX idx_content_status (content_status),
                INDEX idx_author_id (author_id),
                INDEX idx_published_at (published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // API API (سراج - Light)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS siraj_api (
                id INT AUTO_INCREMENT PRIMARY KEY,
                endpoint_name VARCHAR(100) NOT NULL UNIQUE,
                endpoint_path VARCHAR(255) NOT NULL,
                endpoint_method ENUM('GET', 'POST', 'PUT', 'DELETE') NOT NULL,
                endpoint_description TEXT,
                endpoint_parameters JSON,
                endpoint_response JSON,
                endpoint_rate_limit INT DEFAULT 1000,
                endpoint_status ENUM('active', 'inactive', 'deprecated') DEFAULT 'active',
                endpoint_version VARCHAR(20) DEFAULT '1.0',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_endpoint_path (endpoint_path),
                INDEX idx_endpoint_method (endpoint_method),
                INDEX idx_endpoint_status (endpoint_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Routing Cache (رحلة - Journey)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rihlah_cache (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cache_key VARCHAR(255) NOT NULL UNIQUE,
                cache_value LONGTEXT,
                cache_type VARCHAR(50) NOT NULL,
                cache_tags JSON,
                cache_ttl INT DEFAULT 3600,
                cache_size INT DEFAULT 0,
                cache_hits INT DEFAULT 0,
                cache_misses INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                INDEX idx_cache_key (cache_key),
                INDEX idx_cache_type (cache_type),
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Create Islamic system relationships.
     *
     * @return void
     */
    protected function createIslamicSystemRelationships(): void
    {
        // System Dependencies
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS islamic_system_dependencies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                dependent_system VARCHAR(100) NOT NULL,
                dependency_system VARCHAR(100) NOT NULL,
                dependency_type ENUM('required', 'optional', 'conflicts') DEFAULT 'required',
                dependency_version VARCHAR(20),
                dependency_description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_dependency (dependent_system, dependency_system),
                INDEX idx_dependent_system (dependent_system),
                INDEX idx_dependency_system (dependency_system)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // System Events
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS islamic_system_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_name VARCHAR(100) NOT NULL,
                event_system VARCHAR(100) NOT NULL,
                event_type ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
                event_message TEXT,
                event_data JSON,
                event_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_event_system (event_system),
                INDEX idx_event_type (event_type),
                INDEX idx_event_timestamp (event_timestamp)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Migrate existing data to new structure.
     *
     * @return void
     */
    protected function migrateExistingData(): void
    {
        // Migrate existing configuration to Configuration
        $this->db->exec("
            INSERT INTO tadbir_config (config_key, config_value, config_type, config_category, config_description)
            SELECT 
                'site_name' as config_key,
                'IslamWiki' as config_value,
                'string' as config_type,
                'core' as config_category,
                'Site name configuration' as config_description
            FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM tadbir_config WHERE config_key = 'site_name')
        ");

        // Migrate existing routes to Sabil
        $this->db->exec("
            INSERT INTO sabil_routes (route_name, route_path, route_method, route_group, route_handler)
            VALUES 
            ('home', '/', 'GET', 'asas', 'HomeController@index'),
            ('dashboard', '/dashboard', 'GET', 'aman', 'DashboardController@index'),
            ('wiki', '/wiki', 'GET', 'bayan', 'PageController@index')
            ON DUPLICATE KEY UPDATE route_handler = VALUES(route_handler)
        ");

        // Insert default Islamic system rules
        $this->db->exec("
            INSERT INTO usul_rules (rule_name, rule_category, rule_type, rule_definition, rule_description)
            VALUES 
            ('islamic_content_validation', 'content', 'islamic', '{\"validate_source\": true, \"check_authenticity\": true}', 'Validate Islamic content authenticity'),
            ('user_permission_check', 'security', 'business', '{\"check_role\": true, \"verify_permissions\": true}', 'Check user permissions before access'),
            ('content_moderation', 'content', 'business', '{\"moderate_new_content\": true, \"review_changes\": true}', 'Moderate new content and changes')
            ON DUPLICATE KEY UPDATE rule_definition = VALUES(rule_definition)
        ");
    }

    /**
     * Drop Islamic system relationships.
     *
     * @return void
     */
    protected function dropIslamicSystemRelationships(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS islamic_system_dependencies");
        $this->db->exec("DROP TABLE IF EXISTS islamic_system_events");
    }

    /**
     * Drop User Interface Layer tables.
     *
     * @return void
     */
    protected function dropUserInterfaceLayer(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS rihlah_cache");
        $this->db->exec("DROP TABLE IF EXISTS siraj_api");
        $this->db->exec("DROP TABLE IF EXISTS bayan_content");
        $this->db->exec("DROP TABLE IF EXISTS iqra_search");
    }

    /**
     * Drop Application Layer tables.
     *
     * @return void
     */
    protected function dropApplicationLayer(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS usul_rules");
        $this->db->exec("DROP TABLE IF EXISTS sabr_queues");
        $this->db->exec("DROP TABLE IF EXISTS wisal_sessions");
        $this->db->exec("DROP TABLE IF EXISTS aman_security");
    }

    /**
     * Drop Infrastructure Layer tables.
     *
     * @return void
     */
    protected function dropInfrastructureLayer(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS tadbir_config");
        $this->db->exec("DROP TABLE IF EXISTS mizan_metrics");
        $this->db->exec("DROP TABLE IF EXISTS nizam_systems");
        $this->db->exec("DROP TABLE IF EXISTS sabil_routes");
    }

    /**
     * Drop Container Layer tables.
     *
     * @return void
     */
    protected function dropContainerLayer(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS asas_utilities");
        $this->db->exec("DROP TABLE IF EXISTS asas_foundation");
    }
} 