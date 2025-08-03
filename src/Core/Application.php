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

class Application
{
    /**
     * The base path of the application.
     */
    protected string $basePath;

    /**
     * The application's service container.
     */
    protected \IslamWiki\Core\Container\Asas $container;

    /**
     * The application's router instance.
     */
    protected $router = null;

    /**
     * Create a new application instance.
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
        $this->container = new \IslamWiki\Core\Container\Asas();

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
        $logger = new \IslamWiki\Core\Logging\Shahid($logDir, 'debug');
        $this->container->instance(\IslamWiki\Core\Logging\Shahid::class, $logger);

        // Bind ControllerFactory for IslamRouter
        $this->container->singleton('controller.factory', function () {
            $db = $this->container->get(\IslamWiki\Core\Database\Connection::class);
            $logger = $this->container->get(\IslamWiki\Core\Logging\Shahid::class);
            return new \IslamWiki\Core\Routing\ControllerFactory($db, $logger, $this->container);
        });

        // Register core settings binding (needed by LoggingServiceProvider)
        $this->container->singleton('settings', function () {
            return [
                'logging' => [
                    'log_path' => $this->basePath('storage/logs'),
                    'level' => \Psr\Log\LogLevel::DEBUG,
                    'max_file_size' => 10,
                    'max_files' => 5,
                ],
                'app_name' => 'IslamWiki',
                'app_debug' => true,
                'default_skin' => 'Bismillah',
            ];
        });

        // Register service providers
        $this->registerServiceProviders();

        // Initialize the router
        $this->router = new \IslamWiki\Core\Routing\IslamRouter($this->container);

        // Initialize error handling after all services are set up
        $this->initializeErrorHandling();
    }



    /**
     * Register service providers.
     */
    protected function registerServiceProviders(): void
    {
        // Register the DatabaseServiceProvider
        $databaseServiceProvider = new \IslamWiki\Providers\DatabaseServiceProvider();
        $databaseServiceProvider->register($this->container);

        // Register the ViewServiceProvider
        $viewServiceProvider = new \IslamWiki\Providers\ViewServiceProvider();
        $viewServiceProvider->register($this->container);
        
        // Register the SessionServiceProvider
        $sessionServiceProvider = new \IslamWiki\Providers\SessionServiceProvider();
        $sessionServiceProvider->register($this->container);
        $sessionServiceProvider->boot($this->container);

        // Register the ExtensionServiceProvider
        $extensionServiceProvider = new \IslamWiki\Providers\ExtensionServiceProvider();
        $extensionServiceProvider->register($this->container);
        $extensionServiceProvider->boot($this->container);

        // Register the ConfigurationServiceProvider
        $configurationServiceProvider = new \IslamWiki\Providers\ConfigurationServiceProvider($this->container);
        $configurationServiceProvider->register();
        $configurationServiceProvider->boot();

        // Register the LoggingServiceProvider
        $loggingServiceProvider = new \IslamWiki\Providers\LoggingServiceProvider();
        $loggingServiceProvider->register($this->container);
        $loggingServiceProvider->boot($this->container);

        // Register the SkinServiceProvider
        $skinServiceProvider = new \IslamWiki\Providers\SkinServiceProvider($this);
        $skinServiceProvider->register();
        $skinServiceProvider->boot();

        // Register the BayanServiceProvider
        $bayanServiceProvider = new \IslamWiki\Providers\BayanServiceProvider();
        $bayanServiceProvider->register($this->container);

        // Register the SirajServiceProvider
        $sirajServiceProvider = new \IslamWiki\Providers\SirajServiceProvider();
        $sirajServiceProvider->register($this->container);
        $sirajServiceProvider->boot($this->container);

        // Register the UsulServiceProvider
        $usulServiceProvider = new \IslamWiki\Providers\UsulServiceProvider();
        $usulServiceProvider->register($this->container);
        $usulServiceProvider->boot($this->container);

        // Register the RihlahServiceProvider
        $rihlahServiceProvider = new \IslamWiki\Providers\RihlahServiceProvider();
        $rihlahServiceProvider->register($this->container);
        $rihlahServiceProvider->boot($this->container);
        
        // Register Sabr queue system
        $sabrServiceProvider = new \IslamWiki\Providers\SabrServiceProvider();
        $sabrServiceProvider->register($this->container);
        $sabrServiceProvider->boot($this->container);
        
        $bayanServiceProvider->boot($this->container);
    }

