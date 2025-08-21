<?php

declare(strict_types=1);

/**
 * Error Handling Configuration
 * 
 * Configuration for the Shahid error handling and logging system.
 * This file defines how errors are handled, logged, and displayed.
 * 
 * @package IslamWiki\Core\Configuration
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Error Display Settings
    |--------------------------------------------------------------------------
    |
    | These settings control whether errors are displayed to users
    | and how much detail is shown.
    |
    */
    'display_errors' => env('APP_DEBUG', false),
    
    /*
    |--------------------------------------------------------------------------
    | Error Logging Settings
    |--------------------------------------------------------------------------
    |
    | These settings control error logging behavior and destinations.
    |
    */
    'log_errors' => true,
    'log_level' => env('LOG_LEVEL', 'warning'),
    
    /*
    |--------------------------------------------------------------------------
    | Log File Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for file-based error logging.
    |
    */
    'log_file' => [
        'enabled' => true,
        'path' => storage_path('logs/errors.log'),
        'max_size' => '10MB',
        'max_files' => 30,
        'permissions' => 0644,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Database Logging Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for database-based error logging.
    |
    */
    'log_database' => [
        'enabled' => true,
        'table' => 'mizan_error_logs',
        'max_records' => 10000,
        'cleanup_older_than' => '30 days',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Error Page Templates
    |--------------------------------------------------------------------------
    |
    | Paths to error page templates for different HTTP status codes.
    |
    */
    'error_pages' => [
        400 => 'errors/400.twig',
        401 => 'errors/401.twig',
        403 => 'errors/403.twig',
        404 => 'errors/404.twig',
        500 => 'errors/500.twig',
        503 => 'errors/503.twig',
        'default' => 'errors/500.twig',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Error Reporting Settings
    |--------------------------------------------------------------------------
    |
    | PHP error reporting configuration.
    |
    */
    'error_reporting' => [
        'development' => E_ALL,
        'production' => E_ALL & ~E_DEPRECATED & ~E_STRICT,
        'testing' => E_ALL,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    |
    | Configuration for exception handling and reporting.
    |
    */
    'exceptions' => [
        'log_uncaught' => true,
        'log_handled' => true,
        'max_trace_depth' => 10,
        'include_context' => true,
        'include_server_vars' => false,
        'include_request_data' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for monitoring performance and slow operations.
    |
    */
    'performance' => [
        'enabled' => true,
        'slow_query_threshold' => 1000, // milliseconds
        'memory_threshold' => '128MB',
        'log_slow_operations' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related error handling configuration.
    |
    */
    'security' => [
        'log_failed_logins' => true,
        'log_suspicious_activity' => true,
        'log_file_access_attempts' => true,
        'log_sql_injection_attempts' => true,
        'log_xss_attempts' => true,
        'log_csrf_violations' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for error notifications and alerts.
    |
    */
    'notifications' => [
        'enabled' => false,
        'email' => [
            'enabled' => false,
            'recipients' => [],
            'subject_prefix' => '[IslamWiki Error]',
            'include_stack_trace' => false,
        ],
        'slack' => [
            'enabled' => false,
            'webhook_url' => null,
            'channel' => '#errors',
            'username' => 'Shahid Logger',
        ],
        'discord' => [
            'enabled' => false,
            'webhook_url' => null,
            'username' => 'Shahid Logger',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Error Categories
    |--------------------------------------------------------------------------
    |
    | Classification of different types of errors for better organization.
    |
    */
    'categories' => [
        'authentication' => [
            'failed_login',
            'invalid_token',
            'expired_session',
            'unauthorized_access',
        ],
        'database' => [
            'connection_failed',
            'query_failed',
            'transaction_failed',
            'constraint_violation',
        ],
        'file_system' => [
            'file_not_found',
            'permission_denied',
            'disk_full',
            'corrupted_file',
        ],
        'network' => [
            'timeout',
            'connection_refused',
            'dns_failure',
            'ssl_error',
        ],
        'validation' => [
            'invalid_input',
            'missing_required',
            'format_mismatch',
            'constraint_violation',
        ],
        'system' => [
            'memory_limit',
            'timeout',
            'resource_unavailable',
            'configuration_error',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Error Severity Levels
    |--------------------------------------------------------------------------
    |
    | Definition of error severity levels and their handling.
    |
    */
    'severity_levels' => [
        'emergency' => [
            'level' => 0,
            'description' => 'System is unusable',
            'action' => 'immediate_attention',
            'log_level' => 'emergency',
        ],
        'alert' => [
            'level' => 1,
            'description' => 'Action must be taken immediately',
            'action' => 'immediate_attention',
            'log_level' => 'alert',
        ],
        'critical' => [
            'level' => 2,
            'description' => 'Critical conditions',
            'action' => 'immediate_attention',
            'log_level' => 'critical',
        ],
        'error' => [
            'level' => 3,
            'description' => 'Error conditions',
            'action' => 'investigation_required',
            'log_level' => 'error',
        ],
        'warning' => [
            'level' => 4,
            'description' => 'Warning conditions',
            'action' => 'monitor',
            'log_level' => 'warning',
        ],
        'notice' => [
            'level' => 5,
            'description' => 'Normal but significant',
            'action' => 'log_only',
            'log_level' => 'notice',
        ],
        'info' => [
            'level' => 6,
            'description' => 'Informational messages',
            'action' => 'log_only',
            'log_level' => 'info',
        ],
        'debug' => [
            'level' => 7,
            'description' => 'Debug-level messages',
            'action' => 'log_only',
            'log_level' => 'debug',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Error Context Data
    |--------------------------------------------------------------------------
    |
    | Additional context data to include with error logs.
    |
    */
    'context_data' => [
        'user' => [
            'id' => true,
            'username' => true,
            'email' => false, // Privacy concern
            'ip_address' => true,
            'user_agent' => true,
            'session_id' => true,
        ],
        'request' => [
            'url' => true,
            'method' => true,
            'headers' => false, // Security concern
            'parameters' => false, // Security concern
            'body' => false, // Security concern
        ],
        'server' => [
            'hostname' => true,
            'software' => true,
            'php_version' => true,
            'memory_usage' => true,
            'load_average' => true,
        ],
        'environment' => [
            'app_env' => true,
            'app_debug' => true,
            'app_version' => true,
            'database_connection' => true,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Error Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic cleanup of old error logs.
    |
    */
    'cleanup' => [
        'enabled' => true,
        'schedule' => 'daily',
        'file_logs' => [
            'max_age' => '30 days',
            'max_size' => '100MB',
        ],
        'database_logs' => [
            'max_age' => '90 days',
            'max_records' => 50000,
        ],
        'temp_files' => [
            'max_age' => '7 days',
            'max_size' => '50MB',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Error Analysis Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for error analysis and reporting.
    |
    */
    'analysis' => [
        'enabled' => true,
        'patterns' => [
            'enabled' => true,
            'min_occurrences' => 3,
            'time_window' => '24 hours',
        ],
        'trends' => [
            'enabled' => true,
            'time_periods' => ['1 hour', '24 hours', '7 days', '30 days'],
        ],
        'correlations' => [
            'enabled' => true,
            'max_correlations' => 10,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to development environment.
    |
    */
    'development' => [
        'show_stack_trace' => true,
        'show_error_details' => true,
        'log_all_errors' => true,
        'debug_mode' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Production Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to production environment.
    |
    */
    'production' => [
        'show_stack_trace' => false,
        'show_error_details' => false,
        'log_all_errors' => true,
        'debug_mode' => false,
        'rate_limit_logging' => true,
        'max_logs_per_minute' => 100,
    ],
]; 