<?php

/**
 * Core Skin Registry
 *
 * Handles skin discovery, registration, and metadata management.
 *
 * @package IslamWiki\Core\Skin
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core\Skin;

use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Logging\Logger;

/**
 * Core Skin Registry - Skin Discovery and Registration System
 */
class SkinRegistry
{
    private Container $container;
    private Logger $logger;
    private array $registeredSkins = [];
    private array $skinMetadata = [];
    private string $skinsPath;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');
        $this->skinsPath = $container->get('base_path') . '/skins';
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
     * Get all registered skins
     */
    public function getRegisteredSkins(): array
    {
        return $this->registeredSkins;
    }

    /**
     * Get a specific skin
     */
    public function getSkin(string $name): ?array
    {
        return $this->registeredSkins[$name] ?? null;
    }

    /**
     * Check if a skin is registered
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

        $this->logger->info('Skin unregistered', ['skin' => $name]);
        return true;
    }

    /**
     * Get skins by category
     */
    public function getSkinsByCategory(string $category): array
    {
        $skins = [];
        foreach ($this->registeredSkins as $name => $config) {
            if (isset($config['category']) && $config['category'] === $category) {
                $skins[$name] = $config;
            }
        }
        return $skins;
    }

    /**
     * Get skins by author
     */
    public function getSkinsByAuthor(string $author): array
    {
        $skins = [];
        foreach ($this->registeredSkins as $name => $config) {
            if (isset($config['author']) && $config['author'] === $author) {
                $skins[$name] = $config;
            }
        }
        return $skins;
    }

    /**
     * Search skins
     */
    public function searchSkins(string $query): array
    {
        $results = [];
        $query = strtolower($query);
        
        foreach ($this->registeredSkins as $name => $config) {
            if (strpos(strtolower($name), $query) !== false ||
                strpos(strtolower($config['description'] ?? ''), $query) !== false ||
                strpos(strtolower($config['author'] ?? ''), $query) !== false) {
                $results[$name] = $config;
            }
        }
        
        return $results;
    }

    /**
     * Get skin statistics
     */
    public function getSkinStatistics(): array
    {
        $total = count($this->registeredSkins);
        $categories = [];
        $authors = [];
        
        foreach ($this->registeredSkins as $config) {
            if (isset($config['category'])) {
                $categories[$config['category']] = ($categories[$config['category']] ?? 0) + 1;
            }
            if (isset($config['author'])) {
                $authors[$config['author']] = ($authors[$config['author']] ?? 0) + 1;
            }
        }
        
        return [
            'total_skins' => $total,
            'categories' => $categories,
            'authors' => $authors,
            'discovery_path' => $this->skinsPath
        ];
    }
} 