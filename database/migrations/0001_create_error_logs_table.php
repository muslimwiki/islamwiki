<?php

declare(strict_types=1);

/**
 * Create Error Logs Table Migration
 * 
 * This migration creates the database table structure for the Shahid
 * error logging system, allowing comprehensive error tracking and analysis.
 * 
 * @package IslamWiki\Database\Migrations
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class CreateErrorLogsTable
{
    private $database;
    private $schema;

    public function __construct($database)
    {
        $this->database = $database;
        $this->schema = $database->getSchema();
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        // Main error logs table
        $this->schema->create('mizan_error_logs', function ($table) {
            $table->id();
            $table->string('error_id', 50)->unique(); // Unique error identifier
            $table->string('level', 20)->index(); // error, warning, info, etc.
            $table->string('category', 50)->index(); // authentication, database, etc.
            $table->string('type', 100)->index(); // Exception class name
            $table->text('message'); // Error message
            $table->text('stack_trace')->nullable(); // Stack trace
            $table->string('file', 500)->nullable(); // File where error occurred
            $table->integer('line')->nullable(); // Line number
            $table->integer('code')->nullable(); // Error code
            
            // Request information
            $table->string('request_url', 1000)->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('client_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 100)->nullable();
            
            // User information
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('username', 100)->nullable();
            
            // Server information
            $table->string('server_hostname', 255)->nullable();
            $table->string('php_version', 20)->nullable();
            $table->string('memory_usage', 20)->nullable();
            $table->string('memory_limit', 20)->nullable();
            
            // Performance metrics
            $table->integer('execution_time')->nullable(); // milliseconds
            $table->string('peak_memory', 20)->nullable();
            
            // Context and additional data
            $table->json('context_data')->nullable(); // Additional context
            $table->json('server_data')->nullable(); // Server variables
            $table->json('request_data')->nullable(); // Request data
            
            // Metadata
            $table->string('environment', 20)->default('production');
            $table->string('version', 20)->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by', 100)->nullable();
            $table->text('resolution_notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->index(['created_at', 'level']);
            $table->index(['category', 'level']);
            $table->index(['user_id', 'created_at']);
            $table->index(['is_resolved', 'created_at']);
        });

        // Error patterns table for analysis
        $this->schema->create('mizan_error_patterns', function ($table) {
            $table->id();
            $table->string('pattern_hash', 64)->unique(); // Hash of error pattern
            $table->string('category', 50)->index();
            $table->string('type', 100)->index();
            $table->text('message_pattern'); // Pattern to match similar errors
            $table->string('file_pattern', 500)->nullable();
            $table->integer('occurrence_count')->default(1);
            $table->timestamp('first_occurrence');
            $table->timestamp('last_occurrence');
            $table->json('sample_context')->nullable(); // Sample context data
            $table->json('sample_stack_trace')->nullable(); // Sample stack trace
            
            // Pattern analysis
            $table->string('severity_level', 20)->default('error');
            $table->boolean('is_analyzed')->default(false);
            $table->json('analysis_data')->nullable();
            
            $table->timestamps();
            $table->index(['category', 'severity_level']);
            $table->index(['occurrence_count', 'last_occurrence']);
        });

        // Error trends table for monitoring
        $this->schema->create('mizan_error_trends', function ($table) {
            $table->id();
            $table->string('period', 20)->index(); // hourly, daily, weekly, monthly
            $table->date('period_date')->index();
            $table->string('category', 50)->index();
            $table->string('level', 20)->index();
            $table->integer('error_count')->default(0);
            $table->integer('unique_users')->default(0);
            $table->integer('unique_ips')->default(0);
            $table->float('avg_execution_time')->nullable();
            $table->string('peak_memory_usage', 20)->nullable();
            
            // Trend indicators
            $table->float('trend_percentage')->nullable(); // Change from previous period
            $table->string('trend_direction', 10)->nullable(); // increasing, decreasing, stable
            
            $table->timestamps();
            $table->unique(['period', 'period_date', 'category', 'level']);
        });

        // Error correlations table
        $this->schema->create('mizan_error_correlations', function ($table) {
            $table->id();
            $table->unsignedBigInteger('primary_error_id');
            $table->unsignedBigInteger('correlated_error_id');
            $table->string('correlation_type', 50); // temporal, user, ip, etc.
            $table->float('correlation_strength'); // 0.0 to 1.0
            $table->json('correlation_data')->nullable();
            $table->timestamp('first_correlation');
            $table->timestamp('last_correlation');
            $table->integer('correlation_count')->default(1);
            
            $table->timestamps();
            $table->unique(['primary_error_id', 'correlated_error_id', 'correlation_type']);
            $table->index(['correlation_type', 'correlation_strength']);
        });

        // Error notifications table
        $this->schema->create('mizan_error_notifications', function ($table) {
            $table->id();
            $table->string('notification_type', 50)->index(); // email, slack, discord
            $table->string('recipient', 255)->index();
            $table->string('subject', 500);
            $table->text('message');
            $table->json('error_data')->nullable();
            $table->string('status', 20)->default('pending'); // pending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry')->nullable();
            
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['notification_type', 'status']);
        });

        // Error cleanup log table
        $this->schema->create('mizan_error_cleanup_logs', function ($table) {
            $table->id();
            $table->string('cleanup_type', 50)->index(); // file_logs, database_logs, temp_files
            $table->integer('records_processed')->default(0);
            $table->integer('records_deleted')->default(0);
            $table->string('cleanup_criteria', 255); // What was cleaned up
            $table->timestamp('cleanup_started');
            $table->timestamp('cleanup_completed')->nullable();
            $table->integer('execution_time')->nullable(); // milliseconds
            $table->string('status', 20)->default('running'); // running, completed, failed
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            $table->index(['cleanup_type', 'status']);
            $table->index(['cleanup_started', 'status']);
        });

        // Insert default error categories
        $this->insertDefaultErrorCategories();
        
        // Insert default error levels
        $this->insertDefaultErrorLevels();
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->dropIfExists('mizan_error_cleanup_logs');
        $this->schema->dropIfExists('mizan_error_notifications');
        $this->schema->dropIfExists('mizan_error_correlations');
        $this->schema->dropIfExists('mizan_error_trends');
        $this->schema->dropIfExists('mizan_error_patterns');
        $this->schema->dropIfExists('mizan_error_logs');
    }

    /**
     * Insert default error categories
     */
    private function insertDefaultErrorCategories(): void
    {
        $categories = [
            'authentication' => 'Authentication and authorization errors',
            'database' => 'Database connection and query errors',
            'file_system' => 'File system and storage errors',
            'network' => 'Network and connectivity errors',
            'validation' => 'Input validation and data format errors',
            'system' => 'System-level errors and resource issues',
            'security' => 'Security-related errors and violations',
            'performance' => 'Performance and timeout errors',
            'third_party' => 'Third-party service integration errors',
            'unknown' => 'Uncategorized errors',
        ];

        foreach ($categories as $name => $description) {
            // This would be inserted into a categories table if we had one
            // For now, we'll just create the structure
        }
    }

    /**
     * Insert default error levels
     */
    private function insertDefaultErrorLevels(): void
    {
        $levels = [
            'emergency' => 'System is unusable',
            'alert' => 'Action must be taken immediately',
            'critical' => 'Critical conditions',
            'error' => 'Error conditions',
            'warning' => 'Warning conditions',
            'notice' => 'Normal but significant',
            'info' => 'Informational messages',
            'debug' => 'Debug-level messages',
        ];

        foreach ($levels as $name => $description) {
            // This would be inserted into a levels table if we had one
            // For now, we'll just create the structure
        }
    }
} 