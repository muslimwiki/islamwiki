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
 * @package   IslamWiki\Core\Routing
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */

declare(strict_types=1);

namespace IslamWiki\Core\Routing;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Configuration\TadbirConfiguration;
use Exception;

/**
 * Route Implementation Service
 *
 * Service responsible for implementing all routes defined in the Islamic
 * architecture configuration. Integrates with SabilRouting to provide
 * comprehensive route management and implementation.
 *
 * @category  Core
 * @package   IslamWiki\Core\Routing
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class RouteImplementationService
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
     * SabilRouting instance.
     */
    protected SabilRouting $routing;

    /**
     * Route configuration.
     *
     * @var array<string, mixed>
     */
    protected array $routeConfig = [];

    /**
     * Implemented routes.
     *
     * @var array<string, mixed>
     */
    protected array $implementedRoutes = [];

    /**
     * Route statistics.
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
        $this->routing = $container->get(SabilRouting::class);
        
        $this->initializeService();
    }

    /**
     * Initialize the service.
     *
     * @return void
     */
    protected function initializeService(): void
    {
        $this->loadRouteConfiguration();
        $this->initializeStatistics();
        $this->logger->info('Route Implementation Service initialized');
    }

    /**
     * Load route configuration.
     *
     * @return void
     */
    protected function loadRouteConfiguration(): void
    {
        $configPath = $this->configuration->get('routes.islamic.path', 'config/routes_islamic.php');
        
        if (file_exists($configPath)) {
            $this->routeConfig = require $configPath;
            $this->logger->info('Loaded Islamic route configuration');
        } else {
            $this->logger->warning("Route configuration file not found: {$configPath}");
            $this->routeConfig = [];
        }
    }

    /**
     * Initialize route statistics.
     *
     * @return void
     */
    protected function initializeStatistics(): void
    {
        $this->statistics = [
            'total_routes' => 0,
            'implemented_routes' => 0,
            'route_groups' => 0,
            'middleware_count' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'performance_metrics' => []
        ];
    }

    /**
     * Implement all routes from configuration.
     *
     * @return bool
     */
    public function implementAllRoutes(): bool
    {
        try {
            $this->logger->info('Starting route implementation for all Islamic systems');

            foreach ($this->routeConfig as $groupKey => $groupConfig) {
                $this->implementRouteGroup($groupKey, $groupConfig);
            }

            $this->updateStatistics();
            $this->logger->info('Route implementation completed successfully');
            
            return true;

        } catch (Exception $e) {
            $this->logger->error('Route implementation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Implement a specific route group.
     *
     * @param string $groupKey Group key
     * @param array  $groupConfig Group configuration
     * @return void
     */
    protected function implementRouteGroup(string $groupKey, array $groupConfig): void
    {
        $this->logger->debug("Implementing route group: {$groupKey}");

        $prefix = $groupConfig['prefix'] ?? '';
        $middleware = $groupConfig['middleware'] ?? [];
        $routes = $groupConfig['routes'] ?? [];

        // Register middleware for the group
        $this->registerGroupMiddleware($groupKey, $middleware);

        // Implement routes for each HTTP method
        foreach ($routes as $method => $methodRoutes) {
            $this->implementMethodRoutes($groupKey, $method, $methodRoutes, $prefix, $middleware);
        }

        $this->statistics['route_groups']++;
    }

    /**
     * Implement routes for a specific HTTP method.
     *
     * @param string $groupKey Group key
     * @param string $method HTTP method
     * @param array  $routes Route definitions
     * @param string $prefix URL prefix
     * @param array  $middleware Group middleware
     * @return void
     */
    protected function implementMethodRoutes(
        string $groupKey,
        string $method,
        array $routes,
        string $prefix,
        array $middleware
    ): void {
        foreach ($routes as $path => $routeConfig) {
            $this->implementRoute($groupKey, $method, $path, $routeConfig, $prefix, $middleware);
        }
    }

    /**
     * Implement a single route.
     *
     * @param string $groupKey Group key
     * @param string $method HTTP method
     * @param string $path Route path
     * @param array  $routeConfig Route configuration
     * @param string $prefix URL prefix
     * @param array  $middleware Group middleware
     * @return void
     */
    protected function implementRoute(
        string $groupKey,
        string $method,
        string $path,
        array $routeConfig,
        string $prefix,
        array $middleware
    ): void {
        $fullPath = $prefix . $path;
        $routeName = $routeConfig['name'] ?? '';
        $controller = $routeConfig['controller'] ?? '';
        $action = $routeConfig['action'] ?? '';

        try {
            // Register route with SabilRouting
            $this->routing->addRoute($method, $fullPath, [
                'controller' => $controller,
                'action' => $action,
                'name' => $routeName,
                'group' => $groupKey,
                'middleware' => array_merge($middleware, $routeConfig['middleware'] ?? []),
                'description' => $routeConfig['description'] ?? ''
            ]);

            // Track implemented route
            $this->implementedRoutes[$routeName] = [
                'method' => $method,
                'path' => $fullPath,
                'controller' => $controller,
                'action' => $action,
                'group' => $groupKey,
                'middleware' => $middleware,
                'implemented_at' => microtime(true)
            ];

            $this->statistics['implemented_routes']++;
            $this->logger->debug("Implemented route: {$method} {$fullPath}");

        } catch (Exception $e) {
            $this->logger->error("Failed to implement route {$method} {$fullPath}: " . $e->getMessage());
        }
    }

    /**
     * Register middleware for a route group.
     *
     * @param string $groupKey Group key
     * @param array  $middleware Middleware list
     * @return void
     */
    protected function registerGroupMiddleware(string $groupKey, array $middleware): void
    {
        foreach ($middleware as $middlewareName) {
            try {
                $this->routing->middleware($middlewareName, $this->createMiddleware($middlewareName));
                $this->statistics['middleware_count']++;
                $this->logger->debug("Registered middleware: {$middlewareName} for group: {$groupKey}");
            } catch (Exception $e) {
                $this->logger->warning("Failed to register middleware {$middlewareName}: " . $e->getMessage());
            }
        }
    }

    /**
     * Create middleware instance.
     *
     * @param string $middlewareName Middleware name
     * @return callable
     */
    protected function createMiddleware(string $middlewareName): callable
    {
        // Return a default middleware function
        return function ($request, $next) use ($middlewareName) {
            $this->logger->debug("Executing middleware: {$middlewareName}");
            return $next($request);
        };
    }

    /**
     * Get route by name.
     *
     * @param string $routeName Route name
     * @return array|null
     */
    public function getRoute(string $routeName): ?array
    {
        return $this->implementedRoutes[$routeName] ?? null;
    }

    /**
     * Get all implemented routes.
     *
     * @return array<string, mixed>
     */
    public function getAllRoutes(): array
    {
        return $this->implementedRoutes;
    }

    /**
     * Get routes by group.
     *
     * @param string $groupKey Group key
     * @return array<string, mixed>
     */
    public function getRoutesByGroup(string $groupKey): array
    {
        return array_filter($this->implementedRoutes, function ($route) use ($groupKey) {
            return $route['group'] === $groupKey;
        });
    }

    /**
     * Get routes by HTTP method.
     *
     * @param string $method HTTP method
     * @return array<string, mixed>
     */
    public function getRoutesByMethod(string $method): array
    {
        return array_filter($this->implementedRoutes, function ($route) use ($method) {
            return $route['method'] === strtoupper($method);
        });
    }

    /**
     * Check if route exists.
     *
     * @param string $routeName Route name
     * @return bool
     */
    public function hasRoute(string $routeName): bool
    {
        return isset($this->implementedRoutes[$routeName]);
    }

    /**
     * Get route statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $this->statistics['total_routes'] = count($this->routeConfig);
        $this->statistics['implementation_percentage'] = 
            $this->statistics['total_routes'] > 0 
                ? round(($this->statistics['implemented_routes'] / $this->statistics['total_routes']) * 100, 2)
                : 0;

        return $this->statistics;
    }

    /**
     * Update route statistics.
     *
     * @return void
     */
    protected function updateStatistics(): void
    {
        $this->statistics['last_updated'] = microtime(true);
        $this->statistics['total_routes'] = count($this->routeConfig);
        $this->statistics['implementation_percentage'] = 
            $this->statistics['total_routes'] > 0 
                ? round(($this->statistics['implemented_routes'] / $this->statistics['total_routes']) * 100, 2)
                : 0;
    }

    /**
     * Clear route cache.
     *
     * @return bool
     */
    public function clearRouteCache(): bool
    {
        try {
            $this->routing->clearCache();
            $this->logger->info('Route cache cleared successfully');
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to clear route cache: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Refresh route implementation.
     *
     * @return bool
     */
    public function refreshRoutes(): bool
    {
        try {
            $this->logger->info('Refreshing route implementation');
            
            // Clear existing routes
            $this->implementedRoutes = [];
            $this->statistics['implemented_routes'] = 0;
            
            // Reload configuration
            $this->loadRouteConfiguration();
            
            // Re-implement all routes
            return $this->implementAllRoutes();

        } catch (Exception $e) {
            $this->logger->error('Failed to refresh routes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get route performance metrics.
     *
     * @return array<string, mixed>
     */
    public function getPerformanceMetrics(): array
    {
        return $this->statistics['performance_metrics'] ?? [];
    }

    /**
     * Track route performance.
     *
     * @param string $routeName Route name
     * @param float  $executionTime Execution time in seconds
     * @return void
     */
    public function trackRoutePerformance(string $routeName, float $executionTime): void
    {
        if (!isset($this->statistics['performance_metrics'][$routeName])) {
            $this->statistics['performance_metrics'][$routeName] = [];
        }

        $this->statistics['performance_metrics'][$routeName][] = [
            'execution_time' => $executionTime,
            'timestamp' => microtime(true)
        ];

        // Keep only last 100 performance records per route
        if (count($this->statistics['performance_metrics'][$routeName]) > 100) {
            array_shift($this->statistics['performance_metrics'][$routeName]);
        }
    }

    /**
     * Get route configuration.
     *
     * @return array<string, mixed>
     */
    public function getRouteConfiguration(): array
    {
        return $this->routeConfig;
    }

    /**
     * Validate route configuration.
     *
     * @return array<string, mixed>
     */
    public function validateRouteConfiguration(): array
    {
        $errors = [];
        $warnings = [];

        foreach ($this->routeConfig as $groupKey => $groupConfig) {
            if (!isset($groupConfig['routes'])) {
                $errors[] = "Group '{$groupKey}' missing routes configuration";
                continue;
            }

            foreach ($groupConfig['routes'] as $method => $routes) {
                foreach ($routes as $path => $routeConfig) {
                    $routeName = $routeConfig['name'] ?? '';
                    $controller = $routeConfig['controller'] ?? '';
                    $action = $routeConfig['action'] ?? '';

                    if (empty($routeName)) {
                        $warnings[] = "Route in group '{$groupKey}' missing name";
                    }

                    if (empty($controller)) {
                        $errors[] = "Route '{$routeName}' missing controller";
                    }

                    if (empty($action)) {
                        $errors[] = "Route '{$routeName}' missing action";
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
} 