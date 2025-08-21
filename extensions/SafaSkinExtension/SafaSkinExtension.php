<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\SafaSkinExtension;

use IslamWiki\Core\Extensions\ExtensionInterface;
use IslamWiki\Core\Extensions\ExtensionManager;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinManager;
use IslamWiki\Extensions\SafaSkinExtension\Services\TemplateEngine;
use IslamWiki\Extensions\SafaSkinExtension\Services\AssetManager;
use IslamWiki\Extensions\SafaSkinExtension\Services\SkinRegistry;

/**
 * SafaSkinExtension - Unified Skin Management System
 * 
 * This extension consolidates all visual elements (layouts, components, pages)
 * into a unified skin system that provides consistent, professional appearance
 * across all pages.
 * 
 * @package IslamWiki\Extensions\SafaSkinExtension
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class SafaSkinExtension implements ExtensionInterface
{
    private ExtensionManager $manager;
    private AsasContainer $container;
    private SkinManager $skinManager;
    private TemplateEngine $templateEngine;
    private AssetManager $assetManager;
    private SkinRegistry $skinRegistry;

    public function __construct(ExtensionManager $manager, AsasContainer $container)
    {
        $this->manager = $manager;
        $this->container = $container;
    }

    /**
     * Get extension name
     */
    public function getName(): string
    {
        return 'SafaSkinExtension';
    }

    /**
     * Get extension version
     */
    public function getVersion(): string
    {
        return '0.0.1';
    }

    /**
     * Get extension description
     */
    public function getDescription(): string
    {
        return 'Unified skin management system that consolidates all visual elements into skins';
    }

    /**
     * Install the extension
     */
    public function install(): bool
    {
        try {
            // Create necessary directories
            $this->createDirectories();
            
            // Register services
            $this->registerServices();
            
            // Initialize skin system
            $this->initializeSkinSystem();
            
            return true;
        } catch (\Exception $e) {
            error_log('SafaSkinExtension installation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Uninstall the extension
     */
    public function uninstall(): bool
    {
        try {
            // Clean up services
            $this->cleanupServices();
            
            return true;
        } catch (\Exception $e) {
            error_log('SafaSkinExtension uninstallation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate the extension
     */
    public function activate(): bool
    {
        try {
            // Register hooks
            $this->registerHooks();
            
            // Load active skin
            $this->loadActiveSkin();
            
            return true;
        } catch (\Exception $e) {
            error_log('SafaSkinExtension activation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deactivate the extension
     */
    public function deactivate(): bool
    {
        try {
            // Unregister hooks
            $this->unregisterHooks();
            
            return true;
        } catch (\Exception $e) {
            error_log('SafaSkinExtension deactivation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Register extension hooks
     */
    public function registerHooks(): void
    {
        // Register action hooks
        $this->manager->addAction('init', [$this, 'onInit']);
        $this->manager->addAction('template_render', [$this, 'onTemplateRender']);
        $this->manager->addAction('asset_enqueue', [$this, 'onAssetEnqueue']);
        
        // Register filter hooks
        $this->manager->addFilter('template_path', [$this, 'filterTemplatePath']);
        $this->manager->addFilter('skin_assets', [$this, 'filterSkinAssets']);
    }

    /**
     * Unregister extension hooks
     */
    public function unregisterHooks(): void
    {
        // Unregister all hooks
        $this->manager->removeAction('init', [$this, 'onInit']);
        $this->manager->removeAction('template_render', [$this, 'onTemplateRender']);
        $this->manager->removeAction('asset_enqueue', [$this, 'onAssetEnqueue']);
        
        $this->manager->removeFilter('template_path', [$this, 'filterTemplatePath']);
        $this->manager->removeFilter('skin_assets', [$this, 'filterSkinAssets']);
    }

    /**
     * System initialization hook
     */
    public function onInit(): void
    {
        // Initialize skin system
        $this->initializeSkinSystem();
        
        // Load default skin if none active
        if (!$this->skinManager->hasActiveSkin()) {
            $this->skinManager->setActiveSkin('Bismillah');
        }
    }

    /**
     * Template rendering hook
     */
    public function onTemplateRender(string $template, array $data = []): void
    {
        // Process template through skin system
        $this->templateEngine->processTemplate($template, $data);
    }

    /**
     * Asset enqueue hook
     */
    public function onAssetEnqueue(array $assets): void
    {
        // Load skin-specific assets
        $this->assetManager->enqueueSkinAssets($assets);
    }

    /**
     * Filter template path to use skin system
     */
    public function filterTemplatePath(string $template): string
    {
        return $this->templateEngine->resolveTemplatePath($template);
    }

    /**
     * Filter skin assets
     */
    public function filterSkinAssets(array $assets): array
    {
        return $this->assetManager->filterAssets($assets);
    }

    /**
     * Create necessary directories
     */
    private function createDirectories(): void
    {
        $directories = [
            'skins/Bismillah/layouts',
            'skins/Bismillah/components',
            'skins/Bismillah/pages',
            'skins/Bismillah/css',
            'skins/Bismillah/js',
            'skins/Bismillah/assets'
        ];

        foreach ($directories as $directory) {
            $path = $this->container->get('base_path') . '/' . $directory;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * Register extension services
     */
    private function registerServices(): void
    {
        // Register skin manager
        $this->container->set('skin.manager', function () {
            return new SkinManager($this->container);
        });

        // Register template engine
        $this->container->set('skin.template_engine', function () {
            return new TemplateEngine($this->container);
        });

        // Register asset manager
        $this->container->set('skin.asset_manager', function () {
            return new AssetManager($this->container);
        });

        // Register skin registry
        $this->container->set('skin.registry', function () {
            return new SkinRegistry($this->container);
        });
    }

    /**
     * Initialize skin system
     */
    private function initializeSkinSystem(): void
    {
        // Get service instances
        $this->skinManager = $this->container->get('skin.manager');
        $this->templateEngine = $this->container->get('skin.template_engine');
        $this->assetManager = $this->container->get('skin.asset_manager');
        $this->skinRegistry = $this->container->get('skin.registry');

        // Discover available skins
        $this->skinRegistry->discoverSkins();

        // Register default skins
        $this->registerDefaultSkins();
    }

    /**
     * Register default skins
     */
    private function registerDefaultSkins(): void
    {
        // Register Bismillah skin
        $this->skinRegistry->registerSkin('Bismillah', [
            'name' => 'Bismillah',
            'display_name' => 'Bismillah',
            'description' => 'Default Islamic-themed skin with traditional design',
            'version' => '0.0.28',
            'author' => 'IslamWiki Team',
            'path' => 'skins/Bismillah'
        ]);

        // Register Muslim skin
        $this->skinRegistry->registerSkin('Muslim', [
            'name' => 'Muslim',
            'display_name' => 'Muslim',
            'description' => 'Alternative Islamic skin with modern design',
            'version' => '0.0.1',
            'author' => 'IslamWiki Team',
            'path' => 'skins/Muslim'
        ]);
    }

    /**
     * Load active skin
     */
    private function loadActiveSkin(): void
    {
        $activeSkin = $this->skinManager->getActiveSkinName();
        if ($activeSkin) {
            $this->skinManager->setActiveSkin($activeSkin);
        }
    }

    /**
     * Clean up services
     */
    private function cleanupServices(): void
    {
        // Remove services from container
        $this->container->remove('skin.manager');
        $this->container->remove('skin.template_engine');
        $this->container->remove('skin.asset_manager');
        $this->container->remove('skin.registry');
    }

    /**
     * Initialize the extension
     */
    public function init(): void
    {
        try {
            // Register services
            $this->registerServices();
            
            // Initialize skin system
            $this->initializeSkinSystem();
            
            // Load active skin
            $this->loadActiveSkin();
        } catch (\Exception $e) {
            error_log('SafaSkinExtension initialization failed: ' . $e->getMessage());
        }
    }
} 