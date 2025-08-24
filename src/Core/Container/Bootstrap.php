<?php

/**
 * This file is part of IslamWiki.
 *
 * (c) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
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

use Container;\Container
use Logger;\Logger
use Exception;

/**
 * ContainerBootstrap (أساس) - Application Bootstrap System
 *
 * Container provides "Container" in Arabic. This class provides the bootstrap
 * functionality for initializing the IslamWiki application, including
 * environment detection, service provider bootstrapping, error handling,
 * and logging setup.
 *
 * This is the entry point for the application and ensures all foundational
 * systems are properly initialized before the application starts.
 *
 * @category  Core
 * @package   IslamWiki\Core\Container
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.1.1
 */
class ContainerBootstrap
{
    /**
     * The service container.
     */
    protected Container $container;

    /**
     * The foundation system.
     */
    protected Container $foundation;

    /**
     * The logging system.
     */
    protected Logger $logger;

    /**
     * The application base path.
     */
    protected string $basePath;

    /**
     * The environment configuration.
     */
    protected string $environment;

    /**
     * Whether the application has been bootstrapped.
     */
    protected bool $bootstrapped = false;

    /**
     * Service providers to register.
     *
     * @var array<string>
     */
    protected array $serviceProviders = [];

    /**
     * Bootstrap configuration.
     *
     * @var array<string, mixed>
     */
    protected array $bootstrapConfig = [];

