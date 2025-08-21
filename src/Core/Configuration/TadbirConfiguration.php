<?php

declare(strict_types=1);

namespace IslamWiki\Core\Configuration;

use IslamWiki\Core\Container\AsasContainer;

/**
 * Tadbir Configuration System
 * 
 * Manages system configuration, settings, and extension loading.
 * 
 * @package IslamWiki\Core\Configuration
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class TadbirConfiguration
{
    private AsasContainer $container;
    private array $config = [];
    private array $extensions = [];

    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
        $this->loadConfiguration();
        // Don't load extensions immediately - they will be loaded later after service providers are ready
        // $this->loadExtensions();
    }

    /**
     * Load extensions after service providers are ready
     */
    public function loadExtensionsWhenReady(): void
    {
        $this->loadExtensions();
    }

    /**
     * Load main configuration
     */
    private function loadConfiguration(): void
    {
        // Load main config
        $this->config = [
            'app' => [
                'name' => 'IslamWiki',
                'version' => '0.0.1',
                'environment' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => $_ENV['APP_DEBUG'] ?? false,
                'url' => $_ENV['APP_URL'] ?? 'http://localhost',
                'timezone' => 'UTC',
                'locale' => 'en',
            ],
            'database' => [
                'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'connections' => [
                    'mysql' => [
                        'driver' => 'mysql',
                        'host' => $_ENV['DB_HOST'] ?? 'localhost',
                        'port' => $_ENV['DB_PORT'] ?? '3306',
                        'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
                        'username' => $_ENV['DB_USERNAME'] ?? 'root',
                        'password' => $_ENV['DB_PASSWORD'] ?? '',
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                    ],
                    'quran' => [
                        'driver' => 'mysql',
                        'host' => $_ENV['QURAN_DB_HOST'] ?? 'localhost',
                        'port' => $_ENV['QURAN_DB_PORT'] ?? '3306',
                        'database' => $_ENV['QURAN_DB_DATABASE'] ?? 'quran_db',
                        'username' => $_ENV['QURAN_DB_USERNAME'] ?? 'root',
                        'password' => $_ENV['QURAN_DB_PASSWORD'] ?? '',
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                    ],
                    'hadith' => [
                        'driver' => 'mysql',
                        'host' => $_ENV['HADITH_DB_HOST'] ?? 'localhost',
                        'port' => $_ENV['HADITH_DB_PORT'] ?? '3306',
                        'database' => $_ENV['HADITH_DB_DATABASE'] ?? 'hadith_db',
                        'username' => $_ENV['HADITH_DB_USERNAME'] ?? 'root',
                        'password' => $_ENV['HADITH_DB_PASSWORD'] ?? '',
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                    ],
                ],
            ],
            'cache' => [
                'default' => $_ENV['CACHE_DRIVER'] ?? 'file',
                'stores' => [
                    'file' => [
                        'driver' => 'file',
                        'path' => __DIR__ . '/../../storage/framework/cache',
                    ],
                    'redis' => [
                        'driver' => 'redis',
                        'connection' => 'default',
                    ],
                ],
            ],
            'session' => [
                'driver' => $_ENV['SESSION_DRIVER'] ?? 'file',
                'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120,
                'expire_on_close' => false,
                'encrypt' => false,
                'files' => __DIR__ . '/../../storage/framework/sessions',
                'connection' => $_ENV['SESSION_CONNECTION'] ?? null,
                'table' => 'mizan_sessions',
                'store' => $_ENV['SESSION_STORE'] ?? null,
                'lottery' => [2, 100],
                'cookie' => 'islamwiki_session',
                'path' => '/',
                'domain' => $_ENV['SESSION_DOMAIN'] ?? null,
                'secure' => $_ENV['SESSION_SECURE_COOKIE'] ?? false,
                'http_only' => true,
                'same_site' => 'lax',
            ],
            'mail' => [
                'default' => $_ENV['MAIL_MAILER'] ?? 'smtp',
                'mailers' => [
                    'smtp' => [
                        'transport' => 'smtp',
                        'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
                        'port' => $_ENV['MAIL_PORT'] ?? 587,
                        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
                        'username' => $_ENV['MAIL_USERNAME'] ?? '',
                        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
                        'timeout' => null,
                        'local_domain' => $_ENV['MAIL_EHLO_DOMAIN'] ?? null,
                    ],
                ],
                'from' => [
                    'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@islamwiki.com',
                    'name' => $_ENV['MAIL_FROM_NAME'] ?? 'IslamWiki',
                ],
            ],
            'logging' => [
                'default' => $_ENV['LOG_CHANNEL'] ?? 'stack',
                'channels' => [
                    'stack' => [
                        'driver' => 'stack',
                        'channels' => ['single'],
                        'ignore_exceptions' => false,
                    ],
                    'single' => [
                        'driver' => 'single',
                        'path' => __DIR__ . '/../../storage/logs/islamwiki.log',
                        'level' => $_ENV['LOG_LEVEL'] ?? 'debug',
                    ],
                ],
            ],
        ];
    }

    /**
     * Load extensions configuration
     */
    private function loadExtensions(): void
    {
        $extensionsConfig = require __DIR__ . '/../../../config/extensions.php';
        $this->extensions = $extensionsConfig['active_extensions'] ?? [];

        // Register extensions with the container
        $this->registerExtensions();
    }

    /**
     * Register extensions with the container
     */
    private function registerExtensions(): void
    {
        foreach ($this->extensions as $name => $config) {
            if ($config['enabled']) {
                $this->registerExtension($name, $config);
            }
        }
    }

    /**
     * Register a single extension
     */
    private function registerExtension(string $name, array $config): void
    {
        $extensionClass = "IslamWiki\\Extensions\\{$name}\\{$name}";
        
        // echo "🔍 Debug: Registering extension '{$name}' with class '{$extensionClass}'\n";
        
        if (class_exists($extensionClass)) {
            // echo "✅ Class '{$extensionClass}' exists\n";
            
            // Register extension service
            $this->container->set("extension.{$name}", function () use ($extensionClass, $name) {
                // echo "🔍 Debug: Creating instance of '{$extensionClass}' for extension '{$name}'\n";
                
                // Check if this extension extends the Extension base class
                $reflection = new \ReflectionClass($extensionClass);
                $parentClass = $reflection->getParentClass();
                
                if ($parentClass && $parentClass->getName() === 'IslamWiki\\Core\\Extensions\\Extension') {
                    // Extension extends Extension base class - only pass container
                    // echo "🔍 Debug: Extension extends Extension base class, passing only container\n";
                    $instance = new $extensionClass($this->container);
                } else {
                    // Extension implements ExtensionInterface directly - pass manager and container
                    // echo "🔍 Debug: Extension implements ExtensionInterface directly, passing manager and container\n";
                    $extensionManager = $this->container->get('extension.manager');
                    $instance = new $extensionClass($extensionManager, $this->container);
                }
                // echo "✅ Created instance: " . get_class($instance) . "\n";
                return $instance;
            });

            // Register extension configuration
            $this->container->set("extension.{$name}.config", $config);

            // Initialize extension if it has an init method
            $extension = $this->container->get("extension.{$name}");
            if (method_exists($extension, 'init')) {
                $extension->init();
            }

            // Log extension registration
            $this->logExtensionRegistration($name, $config);
        } else {
            $this->logExtensionError($name, "Extension class not found: {$extensionClass}");
        }
    }

    /**
     * Get configuration value
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $segment) {
            if (isset($config[$segment])) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }

        return $config;
    }

    /**
     * Set configuration value
     */
    public function set(string $key, $value): void
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
     * Get all extensions
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get specific extension config
     */
    public function getExtension(string $name): ?array
    {
        return $this->extensions[$name] ?? null;
    }

    /**
     * Check if extension is enabled
     */
    public function isExtensionEnabled(string $name): bool
    {
        return isset($this->extensions[$name]) && $this->extensions[$name]['enabled'];
    }

    /**
     * Get extension priority
     */
    public function getExtensionPriority(string $name): int
    {
        return $this->extensions[$name]['priority'] ?? 999;
    }

    /**
     * Log extension registration
     */
    private function logExtensionRegistration(string $name, array $config): void
    {
        $logger = $this->container->get('logger');
        if ($logger) {
            $logger->info("Extension registered: {$name}", [
                'name' => $name,
                'priority' => $config['priority'] ?? 999,
                'config' => $config['config'] ?? [],
            ]);
        }
    }

    /**
     * Log extension error
     */
    private function logExtensionError(string $name, string $error): void
    {
        $logger = $this->container->get('logger');
        if ($logger) {
            $logger->error("Extension error: {$name} - {$error}", [
                'name' => $name,
                'error' => $error,
            ]);
        }
    }

    /**
     * Get all configuration
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * Reload configuration
     */
    public function reload(): void
    {
        $this->loadConfiguration();
        // Don't load extensions immediately - they will be loaded later after service providers are ready
        // $this->loadExtensions();
    }
}
