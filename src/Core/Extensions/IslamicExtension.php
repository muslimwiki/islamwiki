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
 * Islamic Extension Base Class
 *
 * Modernized extension base class that aligns with the new Islamic architecture.
 * Provides comprehensive extension management, Islamic naming conventions,
 * and integration with all 16 core Islamic systems.
 *
 * @category  Core
 * @package   IslamWiki\Core\Extensions
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
abstract class IslamicExtension
{
    /**
     * Extension name.
     */
    protected string $name;

    /**
     * Extension version.
     */
    protected string $version;

    /**
     * Extension description.
     */
    protected string $description;

    /**
     * Extension author.
     */
    protected string $author;

    /**
     * Extension URL.
     */
    protected string $url;

    /**
     * Extension configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

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
     * Whether the extension is enabled.
     */
    protected bool $enabled = false;

    /**
     * Extension status.
     */
    protected string $status = 'inactive';

    /**
     * Extension dependencies.
     *
     * @var array<string>
     */
    protected array $dependencies = [];

    /**
     * Extension hooks.
     *
     * @var array<string, array>
     */
    protected array $hooks = [];

    /**
     * Extension services.
     *
     * @var array<string, mixed>
     */
    protected array $services = [];

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
        $this->initializeExtension();
    }

    /**
     * Initialize the extension.
     *
     * @return void
     */
    protected function initializeExtension(): void
    {
        try {
            $this->loadExtensionInfo();
            $this->validateDependencies();
            $this->initializeServices();
            $this->registerHooks();
            $this->initializeStatistics();
            
            $this->status = 'active';
            $this->logger->info("Extension '{$this->name}' initialized successfully");
            
        } catch (Exception $e) {
            $this->status = 'error';
            $this->logger->error("Failed to initialize extension '{$this->name}': " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Load extension information from extension.json.
     *
     * @return void
     */
    protected function loadExtensionInfo(): void
    {
        $extensionPath = $this->getExtensionPath();
        $configFile = $extensionPath . '/extension.json';

        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON in extension.json: " . json_last_error_msg());
            }

            $this->name = $config['name'] ?? '';
            $this->version = $config['version'] ?? '0.0.1';
            $this->description = $config['description'] ?? '';
            $this->author = $config['author'] ?? '';
            $this->url = $config['url'] ?? '';
            $this->config = $config['config'] ?? [];
            $this->dependencies = $config['dependencies'] ?? [];
            $this->hooks = $config['hooks'] ?? [];
        } else {
            throw new Exception("Extension configuration file not found: {$configFile}");
        }
    }

    /**
     * Validate extension dependencies.
     *
     * @return void
     */
    protected function validateDependencies(): void
    {
        foreach ($this->dependencies as $dependency) {
            if (!$this->container->has($dependency)) {
                throw new Exception("Required dependency not found: {$dependency}");
            }
        }
    }

    /**
     * Initialize extension services.
     *
     * @return void
     */
    protected function initializeServices(): void
    {
        // Get logger and configuration from container
        $this->logger = $this->container->get(ShahidLogger::class);
        $this->configuration = $this->container->get(TadbirConfiguration::class);

        // Initialize extension-specific services
        $this->onInitializeServices();
    }

    /**
     * Register extension hooks.
     *
     * @return void
     */
    protected function registerHooks(): void
    {
        foreach ($this->hooks as $hookName => $hookConfig) {
            $this->registerHook($hookName, $hookConfig);
        }

        // Register extension-specific hooks
        $this->onRegisterHooks();
    }

    /**
     * Register a hook.
     *
     * @param string $hookName Hook name
     * @param array  $hookConfig Hook configuration
     * @return void
     */
    protected function registerHook(string $hookName, array $hookConfig): void
    {
        if (isset($hookConfig['callback']) && method_exists($this, $hookConfig['callback'])) {
            $priority = $hookConfig['priority'] ?? 10;
            $this->container->get('hook.manager')->register($hookName, [$this, $hookConfig['callback']], $priority);
            $this->logger->debug("Registered hook '{$hookName}' for extension '{$this->name}'");
        }
    }

    /**
     * Initialize extension statistics.
     *
     * @return void
     */
    protected function initializeStatistics(): void
    {
        $this->statistics = [
            'initialization_time' => microtime(true),
            'hooks_registered' => count($this->hooks),
            'services_initialized' => count($this->services),
            'dependencies_met' => count($this->dependencies),
            'status' => $this->status,
            'memory_usage' => memory_get_usage(true)
        ];
    }

    /**
     * Get the extension path.
     *
     * @return string
     */
    protected function getExtensionPath(): string
    {
        $reflection = new \ReflectionClass($this);
        return dirname($reflection->getFileName());
    }

    /**
     * Get extension configuration.
     *
     * @param string|null $key Configuration key
     * @param mixed       $default Default value
     * @return mixed
     */
    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? $default;
    }

    /**
     * Set extension configuration.
     *
     * @param string $key Configuration key
     * @param mixed  $value Configuration value
     * @return self
     */
    public function setConfig(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Get extension statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $this->statistics['current_memory_usage'] = memory_get_usage(true);
        $this->statistics['peak_memory_usage'] = memory_get_peak_usage(true);
        $this->statistics['uptime'] = microtime(true) - $this->statistics['initialization_time'];
        
        return $this->statistics;
    }

    /**
     * Get extension status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Check if extension is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the extension.
     *
     * @return self
     */
    public function enable(): self
    {
        $this->enabled = true;
        $this->status = 'active';
        $this->logger->info("Extension '{$this->name}' enabled");
        return $this;
    }

    /**
     * Disable the extension.
     *
     * @return self
     */
    public function disable(): self
    {
        $this->enabled = false;
        $this->status = 'disabled';
        $this->logger->info("Extension '{$this->name}' disabled");
        return $this;
    }

    /**
     * Get extension information.
     *
     * @return array<string, mixed>
     */
    public function getInfo(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'author' => $this->author,
            'url' => $this->url,
            'status' => $this->status,
            'enabled' => $this->enabled,
            'dependencies' => $this->dependencies,
            'hooks' => $this->hooks,
            'statistics' => $this->getStatistics()
        ];
    }

    /**
     * Boot the extension.
     *
     * @return void
     */
    public function boot(): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $this->onBoot();
            $this->logger->info("Extension '{$this->name}' booted successfully");
        } catch (Exception $e) {
            $this->logger->error("Failed to boot extension '{$this->name}': " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Shutdown the extension.
     *
     * @return void
     */
    public function shutdown(): void
    {
        try {
            $this->onShutdown();
            $this->logger->info("Extension '{$this->name}' shutdown successfully");
        } catch (Exception $e) {
            $this->logger->error("Failed to shutdown extension '{$this->name}': " . $e->getMessage());
        }
    }

    /**
     * Get a service from the container.
     *
     * @param string $serviceName Service name
     * @return mixed
     */
    protected function getService(string $serviceName): mixed
    {
        return $this->container->get($serviceName);
    }

    /**
     * Check if a service exists in the container.
     *
     * @param string $serviceName Service name
     * @return bool
     */
    protected function hasService(string $serviceName): bool
    {
        return $this->container->has($serviceName);
    }

    /**
     * Log a message.
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     * @return void
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        $context['extension'] = $this->name;
        $this->logger->log($level, $message, $context);
    }

    // Abstract methods that extensions must implement

    /**
     * Extension-specific service initialization.
     *
     * @return void
     */
    abstract protected function onInitializeServices(): void;

    /**
     * Extension-specific hook registration.
     *
     * @return void
     */
    abstract protected function onRegisterHooks(): void;

    /**
     * Extension boot method.
     *
     * @return void
     */
    abstract protected function onBoot(): void;

    /**
     * Extension shutdown method.
     *
     * @return void
     */
    abstract protected function onShutdown(): void;

    // Optional lifecycle methods that extensions can override

    /**
     * Extension activation method.
     *
     * @return void
     */
    protected function onActivate(): void
    {
        // Override in extensions if needed
    }

    /**
     * Extension deactivation method.
     *
     * @return void
     */
    protected function onDeactivate(): void
    {
        // Override in extensions if needed
    }

    /**
     * Extension update method.
     *
     * @param string $oldVersion Previous version
     * @param string $newVersion New version
     * @return void
     */
    protected function onUpdate(string $oldVersion, string $newVersion): void
    {
        // Override in extensions if needed
    }
} 