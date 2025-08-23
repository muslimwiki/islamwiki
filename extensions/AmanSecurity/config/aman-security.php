<?php

/**
 * AmanSecurity Extension Configuration
 *
 * Configuration file for the AmanSecurity authentication extension.
 *
 * @package IslamWiki\Extensions\AmanSecurity\Config
 * @version 0.0.1.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Configure session timeout and security settings.
    |
    */
    'session_timeout' => env('AMAN_SESSION_TIMEOUT', 3600), // 1 hour
    'session_regeneration' => env('AMAN_SESSION_REGENERATION', true),
    'session_secure' => env('AMAN_SESSION_SECURE', true),
    'session_http_only' => env('AMAN_SESSION_HTTP_ONLY', true),
    'session_same_site' => env('AMAN_SESSION_SAME_SITE', 'Lax'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Configure login attempts, password requirements, and verification.
    |
    */
    'max_login_attempts' => env('AMAN_MAX_LOGIN_ATTEMPTS', 5),
    'login_attempts_window' => env('AMAN_LOGIN_ATTEMPTS_WINDOW', 900), // 15 minutes
    'password_min_length' => env('AMAN_PASSWORD_MIN_LENGTH', 8),
    'password_require_uppercase' => env('AMAN_PASSWORD_REQUIRE_UPPERCASE', true),
    'password_require_lowercase' => env('AMAN_PASSWORD_REQUIRE_LOWERCASE', true),
    'password_require_numbers' => env('AMAN_PASSWORD_REQUIRE_NUMBERS', true),
    'password_require_symbols' => env('AMAN_PASSWORD_REQUIRE_SYMBOLS', false),
    'password_history_count' => env('AMAN_PASSWORD_HISTORY_COUNT', 5),

    /*
    |--------------------------------------------------------------------------
    | Email Verification Configuration
    |--------------------------------------------------------------------------
    |
    | Configure email verification requirements and settings.
    |
    */
    'require_email_verification' => env('AMAN_REQUIRE_EMAIL_VERIFICATION', true),
    'email_verification_expiry' => env('AMAN_EMAIL_VERIFICATION_EXPIRY', 86400), // 24 hours
    'resend_verification_delay' => env('AMAN_RESEND_VERIFICATION_DELAY', 300), // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Configure 2FA settings and requirements.
    |
    */
    'enable_two_factor' => env('AMAN_ENABLE_TWO_FACTOR', false),
    'two_factor_methods' => ['totp', 'sms', 'email'],
    'two_factor_remember_days' => env('AMAN_TWO_FACTOR_REMEMBER_DAYS', 30),
    'two_factor_backup_codes_count' => env('AMAN_TWO_FACTOR_BACKUP_CODES_COUNT', 10),

    /*
    |--------------------------------------------------------------------------
    | Security Features Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CSRF protection, rate limiting, and other security features.
    |
    */
    'csrf_protection' => env('AMAN_CSRF_PROTECTION', true),
    'csrf_token_expiry' => env('AMAN_CSRF_TOKEN_EXPIRY', 3600), // 1 hour
    'rate_limiting' => env('AMAN_RATE_LIMITING', true),
    'rate_limit_requests' => env('AMAN_RATE_LIMIT_REQUESTS', 60), // requests per minute
    'rate_limit_window' => env('AMAN_RATE_LIMIT_WINDOW', 60), // 1 minute

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security event logging and monitoring.
    |
    */
    'log_authentication_events' => env('AMAN_LOG_AUTHENTICATION_EVENTS', true),
    'log_security_events' => env('AMAN_LOG_SECURITY_EVENTS', true),
    'log_user_actions' => env('AMAN_LOG_USER_ACTIONS', true),
    'log_failed_attempts' => env('AMAN_LOG_FAILED_ATTEMPTS', true),

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security notifications and alerts.
    |
    */
    'notify_failed_login' => env('AMAN_NOTIFY_FAILED_LOGIN', true),
    'notify_suspicious_activity' => env('AMAN_NOTIFY_SUSPICIOUS_ACTIVITY', true),
    'notify_password_change' => env('AMAN_NOTIFY_PASSWORD_CHANGE', true),
    'notify_new_device_login' => env('AMAN_NOTIFY_NEW_DEVICE_LOGIN', true),

    /*
    |--------------------------------------------------------------------------
    | Advanced Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure advanced security features and policies.
    |
    */
    'ip_whitelist' => env('AMAN_IP_WHITELIST', []),
    'ip_blacklist' => env('AMAN_IP_BLACKLIST', []),
    'geolocation_restrictions' => env('AMAN_GEOLOCATION_RESTRICTIONS', false),
    'allowed_countries' => env('AMAN_ALLOWED_COUNTRIES', []),
    'blocked_countries' => env('AMAN_BLOCKED_COUNTRIES', []),
    'device_fingerprinting' => env('AMAN_DEVICE_FINGERPRINTING', true),
    'suspicious_activity_threshold' => env('AMAN_SUSPICIOUS_ACTIVITY_THRESHOLD', 5),
]; 