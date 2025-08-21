<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SafaSkinExtension\Services;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Configuration\TadbirConfiguration;

/**
 * Skin Manager Service
 * 
 * Manages active skins, skin switching, and skin configuration.
 * 
 * @package IslamWiki\Extensions\SafaSkinExtension\Services
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SkinManager
{
    private AsasContainer $container;
    private TadbirConfiguration $config;
    private array $skins = [];
    private ?string $activeSkin = null;
    private array $skinConfig = [];

    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
        $this->config = $container->get('config');
        $this->loadSkinConfiguration();
    }

    /**
     * Load skin configuration from settings
     */
    private function loadSkinConfiguration(): void
    {
        $this->skinConfig = $this->config->get('skins', []);
        $this->activeSkin = $this->skinConfig['active'] ?? 'Bismillah';
    }

    /**
     * Get all available skins
     */
    public function getAvailableSkins(): array
    {
        return $this->skins;
    }

    /**
     * Get a specific skin by name
     */
    public function getSkin(string $name): ?array
    {
        return $this->skins[$name] ?? null;
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
        return $this->skins[$this->activeSkin] ?? null;
    }

    /**
     * Check if a skin is currently active
     */
    public function hasActiveSkin(): bool
    {
        return $this->activeSkin !== null && isset($this->skins[$this->activeSkin]);
    }

    /**
     * Set the active skin
     */
    public function setActiveSkin(string $name): bool
    {
        if (!isset($this->skins[$name])) {
            return false;
        }

        $this->activeSkin = $name;
        
        // Update configuration
        $this->skinConfig['active'] = $name;
        $this->config->set('skins', $this->skinConfig);
        
        // Trigger skin change event
        $this->triggerSkinChangeEvent($name);
        
        return true;
    }

    /**
     * Register a new skin
     */
    public function registerSkin(string $name, array $config): bool
    {
        if (empty($config['name']) || empty($config['path'])) {
            return false;
        }

        $this->skins[$name] = array_merge($config, [
            'registered_at' => date('Y-m-d H:i:s'),
            'enabled' => true
        ]);

        return true;
    }

    /**
     * Unregister a skin
     */
    public function unregisterSkin(string $name): bool
    {
        if (!isset($this->skins[$name])) {
            return false;
        }

        // Don't allow unregistering the active skin
        if ($this->activeSkin === $name) {
            return false;
        }

        unset($this->skins[$name]);
        return true;
    }

    /**
     * Enable a skin
     */
    public function enableSkin(string $name): bool
    {
        if (!isset($this->skins[$name])) {
            return false;
        }

        $this->skins[$name]['enabled'] = true;
        return true;
    }

    /**
     * Disable a skin
     */
    public function disableSkin(string $name): bool
    {
        if (!isset($this->skins[$name])) {
            return false;
        }

        // Don't allow disabling the active skin
        if ($this->activeSkin === $name) {
            return false;
        }

        $this->skins[$name]['enabled'] = false;
        return true;
    }

    /**
     * Get skin assets (CSS, JS, images)
     */
    public function getSkinAssets(string $name): array
    {
        $skin = $this->getSkin($name);
        if (!$skin) {
            return [];
        }

        $assets = [
            'css' => [],
            'js' => [],
            'images' => []
        ];

        $skinPath = $skin['path'];

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
     * Get active skin assets
     */
    public function getActiveSkinAssets(): array
    {
        if (!$this->activeSkin) {
            return [];
        }

        return $this->getSkinAssets($this->activeSkin);
    }

    /**
     * Validate skin configuration
     */
    public function validateSkin(string $name): bool
    {
        $skin = $this->getSkin($name);
        if (!$skin) {
            return false;
        }

        // Check required fields
        $required = ['name', 'path', 'version'];
        foreach ($required as $field) {
            if (empty($skin[$field])) {
                return false;
            }
        }

        // Check if skin directory exists
        if (!is_dir($skin['path'])) {
            return false;
        }

        // Check if skin.json exists
        $configFile = $skin['path'] . '/skin.json';
        if (!file_exists($configFile)) {
            return false;
        }

        return true;
    }

    /**
     * Get skin configuration
     */
    public function getSkinConfiguration(string $name): array
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
            return is_array($config) ? $config : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Trigger skin change event
     */
    private function triggerSkinChangeEvent(string $skinName): void
    {
        // This would integrate with the event system
        // For now, we'll just log the change
        error_log("Active skin changed to: {$skinName}");
    }

    /**
     * Get skin statistics
     */
    public function getSkinStats(): array
    {
        $total = count($this->skins);
        $enabled = 0;
        $disabled = 0;

        foreach ($this->skins as $skin) {
            if ($skin['enabled'] ?? false) {
                $enabled++;
            } else {
                $disabled++;
            }
        }

        return [
            'total' => $total,
            'enabled' => $enabled,
            'disabled' => $disabled,
            'active' => $this->activeSkin
        ];
    }
} 