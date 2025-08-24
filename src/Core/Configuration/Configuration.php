<?php

/**
 * Core Configuration Service
 *
 * Simple configuration management for the core system.
 *
 * @package IslamWiki\Core\Configuration
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Configuration;

/**
 * Core Configuration Service
 */
class Configuration
{
    private array $config = [];

    public function __construct(array $initialConfig = [])
    {
        $this->config = $initialConfig;
        
        // Load default configuration
        $this->loadDefaultConfig();
    }

    /**
     * Load default configuration
     */
    private function loadDefaultConfig(): void
    {
        $defaults = [
            'skins' => [
                'active' => 'Bismillah',
                'allow_user_selection' => false,
                'admin_only_management' => true
            ],
            'app' => [
                'name' => 'IslamWiki',
                'version' => '0.0.3.0',
                'debug' => false
            ],
            'database' => [
                'host' => 'localhost',
                'database' => 'islamwiki',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4'
            ]
        ];

        $this->config = array_merge($defaults, $this->config);
    }

    /**
     * Get a configuration value
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set a configuration value
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * Check if a configuration key exists
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }

        return true;
    }

    /**
     * Get all configuration
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Load configuration from file
     */
    public function loadFromFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                return false;
            }

            $config = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            $this->config = array_merge($this->config, $config);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Save configuration to file
     */
    public function saveToFile(string $filePath): bool
    {
        try {
            $content = json_encode($this->config, JSON_PRETTY_PRINT);
            if ($content === false) {
                return false;
            }

            $result = file_put_contents($filePath, $content);
            return $result !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