    /**
     * Initialize error handling for the application.
     */
    protected function initializeErrorHandling(): void
    {
        // Initialize the error handler with debug mode based on environment
        $debug = $this->environment('development') || $this->environment('local');
        \IslamWiki\Core\Error\ErrorHandler::initialize($debug);
        
        // Set up the logger for the error handler
        $this->container->afterResolving(\IslamWiki\Core\Logging\Logger::class, function ($logger) {
            if ($logger instanceof \IslamWiki\Core\Logging\Logger) {
                \IslamWiki\Core\Error\ErrorHandler::setLogger($logger);
            }
        });
    }
    
    /**
     * Handle an uncaught exception.
     * This is kept for backward compatibility but will be handled by ErrorHandler.
     */
    protected function handleException(\Throwable $e): void
    {
        // The ErrorHandler will handle this exception
        throw $e;
    }

    /**
     * Register the core class aliases in the container.
     */
    protected function registerCoreContainerAliases(): void
    {
        $aliases = [
            'app' => [self::class, \Psr\Container\ContainerInterface::class],
            'config' => [\IslamWiki\Core\Config::class],
            'db' => [\IslamWiki\Core\Database\Connection::class],
            'request' => [Request::class, \Psr\Http\Message\ServerRequestInterface::class],
            'response' => [Response::class, \Psr\Http\Message\ResponseInterface::class],
            'router' => [IslamRouter::class],
        ];

        foreach ($aliases as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->container->alias($key, $alias);
            }
        }
    }

    /**
     * Get the application's base path.
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '');
    }

    /**
     * Get the application's environment file path.
     */
    public function environmentFilePath(): string
    {
        return $this->basePath('.env');
    }

    /**
     * Get the current application environment.
     */
    public function environment(string $environment = null): string|bool
    {
        if (func_num_args() > 0) {
            return ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production') === $environment;
        }

        return $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
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
            // Handle the request and send the response
            $response = $this->handleRequest(Request::capture());
            $this->sendResponse($response);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle the incoming request.
     */
    protected function handleRequest(Request $request): Response
    {
        try {
            // Convert our Request to PSR-7 ServerRequest for the router
            $psrRequest = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
            
            // Handle the request with the router
            $psrResponse = $this->router->handle($psrRequest);
            
            // Convert PSR-7 Response back to our Response class
            try {
                $body = $psrResponse->getBody();
                $bodyContents = $body->getContents();
                return new Response(
                    $bodyContents,
                    $psrResponse->getStatusCode(),
                    $psrResponse->getHeaders()
                );
            } catch (\Throwable $e) {
                error_log("Error converting PSR-7 response: " . $e->getMessage());
                return new Response('Error converting response', 500);
            }
        } catch (\Exception $e) {
            $response = $this->handleRouterException($e, $request);
        }

        return $response;
    }

    /**
     * Handle exceptions thrown in the router.
     */
    protected function handleRouterException(\Exception $e, Request $request): Response
    {
        // Log the exception
        error_log($e->getMessage());

        // Return appropriate response
        if ($e->getCode() === 404) {
            return new Response('404 Not Found', 404);
        }

        return new Response('500 Internal Server Error', 500);
    }

    /**
     * Send the response to the browser.
     */
    protected function sendResponse(Response $response): void
    {
        // Send status line
        header(sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ));

        // Send headers
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        // Send body
        echo $response->getBody();
    }

    /**
     * Get the application's service container.
     */
    public function getContainer(): \IslamWiki\Core\Container\Asas
    {
        return $this->container;
    }

    /**
     * Get the application's router.
     */
    public function getRouter(): IslamRouter
    {
        return $this->router;
    }
}
