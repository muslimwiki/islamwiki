<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @category  Core
 * @package   IslamWiki\Core\Extensions
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Extensions;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Configuration\TadbirConfiguration;
use Exception;

/**
 * Islamic Extension Manager
 *
 * Modernized extension manager that integrates with the new Islamic architecture.
 * Provides comprehensive extension lifecycle management, dependency resolution,
 * and integration with all 16 core Islamic systems.
 *
 * @category  Core
 * @package   IslamWiki\Core\Extensions
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class IslamicExtensionManager
{
    /**
     * Application container.
     */
    protected AsasContainer $container;

    /**
     * Logger instance.
     */
    protected ShahidLogger $logger;

    /**
     * Configuration manager.
     */
    protected TadbirConfiguration $configuration;

    /**
     * Registered extensions.
     *
     * @var array<string, IslamicExtension>
     */
    protected array $extensions = [];

    /**
     * Extension configurations.
     *
     * @var array<string, array>
     */
    protected array $extensionConfigs = [];

    /**
     * Extension dependencies.
     *
     * @var array<string, array>
     */
    protected array $extensionDependencies = [];

    /**
     * Extension statistics.
     *
     * @var array<string, mixed>
     */
    protected array $statistics = [];

    /**
     * Constructor.
     *
     * @param AsasContainer $container Application container
     */
    public function __construct(AsasContainer $container)
    {
        $this->container = $container;
        $this->logger = $container->get(ShahidLogger::class);
        $this->configuration = $container->get(TadbirConfiguration::class);
        $this->initializeManager();
    }

    /**
     * Initialize the extension manager.
     *
     * @return void
     */
    protected function initializeManager(): void
    {
        $this->statistics = [
            'total_extensions' => 0,
            'active_extensions' => 0,
            'disabled_extensions' => 0,
            'failed_extensions' => 0,
            'total_hooks' => 0,
            'total_services' => 0
        ];

        $this->logger->info('Islamic Extension Manager initialized');
    }

    /**
     * Discover and load extensions.
     *
     * @return void
     */
    public function discoverExtensions(): void
    {
        $extensionsPath = $this->getExtensionsPath();
        
        if (!is_dir($extensionsPath)) {
            $this->logger->warning("Extensions directory not found: {$extensionsPath}");
            return;
        }

        $extensionDirs = glob($extensionsPath . '/*', GLOB_ONLYDIR);
        
        foreach ($extensionDirs as $extensionDir) {
            $extensionName = basename($extensionDir);
            $this->loadExtension($extensionName);
        }

        $this->logger->info("Discovered " . count($this->extensions) . " extensions");
    }

    /**
     * Load a specific extension.
     *
     * @param string $extensionName Extension name
     * @return bool
     */
    public function loadExtension(string $extensionName): bool
    {
        try {
            $extensionPath = $this->getExtensionsPath() . '/' . $extensionName;
            $extensionFile = $extensionPath . '/' . $extensionName . '.php';
            $configFile = $extensionPath . '/extension.json';

            if (!file_exists($extensionFile)) {
                $this->logger->warning("Extension file not found: {$extensionFile}");
                return false;
            }

            if (!file_exists($configFile)) {
                $this->logger->warning("Extension config not found: {$configFile}");
                return false;
            }

            // Load extension configuration
            $config = json_decode(file_get_contents($configFile), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON in extension config: " . json_last_error_msg());
            }

            $this->extensionConfigs[$extensionName] = $config;
            $this->extensionDependencies[$extensionName] = $config['dependencies'] ?? [];

            // Load extension class
            require_once $extensionFile;
            $className = "IslamWiki\\Extensions\\{$extensionName}\\{$extensionName}";

            if (!class_exists($className)) {
                throw new Exception("Extension class not found: {$className}");
            }

            // Create extension instance
            $extension = new $className($this->container);
            $this->extensions[$extensionName] = $extension;

            $this->statistics['total_extensions']++;
            $this->logger->info("Extension '{$extensionName}' loaded successfully");

            return true;

        } catch (Exception $e) {
            $this->statistics['failed_extensions']++;
            $this->logger->error("Failed to load extension '{$extensionName}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Initialize all loaded extensions.
     *
     * @return void
     */
    public function initializeExtensions(): void
    {
        $this->logger->info("Initializing " . count($this->extensions) . " extensions");

        foreach ($this->extensions as $extensionName => $extension) {
            try {
                $extension->enable();
                $this->statistics['active_extensions']++;
                $this->logger->debug("Extension '{$extensionName}' initialized");
            } catch (Exception $e) {
                $this->statistics['failed_extensions']++;
                $this->logger->error("Failed to initialize extension '{$extensionName}': " . $e->getMessage());
            }
        }
    }

    /**
     * Boot all active extensions.
     *
     * @return void
     */
    public function bootExtensions(): void
    {
        $this->logger->info("Booting " . $this->statistics['active_extensions'] . " active extensions");

        foreach ($this->extensions as $extensionName => $extension) {
            if ($extension->isEnabled()) {
                try {
                    $extension->boot();
                    $this->logger->debug("Extension '{$extensionName}' booted");
                } catch (Exception $e) {
                    $this->logger->error("Failed to boot extension '{$extensionName}': " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Shutdown all extensions.
     *
     * @return void
     */
    public function shutdownExtensions(): void
    {
        $this->logger->info("Shutting down " . count($this->extensions) . " extensions");

        foreach ($this->extensions as $extensionName => $extension) {
            try {
                $extension->shutdown();
                $this->logger->debug("Extension '{$extensionName}' shutdown");
            } catch (Exception $e) {
                $this->logger->error("Failed to shutdown extension '{$extensionName}': " . $e->getMessage());
            }
        }
    }

    /**
     * Get extension by name.
     *
     * @param string $extensionName Extension name
     * @return IslamicExtension|null
     */
    public function getExtension(string $extensionName): ?IslamicExtension
    {
        return $this->extensions[$extensionName] ?? null;
    }

    /**
     * Get all extensions.
     *
     * @return array<string, IslamicExtension>
     */
    public function getAllExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get active extensions.
     *
     * @return array<string, IslamicExtension>
     */
    public function getActiveExtensions(): array
    {
        return array_filter($this->extensions, fn($ext) => $ext->isEnabled());
    }

    /**
     * Check if extension is loaded.
     *
     * @param string $extensionName Extension name
     * @return bool
     */
    public function hasExtension(string $extensionName): bool
    {
        return isset($this->extensions[$extensionName]);
    }

    /**
     * Enable an extension.
     *
     * @param string $extensionName Extension name
     * @return bool
     */
    public function enableExtension(string $extensionName): bool
    {
        if (!isset($this->extensions[$extensionName])) {
            $this->logger->warning("Extension '{$extensionName}' not found");
            return false;
        }

        try {
            $extension = $this->extensions[$extensionName];
            $extension->enable();
            
            $this->statistics['active_extensions']++;
            $this->statistics['disabled_extensions']--;
            
            $this->logger->info("Extension '{$extensionName}' enabled");
            return true;

        } catch (Exception $e) {
            $this->logger->error("Failed to enable extension '{$extensionName}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable an extension.
     *
     * @param string $extensionName Extension name
     * @return bool
     */
    public function disableExtension(string $extensionName): bool
    {
        if (!isset($this->extensions[$extensionName])) {
            $this->logger->warning("Extension '{$extensionName}' not found");
            return false;
        }

        try {
            $extension = $this->extensions[$extensionName];
            $extension->disable();
            
            $this->statistics['active_extensions']--;
            $this->statistics['disabled_extensions']++;
            
            $this->logger->info("Extension '{$extensionName}' disabled");
            return true;

        } catch (Exception $e) {
            $this->logger->error("Failed to disable extension '{$extensionName}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get extension configuration.
     *
     * @param string $extensionName Extension name
     * @return array|null
     */
    public function getExtensionConfig(string $extensionName): ?array
    {
        return $this->extensionConfigs[$extensionName] ?? null;
    }

    /**
     * Get extension dependencies.
     *
     * @param string $extensionName Extension name
     * @return array
     */
    public function getExtensionDependencies(string $extensionName): array
    {
        return $this->extensionDependencies[$extensionName] ?? [];
    }

    /**
     * Check extension dependencies.
     *
     * @param string $extensionName Extension name
     * @return array
     */
    public function checkExtensionDependencies(string $extensionName): array
    {
        $dependencies = $this->getExtensionDependencies($extensionName);
        $results = [];

        foreach ($dependencies as $dependency) {
            $results[$dependency] = [
                'required' => true,
                'available' => $this->container->has($dependency),
                'status' => $this->container->has($dependency) ? 'available' : 'missing'
            ];
        }

        return $results;
    }

    /**
     * Get extension statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $this->statistics['total_hooks'] = array_sum(array_map(fn($ext) => count($ext->getInfo()['hooks']), $this->extensions));
        $this->statistics['total_services'] = array_sum(array_map(fn($ext) => count($ext->getInfo()['services'] ?? []), $this->extensions));
        
        return $this->statistics;
    }

    /**
     * Get extensions by layer.
     *
     * @return array<string, array>
     */
    public function getExtensionsByLayer(): array
    {
        $layers = [
            'foundation' => [],
            'infrastructure' => [],
            'application' => [],
            'user_interface' => []
        ];

        foreach ($this->extensions as $extensionName => $extension) {
            $config = $this->getExtensionConfig($extensionName);
            $layer = $config['layer'] ?? 'application';
            
            if (isset($layers[$layer])) {
                $layers[$layer][$extensionName] = $extension;
            }
        }

        return $layers;
    }

    /**
     * Validate extension compatibility.
     *
     * @param string $extensionName Extension name
     * @return array
     */
    public function validateExtensionCompatibility(string $extensionName): array
    {
        $config = $this->getExtensionConfig($extensionName);
        if (!$config) {
            return ['compatible' => false, 'errors' => ['Extension not found']];
        }

        $errors = [];
        $warnings = [];

        // Check version compatibility
        $requiredVersion = $config['requires']['islamwiki'] ?? '0.0.1';
        $currentVersion = '0.0.1.1'; // This should come from application config
        
        if (version_compare($currentVersion, $requiredVersion, '<')) {
            $errors[] = "Requires IslamWiki version {$requiredVersion} or higher";
        }

        // Check PHP version compatibility
        $requiredPhpVersion = $config['requires']['php'] ?? '8.0.0';
        if (version_compare(PHP_VERSION, $requiredPhpVersion, '<')) {
            $errors[] = "Requires PHP version {$requiredPhpVersion} or higher";
        }

        // Check dependencies
        $dependencies = $this->checkExtensionDependencies($extensionName);
        foreach ($dependencies as $dep => $info) {
            if (!$info['available']) {
                $errors[] = "Missing dependency: {$dep}";
            }
        }

        return [
            'compatible' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'dependencies' => $dependencies
        ];
    }

    /**
     * Get extensions path.
     *
     * @return string
     */
    protected function getExtensionsPath(): string
    {
        return $this->container->get('app')->basePath('extensions');
    }

    /**
     * Update extension statistics.
     *
     * @return void
     */
    protected function updateStatistics(): void
    {
        $this->statistics['active_extensions'] = count($this->getActiveExtensions());
        $this->statistics['disabled_extensions'] = $this->statistics['total_extensions'] - $this->statistics['active_extensions'];
    }
} 