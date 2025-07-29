<?
declare(strict_types=1);
php\nreturn [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */
    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Available
    | channels include "single", "daily", "slack", "syslog", "errorlog",
    | and custom channels for different parts of your application.
    |
    */
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'errorlog'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/application.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/application.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'permission' => 0664,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'error',
        ],

        'emergency' => [
            'path' => storage_path('logs/emergency.log'),
        ],

        // Application specific channels
        'auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth.log'),
            'level' => 'info',
            'days' => 30,
        ],

        'page_edits' => [
            'driver' => 'daily',
            'path' => storage_path('logs/page_edits.log'),
            'level' => 'info',
            'days' => 60,
        ],

        'api' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'warning',
            'days' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Threshold
    |--------------------------------------------------------------------------
    |
    | You can enable error logging by setting a threshold over zero. The
    | threshold determines what gets logged. Any error below this threshold
    | will be ignored. You can log all errors by setting the threshold to 0.
    |
    | 0 = Disables logging, Error logging TURNED OFF
    | 1 = Error Messages (including PHP errors)
    | 2 = Debug Messages
    | 3 = Informational Messages
    | 4 = All Messages
    |
    */
    'threshold' => 1,

    /*
    |--------------------------------------------------------------------------
    | Error Logging Directory Path
    |--------------------------------------------------------------------------
    |
    | Leave this BLANK unless you would like to set something other than the default
    | application/logs/ directory. Use a full server path with trailing slash.
    |
    */
    'log_path' => storage_path('logs/'),

    /*
    |--------------------------------------------------------------------------
    | Date Format for Logs
    |--------------------------------------------------------------------------
    |
    | Each item that is logged has an associated date. You can use PHP date
    | codes to set your own date formatting
    |
    */
    'date_fmt' => 'Y-m-d H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Log File Permissions
    |--------------------------------------------------------------------------
    |
    | The file system permissions to be applied on newly created log files.
    |
    | IMPORTANT: This MUST be an integer (no quotes) and you MUST use the
    | octal notation (i.e. 0700, 0644, etc.)
    */
    'file_permissions' => 0644,

    /*
    |--------------------------------------------------------------------------
    | Error Views Directory Path
    |--------------------------------------------------------------------------
    |
    | Leave this BLANK unless you would like to set something other than the default
    | application/views/errors/ directory.  Use a full server path with trailing slash.
    |
    */
    'error_views_path' => __DIR__ . '/../resources/views/errors/',
];
