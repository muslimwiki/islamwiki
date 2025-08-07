<?php

declare(strict_types=1);

namespace IslamWiki\Core\Extensions;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Extensions\Hooks\HookManager;

/**
 * Base Extension Class
 *
 * All extensions should extend this class to integrate with the IslamWiki system.
 * Provides common functionality for extension loading, configuration, and hooks.
 */
abstract class Extension
{
    /**
     * @var string Extension name
     */
    protected string $name;

    /**
     * @var string Extension version
     */
    protected string $version;

    /**
     * @var string Extension description
     */
    protected string $description;

    /**
     * @var string Extension author
     */
    protected string $author;

    /**
     * @var string Extension URL
     */
    protected string $url;

    /**
     * @var array Extension configuration
     */
    protected array $config = [];

    /**
     * @var Container Application container
     */
    protected AsasContainer $container;

    /**
     * @var HookManager Hook manager instance
     */
    protected HookManager $hookManager;

    /**
     * @var bool Whether the extension is enabled
     */
    protected bool $enabled = false;

    /**
     * Constructor
     */
    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
        $this->hookManager = $container->get(HookManager::class);
        $this->loadExtensionInfo();
    }

    /**
     * Load extension information from extension.json
     */
    protected function loadExtensionInfo(): void
    {
        $extensionPath = $this->getExtensionPath();
        $configFile = $extensionPath . '/extension.json';

        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);

            $this->name = $config['name'] ?? '';
            $this->version = $config['version'] ?? '1.0.0';
            $this->description = $config['description'] ?? '';
            $this->author = $config['author'] ?? '';
            $this->url = $config['url'] ?? '';
            $this->config = $config['config'] ?? [];
        }
    }

    /**
     * Get the extension path
     */
    protected function getExtensionPath(): string
    {
        $reflection = new \ReflectionClass($this);
        return dirname($reflection->getFileName());
    }

    /**
     * Initialize the extension
     * Called when the extension is loaded
     */
    public function initialize(): void
    {
        $this->enabled = true;
        $this->registerHooks();
        $this->loadResources();
        $this->onInitialize();
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        // Override in child classes to register specific hooks
    }

    /**
     * Load extension resources (CSS, JS, etc.)
     */
    protected function loadResources(): void
    {
        $extensionPath = $this->getExtensionPath();

        // Load CSS files
        $cssPath = $extensionPath . '/modules/css';
        if (is_dir($cssPath)) {
            $this->loadCssFiles($cssPath);
        }

        // Load JS files
        $jsPath = $extensionPath . '/modules/js';
        if (is_dir($jsPath)) {
            $this->loadJsFiles($jsPath);
        }
    }

    /**
     * Load CSS files from the given path
     */
    protected function loadCssFiles(string $path): void
    {
        $files = glob($path . '/*.css');
        foreach ($files as $file) {
            $this->addCssFile($file);
        }
    }

    /**
     * Load JS files from the given path
     */
    protected function loadJsFiles(string $path): void
    {
        $files = glob($path . '/*.js');
        foreach ($files as $file) {
            $this->addJsFile($file);
        }
    }

    /**
     * Add a CSS file to the page
     */
    protected function addCssFile(string $file): void
    {
        // This will be implemented to add CSS to the page head
        // For now, we'll just log it
        error_log("Loading CSS file: $file");
    }

    /**
     * Add a JS file to the page
     */
    protected function addJsFile(string $file): void
    {
        // This will be implemented to add JS to the page
        // For now, we'll just log it
        error_log("Loading JS file: $file");
    }

    /**
     * Called when the extension is initialized
     * Override in child classes
     */
    protected function onInitialize(): void
    {
        // Override in child classes
    }

    /**
     * Called when the extension is disabled
     */
    public function disable(): void
    {
        $this->enabled = false;
        $this->onDisable();
    }

    /**
     * Called when the extension is disabled
     * Override in child classes
     */
    protected function onDisable(): void
    {
        // Override in child classes
    }

    /**
     * Get extension name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get extension version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get extension description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get extension author
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Get extension URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get extension configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get a specific configuration value
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Check if the extension is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the hook manager instance
     */
    public function getHookManager(): HookManager
    {
        return $this->hookManager;
    }

    /**
     * Get extension information as array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'author' => $this->author,
            'url' => $this->url,
            'enabled' => $this->enabled,
            'config' => $this->config,
        ];
    }
}
