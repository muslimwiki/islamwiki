<?php

declare(strict_types=1);

namespace IslamWiki\Core\Configuration;

// Define the path to LocalSettings.php
if (!defined('LOCAL_SETTINGS_PATH')) {
    define('LOCAL_SETTINGS_PATH', __DIR__ . '/../../../LocalSettings.php');
}

use IslamWiki\Core\Logging\ShahidLogger;

/**
 * Tadbir (تدبير) - Configuration Management System
 *
 * Tadbir means "Management" or "Planning" in Arabic. This system provides
 * comprehensive configuration management, settings organization, and planning
 * capabilities for the IslamWiki application.
 *
 * @package IslamWiki\Core\Configuration
 */
class TadbirConfiguration
{
    /**
     * The logger instance.
     *
     * @var Shahid
     */
    private ShahidLogger $logger;

    /**
     * Configuration data.
     *
     * @var array
     */
    private array $config = [];

    /**
     * Configuration validation rules.
     *
     * @var array
     */
    private array $validationRules = [];

    /**
     * Configuration categories.
     *
     * @var array
     */
    private array $categories = [
        'core' => 'Core application settings',
        'database' => 'Database configuration',
        'security' => 'Security and authentication settings',
        'islamic' => 'Islamic-specific settings',
        'extensions' => 'Extension configuration',
        'performance' => 'Performance and caching settings',
        'logging' => 'Logging and debugging settings'
    ];

    /**
     * Configuration statistics.
     *
     * @var array
     */
    private array $statistics = [
        'loads' => 0,
        'saves' => 0,
        'validations' => 0,
        'errors' => 0
    ];

    /**
     * Create a new Tadbir configuration instance.
     *
     * @param Shahid $logger The logger instance
     */
    public function __construct(ShahidLogger $logger)
    {
        $this->logger = $logger;
        $this->initializeDefaultConfig();
        $this->loadConfiguration();
    }

    /**
     * Initialize default configuration.
     *
     * @return void
     */
    private function initializeDefaultConfig(): void
    {
        $this->config = [
            'core' => [
                'app_name' => 'IslamWiki',
                'app_version' => '0.0.50',
                'app_debug' => false,
                'app_environment' => 'production',
                'timezone' => 'UTC',
                'locale' => 'en',
                'charset' => 'utf8mb4'
            ],
            'database' => [
                'host' => 'localhost',
                'port' => 3306,
                'database' => 'islamwiki',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci'
            ],
            'security' => [
                'session_lifetime' => 3600,
                'csrf_protection' => true,
                'password_min_length' => 8,
                'max_login_attempts' => 5,
                'lockout_duration' => 900
            ],
            'islamic' => [
                'prayer_calculation_method' => 'MWL',
                'qibla_direction' => true,
                'hijri_calendar' => true,
                'arabic_support' => true,
                'scholar_verification' => true
            ],
            'extensions' => [
                'enabled_extensions' => [],
                'extension_auto_load' => true,
                'extension_validation' => true
            ],
            'performance' => [
                'cache_enabled' => true,
                'cache_driver' => 'database',
                'cache_ttl' => 3600,
                'query_cache' => true,
                'asset_minification' => true
            ],
            'logging' => [
                'log_level' => 'info',
                'log_driver' => 'file',
                'log_max_files' => 30,
                'debug_mode' => false,
                'error_reporting' => true
            ]
        ];
    }

    /**
     * Load configuration from files.
     *
     * @return void
     */
    private function loadConfiguration(): void
    {
        $this->statistics['loads']++;

        // Load LocalSettings.php if exists
        if (file_exists(LOCAL_SETTINGS_PATH)) {
            $this->loadFromLocalSettings();
        }

        // Load environment variables
        $this->loadFromEnvironment();

        $this->logger->info('Tadbir configuration system initialized', [
            'system' => 'Tadbir',
            'categories' => count($this->categories),
            'settings' => count($this->config, COUNT_RECURSIVE)
        ]);
    }

