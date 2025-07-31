<?php
declare(strict_types=1);

namespace IslamWiki\Core\Extensions;

use IslamWiki\Core\Container;
use IslamWiki\Core\Extensions\Hooks\HookManager;

/**
 * Extension Manager
 * 
 * Manages the loading, initialization, and lifecycle of extensions.
 * Provides a centralized way to discover, load, and manage extensions.
 */
class ExtensionManager
{
    /**
     * @var Container Application container
     */
    private Container $container;

    /**
     * @var HookManager Hook manager instance
     */
    private HookManager $hookManager;

    /**
     * @var array Loaded extensions
     */
    private array $extensions = [];

    /**
     * @var array Extension metadata
     */
    private array $extensionMetadata = [];

    /**
     * @var string Extensions directory path
     */
    private string $extensionsPath;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->hookManager = $container->get(HookManager::class);
        
        // Get the application instance to get the base path
        // If the application is not bound, use a default path
        try {
            $app = $container->get(\IslamWiki\Core\Application::class);
            $this->extensionsPath = $app->basePath('extensions');
        } catch (\Exception $e) {
            // Use a default path if application is not available
            $this->extensionsPath = __DIR__ . '/../../../extensions';
        }
    }

    /**
     * Load all enabled extensions
     */
    public function loadExtensions(): void
    {
        $enabledExtensions = $this->getEnabledExtensions();
        
        foreach ($enabledExtensions as $extensionName) {
            $this->loadExtension($extensionName);
        }
    }

    /**
     * Load a specific extension
     *
     * @param string $extensionName The name of the extension
     * @return bool True if the extension was loaded successfully
     */
    public function loadExtension(string $extensionName): bool
    {
        try {
            // Check if extension directory exists
            $extensionPath = $this->extensionsPath . '/' . $extensionName;
            if (!is_dir($extensionPath)) {
                error_log("Extension directory not found: {$extensionPath}");
                return false;
            }

            // Load extension configuration
            $configFile = $extensionPath . '/extension.json';
            if (!file_exists($configFile)) {
                error_log("Extension configuration not found: {$configFile}");
                return false;
            }

            $config = json_decode(file_get_contents($configFile), true);
            if (!$config) {
                error_log("Invalid extension configuration: {$configFile}");
                return false;
            }

            // Validate required fields
            if (!isset($config['name'], $config['version'], $config['main'])) {
                error_log("Missing required fields in extension configuration: {$configFile}");
                return false;
            }

            // Load the main extension file
            $mainFile = $extensionPath . '/' . $config['main'];
            if (!file_exists($mainFile)) {
                error_log("Extension main file not found: {$mainFile}");
                return false;
            }

            require_once $mainFile;

            // Get the extension class name
            $className = $config['class'] ?? $extensionName;
            if (!class_exists($className)) {
                error_log("Extension class not found: {$className}");
                return false;
            }

            // Create extension instance
            $extension = new $className($this->container);
            
            // Initialize the extension
            $extension->initialize();

            // Store the extension
            $this->extensions[$extensionName] = $extension;
            $this->extensionMetadata[$extensionName] = $config;

            error_log("Extension loaded successfully: {$extensionName}");
            return true;

        } catch (\Exception $e) {
            error_log("Error loading extension {$extensionName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all available extensions
     *
     * @return array Array of extension names
     */
    public function getAvailableExtensions(): array
    {
        $extensions = [];

        if (!is_dir($this->extensionsPath)) {
            return $extensions;
        }

        $directories = glob($this->extensionsPath . '/*', GLOB_ONLYDIR);
        
        foreach ($directories as $directory) {
            $extensionName = basename($directory);
            $configFile = $directory . '/extension.json';
            
            if (file_exists($configFile)) {
                $extensions[] = $extensionName;
            }
        }

        return $extensions;
    }

    /**
     * Get enabled extensions from configuration
     *
     * @return array Array of enabled extension names
     */
    public function getEnabledExtensions(): array
    {
        // This would typically read from a configuration file
        // For now, we'll return all available extensions
        return $this->getAvailableExtensions();
    }

    /**
     * Get a loaded extension
     *
     * @param string $extensionName The name of the extension
     * @return Extension|null The extension instance or null if not found
     */
    public function getExtension(string $extensionName): ?Extension
    {
        return $this->extensions[$extensionName] ?? null;
    }

    /**
     * Get all loaded extensions
     *
     * @return array Array of loaded extensions
     */
    public function getLoadedExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Check if an extension is loaded
     *
     * @param string $extensionName The name of the extension
     * @return bool True if the extension is loaded
     */
    public function isExtensionLoaded(string $extensionName): bool
    {
        return isset($this->extensions[$extensionName]);
    }

    /**
     * Get extension metadata
     *
     * @param string $extensionName The name of the extension
     * @return array|null Extension metadata or null if not found
     */
    public function getExtensionMetadata(string $extensionName): ?array
    {
        return $this->extensionMetadata[$extensionName] ?? null;
    }

    /**
     * Get all extension metadata
     *
     * @return array Array of all extension metadata
     */
    public function getAllExtensionMetadata(): array
    {
        return $this->extensionMetadata;
    }

    /**
     * Enable an extension
     *
     * @param string $extensionName The name of the extension
     * @return bool True if the extension was enabled successfully
     */
    public function enableExtension(string $extensionName): bool
    {
        if ($this->isExtensionLoaded($extensionName)) {
            return true; // Already loaded
        }

        return $this->loadExtension($extensionName);
    }

    /**
     * Disable an extension
     *
     * @param string $extensionName The name of the extension
     * @return bool True if the extension was disabled successfully
     */
    public function disableExtension(string $extensionName): bool
    {
        if (!isset($this->extensions[$extensionName])) {
            return false;
        }

        $extension = $this->extensions[$extensionName];
        $extension->disable();

        unset($this->extensions[$extensionName]);
        unset($this->extensionMetadata[$extensionName]);

        return true;
    }

    /**
     * Get extension statistics
     *
     * @return array Statistics about loaded extensions
     */
    public function getStatistics(): array
    {
        $stats = [
            'total_extensions' => count($this->extensions),
            'available_extensions' => count($this->getAvailableExtensions()),
            'enabled_extensions' => count($this->getEnabledExtensions()),
            'extensions' => [],
        ];

        foreach ($this->extensions as $name => $extension) {
            $stats['extensions'][$name] = $extension->toArray();
        }

        return $stats;
    }

    /**
     * Get the hook manager instance
     *
     * @return HookManager The hook manager
     */
    public function getHookManager(): HookManager
    {
        return $this->hookManager;
    }

    /**
     * Get the container instance
     *
     * @return Container The container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
} 