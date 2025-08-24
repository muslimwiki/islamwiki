<?php

/**
 * Enhanced Core Skin Manager
 *
 * Consolidated skin management system that provides advanced skin functionality
 * including discovery, registry, customization, and asset management.
 *
 * @package IslamWiki\Core\Skin
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Skin;

use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Configuration\Configuration;

/**
 * Enhanced Core Skin Manager - Consolidated Skin Management System
 */
class SkinManager
{
    private Container $container;
    private Logger $logger;
    private Configuration $config;
    private array $registeredSkins = [];
    private array $skinMetadata = [];
    private ?string $activeSkin = null;
    private array $skinConfig = [];
    private string $skinsPath;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');
        $this->config = $container->get('config');
        $this->skinsPath = $container->get('base_path') . '/skins';
        
        $this->loadSkinConfiguration();
        $this->discoverSkins();
        $this->registerDefaultSkins();
    }

    /**
     * Load skin configuration from settings
     */
    private function loadSkinConfiguration(): void
    {
        try {
            $this->skinConfig = $this->config->get('skins', []);
            $this->activeSkin = $this->skinConfig['active'] ?? 'Bismillah';
            $this->logger->info('Skin configuration loaded', ['active_skin' => $this->activeSkin]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to load skin configuration', ['error' => $e->getMessage()]);
            $this->activeSkin = 'Bismillah'; // Fallback to default
        }
    }

    /**
     * Discover available skins in the skins directory
     */
    public function discoverSkins(): void
    {
        if (!is_dir($this->skinsPath)) {
            $this->logger->warning('Skins directory not found', ['path' => $this->skinsPath]);
            return;
        }

        $skinDirs = glob($this->skinsPath . '/*', GLOB_ONLYDIR);
        
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $this->discoverSkin($skinName, $skinDir);
        }

        $this->logger->info('Skin discovery completed', ['total_skins' => count($this->registeredSkins)]);
    }

    /**
     * Discover a specific skin
     */
    private function discoverSkin(string $skinName, string $skinPath): void
    {
        $configFile = $skinPath . '/skin.json';
        
        if (!file_exists($configFile)) {
            $this->logger->debug('Skin config file not found', ['skin' => $skinName, 'path' => $configFile]);
            return;
        }

        try {
            $config = json_decode(file_get_contents($configFile), true);
            if (!$config || !isset($config['name'])) {
                $this->logger->warning('Invalid skin configuration', ['skin' => $skinName]);
                return;
            }

            // Validate skin configuration
            if ($this->validateSkinConfiguration($config, $skinPath)) {
                $this->registerSkin($skinName, array_merge($config, [
                    'path' => $skinPath,
                    'discovered_at' => date('Y-m-d H:i:s')
                ]));
                $this->logger->info('Skin discovered successfully', ['skin' => $skinName]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to discover skin', ['skin' => $skinName, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Validate skin configuration
     */
    private function validateSkinConfiguration(array $config, string $skinPath): bool
    {
        $required = ['name', 'version', 'description'];
        
        foreach ($required as $field) {
            if (!isset($config[$field])) {
                $this->logger->warning('Missing required skin configuration field', ['field' => $field, 'skin' => $config['name'] ?? 'unknown']);
                return false;
            }
        }

        // Check if skin has basic structure
        $cssPath = $skinPath . '/css';
        $jsPath = $skinPath . '/js';
        
        if (!is_dir($cssPath) && !is_dir($jsPath)) {
            $this->logger->warning('Skin missing basic structure', ['skin' => $config['name'], 'path' => $skinPath]);
            return false;
        }

        return true;
    }

    /**
     * Register a skin with the registry
     */
    public function registerSkin(string $name, array $config): bool
    {
        if (empty($config['name']) || empty($config['path'])) {
            $this->logger->warning('Invalid skin registration data', ['skin' => $name]);
            return false;
        }

        // Validate skin structure
        if (!$this->validateSkinStructure($config['path'])) {
            return false;
        }

        $this->registeredSkins[$name] = $config;
        $this->skinMetadata[$name] = $this->extractSkinMetadata($config);

        $this->logger->info('Skin registered successfully', ['skin' => $name]);
        return true;
    }

    /**
     * Validate skin structure
     */
    private function validateSkinStructure(string $skinPath): bool
    {
        if (!is_dir($skinPath)) {
            return false;
        }

        // Check for essential directories
        $cssPath = $skinPath . '/css';
        $jsPath = $skinPath . '/js';
        
        // At least one of CSS or JS should exist
        return is_dir($cssPath) || is_dir($jsPath);
    }

    /**
     * Extract skin metadata
     */
    private function extractSkinMetadata(array $config): array
    {
        return [
            'name' => $config['name'],
            'version' => $config['version'] ?? '0.0.0',
            'description' => $config['description'] ?? '',
            'author' => $config['author'] ?? 'Unknown',
            'path' => $config['path'],
            'assets' => $config['assets'] ?? [],
            'config' => $config['config'] ?? []
        ];
    }

    /**
     * Register default skins
     */
    private function registerDefaultSkins(): void
    {
        // Register Bismillah skin if not already discovered
        if (!isset($this->registeredSkins['Bismillah'])) {
            $this->registerSkin('Bismillah', [
                'name' => 'Bismillah',
                'display_name' => 'Bismillah',
                'description' => 'Default Islamic-themed skin with traditional design and beautiful gradients',
                'version' => '0.0.28',
                'author' => 'IslamWiki Team',
                'path' => $this->skinsPath . '/Bismillah'
            ]);
        }

        // Register Muslim skin if not already discovered
        if (!isset($this->registeredSkins['Muslim'])) {
            $this->registerSkin('Muslim', [
                'name' => 'Muslim',
                'display_name' => 'Muslim',
                'description' => 'Alternative Islamic skin with modern design',
                'version' => '0.0.1',
                'author' => 'IslamWiki Team',
                'path' => $this->skinsPath . '/Muslim'
            ]);
        }
    }

    /**
     * Get all available skins
     */
    public function getAvailableSkins(): array
    {
        return $this->registeredSkins;
    }

    /**
     * Get a specific skin by name
     */
    public function getSkin(string $name): ?array
    {
        return $this->registeredSkins[$name] ?? null;
    }

    /**
     * Get the currently active skin name
     */
    public function getActiveSkinName(): ?string
    {
        return $this->activeSkin;
    }

    /**
     * Get the currently active skin configuration
     */
    public function getActiveSkin(): ?array
    {
        if (!$this->activeSkin) {
            return null;
        }
        return $this->registeredSkins[$this->activeSkin] ?? null;
    }

    /**
     * Check if a skin is currently active
     */
    public function hasActiveSkin(): bool
    {
        return $this->activeSkin !== null && isset($this->registeredSkins[$this->activeSkin]);
    }

    /**
     * Set the active skin
     */
    public function setActiveSkin(string $name): bool
    {
        if (!isset($this->registeredSkins[$name])) {
            $this->logger->warning('Attempted to set non-existent skin as active', ['skin' => $name]);
            return false;
        }

        $this->activeSkin = $name;
        
        // Update configuration
        $this->skinConfig['active'] = $name;
        try {
            $this->config->set('skins', $this->skinConfig);
            $this->logger->info('Active skin changed', ['new_skin' => $name]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save skin configuration', ['error' => $e->getMessage()]);
        }

        return true;
    }

    /**
     * Get active skin CSS content
     */
    public function getActiveSkinCss(): string
    {
        $skin = $this->getActiveSkin();
        if (!$skin) {
            return '';
        }

        $cssPath = $skin['path'] . '/css';
        if (!is_dir($cssPath)) {
            return '';
        }

        $cssFiles = glob($cssPath . '/*.css');
        $cssContent = '';

        foreach ($cssFiles as $cssFile) {
            if (file_exists($cssFile)) {
                $cssContent .= file_get_contents($cssFile) . "\n";
            }
        }

        return $cssContent;
    }

    /**
     * Get active skin JavaScript content
     */
    public function getActiveSkinJs(): string
    {
        $skin = $this->getActiveSkin();
        if (!$skin) {
            return '';
        }

        $jsPath = $skin['path'] . '/js';
        if (!is_dir($jsPath)) {
            return '';
        }

        $jsFiles = glob($jsPath . '/*.js');
        $jsContent = '';

        foreach ($jsFiles as $jsFile) {
            if (file_exists($jsFile)) {
                $jsContent .= file_get_contents($jsFile) . "\n";
            }
        }

        return $jsContent;
    }

    /**
     * Get skin assets URLs
     */
    public function getSkinAssets(string $skinName = null): array
    {
        $skin = $skinName ? $this->getSkin($skinName) : $this->getActiveSkin();
        if (!$skin) {
            return [];
        }

        $assets = [];
        $skinPath = $skin['path'];
        $skinName = $skin['name'];

        // CSS files
        $cssPath = $skinPath . '/css';
        if (is_dir($cssPath)) {
            $cssFiles = glob($cssPath . '/*.css');
            foreach ($cssFiles as $cssFile) {
                $fileName = basename($cssFile);
                $assets['css'][] = "/skins/{$skinName}/css/{$fileName}";
            }
        }

        // JavaScript files
        $jsPath = $skinPath . '/js';
        if (is_dir($jsPath)) {
            $jsFiles = glob($jsPath . '/*.js');
            foreach ($jsFiles as $jsFile) {
                $fileName = basename($jsFile);
                $assets['js'][] = "/skins/{$skinName}/js/{$fileName}";
            }
        }

        return $assets;
    }

    /**
     * Check if a skin exists
     */
    public function hasSkin(string $name): bool
    {
        return isset($this->registeredSkins[$name]);
    }

    /**
     * Get skin metadata
     */
    public function getSkinMetadata(string $name): ?array
    {
        return $this->skinMetadata[$name] ?? null;
    }

    /**
     * Unregister a skin
     */
    public function unregisterSkin(string $name): bool
    {
        if (!isset($this->registeredSkins[$name])) {
            return false;
        }

        unset($this->registeredSkins[$name]);
        unset($this->skinMetadata[$name]);

        // If this was the active skin, reset to default
        if ($this->activeSkin === $name) {
            $this->setActiveSkin('Bismillah');
        }

        $this->logger->info('Skin unregistered', ['skin' => $name]);
        return true;
    }

    /**
     * Get skin configuration
     */
    public function getSkinConfig(): array
    {
        return $this->skinConfig;
    }

    /**
     * Update skin configuration
     */
    public function updateSkinConfig(array $config): bool
    {
        try {
            $this->skinConfig = array_merge($this->skinConfig, $config);
            $this->config->set('skins', $this->skinConfig);
            $this->logger->info('Skin configuration updated', ['config' => $config]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update skin configuration', ['error' => $e->getMessage()]);
            return false;
        }
    }
} 