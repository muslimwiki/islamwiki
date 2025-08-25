<?php

/**
 * Core Application
 *
 * Centralized application system for IslamWiki.
 * Handles application bootstrapping and core system management.
 *
 * @package IslamWiki\Core
 * @version 0.0.3.1
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Core;

// Include helper functions
require_once __DIR__ . '/../helpers.php';

use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Routing\Router;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;
use IslamWiki\Core\Configuration\ConfigurationService;

/**
 * Core Application - Centralized Application System
 *
 * This class provides comprehensive application management capabilities
 * for bootstrapping and coordinating all core systems.
 */
class Application
{
    /**
     * The base path of the application.
     */
    protected string $basePath;

    /**
     * The application's service container.
     */
    protected Container $container;

    /**
     * The application's router instance.
     */
    protected Router $router;

    /**
     * The application logger.
     */
    protected Logger $logger;

    /**
     * Create a new application instance.
     *
     * @param string $basePath The base path of the application
     */
    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath ?: dirname(__DIR__, 2);
        $this->initialize();
    }

    /**
     * Initialize the application.
     */
    protected function initialize(): void
    {
        try {
            error_log('Application::initialize - Starting initialization');
            
            // Create container
            $this->container = new Container();
            error_log('Application::initialize - Container created');
            
            // Create logger
            $this->logger = new Logger($this->basePath . '/storage/logs');
            $this->container->set(Logger::class, $this->logger);
            $this->container->set('logger', $this->logger);
            $this->container->set(\Psr\Log\LoggerInterface::class, $this->logger);
            $this->container->alias('logger', Logger::class);
            error_log('Application::initialize - Logger created and registered');
            
            // Create router
            $this->router = new Router($this->logger);
            $this->container->set(Router::class, $this->router);
            $this->container->set('router', $this->router);
            $this->container->alias('router', Router::class);
            error_log('Application::initialize - Router created and registered');
            
            // Register core services
            $this->registerCoreServices();
            error_log('Application::initialize - Core services registered');
            
            // Load routes
            $this->loadRoutes();
            error_log('Application::initialize - Routes loaded');
            
            $this->logger->info('Application initialized successfully');
            error_log('Application::initialize - Initialization completed successfully');
        } catch (\Exception $e) {
            // Fallback error handling if logger isn't available
            error_log('Application initialization failed: ' . $e->getMessage());
            error_log('Application initialization stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Register core services in the container.
     */
    protected function registerCoreServices(): void
    {
        try {
            // Register security service
            $this->container->set('security', function (Container $container) {
                return new \IslamWiki\Core\Auth\Security(
                    $container->get('session'),
                    $container->get('db'),
                    $container->get('logger'),
                    []
                );
            });
            
            // Register auth service as alias for security
            $this->container->set('auth', function (Container $container) {
                return $container->get('security');
            });

            // Register session service
            $this->container->set('session', function (Container $container) {
                return new \IslamWiki\Core\Session\Session(
                    $container->get('logger'),
                    []
                );
            });

            // Register database service
            $this->container->set('db', function (Container $container) {
                return new \IslamWiki\Core\Database\Connection([
                    'host' => $_ENV['DB_HOST'] ?? 'localhost',
                    'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
                    'username' => $_ENV['DB_USERNAME'] ?? 'root',
                    'password' => $_ENV['DB_PASSWORD'] ?? '',
                    'charset' => 'utf8mb4'
                ]);
            });
            $this->container->set(\IslamWiki\Core\Database\Connection::class, function (Container $container) {
                return $container->get('db');
            });

            // Register configuration service
            $this->container->set('config', function (Container $container) {
                return new \IslamWiki\Core\Configuration\Configuration();
            });
            $this->container->set(\IslamWiki\Core\Configuration\Configuration::class, function (Container $container) {
                return $container->get('config');
            });

            // Register i18n service
            $this->container->set('i18n', function (Container $container) {
                return new \IslamWiki\Core\I18n\I18nService($container, $container->get('logger'));
            });
            $this->container->set(\IslamWiki\Core\I18n\I18nService::class, function (Container $container) {
                return $container->get('i18n');
            });

            // Register base path service
            $this->container->set('base_path', $this->basePath);

            // Register enhanced skin services
            $this->container->set('skin.manager', function (Container $container) {
                return new \IslamWiki\Core\Skin\SkinManager($container);
            });
            $this->container->set(\IslamWiki\Core\Skin\SkinManager::class, function (Container $container) {
                return $container->get('skin.manager');
            });

            $this->container->set('skin.registry', function (Container $container) {
                return new \IslamWiki\Core\Skin\SkinRegistry($container);
            });
            $this->container->set(\IslamWiki\Core\Skin\SkinRegistry::class, function (Container $container) {
                return $container->get('skin.registry');
            });

            $this->container->set('skin.assets', function (Container $container) {
                return new \IslamWiki\Core\Skin\AssetManager($container);
            });
            $this->container->set(\IslamWiki\Core\Skin\AssetManager::class, function (Container $container) {
                return $container->get('skin.assets');
            });

            $this->container->set('skin.templates', function (Container $container) {
                return new \IslamWiki\Core\Skin\TemplateEngine($container);
            });
            $this->container->set(\IslamWiki\Core\Skin\TemplateEngine::class, function (Container $container) {
                return $container->get('skin.templates');
            });

            // Register static data service provider
            $staticDataProvider = new \IslamWiki\Providers\StaticDataServiceProvider();
            $staticDataProvider->register($this->container);
            $staticDataProvider->boot($this->container);

            // Register view service
            $this->container->set('view', function (Container $container) {
                $templatePath = $this->basePath . '/resources/views';
                $cachePath = $this->basePath . '/storage/framework/views';
                
                // Create cache directory if it doesn't exist
                if (!is_dir($cachePath)) {
                    mkdir($cachePath, 0755, true);
                }
                
                $isDebug = ($_ENV['APP_ENV'] ?? 'production') !== 'production';
                
                return new \IslamWiki\Core\View\TwigRenderer(
                    $templatePath,
                    false, // Disable cache for now
                    $isDebug
                );
            });
            $this->container->set(\IslamWiki\Core\View\TwigRenderer::class, function (Container $container) {
                return $container->get('view');
            });

            $this->logger->info('Core services registered');
            
            // Set up View container for skin integration
            \IslamWiki\Http\Views\View::setContainer($this->container);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to register core services', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the service container.
     *
     * @return Container The service container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the router instance.
     *
     * @return Router The router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Get the logger instance.
     *
     * @return Logger The logger instance
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Get the base path.
     *
     * @return string The base path
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Run the application.
     *
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    public function run(Request $request): Response
    {
        try {
            $this->logger->info('Application request started', [
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath()
            ]);

            // Dispatch request through router
            $response = $this->router->dispatch($request);

            $this->logger->info('Application request completed', [
                'status' => $response->getStatusCode()
            ]);

            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Application request failed', [
                'error' => $e->getMessage()
            ]);

            // Return error response
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Bootstrap the application.
     *
     * @return self
     */
    public function bootstrap(): self
    {
        try {
            // Load environment variables
            $this->loadEnvironment();
            
            // Set error handling
            $this->setupErrorHandling();
            
            // Load configuration
            $this->loadConfiguration();
            
            $this->logger->info('Application bootstrapped successfully');
        } catch (\Exception $e) {
            $this->logger->error('Application bootstrap failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $this;
    }

    /**
     * Load environment variables.
     */
    protected function loadEnvironment(): void
    {
        $envFile = $this->basePath . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }

    /**
     * Setup error handling.
     */
    protected function setupErrorHandling(): void
    {
        if ($_ENV['APP_DEBUG'] ?? false) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    /**
     * Load application configuration.
     */
    protected function loadConfiguration(): void
    {
        // Load configuration files if they exist
        $configDir = $this->basePath . '/config';
        if (is_dir($configDir)) {
            $this->logger->info('Configuration directory found', ['path' => $configDir]);
        }
    }

    /**
     * Load application routes.
     */
    protected function loadRoutes(): void
    {
        try {
            // Load main routes
            $routesFile = $this->basePath . '/config/routes.php';
            if (file_exists($routesFile)) {
                error_log('Application::loadRoutes - Loading main routes from: ' . $routesFile);
                
                $routesCallback = require $routesFile;
                if (is_callable($routesCallback)) {
                    $routesCallback($this);
                    error_log('Application::loadRoutes - Main routes loaded successfully');
                } else {
                    error_log('Application::loadRoutes - Main routes file did not return a callable');
                }
            } else {
                error_log('Application::loadRoutes - Main routes file not found: ' . $routesFile);
            }
            
            // Load i18n routes
            $this->loadI18nRoutes();
            
        } catch (\Exception $e) {
            error_log('Application::loadRoutes - Failed to load routes: ' . $e->getMessage());
            error_log('Application::loadRoutes - Stack trace: ' . $e->getTraceAsString());
        }
    }
    
    /**
     * Load i18n routes for all supported languages.
     */
    protected function loadI18nRoutes(): void
    {
        try {
            $i18nConfigPath = $this->basePath . '/i18n/config.php';
            if (!file_exists($i18nConfigPath)) {
                error_log('Application::loadI18nRoutes - i18n config not found: ' . $i18nConfigPath);
                return;
            }
            
            $i18nConfig = require $i18nConfigPath;
            $supportedLanguages = $i18nConfig['languages'] ?? [];
            
            foreach ($supportedLanguages as $langCode => $langConfig) {
                if (!($langConfig['enabled'] ?? false)) {
                    continue;
                }
                
                $langRoutesFile = $this->basePath . "/i18n/{$langCode}/routes.php";
                if (file_exists($langRoutesFile)) {
                    error_log("Application::loadI18nRoutes - Loading {$langCode} routes from: {$langRoutesFile}");
                    
                    $routesCallback = require $langRoutesFile;
                    if (is_callable($routesCallback)) {
                        $routesCallback($this);
                        error_log("Application::loadI18nRoutes - {$langCode} routes loaded successfully");
                    } else {
                        error_log("Application::loadI18nRoutes - {$langCode} routes file did not return a callable");
                    }
                } else {
                    error_log("Application::loadI18nRoutes - {$langCode} routes file not found: {$langRoutesFile}");
                }
            }
            
        } catch (\Exception $e) {
            error_log('Application::loadI18nRoutes - Failed to load i18n routes: ' . $e->getMessage());
            error_log('Application::loadI18nRoutes - Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    public function handleRequest(Request $request): Response
    {
        try {
            $this->logger->info('Handling request', [
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath()
            ]);

            // Apply i18n middleware
            $i18nMiddleware = new \IslamWiki\Core\I18n\I18nMiddleware($this->container, $this->logger);
            
            $response = $i18nMiddleware->process($request, function ($request) {
                // Dispatch request through router
                return $this->router->dispatch($request);
            });

            $this->logger->info('Request handled successfully', [
                'status' => $response->getStatusCode()
            ]);

            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Request handling failed', [
                'error' => $e->getMessage()
            ]);

            // Return error response
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Send a response to the client.
     *
     * @param Response $response The HTTP response
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
        } catch (\Exception $e) {
            // Fallback to simple output
            http_response_code(500);
            echo 'Internal Server Error';
        }
    }
}
