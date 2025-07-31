<?php
declare(strict_types=1);

/**
 * Migration: Configuration System Schema
 * 
 * This migration creates the database schema for the enhanced configuration system
 * including configuration storage, categories, audit logging, and backup functionality.
 * 
 * @package IslamWiki
 * @version 0.0.20
 * @license AGPL-3.0-only
 */

use IslamWiki\Core\Database\Migrations\Migration;

class Migration_0012_ConfigurationSchema extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // Configuration storage table
        $this->createTable('configuration', function ($table) {
            $table->id();
            $table->string('category', 50)->comment('Configuration category (core, database, security, etc.)');
            $table->string('key_name', 100)->comment('Configuration key name');
            $table->text('value')->nullable()->comment('Configuration value');
            $table->enum('type', ['string', 'integer', 'boolean', 'array', 'json'])->default('string')->comment('Data type of the configuration value');
            $table->text('description')->nullable()->comment('Description of the configuration option');
            $table->boolean('is_sensitive')->default(false)->comment('Whether this configuration contains sensitive data');
            $table->boolean('is_required')->default(false)->comment('Whether this configuration is required');
            $table->text('validation_rules')->nullable()->comment('JSON validation rules for this configuration');
            $table->timestamps();
            
            $table->unique(['category', 'key_name'], 'unique_config');
            $table->index('category');
            $table->index('key_name');
        });

        // Configuration categories table
        $this->createTable('configuration_categories', function ($table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Category name (core, database, security, etc.)');
            $table->string('display_name', 100)->comment('Human-readable display name');
            $table->text('description')->nullable()->comment('Category description');
            $table->string('icon', 50)->nullable()->comment('Icon for the category');
            $table->integer('sort_order')->default(0)->comment('Sort order for display');
            $table->boolean('is_active')->default(true)->comment('Whether this category is active');
            $table->timestamps();
            
            $table->index('sort_order');
            $table->index('is_active');
        });

        // Configuration audit log table
        $this->createTable('configuration_audit', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('User who made the change');
            $table->string('category', 50)->comment('Configuration category');
            $table->string('key_name', 100)->comment('Configuration key name');
            $table->text('old_value')->nullable()->comment('Previous configuration value');
            $table->text('new_value')->nullable()->comment('New configuration value');
            $table->enum('change_type', ['create', 'update', 'delete'])->comment('Type of change made');
            $table->string('ip_address', 45)->nullable()->comment('IP address of the user');
            $table->text('user_agent')->nullable()->comment('User agent string');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index(['category', 'key_name']);
            $table->index('created_at');
            $table->index('change_type');
        });

        // Configuration backups table
        $this->createTable('configuration_backups', function ($table) {
            $table->id();
            $table->string('backup_name', 100)->comment('Name of the backup');
            $table->json('configuration_data')->comment('JSON data of the configuration backup');
            $table->unsignedBigInteger('created_by')->nullable()->comment('User who created the backup');
            $table->text('description')->nullable()->comment('Description of the backup');
            $table->timestamps();
            
            $table->index('created_at');
            $table->index('created_by');
        });

        // Insert default configuration categories
        $this->insertDefaultCategories();
        
        // Insert default configuration values
        $this->insertDefaultConfiguration();
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        $this->dropTable('configuration_backups');
        $this->dropTable('configuration_audit');
        $this->dropTable('configuration_categories');
        $this->dropTable('configuration');
    }

    /**
     * Insert default configuration categories.
     */
    private function insertDefaultCategories(): void
    {
        $categories = [
            [
                'name' => 'core',
                'display_name' => 'Core Settings',
                'description' => 'Basic application configuration settings',
                'icon' => 'settings',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'database',
                'display_name' => 'Database Settings',
                'description' => 'Database connection and optimization settings',
                'icon' => 'database',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'security',
                'display_name' => 'Security Settings',
                'description' => 'Security and authentication configuration',
                'icon' => 'shield',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'islamic',
                'display_name' => 'Islamic Settings',
                'description' => 'Islamic-specific configuration options',
                'icon' => 'mosque',
                'sort_order' => 4,
                'is_active' => true
            ],
            [
                'name' => 'extensions',
                'display_name' => 'Extension Settings',
                'description' => 'Extension-specific configuration management',
                'icon' => 'puzzle',
                'sort_order' => 5,
                'is_active' => true
            ],
            [
                'name' => 'performance',
                'display_name' => 'Performance Settings',
                'description' => 'Caching and performance optimization settings',
                'icon' => 'speed',
                'sort_order' => 6,
                'is_active' => true
            ],
            [
                'name' => 'logging',
                'display_name' => 'Logging Settings',
                'description' => 'Logging and debugging configuration',
                'icon' => 'log',
                'sort_order' => 7,
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            $this->insert('configuration_categories', $category);
        }
    }

    /**
     * Insert default configuration values.
     */
    private function insertDefaultConfiguration(): void
    {
        $configurations = [
            // Core Settings
            [
                'category' => 'core',
                'key_name' => 'site_name',
                'value' => 'IslamWiki',
                'type' => 'string',
                'description' => 'Name of the website',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'min:1', 'max:100'])
            ],
            [
                'category' => 'core',
                'key_name' => 'site_description',
                'value' => 'A modern Islamic wiki system',
                'type' => 'string',
                'description' => 'Description of the website',
                'is_sensitive' => false,
                'is_required' => false,
                'validation_rules' => json_encode(['max:500'])
            ],
            [
                'category' => 'core',
                'key_name' => 'default_language',
                'value' => 'en',
                'type' => 'string',
                'description' => 'Default language for the site',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'in:en,ar,ur,tr'])
            ],
            [
                'category' => 'core',
                'key_name' => 'timezone',
                'value' => 'UTC',
                'type' => 'string',
                'description' => 'Default timezone for the site',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required'])
            ],

            // Database Settings
            [
                'category' => 'database',
                'key_name' => 'connection',
                'value' => 'mysql',
                'type' => 'string',
                'description' => 'Database connection type',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'in:mysql,pgsql,sqlite'])
            ],
            [
                'category' => 'database',
                'key_name' => 'host',
                'value' => '127.0.0.1',
                'type' => 'string',
                'description' => 'Database host address',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required'])
            ],
            [
                'category' => 'database',
                'key_name' => 'database',
                'value' => 'islamwiki',
                'type' => 'string',
                'description' => 'Database name',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required'])
            ],

            // Security Settings
            [
                'category' => 'security',
                'key_name' => 'session_lifetime',
                'value' => '7200',
                'type' => 'integer',
                'description' => 'Session lifetime in seconds',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:300', 'max:86400'])
            ],
            [
                'category' => 'security',
                'key_name' => 'csrf_protection',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable CSRF protection',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'boolean'])
            ],
            [
                'category' => 'security',
                'key_name' => 'rate_limiting',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable rate limiting',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'boolean'])
            ],

            // Islamic Settings
            [
                'category' => 'islamic',
                'key_name' => 'default_prayer_method',
                'value' => 'MWL',
                'type' => 'string',
                'description' => 'Default prayer time calculation method',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'in:MWL,ISNA,EGYPT,MAKKAH,KARACHI,TEHRAN,JAFARI'])
            ],
            [
                'category' => 'islamic',
                'key_name' => 'enable_quran_integration',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable Quran integration features',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'boolean'])
            ],
            [
                'category' => 'islamic',
                'key_name' => 'enable_hadith_integration',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable Hadith integration features',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'boolean'])
            ],

            // Extension Settings
            [
                'category' => 'extensions',
                'key_name' => 'enable_enhanced_markdown',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable Enhanced Markdown extension',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'boolean'])
            ],
            [
                'category' => 'extensions',
                'key_name' => 'enable_git_integration',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable Git Integration extension',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'boolean'])
            ],

            // Performance Settings
            [
                'category' => 'performance',
                'key_name' => 'enable_caching',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable application caching',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'boolean'])
            ],
            [
                'category' => 'performance',
                'key_name' => 'cache_lifetime',
                'value' => '3600',
                'type' => 'integer',
                'description' => 'Cache lifetime in seconds',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'integer', 'min:60', 'max:86400'])
            ],

            // Logging Settings
            [
                'category' => 'logging',
                'key_name' => 'log_level',
                'value' => 'info',
                'type' => 'string',
                'description' => 'Logging level',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'in:debug,info,warning,error,critical'])
            ],
            [
                'category' => 'logging',
                'key_name' => 'enable_debug_logging',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable debug logging',
                'is_sensitive' => false,
                'is_required' => true,
                'validation_rules' => json_encode(['required', 'boolean'])
            ]
        ];

        foreach ($configurations as $config) {
            $this->insert('configuration', $config);
        }
    }
} 