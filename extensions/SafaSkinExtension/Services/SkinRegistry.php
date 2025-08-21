<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SafaSkinExtension\Services;

use IslamWiki\Core\Container\AsasContainer;

/**
 * Skin Registry Service
 * 
 * Handles skin discovery, registration, and management.
 * 
 * @package IslamWiki\Extensions\SafaSkinExtension\Services
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SkinRegistry
{
    private AsasContainer $container;
    private array $registeredSkins = [];
    private array $skinMetadata = [];
    private string $skinsPath;

    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
        $this->skinsPath = $container->get('base_path') . '/skins';
    }

    /**
     * Discover available skins in the skins directory
     */
    public function discoverSkins(): void
    {
        if (!is_dir($this->skinsPath)) {
            return;
        }

        $skinDirs = glob($this->skinsPath . '/*', GLOB_ONLYDIR);
        
        foreach ($skinDirs as $skinDir) {
            $skinName = basename($skinDir);
            $this->discoverSkin($skinName, $skinDir);
        }
    }

    /**
     * Discover a specific skin
     */
    private function discoverSkin(string $skinName, string $skinPath): void
    {
        $configFile = $skinPath . '/skin.json';
        
        if (!file_exists($configFile)) {
            return;
        }

        try {
            $config = json_decode(file_get_contents($configFile), true);
            if (!$config || !isset($config['name'])) {
                return;
            }

            // Validate skin configuration
            if ($this->validateSkinConfiguration($config, $skinPath)) {
                $this->registerSkin($skinName, array_merge($config, [
                    'path' => $skinPath,
                    'discovered_at' => date('Y-m-d H:i:s')
                ]));
            }
        } catch (\Exception $e) {
            error_log("Failed to discover skin {$skinName}: " . $e->getMessage());
        }
    }

    /**
     * Register a skin with the registry
     */
    public function registerSkin(string $name, array $config): bool
    {
        if (empty($config['name']) || empty($config['path'])) {
            return false;
        }

        // Validate skin structure
        if (!$this->validateSkinStructure($config['path'])) {
            return false;
        }

        $this->registeredSkins[$name] = $config;
        $this->skinMetadata[$name] = $this->extractSkinMetadata($config);

        return true;
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

        return true;
    }

    /**
     * Get all registered skins
     */
    public function getRegisteredSkins(): array
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
     * Get skin metadata
     */
    public function getSkinMetadata(string $name): ?array
    {
        return $this->skinMetadata[$name] ?? null;
    }

    /**
     * Check if a skin is registered
     */
    public function isSkinRegistered(string $name): bool
    {
        return isset($this->registeredSkins[$name]);
    }

    /**
     * Get available skin names
     */
    public function getAvailableSkinNames(): array
    {
        return array_keys($this->registeredSkins);
    }

    /**
     * Validate skin configuration
     */
    private function validateSkinConfiguration(array $config, string $skinPath): bool
    {
        // Check required fields
        $required = ['name', 'version'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                return false;
            }
        }

        // Check if skin directory exists
        if (!is_dir($skinPath)) {
            return false;
        }

        return true;
    }

    /**
     * Validate skin structure
     */
    private function validateSkinStructure(string $skinPath): bool
    {
        // Check if skin directory exists
        if (!is_dir($skinPath)) {
            return false;
        }

        // Check for required subdirectories (at least one should exist)
        $requiredDirs = ['layouts', 'components', 'pages', 'css', 'js'];
        $hasRequiredDir = false;

        foreach ($requiredDirs as $dir) {
            if (is_dir($skinPath . '/' . $dir)) {
                $hasRequiredDir = true;
                break;
            }
        }

        if (!$hasRequiredDir) {
            return false;
        }

        return true;
    }

    /**
     * Extract skin metadata
     */
    private function extractSkinMetadata(array $config): array
    {
        $metadata = [
            'name' => $config['name'],
            'version' => $config['version'],
            'description' => $config['description'] ?? '',
            'author' => $config['author'] ?? '',
            'path' => $config['path'],
            'registered_at' => $config['registered_at'] ?? date('Y-m-d H:i:s')
        ];

        // Extract additional metadata from skin.json
        $configFile = $config['path'] . '/skin.json';
        if (file_exists($configFile)) {
            try {
                $skinConfig = json_decode(file_get_contents($configFile), true);
                if ($skinConfig) {
                    $metadata = array_merge($metadata, $skinConfig);
                }
            } catch (\Exception $e) {
                // Continue with basic metadata
            }
        }

        return $metadata;
    }

    /**
     * Get skin information for display
     */
    public function getSkinInfo(string $name): ?array
    {
        $skin = $this->getSkin($name);
        if (!$skin) {
            return null;
        }

        $metadata = $this->getSkinMetadata($name);
        
        return [
            'name' => $skin['name'],
            'display_name' => $skin['display_name'] ?? $skin['name'],
            'description' => $metadata['description'] ?? '',
            'version' => $skin['version'],
            'author' => $metadata['author'] ?? '',
            'path' => $skin['path'],
            'registered_at' => $metadata['registered_at'] ?? '',
            'assets' => $this->getSkinAssetInfo($skin['path']),
            'templates' => $this->getSkinTemplateInfo($skin['path']),
            'valid' => $this->validateSkinStructure($skin['path'])
        ];
    }

    /**
     * Get skin asset information
     */
    private function getSkinAssetInfo(string $skinPath): array
    {
        $assets = [
            'css' => [],
            'js' => [],
            'images' => []
        ];

        // CSS files
        $cssPath = $skinPath . '/css';
        if (is_dir($cssPath)) {
            $cssFiles = glob($cssPath . '/*.css');
            $assets['css'] = array_map('basename', $cssFiles);
        }

        // JavaScript files
        $jsPath = $skinPath . '/js';
        if (is_dir($jsPath)) {
            $jsFiles = glob($jsPath . '/*.js');
            $assets['js'] = array_map('basename', $jsFiles);
        }

        // Image files
        $imagesPath = $skinPath . '/assets/images';
        if (is_dir($imagesPath)) {
            $imageFiles = glob($imagesPath . '/*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
            $assets['images'] = array_map('basename', $imageFiles);
        }

        return $assets;
    }

    /**
     * Get skin template information
     */
    private function getSkinTemplateInfo(string $skinPath): array
    {
        $templates = [
            'layouts' => [],
            'components' => [],
            'pages' => []
        ];

        $templateDirs = ['layouts', 'components', 'pages'];
        
        foreach ($templateDirs as $dir) {
            $dirPath = $skinPath . '/' . $dir;
            if (is_dir($dirPath)) {
                $files = glob($dirPath . '/*.twig');
                $templates[$dir] = array_map('basename', $files);
            }
        }

        return $templates;
    }

    /**
     * Get skin statistics
     */
    public function getSkinStats(): array
    {
        $total = count($this->registeredSkins);
        $valid = 0;
        $invalid = 0;

        foreach ($this->registeredSkins as $skin) {
            if ($this->validateSkinStructure($skin['path'])) {
                $valid++;
            } else {
                $invalid++;
            }
        }

        return [
            'total_skins' => $total,
            'valid_skins' => $valid,
            'invalid_skins' => $invalid,
            'skins_path' => $this->skinsPath
        ];
    }

    /**
     * Refresh skin registry
     */
    public function refresh(): void
    {
        $this->registeredSkins = [];
        $this->skinMetadata = [];
        $this->discoverSkins();
    }

    /**
     * Get skin dependencies
     */
    public function getSkinDependencies(string $name): array
    {
        $skin = $this->getSkin($name);
        if (!$skin) {
            return [];
        }

        $configFile = $skin['path'] . '/skin.json';
        if (!file_exists($configFile)) {
            return [];
        }

        try {
            $config = json_decode(file_get_contents($configFile), true);
            return $config['dependencies'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check skin compatibility
     */
    public function isSkinCompatible(string $name): bool
    {
        $dependencies = $this->getSkinDependencies($name);
        
        // Check PHP version compatibility
        if (isset($dependencies['php'])) {
            $requiredVersion = $dependencies['php'];
            if (version_compare(PHP_VERSION, $requiredVersion, '<')) {
                return false;
            }
        }

        // Check IslamWiki version compatibility
        if (isset($dependencies['islamwiki'])) {
            $requiredVersion = $dependencies['islamwiki'];
            $currentVersion = $this->container->get('version') ?? '0.0.0';
            if (version_compare($currentVersion, $requiredVersion, '<')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get compatible skins
     */
    public function getCompatibleSkins(): array
    {
        $compatible = [];
        
        foreach ($this->registeredSkins as $name => $skin) {
            if ($this->isSkinCompatible($name)) {
                $compatible[$name] = $skin;
            }
        }

        return $compatible;
    }
} 