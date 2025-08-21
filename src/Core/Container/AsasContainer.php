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
 * @package   IslamWiki\Core\Container
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use InvalidArgumentException;
use Exception;

/**
 * AsasContainer (أساس) - Foundation Container System
 *
 * Asas means "Foundation" in Arabic. This is the dependency injection container
 * that provides the foundation for all Islamic-named systems in IslamWiki.
 *
 * This container implements the PSR-11 Container interface and provides advanced
 * features for service management, dependency resolution, and lifecycle management.
 *
 * @category  Core
 * @package   IslamWiki\Core\Container
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class AsasContainer implements ContainerInterface
{
    /**
     * Registered services and their definitions.
     *
     * @var array<string, mixed>
     */
    protected array $services = [];

    /**
     * Service instances that have been resolved.
     *
     * @var array<string, object>
     */
    protected array $instances = [];

    /**
     * Service aliases for easier access.
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * Service tags for grouped access.
     *
     * @var array<string, array<string>>
     */
    protected array $tagged = [];

    /**
     * Service providers for automatic registration.
     *
     * @var array<string, string>
     */
    protected array $providers = [];

    /**
     * Whether the container has been booted.
     *
     * @var bool
     */
    protected bool $booted = false;

    /**
     * Services currently being resolved (for circular dependency detection).
     *
     * @var array<string, bool>
     */
    protected array $resolving = [];

    /**
     * Constructor.
     *
     * @param array<string, mixed> $services Initial services to register
     */
    public function __construct(array $services = [])
    {
        $this->registerServices($services);
    }

    /**
     * Register multiple services at once.
     *
     * @param array<string, mixed> $services Services to register
     * @return self
     */
    public function registerServices(array $services): self
    {
        foreach ($services as $id => $service) {
            $this->set($id, $service);
        }

        return $this;
    }

    /**
     * Register a service with the container.
     *
     * @param string $id Service identifier
     * @param mixed  $service Service definition or instance
     * @return self
     */
    public function set(string $id, mixed $service): self
    {
        $this->services[$id] = $service;
        unset($this->instances[$id]);

        return $this;
    }

    /**
     * Register a service alias.
     *
     * @param string $alias Alias name
     * @param string $id    Service identifier
     * @return self
     */
    public function alias(string $alias, string $id): self
    {
        $this->aliases[$alias] = $id;

        return $this;
    }

    /**
     * Tag a service for grouped access.
     *
     * @param string $tag     Tag name
     * @param string $service Service identifier
     * @return self
     */
    public function tag(string $tag, string $service): self
    {
        if (!isset($this->tagged[$tag])) {
            $this->tagged[$tag] = [];
        }

        $this->tagged[$tag][] = $service;

        return $this;
    }

    /**
     * Get all services with a specific tag.
     *
     * @param string $tag Tag name
     * @return array<object> Array of service instances
     */
    public function tagged(string $tag): array
    {
        if (!isset($this->tagged[$tag])) {
            return [];
        }

        $services = [];
        foreach ($this->tagged[$tag] as $serviceId) {
            if ($this->has($serviceId)) {
                $services[] = $this->get($serviceId);
            }
        }

        return $services;
    }

    /**
     * Register a service provider.
     *
     * @param string $provider Provider class name
     * @return self
     */
    public function register(string $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * Boot all registered service providers.
     *
     * @return self
     */
    public function boot(): self
    {
        if ($this->booted) {
            return $this;
        }

        foreach ($this->providers as $providerClass) {
            if (class_exists($providerClass)) {
                $provider = new $providerClass();
                if (method_exists($provider, 'register')) {
                    $provider->register($this);
                }
                if (method_exists($provider, 'boot')) {
                    $provider->boot($this);
                }
            }
        }

        $this->booted = true;

        return $this;
    }

    /**
     * Check if a service is registered.
     *
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->aliases[$id]);
    }

    /**
     * Get a service from the container.
     *
     * @param string $id Service identifier
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function get(string $id): mixed
    {
        // Check aliases first
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        // Return cached instance if available
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Check if service exists
        if (!isset($this->services[$id])) {
            throw new class($id) extends Exception implements NotFoundExceptionInterface {
                public function __construct(string $id) {
                    parent::__construct("Service '{$id}' not found in container");
                }
            };
        }

        // Check for circular dependencies
        if (isset($this->resolving[$id])) {
            throw new Exception("Circular dependency detected for service '{$id}'");
        }

        try {
            $this->resolving[$id] = true;
            $service = $this->resolveService($id, $this->services[$id]);
            $this->instances[$id] = $service;
            unset($this->resolving[$id]);
            
            // Debug: Log what type of service is being returned
            error_log("Container: Resolved service '{$id}' to type: " . gettype($service) . " (class: " . (is_object($service) ? get_class($service) : 'not object') . ")");

            return $service;
        } catch (Exception $e) {
            unset($this->resolving[$id]);
            throw new class($e->getMessage(), 0, $e) extends Exception implements ContainerExceptionInterface {
            };
        }
    }

    /**
     * Resolve a service definition to an instance.
     *
     * @param string $id      Service identifier
     * @param mixed  $service Service definition
     * @return mixed
     * @throws Exception
     */
    protected function resolveService(string $id, mixed $service): mixed
    {
        // Check if callable first (closures are objects, so this must come before is_object check)
        if (is_callable($service)) {
            return $service($this);
        }

        if (is_object($service)) {
            return $service;
        }

        if (is_string($service)) {
            // Check if it's a class name or just a string value
            if (class_exists($service)) {
                return new $service();
            }
            // It's just a string value, return it as-is
            return $service;
        }

        if (is_array($service)) {
            return $this->resolveArrayService($id, $service);
        }

        // For other types (int, float, bool, null), return as-is
        return $service;
    }

    /**
     * Resolve an array-based service definition.
     *
     * @param string $id      Service identifier
     * @param array  $service Service definition array
     * @return object
     * @throws Exception
     */
    protected function resolveArrayService(string $id, array $service): object
    {
        if (!isset($service['class'])) {
            throw new Exception("Service '{$id}' missing 'class' definition");
        }

        $class = $service['class'];
        if (!class_exists($class)) {
            throw new Exception("Class '{$class}' not found for service '{$id}'");
        }

        $arguments = $service['arguments'] ?? [];
        $arguments = $this->resolveArguments($arguments);

        return new $class(...$arguments);
    }

    /**
     * Resolve service arguments.
     *
     * @param array $arguments Arguments to resolve
     * @return array
     */
    protected function resolveArguments(array $arguments): array
    {
        $resolved = [];

        foreach ($arguments as $argument) {
            if (is_string($argument) && $this->has($argument)) {
                $resolved[] = $this->get($argument);
            } else {
                $resolved[] = $argument;
            }
        }

        return $resolved;
    }

    /**
     * Remove a service from the container.
     *
     * @param string $id Service identifier
     * @return self
     */
    public function remove(string $id): self
    {
        unset($this->services[$id], $this->instances[$id], $this->aliases[$id]);

        return $this;
    }

    /**
     * Clear all services from the container.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->services = [];
        $this->instances = [];
        $this->aliases = [];
        $this->tagged = [];
        $this->providers = [];
        $this->booted = false;
        $this->resolving = [];

        return $this;
    }

    /**
     * Get all registered service identifiers.
     *
     * @return array<string>
     */
    public function keys(): array
    {
        return array_keys($this->services);
    }

    /**
     * Check if the container has been booted.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Get the number of registered services.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->services);
    }
}
