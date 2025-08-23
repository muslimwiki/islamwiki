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
 * @package   IslamWiki
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1
 */

declare(strict_types=1);

namespace IslamWiki\Core;

use Exception;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Routing\IslamRouter;
use IslamWiki\Core\Routing\SabilRouting;
use IslamWiki\Core\Logging\ShahidLogger;
use IslamWiki\Core\Auth\AmanSecurity;
use IslamWiki\Core\Session\WisalSession;
use IslamWiki\Core\Caching\RihlahCaching;
use IslamWiki\Core\Queue\SabrQueue;
use IslamWiki\Core\Knowledge\UsulKnowledge;
use IslamWiki\Core\Search\IqraSearch;
use IslamWiki\Core\Formatter\BayanFormatter;
use IslamWiki\Core\API\SirajAPI;
use IslamWiki\Core\Database\MizanDatabase;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Configuration\TadbirConfiguration;

/**
 * NizamApplication (نظام) - Main Application System
 *
 * Nizam means "System" or "Order" in Arabic. This is the main application
 * framework that orchestrates all Islamic-named systems and provides the
 * foundation for the entire IslamWiki application.
 *
 * This system is part of the Infrastructure Layer and serves as the central
 * orchestrator for all Islamic systems, managing their lifecycle, dependencies,
 * and coordination.
 *
 * @category  Core
 * @package   IslamWiki\Core
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class NizamApplication
{
    /**
     * The base path of the application.
     */
    protected string $basePath;

    /**
     * The application's service container.
     */
    protected \IslamWiki\Core\Container\AsasContainer $container;

    /**
     * The application's router instance.
     */
    protected $router = null;

    /**
     * The routing system.
     */
    private SabilRouting $_sabilRouter;

    /**
     * The application logger.
     */
    private ShahidLogger $_logger;

    /**
     * The security and authentication system.
     */
    private AmanSecurity $_auth;

    /**
     * The session management system.
     */
    private WisalSession $_session;

    /**
     * The caching system.
     */
    private RihlahCaching $_cache;

    /**
     * The job queue system.
     */
    private SabrQueue $_queue;

    /**
     * The knowledge management system.
     */
    private UsulKnowledge $knowledge;

    /**
     * The Islamic search engine.
     */
    private IqraSearch $search;

    /**
     * The content formatting system.
     */
    private BayanFormatter $formatter;

    /**
     * The API management system.
     */
    private SirajAPI $api;

    /**
     * The database management system.
     */
    private MizanDatabase $database;

    /**
     * The database connection.
     */
    private Connection $connection;

    /**
     * The configuration management system.
     */
    private TadbirConfiguration $config;

    /**
     * Create a new NizamApplication instance.
     */
    public function __construct(string $basePath, \IslamWiki\Core\Container\AsasContainer $container = null)
    {
        $this->basePath = rtrim($basePath, '\/');
        $this->container = $container ?? new \IslamWiki\Core\Container\AsasContainer();
        $this->bootstrap();
    }

    /**
     * Set the application's router instance.
     */
    public function setRouter($router): void
    {
        $this->router = $router;
    }

    /**
     * Bootstrap the application.
     */
    protected function bootstrap(): void
    {
        // Container is now passed in constructor or created as fallback

        // Bind the application instance to the container
        $this->container->set(self::class, $this);
        $this->container->set('app', $this);

        // Register core bindings
        $this->registerCoreContainerAliases();

        // Bind Shahid
        $logDir = $this->basePath('storage/logs');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logger = new \IslamWiki\Core\Logging\ShahidLogger($logDir);
        $this->container->set('logger', $logger);
        $this->container->set(\IslamWiki\Core\Logging\ShahidLogger::class, $logger);

        // Register service providers first
        $this->registerServiceProviders();

        // Initialize all Islamic systems after providers are registered
        $this->initializeSystems();

        // Initialize error handling
        $this->initializeErrorHandling();

        // Register ControllerFactory
        $db = $this->container->get('db');
        $logger = $this->container->get('logger');
        $controllerFactory = new \IslamWiki\Core\Routing\ControllerFactory($db, $logger, $this->container);
        $this->container->set('controller.factory', $controllerFactory);
    }

    /**
     * Initialize all Islamic-named systems.
     */
    private function initializeSystems(): void
    {
        // Get logger from container
        $this->_logger = $this->container->get('logger');

        // Initialize SabilRouting (Routing) with proper parameters
        $this->_sabilRouter = new SabilRouting($this->container, $this->_logger);

        // Initialize Mizan (Database) with proper parameters
        $this->database = new MizanDatabase($this->_logger, []);

        // Initialize Connection (Database)
        $this->connection = new Connection([]);

        // Initialize Wisal (Session) with proper parameters
        $this->_session = new WisalSession($this->_logger, []);

        // Initialize Aman (Security) with proper parameters
        $this->_auth = new AmanSecurity($this->_session, $this->connection);

        // Initialize Sabr (Queue) with proper parameters
        $this->_queue = new SabrQueue($this->_logger, []);

        // Initialize Usul (Knowledge) with proper parameters
        $this->knowledge = new UsulKnowledge($this->_logger, []);

        // Initialize Tadbir (Configuration) with proper parameters
        $this->config = new TadbirConfiguration($this->container);

        // Initialize User Interface Layer components
        $this->search = new IqraSearch($this->_logger, []);
        $this->formatter = new BayanFormatter($this->_logger, []);
        $this->api = new SirajAPI($this->_logger, []);
        $this->_cache = new RihlahCaching($this->_logger, []);

        // Store all systems in container for easy access
        $this->container->set('sabil.routing', $this->_sabilRouter);
        $this->container->set('mizan.database', $this->database);
        $this->container->set('wisal.session', $this->_session);
        $this->container->set('aman.security', $this->_auth);
        $this->container->set('sabr.queue', $this->_queue);
        $this->container->set('usul.knowledge', $this->knowledge);
        $this->container->set('tadbir.config', $this->config);
        $this->container->set('iqra.search', $this->search);
        $this->container->set('bayan.formatter', $this->formatter);
        $this->container->set('siraj.api', $this->api);
        $this->container->set('rihlah.caching', $this->_cache);

        $this->_logger->info('All Islamic systems initialized successfully');
    }

    /**
     * Register service providers.
     */
    protected function registerServiceProviders(): void
    {
        $providers = [
            \IslamWiki\Providers\DatabaseServiceProvider::class,
            \IslamWiki\Providers\ConfigurationServiceProvider::class,
            \IslamWiki\Providers\LoggingServiceProvider::class,
            \IslamWiki\Providers\SessionServiceProvider::class,
            \IslamWiki\Providers\AuthServiceProvider::class,
            \IslamWiki\Providers\LanguageServiceProvider::class,
            \IslamWiki\Providers\ViewServiceProvider::class,
            \IslamWiki\Providers\SkinServiceProvider::class,
            \IslamWiki\Providers\StaticDataServiceProvider::class,
            \IslamWiki\Providers\BayanServiceProvider::class,
            \IslamWiki\Providers\ExtensionServiceProvider::class,
            \IslamWiki\Extensions\EnhancedMarkdown\Providers\EnhancedMarkdownServiceProvider::class,
        ];

        // Register all providers first
        $providerInstances = [];
        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                try {
                    $providerInstance = new $provider();
                    $providerInstance->register($this->container);
                    $providerInstances[] = $providerInstance;
                } catch (\Exception $e) {
                    error_log("Failed to register provider $provider: " . $e->getMessage());
                }
            }
        }

        // Boot all providers
        foreach ($providerInstances as $providerInstance) {
            try {
                if (method_exists($providerInstance, 'boot')) {
                    $providerInstance->boot($this->container);
                }
            } catch (\Exception $e) {
                error_log("Failed to boot provider " . get_class($providerInstance) . ": " . $e->getMessage());
            }
        }
    }

    /**
     * Initialize error handling.
     */
    protected function initializeErrorHandling(): void
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handleException']);
    }

    /**
     * Handle exceptions.
     */
    public static function handleException(\Throwable $e): void
    {
        // Log the exception
        error_log('Unhandled exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

        if (php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg') {
            echo "Error: " . $e->getMessage() . "\n";
        } else {
            http_response_code(500);
            echo "Internal Server Error";
        }
    }

    /**
     * Register core container aliases.
     */
    protected function registerCoreContainerAliases(): void
    {
        $this->container->alias('app', self::class);
        $this->container->alias('container', \IslamWiki\Core\Container\AsasContainer::class);
        $this->container->alias(\IslamWiki\Core\Database\Connection::class, 'db');
        $this->container->alias('logger', \IslamWiki\Core\Logging\ShahidLogger::class);
        $this->container->alias(\Psr\Log\LoggerInterface::class, \IslamWiki\Core\Logging\ShahidLogger::class);
        $this->container->alias('auth', \IslamWiki\Core\Auth\AmanSecurity::class);
        $this->container->alias('session', \IslamWiki\Core\Session\WisalSession::class);
        $this->container->alias('cache', \IslamWiki\Core\Caching\RihlahCaching::class);
        $this->container->alias('queue', \IslamWiki\Core\Queue\SabrQueue::class);
        $this->container->alias('knowledge', \IslamWiki\Core\Knowledge\UsulKnowledge::class);
        $this->container->alias('search', \IslamWiki\Core\Search\IqraSearch::class);
        // Ensure legacy 'formatter' alias points to the BayanFormatter binding
        $this->container->alias(\IslamWiki\Core\Formatter\BayanFormatter::class, 'formatter');
        $this->container->alias('api', \IslamWiki\Core\API\SirajAPI::class);
        $this->container->alias('config', \IslamWiki\Core\Configuration\TadbirConfiguration::class);
    }

    /**
     * Get the base path for the application.
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * Get the path to the environment file.
     */
    public function environmentFilePath(): string
    {
        return $this->basePath('.env');
    }

    /**
     * Get the application environment.
     */
    public function environment(string $environment = null): string|bool
    {
        if ($environment !== null) {
            return getenv('APP_ENV') === $environment;
        }

        return getenv('APP_ENV') ?: 'production';
    }

    /**
     * Determine if the application is running in the console.
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

    /**
     * Run the application.
     */
    public function run(): void
    {
        try {
            // Create a simple request object for now
            $request = new Request('GET', '/', [], [], [], []);
            $response = $this->handleRequest($request);
            $this->sendResponse($response);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle the incoming request.
     */
    public function handleRequest(Request $request): Response
    {
        try {
            
            // Use SabilRouting to find and dispatch the route
            $method = $request->getMethod();
            $path = $request->getUri()->getPath();
            
            
            $route = $this->_sabilRouter->findRoute($method, $path);
            
            
            if ($route) {
                
                // Route found, call the handler
                $handler = $route['handler'];
                
                if (is_array($handler) && count($handler) === 2) {
                    // Controller method handler
                    $controller = $handler[0];
                    $method = $handler[1];
                    
                    
                    // Extract path parameters if any
                    $params = $this->extractPathParameters($route['path'], $path);
                    
                    
                    // Call the controller method with request and parameters
                    if (count($params) > 0) {
                        return call_user_func($handler, $request, ...$params);
                    } else {
                        return call_user_func($handler, $request);
                    }
                } else if (is_callable($handler)) {
                    // Callable handler
                    return call_user_func($handler, $request);
                } else {
                    throw new \Exception("Invalid route handler");
                }
            }

            
            // Route not found - return 404 with proper logging
            $this->_logger->warning('Route not found', [
                'request_uri' => $request->getUri()->getPath(),
                'request_method' => $request->getMethod(),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'remote_addr' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                'context' => 'route_not_found'
            ]);

            return new Response(404, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Error-Type' => 'route_not_found'
            ], $this->renderErrorPage(404));
        } catch (\Exception $e) {
            return $this->handleRouterException($e, $request);
        }
    }

    /**
     * Extract path parameters from route pattern.
     */
    protected function extractPathParameters(string $routePath, string $requestPath): array
    {
        // Simple parameter extraction for patterns like /wiki/{slug}
        // This is a basic implementation - can be enhanced later
        if (strpos($routePath, '{') !== false) {
            $routeParts = explode('/', trim($routePath, '/'));
            $requestParts = explode('/', trim($requestPath, '/'));
            
            $params = [];
            for ($i = 0; $i < count($routeParts); $i++) {
                if (isset($routeParts[$i]) && strpos($routeParts[$i], '{') === 0) {
                    // This is a parameter
                    $params[] = $requestParts[$i] ?? '';
                }
            }
            return $params;
        }
        
        return [];
    }

    /**
     * Handle router exceptions.
     */
    protected function handleRouterException(\Exception $e, Request $request): Response
    {
        // Log the exception with comprehensive context through Shahid
        $this->_logger->exception($e, [
            'request_uri' => $request->getUri()->getPath(),
            'request_method' => $request->getMethod(),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'remote_addr' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            'context' => 'router_exception'
        ], 'error');

        // Return a proper error response
        return new Response(500, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Error-Type' => 'router_exception'
        ], $this->renderErrorPage(500, $e));
    }

    /**
     * Render an error page using the view system.
     */
    protected function renderErrorPage(int $statusCode, \Throwable $e = null): string
    {
        try {
            // Try to get the view service from container
            if ($this->container->has('view')) {
                $view = $this->container->get('view');
                
                $errorData = [
                    'title' => $this->getErrorTitle($statusCode),
                    'error' => $e ? $e->getMessage() : 'An error occurred',
                    'status_code' => $statusCode,
                    'exception' => $e,
                    'current_language' => 'en',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'request_id' => uniqid('req_', true)
                ];

                // Log additional context for debugging
                if ($e) {
                    $this->_logger->error('Rendering error page', [
                        'status_code' => $statusCode,
                        'exception_class' => get_class($e),
                        'exception_file' => $e->getFile(),
                        'exception_line' => $e->getLine(),
                        'request_id' => $errorData['request_id']
                    ]);
                }

                $result = $view->render("errors/{$statusCode}.twig", $errorData);
                return $result;
            }
        } catch (\Exception $viewError) {
            // Log the view rendering error
            $this->_logger->error('Failed to render error page', [
                'original_status' => $statusCode,
                'view_error' => $viewError->getMessage(),
                'view_error_file' => $viewError->getFile(),
                'view_error_line' => $viewError->getLine()
            ]);
        }

        // Fallback to simple HTML error page
        return $this->getFallbackErrorPage($statusCode, $e);
    }

    /**
     * Get error title based on status code.
     */
    protected function getErrorTitle(int $statusCode): string
    {
        $titles = [
            400 => 'Bad Request - IslamWiki',
            401 => 'Unauthorized - IslamWiki',
            403 => 'Forbidden - IslamWiki',
            404 => 'Page Not Found - IslamWiki',
            500 => 'Internal Server Error - IslamWiki',
            503 => 'Service Unavailable - IslamWiki'
        ];

        return $titles[$statusCode] ?? 'Error - IslamWiki';
    }

    /**
     * Get fallback error page HTML.
     */
    protected function getFallbackErrorPage(int $statusCode, \Throwable $e = null): string
    {
        $title = $this->getErrorTitle($statusCode);
        $message = $e ? htmlspecialchars($e->getMessage()) : 'An error occurred';
        $requestId = uniqid('req_', true);

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; text-align: center; }
        .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { background: #e3f2fd; color: #1565c0; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .debug { background: #f3e5f5; color: #6a1b9a; padding: 15px; border-radius: 5px; margin: 20px 0; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 ' . $title . '</h1>
        
        <div class="error">
            <strong>❌ Error Details:</strong>
            <p>' . $message . '</p>
        </div>
        
        <div class="info">
            <strong>ℹ️ Information:</strong>
            <p>An error occurred while processing your request. Please try again later or contact the administrator.</p>
            <p><strong>Request ID:</strong> ' . $requestId . '</p>
            <p><strong>Status Code:</strong> ' . $statusCode . '</p>
            <p><strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '</p>
        </div>

        <div class="debug">
            <strong>🐛 Debug Information:</strong>
            <p>This is a fallback error page. The main error template could not be rendered.</p>
            <p>Check the application logs for more details.</p>
        </div>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="/" style="background: #1976d2; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">🏠 Return to Home</a>
        </p>
    </div>
</body>
</html>';
    }

    /**
     * Send the response to the client.
     */
    public function sendResponse(Response $response): void
    {
        try {
            // Send status code
            http_response_code($response->getStatusCode());

            // Send headers
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header("$name: $value");
                }
            }

            // Send body
            echo $response->getBody();
        } catch (Exception $e) {
            // Fallback to simple output
            echo $response->getBody();
        }
    }

    /**
     * Get the service container.
     */
    public function getContainer(): \IslamWiki\Core\Container\AsasContainer
    {
        return $this->container;
    }

    /**
     * Register a GET route.
     */
    public function get(string $path, $handler, array $options = []): self
    {
        $this->_sabilRouter->get($path, $handler, $options);
        return $this;
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, $handler, array $options = []): self
    {
        $this->_sabilRouter->post($path, $handler, $options);
        return $this;
    }

    /**
     * Register a PUT route.
     */
    public function put(string $path, $handler, array $options = []): self
    {
        $this->_sabilRouter->put($path, $handler, $options);
        return $this;
    }

    /**
     * Register a DELETE route.
     */
    public function delete(string $path, $handler, array $options = []): self
    {
        $this->_sabilRouter->delete($path, $handler, $options);
        return $this;
    }

    /**
     * Get the router instance.
     */
    public function getRouter(): mixed
    {
        return $this->router;
    }

    /**
     * Get the SabilRouting routing system.
     */
    public function getSabilRouter(): SabilRouting
    {
        return $this->_sabilRouter;
    }

    /**
     * Get the authentication system.
     */
    public function getAuth(): AmanSecurity
    {
        return $this->_auth;
    }

    /**
     * Get the session management system.
     */
    public function getSession(): WisalSession
    {
        return $this->_session;
    }

    /**
     * Get the caching system.
     */
    public function getCache(): RihlahCaching
    {
        return $this->_cache;
    }

    /**
     * Get the queue system.
     */
    public function getQueue(): SabrQueue
    {
        return $this->_queue;
    }

    /**
     * Get the knowledge management system.
     */
    public function getKnowledge(): UsulKnowledge
    {
        return $this->knowledge;
    }

    /**
     * Get the search system.
     */
    public function getSearch(): IqraSearch
    {
        return $this->search;
    }

    /**
     * Get the formatter system.
     */
    public function getFormatter(): BayanFormatter
    {
        return $this->formatter;
    }

    /**
     * Get the API system.
     */
    public function getApi(): SirajAPI
    {
        return $this->api;
    }

    /**
     * Get the database system.
     */
    public function getDatabase(): MizanDatabase
    {
        return $this->database;
    }

    /**
     * Get the configuration system.
     */
    public function getConfig(): TadbirConfiguration
    {
        return $this->config;
    }

    /**
     * Get the logger system.
     */
    public function getLogger(): ShahidLogger
    {
        return $this->_logger;
    }

    /**
     * Boot the application.
     */
    public function boot(): void
    {
        // Get the session from the container instead of using the local one
        try {
            if ($this->container && $this->container->has('session')) {
                $this->_session = $this->container->get('session');
            }
        } catch (\Exception $e) {
            error_log("Could not get session from container: " . $e->getMessage());
        }
        
        // Start session immediately to prevent "headers already sent" errors
        if (isset($this->_session) && $this->_session && method_exists($this->_session, 'start')) {
            $this->_session->start();
        }
        
        // Boot all systems that have boot methods
        if (isset($this->_auth) && $this->_auth && method_exists($this->_auth, 'boot')) {
            $this->_auth->boot();
        }

        if (isset($this->_session) && $this->_session && method_exists($this->_session, 'boot')) {
            $this->_session->boot();
        }

        if (isset($this->_cache) && $this->_cache && method_exists($this->_cache, 'boot')) {
            $this->_cache->boot();
        }

        if (isset($this->_queue) && $this->_queue && method_exists($this->_queue, 'boot')) {
            $this->_queue->boot();
        }

        if (isset($this->knowledge) && $this->knowledge && method_exists($this->knowledge, 'boot')) {
            $this->knowledge->boot();
        }

        if (isset($this->search) && $this->search && method_exists($this->search, 'boot')) {
            $this->search->boot();
        }

        if (isset($this->formatter) && $this->formatter && method_exists($this->formatter, 'boot')) {
            $this->formatter->boot();
        }

        if (isset($this->api) && $this->api && method_exists($this->api, 'boot')) {
            $this->api->boot();
        }

        if (isset($this->database) && $this->database && method_exists($this->database, 'boot')) {
            $this->database->boot();
        }

        if (isset($this->config) && $this->config && method_exists($this->config, 'boot')) {
            $this->config->boot();
        }

        $this->_logger->info('NizamApplication booted successfully');
    }

    /**
     * Shutdown the application.
     */
    public function shutdown(): void
    {
        // Shutdown all systems gracefully
        if ($this->_queue) {
            $this->_queue->shutdown();
        }

        if ($this->_cache) {
            $this->_cache->shutdown();
        }

        if ($this->_session) {
            $this->_session->shutdown();
        }

        if ($this->database) {
            $this->database->shutdown();
        }

        $this->_logger->info('NizamApplication shutdown completed');
    }

    /**
     * Get application statistics.
     */
    public function getStatistics(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'uptime' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true),
            'systems' => [
                'auth' => $this->_auth ? 'active' : 'inactive',
                'session' => $this->_session ? 'active' : 'inactive',
                'cache' => $this->_cache ? 'active' : 'inactive',
                'queue' => $this->_queue ? 'active' : 'inactive',
                'knowledge' => $this->knowledge ? 'active' : 'inactive',
                'search' => $this->search ? 'active' : 'inactive',
                'formatter' => $this->formatter ? 'active' : 'inactive',
                'api' => $this->api ? 'active' : 'inactive',
                'database' => $this->database ? 'active' : 'inactive',
                'config' => $this->config ? 'active' : 'inactive',
            ]
        ];
    }
}
