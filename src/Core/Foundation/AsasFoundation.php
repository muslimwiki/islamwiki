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
 * @package   IslamWiki\Core\Foundation
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Foundation;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Configuration\TadbirConfiguration;
use IslamWiki\Core\Logging\ShahidLogger;
use Exception;

/**
 * AsasFoundation (أساس) - Foundation Services System
 *
 * Asas means "Foundation" in Arabic. This class provides core foundation services
 * and utilities that are essential for the operation of all Islamic-named systems
 * in IslamWiki.
 *
 * This includes system initialization, core service management, utility functions,
 * and foundational operations that other systems depend on.
 *
 * @category  Core
 * @package   IslamWiki\Core\Foundation
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class AsasFoundation
{
    /**
     * The application's service container.
     */
    protected AsasContainer $container;

    /**
     * The configuration system.
     */
    protected TadbirConfiguration $configuration;

    /**
     * The logging system.
     */
    protected ShahidLogger $logger;

    /**
     * The application base path.
     */
    protected string $basePath;

    /**
     * Whether the foundation has been initialized.
     */
    protected bool $initialized = false;

    /**
     * Core services that must be available.
     *
     * @var array<string>
     */
    protected array $coreServices = [
        'config',
        'logger',
        'database',
        'session',
        'auth',
        'router',
        'view',
        'cache',
        'queue',
        'search',
        'api',
        'formatter',
        'security',
        'knowledge',
        'community',
        'media',
        'wiki',
        'language',
        'http',
        'routing',
        'logging',
        'utils',
        'skin',
        'extensions',
        'islamic',
        'error',
        'caching',
        'container',
        'support'
    ];

    /**
     * Constructor.
     *
     * @param AsasContainer $container The service container
     * @param string        $basePath  The application base path
     */
    public function __construct(AsasContainer $container, string $basePath = '')
    {
        $this->container = $container;
        $this->basePath = $basePath ?: $this->detectBasePath();
    }

    /**
     * Initialize the foundation system.
     *
     * @return self
     * @throws Exception If initialization fails
     */
    public function initialize(): self
    {
        if ($this->initialized) {
            return $this;
        }

        try {
            $this->logger = new ShahidLogger($this->basePath . '/logs');
            $this->configuration = new TadbirConfiguration($this->logger);

            $this->registerCoreServices();
            $this->validateCoreServices();
            $this->initializeCoreServices();

            $this->initialized = true;
            $this->logger->info('AsasFoundation initialized successfully');

        } catch (Exception $e) {
            if (isset($this->logger)) {
                $this->logger->error('Failed to initialize AsasFoundation: ' . $e->getMessage());
            }
            throw $e;
        }

        return $this;
    }

    /**
     * Register core services with the container.
     *
     * @return self
     */
    protected function registerCoreServices(): self
    {
        // Register configuration service
        $this->container->set('config', $this->configuration);
        $this->container->alias('configuration', 'config');

        // Register logger service
        $this->container->set('logger', $this->logger);
        $this->container->alias('shahid', 'logger');

        // Register other core services as placeholders
        foreach ($this->coreServices as $service) {
            if (!$this->container->has($service)) {
                $this->container->set($service, null);
            }
        }

        return $this;
    }

    /**
     * Validate that all core services are available.
     *
     * @return self
     * @throws Exception If validation fails
     */
    protected function validateCoreServices(): self
    {
        $missingServices = [];

        foreach ($this->coreServices as $service) {
            if (!$this->container->has($service)) {
                $missingServices[] = $service;
            }
        }

        if (!empty($missingServices)) {
            throw new Exception('Missing core services: ' . implode(', ', $missingServices));
        }

        return $this;
    }

    /**
     * Initialize core services.
     *
     * @return self
     */
    protected function initializeCoreServices(): self
    {
        // Configuration is already loaded in constructor
        // Logger is already initialized in constructor

        return $this;
    }

    /**
     * Get the service container.
     *
     * @return AsasContainer
     */
    public function getContainer(): AsasContainer
    {
        return $this->container;
    }

    /**
     * Get the configuration system.
     *
     * @return TadbirConfiguration
     */
    public function getConfiguration(): TadbirConfiguration
    {
        return $this->configuration;
    }

    /**
     * Get the logging system.
     *
     * @return ShahidLogger
     */
    public function getLogger(): ShahidLogger
    {
        return $this->logger;
    }

    /**
     * Get the application base path.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Check if the foundation has been initialized.
     *
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Get a service from the container.
     *
     * @param string $id Service identifier
     * @return mixed
     */
    public function getService(string $id): mixed
    {
        return $this->container->get($id);
    }

    /**
     * Check if a service exists in the container.
     *
     * @param string $id Service identifier
     * @return bool
     */
    public function hasService(string $id): bool
    {
        return $this->container->has($id);
    }

    /**
     * Register a service with the container.
     *
     * @param string $id      Service identifier
     * @param mixed  $service Service definition
     * @return self
     */
    public function registerService(string $id, mixed $service): self
    {
        $this->container->set($id, $service);

        return $this;
    }

    /**
     * Get all registered service identifiers.
     *
     * @return array<string>
     */
    public function getServiceIds(): array
    {
        return $this->container->keys();
    }

    /**
     * Get services with a specific tag.
     *
     * @param string $tag Tag name
     * @return array<object>
     */
    public function getTaggedServices(string $tag): array
    {
        return $this->container->tagged($tag);
    }

    /**
     * Boot the foundation system.
     *
     * @return self
     */
    public function boot(): self
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $this->container->boot();

        return $this;
    }

    /**
     * Detect the application base path.
     *
     * @return string
     */
    protected function detectBasePath(): string
    {
        // Try to detect from current working directory
        $cwd = getcwd();
        if ($cwd && is_dir($cwd)) {
            return $cwd;
        }

        // Fallback to script directory
        return dirname($_SERVER['SCRIPT_NAME'] ?? __DIR__);
    }

    /**
     * Get system information.
     *
     * @return array<string, mixed>
     */
    public function getSystemInfo(): array
    {
        return [
            'version' => '0.0.1.1',
            'phase' => 'Site Restructuring & Architecture Implementation',
            'initialized' => $this->initialized,
            'base_path' => $this->basePath,
            'core_services' => $this->coreServices,
            'registered_services' => $this->getServiceIds(),
            'php_version' => PHP_VERSION,
            'php_extensions' => get_loaded_extensions(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'timezone' => date_default_timezone_get(),
        ];
    }

    /**
     * Clean up resources.
     *
     * @return self
     */
    public function cleanup(): self
    {
        if ($this->logger) {
            $this->logger->info('AsasFoundation cleanup initiated');
        }

        $this->initialized = false;

        return $this;
    }
} 