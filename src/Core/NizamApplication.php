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
 * This class combines the functionality of the original Application.php and Nizam.php
 * into a single, comprehensive application system.
 *
 * @category  Core
 * @package   IslamWiki\Core
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1
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
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
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
        // Initialize the service container first
        $this->container = new \IslamWiki\Core\Container\AsasContainer();

        // Bind the application instance to the container
        $this->container->instance(self::class, $this);
        $this->container->instance('app', $this);

        // Register core bindings
        $this->registerCoreContainerAliases();

        // Set up database connection first as it's needed for other services
        $dbConfig = [
            'driver' => getenv('DB_CONNECTION') ?: 'mysql',
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'database' => getenv('DB_DATABASE') ?: 'islamwiki',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ];

        // Create and bind the database connection
        $db = new \IslamWiki\Core\Database\Connection($dbConfig);
        $this->container->instance('db', $db);
        $this->container->instance(\IslamWiki\Core\Database\Connection::class, $db);

        // Bind Shahid
        $logDir = $this->basePath('storage/logs');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logger = new \IslamWiki\Core\Logging\ShahidLogger($logDir);
        $this->container->instance('logger', $logger);
        $this->container->instance(\IslamWiki\Core\Logging\ShahidLogger::class, $logger);

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
        $this->container->instance('controller.factory', $controllerFactory);
    }

    /**
     * Initialize all Islamic-named systems.
     */
    private function initializeSystems(): void
    {
        // Initialize SabilRouting (Routing)
        $this->_sabilRouter = new SabilRouting($this->container);

        // Add SkinMiddleware to the router
        $skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($this);
        $this->_sabilRouter->addMiddleware([$skinMiddleware, 'handle']);

        // Initialize Shahid (Logging)
        $this->_logger = new ShahidLogger($this->basePath('storage/logs'));

        // Initialize Mizan (Database)
        $this->database = new MizanDatabase($this->_logger, []);

        // Initialize Connection (Database)
        $this->connection = new Connection([]);

        // Initialize Wisal (Session)
        $this->_session = new WisalSession([]);

        // Initialize Aman (Security)
        $this->_auth = new AmanSecurity($this->_session, $this->connection);

        // Initialize Rihlah (Caching)
        $this->_cache = new RihlahCaching($this->container, $this->_logger, $this->connection);

        // Initialize Sabr (Queue)
        $this->_queue = new SabrQueue($this->container, $this->_logger, $this->connection);

        // Initialize Usul (Knowledge)
        $this->knowledge = new UsulKnowledge($this->container, $this->_logger, $this->connection);

        // Initialize Iqra (Search)
        $this->search = new IqraSearch($this->connection);

        // Initialize Bayan (Formatter)
        $this->formatter = new BayanFormatter($this->connection, $this->_logger);

        // Initialize Siraj (API) - temporarily disabled due to GuzzleHttp StreamFactory issue
        // $this->api = new SirajAPI($this->container, $this->logger, $this->session);

        // Initialize Tadbir (Configuration)
        $this->config = new TadbirConfiguration($this->_logger);
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
            \IslamWiki\Providers\ViewServiceProvider::class,
            \IslamWiki\Providers\SkinServiceProvider::class,
            \IslamWiki\Providers\StaticDataServiceProvider::class,
            // Temporarily disable non-essential providers
            // \IslamWiki\Providers\RihlahServiceProvider::class,
            // \IslamWiki\Providers\SabrServiceProvider::class,
            // \IslamWiki\Providers\UsulServiceProvider::class,
            // \IslamWiki\Providers\SirajServiceProvider::class,
            // \IslamWiki\Providers\BayanServiceProvider::class,
            // \IslamWiki\Providers\ExtensionServiceProvider::class,
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
        $this->container->alias('db', \IslamWiki\Core\Database\Connection::class);
        $this->container->alias('logger', \IslamWiki\Core\Logging\ShahidLogger::class);
        $this->container->alias(\Psr\Log\LoggerInterface::class, \IslamWiki\Core\Logging\ShahidLogger::class);
        $this->container->alias('auth', \IslamWiki\Core\Auth\AmanSecurity::class);
        $this->container->alias('session', \IslamWiki\Core\Session\WisalSession::class);
        $this->container->alias('cache', \IslamWiki\Core\Caching\RihlahCaching::class);
        $this->container->alias('queue', \IslamWiki\Core\Queue\SabrQueue::class);
        $this->container->alias('knowledge', \IslamWiki\Core\Knowledge\UsulKnowledge::class);
        $this->container->alias('search', \IslamWiki\Core\Search\IqraSearch::class);
        $this->container->alias('formatter', \IslamWiki\Core\Formatter\BayanFormatter::class);
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
            return appenv('APP_ENV') === $environment;
        }

        return appenv('APP_ENV') ?: 'production';
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
            $request = Request::createFromGlobals();
            $response = $this->handleRequest($request);
            $this->sendResponse($response);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle the incoming request.
     */
    protected function handleRequest(Request $request): Response
    {
        try {
            if ($this->router) {
                return $this->router->dispatch($request);
            }

            // Fallback to simple response
            return new Response(200, ['Content-Type' => 'text/html'], 'Application is running');
        } catch (\Exception $e) {
            return $this->handleRouterException($e, $request);
        }
    }

    /**
     * Handle router exceptions.
     */
    protected function handleRouterException(\Exception $e, Request $request): Response
    {
        $this->_logger->error('Router exception: ' . $e->getMessage(), [
            'exception' => $e,
            'request' => $request->getUri()
        ]);

        return new Response(500, ['Content-Type' => 'text/html'], 'Internal Server Error');
    }

    /**
     * Send the response to the client.
     */
    protected function sendResponse(Response $response): void
    {
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
    }

    /**
     * Get the service container.
     */
    public function getContainer(): \IslamWiki\Core\Container\AsasContainer
    {
        return $this->container;
    }

    /**
     * Get the router instance.
     */
    public function getRouter(): IslamRouter
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
        // Start session immediately to prevent "headers already sent" errors
        if (isset($this->_session) && $this->_session) {
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
