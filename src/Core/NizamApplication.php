<?php
declare(strict_types=1);
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
 */

namespace IslamWiki\Core;

use Exception;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Routing\IslamRouter;
use IslamWiki\Core\Routing\Sabil;
use IslamWiki\Core\Logging\Shahid;
use IslamWiki\Core\Auth\Aman;
use IslamWiki\Core\Session\Wisal;
use IslamWiki\Core\Caching\Rihlah;
use IslamWiki\Core\Queue\Sabr;
use IslamWiki\Core\Knowledge\Usul;
use IslamWiki\Core\Search\IqraSearchEngine;
use IslamWiki\Core\Formatter\BayanManager;
use IslamWiki\Core\API\Siraj;
use IslamWiki\Core\Database\Mizan;
use IslamWiki\Core\Configuration\Tadbir;

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
 * @package IslamWiki\Core
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
    private SabilRouting $sabilRouter;

    /**
     * The application logger.
     */
    private ShahidLogger $logger;

    /**
     * The security and authentication system.
     */
    private AmanSecurity $auth;

    /**
     * The session management system.
     */
    private WisalSession $session;

    /**
     * The caching system.
     */
    private RihlahCaching $cache;

    /**
     * The job queue system.
     */
    private SabrQueue $queue;

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
        $logger = new \IslamWiki\Core\Logging\Shahid($logDir);
        $this->container->instance('logger', $logger);
        $this->container->instance(\IslamWiki\Core\Logging\Shahid::class, $logger);

        // Initialize all Islamic systems
        $this->initializeSystems();

        // Register service providers
        $this->registerServiceProviders();

        // Initialize error handling
        $this->initializeErrorHandling();
    }

    /**
     * Initialize all Islamic-named systems.
     */
    private function initializeSystems(): void
    {
        // Initialize Sabil (Routing)
        $this->sabilRouter = new Sabil($this->container);

        // Initialize Shahid (Logging)
        $this->logger = new Shahid($this->basePath('storage/logs'));

        // Initialize Aman (Security)
        $this->auth = new Aman($this->container);

        // Initialize Wisal (Session)
        $this->session = new Wisal($this->container);

        // Initialize Rihlah (Caching)
        $this->cache = new Rihlah($this->container, $this->logger, $this->database);

        // Initialize Sabr (Queue)
        $this->queue = new Sabr($this->container, $this->logger, $this->database);

        // Initialize Usul (Knowledge)
        $this->knowledge = new Usul($this->container, $this->logger, $this->database);

        // Initialize Iqra (Search)
        $this->search = new IqraSearchEngine($this->container);

        // Initialize Bayan (Formatter)
        $this->formatter = new BayanManager($this->container);

        // Initialize Siraj (API)
        $this->api = new Siraj($this->container, $this->logger, $this->session);

        // Initialize Mizan (Database)
        $this->database = new Mizan($this->container);

        // Initialize Tadbir (Configuration)
        $this->config = new Tadbir($this->container);
    }

    /**
     * Register service providers.
     */
    protected function registerServiceProviders(): void
    {
        $providers = [
            \IslamWiki\Providers\DatabaseServiceProvider::class,
            \IslamWiki\Providers\LoggingServiceProvider::class,
            \IslamWiki\Providers\SessionServiceProvider::class,
            \IslamWiki\Providers\AuthServiceProvider::class,
            \IslamWiki\Providers\ViewServiceProvider::class,
            \IslamWiki\Providers\ConfigurationServiceProvider::class,
            \IslamWiki\Providers\RihlahServiceProvider::class,
            \IslamWiki\Providers\SabrServiceProvider::class,
            \IslamWiki\Providers\UsulServiceProvider::class,
            \IslamWiki\Providers\SirajServiceProvider::class,
            \IslamWiki\Providers\BayanServiceProvider::class,
            \IslamWiki\Providers\ExtensionServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                $providerInstance = new $provider();
                $providerInstance->register($this->container);
            }
        }

        // Boot all providers
        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                $providerInstance = new $provider();
                if (method_exists($providerInstance, 'boot')) {
                    $providerInstance->boot($this->container);
                }
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
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([$this, 'handleException']);
    }

    /**
     * Handle exceptions.
     */
    protected function handleException(\Throwable $e): void
    {
        $this->logger->error('Unhandled exception: ' . $e->getMessage(), [
            'exception' => $e,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($this->runningInConsole()) {
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
        $this->container->alias('logger', \IslamWiki\Core\Logging\Shahid::class);
        $this->container->alias('auth', \IslamWiki\Core\Auth\Aman::class);
        $this->container->alias('session', \IslamWiki\Core\Session\Wisal::class);
        $this->container->alias('cache', \IslamWiki\Core\Caching\Rihlah::class);
        $this->container->alias('queue', \IslamWiki\Core\Queue\Sabr::class);
        $this->container->alias('knowledge', \IslamWiki\Core\Knowledge\Usul::class);
        $this->container->alias('search', \IslamWiki\Core\Search\IqraSearchEngine::class);
        $this->container->alias('formatter', \IslamWiki\Core\Formatter\BayanManager::class);
        $this->container->alias('api', \IslamWiki\Core\API\Siraj::class);
        $this->container->alias('config', \IslamWiki\Core\Configuration\Tadbir::class);
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
        $this->logger->error('Router exception: ' . $e->getMessage(), [
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
     * Get the Sabil routing system.
     */
    public function getSabilRouter(): SabilRouting
    {
        return $this->sabilRouter;
    }

    /**
     * Get the authentication system.
     */
    public function getAuth(): AmanSecurity
    {
        return $this->auth;
    }

    /**
     * Get the session management system.
     */
    public function getSession(): WisalSession
    {
        return $this->session;
    }

    /**
     * Get the caching system.
     */
    public function getCache(): RihlahCaching
    {
        return $this->cache;
    }

    /**
     * Get the queue system.
     */
    public function getQueue(): SabrQueue
    {
        return $this->queue;
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
        return $this->logger;
    }

    /**
     * Boot the application.
     */
    public function boot(): void
    {
        // Boot all systems
        if ($this->auth) {
            $this->auth->boot();
        }

        if ($this->session) {
            $this->session->boot();
        }

        if ($this->cache) {
            $this->cache->boot();
        }

        if ($this->queue) {
            $this->queue->boot();
        }

        if ($this->knowledge) {
            $this->knowledge->boot();
        }

        if ($this->search) {
            $this->search->boot();
        }

        if ($this->formatter) {
            $this->formatter->boot();
        }

        if ($this->api) {
            $this->api->boot();
        }

        if ($this->database) {
            $this->database->boot();
        }

        if ($this->config) {
            $this->config->boot();
        }

        $this->logger->info('NizamApplication booted successfully');
    }

    /**
     * Shutdown the application.
     */
    public function shutdown(): void
    {
        // Shutdown all systems gracefully
        if ($this->queue) {
            $this->queue->shutdown();
        }

        if ($this->cache) {
            $this->cache->shutdown();
        }

        if ($this->session) {
            $this->session->shutdown();
        }

        if ($this->database) {
            $this->database->shutdown();
        }

        $this->logger->info('NizamApplication shutdown completed');
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
                'auth' => $this->auth ? 'active' : 'inactive',
                'session' => $this->session ? 'active' : 'inactive',
                'cache' => $this->cache ? 'active' : 'inactive',
                'queue' => $this->queue ? 'active' : 'inactive',
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