    /**
     * Load configuration from LocalSettings.php.
     *
     * @return void
     */
    private function loadFromLocalSettings(): void
    {
        try {
            $localSettings = include LOCAL_SETTINGS_PATH;
            if (is_array($localSettings)) {
                $this->config = array_merge_recursive($this->config, $localSettings);
                $this->logger->debug('Tadbir loaded LocalSettings.php', [
                    'system' => 'Tadbir'
                ]);
            }
        } catch (\Exception $e) {
            $this->statistics['errors']++;
            $this->logger->error('Tadbir failed to load LocalSettings.php', [
                'system' => 'Tadbir',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Load configuration from environment variables.
     *
     * @return void
     */
    private function loadFromEnvironment(): void
    {
        $envMappings = [
            'DB_HOST' => 'database.host',
            'DB_PORT' => 'database.port',
            'DB_DATABASE' => 'database.database',
            'DB_USERNAME' => 'database.username',
            'DB_PASSWORD' => 'database.password',
            'APP_DEBUG' => 'core.app_debug',
            'APP_ENVIRONMENT' => 'core.app_environment',
            'LOG_LEVEL' => 'logging.log_level'
        ];

        foreach ($envMappings as $envVar => $configPath) {
            if (isset($_ENV[$envVar])) {
                $this->setNestedValue($configPath, $_ENV[$envVar]);
            }
        }
    }

    /**
     * Get a configuration value.
     *
     * @param string $key The configuration key (dot notation supported)
     * @param mixed $default The default value if key not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $value = $this->getNestedValue($key);
        return $value !== null ? $value : $default;
    }

    /**
     * Set a configuration value.
     *
     * @param string $key The configuration key (dot notation supported)
     * @param mixed $value The value to set
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->setNestedValue($key, $value);
        $this->logger->debug('Tadbir configuration value set', [
            'system' => 'Tadbir',
            'key' => $key,
            'value' => is_array($value) ? '[array]' : $value
        ]);
    }

    /**
     * Get nested configuration value.
     *
     * @param string $key The dot notation key
     * @return mixed
     */
    private function getNestedValue(string $key)
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return null;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Set nested configuration value.
     *
     * @param string $key The dot notation key
     * @param mixed $value The value to set
     * @return void
     */
    private function setNestedValue(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
    }

    /**
     * Validate configuration.
     *
     * @return array
     */
    public function validate(): array
    {
        $this->statistics['validations']++;
        $errors = [];
        $warnings = [];

        // Validate required settings
        $required = [
            'core.app_name',
            'core.app_version',
            'database.host',
            'database.database'
        ];

        foreach ($required as $key) {
            if ($this->get($key) === null) {
                $errors[] = "Required configuration missing: {$key}";
            }
        }

        // Validate database connection
        if (!$this->validateDatabaseConfig()) {
            $errors[] = 'Database configuration is invalid';
        }

        // Validate security settings
        if (!$this->validateSecurityConfig()) {
            $warnings[] = 'Security configuration may need adjustment';
        }

        $this->logger->info('Tadbir configuration validation completed', [
            'system' => 'Tadbir',
            'errors' => count($errors),
            'warnings' => count($warnings)
        ]);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Validate database configuration.
     *
     * @return bool
     */
    private function validateDatabaseConfig(): bool
    {
        $required = ['host', 'database', 'username'];
        foreach ($required as $field) {
            if (empty($this->get("database.{$field}"))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate security configuration.
     *
     * @return bool
     */
    private function validateSecurityConfig(): bool
    {
        $minPasswordLength = $this->get('security.password_min_length', 8);
        return $minPasswordLength >= 8;
    }

    /**
     * Get all configuration.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Get configuration by category.
     *
     * @param string $category The configuration category
     * @return array
     */
    public function getCategory(string $category): array
    {
        return $this->config[$category] ?? [];
    }

    /**
     * Get configuration categories.
     *
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Save configuration to file.
     *
     * @param string $file The file path
     * @return bool
     */
    public function save(string $file): bool
    {
        try {
            $this->statistics['saves']++;
            $content = "<?php\nreturn " . var_export($this->config, true) . ";\n";
            file_put_contents($file, $content);

            $this->logger->info('Tadbir configuration saved', [
                'system' => 'Tadbir',
                'file' => $file
            ]);

            return true;
        } catch (\Exception $e) {
            $this->statistics['errors']++;
            $this->logger->error('Tadbir configuration save failed', [
                'system' => 'Tadbir',
                'file' => $file,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get configuration statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'system' => 'Tadbir',
            'statistics' => $this->statistics,
            'categories' => count($this->categories),
            'settings' => count($this->config, COUNT_RECURSIVE),
            'validation' => $this->validate()
        ];
    }

    /**
     * Reset configuration to defaults.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->initializeDefaultConfig();
        $this->logger->info('Tadbir configuration reset to defaults', [
            'system' => 'Tadbir'
        ]);
    }
}