    /**
     * Constructor.
     *
     * @param string $basePath The application base path
     */
    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath ?: $this->detectBasePath();
        $this->environment = $this->detectEnvironment();
        $this->loadBootstrapConfig();
    }

    /**
     * Bootstrap the application.
     *
     * @return self
     * @throws Exception If bootstrapping fails
     */
    public function bootstrap(): self
    {
        if ($this->bootstrapped) {
            return $this;
        }

        try {
            $this->logger = new LoggerLogger($this->basePath . '/logs');
            $this->logger->info('Starting IslamWiki bootstrap process');

            $this->initializeContainer();
            $this->initializeContainer();
            $this->registerServiceProviders();
            $this->bootServiceProviders();
            $this->finalizeBootstrap();

            $this->bootstrapped = true;
            $this->logger->info('IslamWiki bootstrap completed successfully');

        } catch (Exception $e) {
            if (isset($this->logger)) {
                $this->logger->error('Bootstrap failed: ' . $e->getMessage());
            }
            throw $e;
        }

        return $this;
    }

    /**
     * Initialize the service container.
     *
     * @return self
     */
    protected function initializeContainer(): self
    {
        $this->container = new ContainerContainer();
        $this->logger->info('Service container initialized');

        return $this;
    }

    /**
     * Initialize the foundation system.
     *
     * @return self
     */
    protected function initializeContainer(): self
    {
        $this->foundation = new ContainerContainer($this->container, $this->basePath);
        $this->foundation->initialize();
        $this->logger->info('Container system initialized');

        return $this;
    }

    /**
     * Register service providers.
     *
     * @return self
     */
    protected function registerServiceProviders(): self
    {
        // Register core service providers
        $coreProviders = [
            'IslamWiki\\Core\\Providers\\CoreServiceProvider',
            'IslamWiki\\Core\\Providers\\DatabaseServiceProvider',
            'IslamWiki\\Core\\Providers\\RoutingServiceProvider',
            'IslamWiki\\Core\\Providers\\ViewServiceProvider',
            'IslamWiki\\Core\\Providers\\SecurityServiceProvider',
            'IslamWiki\\Core\\Providers\\ExtensionServiceProvider',
        ];

        foreach ($coreProviders as $provider) {
            if (class_exists($provider)) {
                $this->container->register($provider);
                $this->logger->info("Registered service provider: {$provider}");
            }
        }

        // Register environment-specific providers
        if ($this->environment === 'development') {
            $devProviders = [
                'IslamWiki\\Core\\Providers\\DevelopmentServiceProvider',
                'IslamWiki\\Core\\Providers\\DebugServiceProvider',
            ];

            foreach ($devProviders as $provider) {
                if (class_exists($provider)) {
                    $this->container->register($provider);
                    $this->logger->info("Registered development provider: {$provider}");
                }
            }
        }

        return $this;
    }

    /**
     * Boot all registered service providers.
     *
     * @return self
     */
    protected function bootServiceProviders(): self
    {
        $this->container->boot();
        $this->logger->info('Service providers booted');

        return $this;
    }

    /**
     * Finalize the bootstrap process.
     *
     * @return self
     */
    protected function finalizeBootstrap(): self
    {
        // Set up error handling
        $this->setupErrorHandling();

        // Set up logging
        $this->setupLogging();

        // Set up environment
        $this->setupEnvironment();

        // Set up security
        $this->setupSecurity();

        $this->logger->info('Bootstrap finalization completed');

        return $this;
    }

    /**
     * Set up error handling.
     *
     * @return self
     */
    protected function setupErrorHandling(): self
    {
        // Set error reporting based on environment
        if ($this->environment === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', '0');
        }

        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);

        return $this;
    }

    /**
     * Set up logging configuration.
     *
     * @return self
     */
    protected function setupLogging(): self
    {
        // Configure logging based on environment
        $logLevel = $this->environment === 'development' ? 'debug' : 'info';
        $this->logger->setMinLevel($logLevel);

        return $this;
    }

    /**
     * Set up environment configuration.
     *
     * @return self
     */
    protected function setupEnvironment(): self
    {
        // Set timezone
        $timezone = $this->bootstrapConfig['timezone'] ?? 'UTC';
        date_default_timezone_set($timezone);

        // Set locale
        $locale = $this->bootstrapConfig['locale'] ?? 'en';
        setlocale(LC_ALL, $locale);

        // Set character encoding
        ini_set('default_charset', 'UTF-8');

        return $this;
    }

    /**
     * Set up security configuration.
     *
     * @return self
     */
    protected function setupSecurity(): self
    {
        // Security headers
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            
            if ($this->environment === 'production') {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }

        // Session security
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $this->environment === 'production' ? '1' : '0');
        ini_set('session.use_strict_mode', '1');

        return $this;
    }

    /**
     * Handle PHP errors.
     *
     * @param int    $errno   Error number
     * @param string $errstr  Error string
     * @param string $errfile Error file
     * @param int    $errline Error line
     * @return bool
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorType = $this->getErrorType($errno);
        $message = "PHP {$errorType}: {$errstr} in {$errfile} on line {$errline}";

        if (isset($this->logger)) {
            $this->logger->error($message);
        }

        if ($this->environment === 'development') {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }

        return true;
    }

    /**
     * Handle uncaught exceptions.
     *
     * @param Throwable $exception The exception
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        $message = "Uncaught Exception: " . $exception->getMessage();
        
        if (isset($this->logger)) {
            $this->logger->critical($message, [
                'exception' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        }

        if ($this->environment === 'development') {
            throw $exception;
        }

        // In production, show a generic error page
        $this->showErrorPage(500, 'Internal Server Error');
    }

    /**
     * Get error type string from error number.
     *
     * @param int $errno Error number
     * @return string
     */
    protected function getErrorType(int $errno): string
    {
        $errorTypes = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        return $errorTypes[$errno] ?? 'Unknown Error';
    }

    /**
     * Show error page.
     *
     * @param int    $statusCode HTTP status code
     * @param string $message    Error message
     * @return void
     */
    protected function showErrorPage(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        
        if (headers_sent()) {
            return;
        }

        echo "<!DOCTYPE html>
<html>
<head>
    <title>Error {$statusCode}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
        .error { color: #721c24; background-color: #f8d7da; padding: 20px; border-radius: 4px; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Error {$statusCode}</h1>
        <div class='error'>{$message}</div>
        <p>Please try again later or contact support if the problem persists.</p>
    </div>
</body>
</html>";
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
     * Detect the application environment.
     *
     * @return string
     */
    protected function detectEnvironment(): string
    {
        // Check environment variable first
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null;
        
        if ($env) {
            return $env;
        }

        // Check for .env file
        $envFile = $this->basePath . '/.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            if (preg_match('/APP_ENV\s*=\s*(\w+)/', $envContent, $matches)) {
                return $matches[1];
            }
        }

        // Check hostname for development
        $hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (in_array($hostname, ['localhost', '127.0.0.1', 'local.islam.wiki'])) {
            return 'development';
        }

        // Default to production
        return 'production';
    }

    /**
     * Load bootstrap configuration.
     *
     * @return self
     */
    protected function loadBootstrapConfig(): self
    {
        $configFile = $this->basePath . '/config/bootstrap.php';
        
        if (file_exists($configFile)) {
            $this->bootstrapConfig = include $configFile;
        } else {
            // Default configuration
            $this->bootstrapConfig = [
                'timezone' => 'UTC',
                'locale' => 'en',
                'debug' => $this->environment === 'development',
                'log_level' => $this->environment === 'development' ? 'debug' : 'info',
            ];
        }

        return $this;
    }

    /**
     * Get the service container.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the foundation system.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->foundation;
    }

    /**
     * Get the logging system.
     *
     * @return Logger
     */
    public function getLogger(): Logger
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
     * Get the environment.
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Check if the application has been bootstrapped.
     *
     * @return bool
     */
    public function isBootstrapped(): bool
    {
        return $this->bootstrapped;
    }

    /**
     * Get bootstrap configuration.
     *
     * @return array<string, mixed>
     */
    public function getBootstrapConfig(): array
    {
        return $this->bootstrapConfig;
    }

    /**
     * Get bootstrap statistics.
     *
     * @return array<string, mixed>
     */
    public function getBootstrapStats(): array
    {
        return [
            'bootstrapped' => $this->bootstrapped,
            'environment' => $this->environment,
            'base_path' => $this->basePath,
            'service_providers' => count($this->serviceProviders),
            'bootstrap_config' => $this->bootstrapConfig,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
        ];
    }
} 