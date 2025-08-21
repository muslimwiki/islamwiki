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
use Exception;

/**
 * SabilRouting (سبيل) - Islamic Routing System
 *
 * Sabil means "Path" or "Way" in Arabic. This is the routing system that
 * provides the path for all requests in IslamWiki, organizing routes by
 * Islamic systems and providing Islamic-named middleware integration.
 *
 * This routing system is designed to work with the new Islamic architecture
 * and provides route grouping, middleware management, and performance optimization.
 *
 * @category  Core
 * @package   IslamWiki\Core\Routing
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class SabilRouting
{
    /**
     * The service container.
     */
    protected AsasContainer $container;

    /**
     * The logging system.
     */
    protected ShahidLogger $logger;

    /**
     * Registered routes organized by Islamic system.
     *
     * @var array<string, array>
     */
    protected array $routes = [];

    /**
     * Route groups for organization.
     *
     * @var array<string, array>
     */
    protected array $groups = [];

    /**
     * Middleware stack.
     *
     * @var array<string, callable>
     */
    protected array $middleware = [];

    /**
     * Route cache for performance.
     *
     * @var array<string, mixed>
     */
    protected array $routeCache = [];

    /**
     * Whether route caching is enabled.
     */
    protected bool $cachingEnabled = true;

    /**
     * Constructor.
     *
     * @param AsasContainer $container The service container
     * @param ShahidLogger  $logger    The logging system
     */
    public function __construct(AsasContainer $container, ShahidLogger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->initializeIslamicSystems();
    }

    /**
     * Initialize Islamic system route groups.
     *
     * @return self
     */
    protected function initializeIslamicSystems(): self
    {
        // Foundation Layer (أساس)
        $this->groups['asas'] = [
            'prefix' => '/asas',
            'name' => 'Foundation Layer',
            'description' => 'Core services and utilities',
            'middleware' => ['asas.foundation']
        ];

        // Infrastructure Layer (سبيل, نظام, ميزان, تدبير)
        $this->groups['sabil'] = [
            'prefix' => '/sabil',
            'name' => 'Path System',
            'description' => 'Routing and navigation',
            'middleware' => ['sabil.routing']
        ];

        $this->groups['nizam'] = [
            'prefix' => '/nizam',
            'name' => 'Order System',
            'description' => 'System organization',
            'middleware' => ['nizam.organization']
        ];

        $this->groups['mizan'] = [
            'prefix' => '/mizan',
            'name' => 'Balance System',
            'description' => 'Performance monitoring',
            'middleware' => ['mizan.monitoring']
        ];

        $this->groups['tadbir'] = [
            'prefix' => '/tadbir',
            'name' => 'Management System',
            'description' => 'Configuration management',
            'middleware' => ['tadbir.config']
        ];

        // Application Layer (أمان, وصل, صبر, أصول)
        $this->groups['aman'] = [
            'prefix' => '/aman',
            'name' => 'Security System',
            'description' => 'Security and authentication',
            'middleware' => ['aman.security']
        ];

        $this->groups['wisal'] = [
            'prefix' => '/wisal',
            'name' => 'Connection System',
            'description' => 'Session management',
            'middleware' => ['wisal.session']
        ];

        $this->groups['sabr'] = [
            'prefix' => '/sabr',
            'name' => 'Patience System',
            'description' => 'Background processing',
            'middleware' => ['sabr.queue']
        ];

        $this->groups['usul'] = [
            'prefix' => '/usul',
            'name' => 'Principles System',
            'description' => 'Business rules',
            'middleware' => ['usul.rules']
        ];

        // User Interface Layer (إقرأ, بيان, سراج, رحلة)
        $this->groups['iqra'] = [
            'prefix' => '/iqra',
            'name' => 'Reading System',
            'description' => 'Search and discovery',
            'middleware' => ['iqra.search']
        ];

        $this->groups['bayan'] = [
            'prefix' => '/bayan',
            'name' => 'Explanation System',
            'description' => 'Content formatting',
            'middleware' => ['bayan.format']
        ];

        $this->groups['siraj'] = [
            'prefix' => '/siraj',
            'name' => 'Light System',
            'description' => 'Knowledge discovery',
            'middleware' => ['siraj.knowledge']
        ];

        $this->groups['rihlah'] = [
            'prefix' => '/rihlah',
            'name' => 'Journey System',
            'description' => 'User experience',
            'middleware' => ['rihlah.ux']
        ];

        return $this;
    }

    /**
     * Register a route group.
     *
     * @param string $name      Group name
     * @param array  $options   Group options
     * @param callable $callback Group callback
     * @return self
     */
    public function group(string $name, array $options, callable $callback): self
    {
        if (!isset($this->groups[$name])) {
            throw new Exception("Unknown route group: {$name}");
        }

        $this->groups[$name] = array_merge($this->groups[$name], $options);
        
        // Execute the group callback
        $callback($this);

        return $this;
    }

    /**
     * Register a GET route.
     *
     * @param string   $path       Route path
     * @param callable $handler    Route handler
     * @param array    $options    Route options
     * @return self
     */
    public function get(string $path, callable $handler, array $options = []): self
    {
        return $this->addRoute('GET', $path, $handler, $options);
    }

    /**
     * Register a POST route.
     *
     * @param string   $path       Route path
     * @param callable $handler    Route handler
     * @param array    $options    Route options
     * @return self
     */
    public function post(string $path, callable $handler, array $options = []): self
    {
        return $this->addRoute('POST', $path, $handler, $options);
    }

    /**
     * Register a PUT route.
     *
     * @param string   $path       Route path
     * @param callable $handler    Route handler
     * @param array    $options    Route options
     * @return self
     */
    public function put(string $path, callable $handler, array $options = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $options);
    }

    /**
     * Register a DELETE route.
     *
     * @param string   $path       Route path
     * @param callable $handler    Route handler
     * @param array    $options    Route options
     * @return self
     */
    public function delete(string $path, callable $handler, array $options = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $options);
    }

    /**
     * Register a route with any HTTP method.
     *
     * @param string   $method     HTTP method
     * @param string   $path       Route path
     * @param callable $handler    Route handler
     * @param array    $options    Route options
     * @return self
     */
    public function addRoute(string $method, string $path, callable $handler, array $options = []): self
    {
        $route = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'options' => $options,
            'middleware' => $options['middleware'] ?? [],
            'group' => $options['group'] ?? null,
            'name' => $options['name'] ?? null,
            'cache' => $options['cache'] ?? false,
        ];

        $this->routes[] = $route;

        // Add to cache if enabled
        if ($this->cachingEnabled && $route['cache']) {
            $this->cacheRoute($route);
        }

        $this->logger->info("Route registered: {$method} {$path}");

        return $this;
    }

    /**
     * Register middleware.
     *
     * @param string   $name       Middleware name
     * @param callable $middleware Middleware function
     * @return self
     */
    public function middleware(string $name, callable $middleware): self
    {
        $this->middleware[$name] = $middleware;

        return $this;
    }

    /**
     * Get all registered routes.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get routes by group.
     *
     * @param string $group Group name
     * @return array
     */
    public function getRoutesByGroup(string $group): array
    {
        return array_filter($this->routes, function ($route) use ($group) {
            return $route['group'] === $group;
        });
    }

    /**
     * Get route groups.
     *
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Find a route by path and method.
     *
     * @param string $method HTTP method
     * @param string $path   Request path
     * @return array|null
     */
    public function findRoute(string $method, string $path): ?array
    {
        $method = strtoupper($method);

        // Check cache first
        $cacheKey = "{$method}:{$path}";
        if (isset($this->routeCache[$cacheKey])) {
            return $this->routeCache[$cacheKey];
        }

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                // Cache the result
                if ($this->cachingEnabled) {
                    $this->routeCache[$cacheKey] = $route;
                }

                return $route;
            }
        }

        return null;
    }

    /**
     * Match a route path against a request path.
     *
     * @param string $routePath Route path pattern
     * @param string $requestPath Request path
     * @return bool
     */
    protected function matchPath(string $routePath, string $requestPath): bool
    {
        // Simple exact match for now
        // TODO: Implement pattern matching with parameters
        return $routePath === $requestPath;
    }

    /**
     * Cache a route for performance.
     *
     * @param array $route Route data
     * @return self
     */
    protected function cacheRoute(array $route): self
    {
        $cacheKey = "{$route['method']}:{$route['path']}";
        $this->routeCache[$cacheKey] = $route;

        return $this;
    }

    /**
     * Clear route cache.
     *
     * @return self
     */
    public function clearCache(): self
    {
        $this->routeCache = [];

        return $this;
    }

    /**
     * Enable or disable route caching.
     *
     * @param bool $enabled Whether caching is enabled
     * @return self
     */
    public function setCaching(bool $enabled): self
    {
        $this->cachingEnabled = $enabled;

        if (!$enabled) {
            $this->clearCache();
        }

        return $this;
    }

    /**
     * Get routing statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $groupCounts = [];
        foreach ($this->groups as $groupName => $group) {
            $groupCounts[$groupName] = count($this->getRoutesByGroup($groupName));
        }

        return [
            'total_routes' => count($this->routes),
            'groups' => $groupCounts,
            'middleware_count' => count($this->middleware),
            'cache_enabled' => $this->cachingEnabled,
            'cached_routes' => count($this->routeCache),
        ];
    }

    /**
     * Generate a URL for a named route.
     *
     * @param string $name Route name
     * @param array  $parameters Route parameters
     * @return string
     * @throws Exception If route not found
     */
    public function generateUrl(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $url = $route['path'];
                
                // Replace parameters in URL
                foreach ($parameters as $key => $value) {
                    $url = str_replace("{{$key}}", $value, $url);
                }

                return $url;
            }
        }

        throw new Exception("Route '{$name}' not found");
    }

    /**
     * Get all route names.
     *
     * @return array<string>
     */
    public function getRouteNames(): array
    {
        $names = [];
        foreach ($this->routes as $route) {
            if ($route['name']) {
                $names[] = $route['name'];
            }
        }

        return $names;
    }
    
    /**
     * Handle the incoming request
     */
    public function handle(\IslamWiki\Core\Http\Request $request): \IslamWiki\Core\Http\Response
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        error_log("SabilRouting: Handling request - Method: {$method}, Path: {$path}");
        
        // Handle static files first (CSS, JS, images, etc.)
        if ($this->isStaticFile($path)) {
            error_log("SabilRouting: Static file detected: {$path}");
            $response = $this->serveStaticFile($path);
            // Send the static file response immediately
            $response->send();
            exit; // Stop execution after sending static file
        }
        
        error_log("SabilRouting: Not a static file, checking routes. Total routes: " . count($this->routes));
        
        // Look for a matching route
        foreach ($this->routes as $index => $route) {
            error_log("SabilRouting: Checking route {$index} - Method: {$route['method']}, Path: {$route['path']}");
            
            if ($route['method'] === $method && $route['path'] === $path) {
                error_log("SabilRouting: Route match found! Executing handler...");
                
                // Execute the route handler
                if (is_callable($route['handler'])) {
                    $response = call_user_func($route['handler'], $request);
                    if ($response instanceof \IslamWiki\Core\Http\Response) {
                        error_log("SabilRouting: Handler returned valid Response object");
                        return $response;
                    } else {
                        error_log("SabilRouting: Handler returned invalid response type: " . gettype($response));
                    }
                } else {
                    error_log("SabilRouting: Handler is not callable");
                }
            }
        }
        
        error_log("SabilRouting: No route match found, returning 404");
        return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/html'], '<h1>Page not found</h1>');
    }
    
    /**
     * Check if the request is for a static file
     */
    private function isStaticFile(string $path): bool
    {
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot'];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $isStatic = in_array($extension, $staticExtensions);
        error_log("SabilRouting: Checking if static file - Path: {$path}, Extension: {$extension}, IsStatic: " . ($isStatic ? 'true' : 'false'));
        
        return $isStatic;
    }
    
    /**
     * Serve a static file
     */
    private function serveStaticFile(string $path): \IslamWiki\Core\Http\Response
    {
        // Remove leading slash for file path
        $filePath = ltrim($path, '/');
        
        // Build the full path relative to the project root (two levels up from public/)
        $fullPath = __DIR__ . '/../../../' . $filePath;
        
        // Check if file exists
        if (!file_exists($fullPath)) {
            error_log("SabilRouting: Static file not found at: {$fullPath}");
            return new \IslamWiki\Core\Http\Response(404, ['Content-Type' => 'text/html'], 'File not found: ' . $filePath);
        }
        
        // Set appropriate content type
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
        
        // Read file content
        $content = file_get_contents($fullPath);
        if ($content === false) {
            error_log("SabilRouting: Error reading static file: {$fullPath}");
            return new \IslamWiki\Core\Http\Response(500, ['Content-Type' => 'text/html'], 'Error reading file: ' . $filePath);
        }
        
        // Add cache headers for static files
        $headers = [
            'Content-Type' => $contentType,
            'Content-Length' => strlen($content),
            'Cache-Control' => 'public, max-age=3600',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT'
        ];
        
        error_log("SabilRouting: Successfully served static file: {$filePath} from {$fullPath}");
        return new \IslamWiki\Core\Http\Response(200, $headers, $content);
    }
}
