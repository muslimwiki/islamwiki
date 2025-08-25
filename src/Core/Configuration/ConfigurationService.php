<?php

namespace IslamWiki\Core\Configuration;

/**
 * Configuration Service for managing application settings
 */
class ConfigurationService
{
    private array $config = [];
    private string $configFile;
    
    public function __construct()
    {
        $this->configFile = storage_path('config/app.json');
        $this->loadConfig();
    }
    
    /**
     * Get a configuration value
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Set a configuration value
     */
    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
        $this->saveConfig();
    }
    
    /**
     * Get the home page setting
     */
    public function getHomePage(): string
    {
        return $this->get('home_page', 'en/wiki/Home');
    }
    
    /**
     * Set the home page setting
     */
    public function setHomePage(string $homePage): void
    {
        $this->set('home_page', $homePage);
    }
    
    /**
     * Load configuration from file
     */
    private function loadConfig(): void
    {
        if (file_exists($this->configFile)) {
            $content = file_get_contents($this->configFile);
            $this->config = json_decode($content, true) ?: [];
        }
    }
    
    /**
     * Save configuration to file
     */
    private function saveConfig(): void
    {
        $dir = dirname($this->configFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($this->configFile, json_encode($this->config, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get storage path
     */
    private function storage_path(string $path): string
    {
        return dirname(__DIR__, 3) . '/storage/' . $path;
    }
} 