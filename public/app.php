<?php

/**
 * IslamWiki Main Application Entry Point
 *
 * Handles routing for the main application including authentication,
 * user management, and other core features.
 *
 * @category  Application
 * @package   IslamWiki
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.34
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Include necessary files
require_once BASE_PATH . '/src/Core/Container/AsasContainer.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';
require_once BASE_PATH . '/src/Core/Auth/AmanSecurity.php';
require_once BASE_PATH . '/src/Core/Session/WisalSession.php';
require_once BASE_PATH . '/src/Core/Routing/ControllerFactory.php';
require_once BASE_PATH . '/src/Http/Controllers/Auth/AuthController.php';
require_once BASE_PATH . '/src/Http/Controllers/DashboardController.php';
require_once BASE_PATH . '/src/Http/Controllers/ProfileController.php';
require_once BASE_PATH . '/src/Http/Controllers/SettingsController.php';
require_once BASE_PATH . '/src/Http/Controllers/HomeController.php';
require_once BASE_PATH . '/src/Http/Controllers/PageController.php';
require_once BASE_PATH . '/src/Core/View/TwigRenderer.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Routing\SabilRouting;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

// Initialize container
error_log("MAIN ENTRY POINT: app.php is being executed");
$container = new AsasContainer();

// Initialize database connection
$db = new Connection();

// Register database connection in container
$container->instance('db', $db);
$container->instance('connection', $db);

// Initialize and register session manager
$sessionManager = new \IslamWiki\Core\Session\WisalSession();
$container->instance('session', $sessionManager);

// Session will be started by SessionServiceProvider during boot
error_log("MAIN ENTRY POINT: Session manager created and registered");

// Create a simple logger (since we don't have a proper logger yet)
$simpleLogger = new class implements \Psr\Log\LoggerInterface
{
    /**
     * Log emergency message
     *
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        error_log("EMERGENCY: $message");
    }

    /**
     * Log alert message
     *
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        error_log("ALERT: $message");
    }

    /**
     * Log critical message
     *
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        error_log("CRITICAL: $message");
    }

    /**
     * Log error message
     *
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        error_log("ERROR: $message");
    }

    /**
     * Log warning message
     *
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        error_log("WARNING: $message");
    }

    /**
     * Log notice message
     *
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        error_log("NOTICE: $message");
    }

    /**
     * Log info message
     *
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        error_log("INFO: $message");
    }

    /**
     * Log debug message
     *
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        error_log("DEBUG: $message");
    }

    /**
     * Log message with level
     *
     * @param mixed  $level   Log level
     * @param string $message Message to log
     * @param array  $context Context data
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        error_log("LOG[$level]: $message");
    }
};

// Create proper ShahidLogger instance
$shahidLogger = new \IslamWiki\Core\Logging\ShahidLogger(BASE_PATH . '/storage/logs');

// Register logger in container
$container->instance(\Psr\Log\LoggerInterface::class, $simpleLogger);
$container->instance('logger', $simpleLogger);
$container->instance(\IslamWiki\Core\Logging\ShahidLogger::class, $shahidLogger);

// Register service providers
error_log("MAIN ENTRY POINT: Registering service providers");

// Register DatabaseServiceProvider first (needed by other services)
require_once BASE_PATH . '/src/Providers/DatabaseServiceProvider.php';
$databaseProvider = new \IslamWiki\Providers\DatabaseServiceProvider();
$databaseProvider->register($container);

// Register SessionServiceProvider (needed by AuthServiceProvider)
require_once BASE_PATH . '/src/Providers/SessionServiceProvider.php';
$sessionProvider = new \IslamWiki\Providers\SessionServiceProvider();
$sessionProvider->register($container);

// Register AuthServiceProvider (needs session and db)
require_once BASE_PATH . '/src/Providers/AuthServiceProvider.php';
$authProvider = new \IslamWiki\Providers\AuthServiceProvider();
$authProvider->register($container);

// Register LanguageServiceProvider BEFORE ViewServiceProvider (needed for TwigTranslationExtension)
require_once BASE_PATH . '/src/Providers/LanguageServiceProvider.php';
$languageProvider = new \IslamWiki\Providers\LanguageServiceProvider();
$languageProvider->register($container);

// Register ViewServiceProvider (needs TwigTranslationExtension from LanguageServiceProvider)
require_once BASE_PATH . '/src/Providers/ViewServiceProvider.php';
$viewProvider = new \IslamWiki\Providers\ViewServiceProvider();
$viewProvider->register($container);

// Register StaticDataServiceProvider
require_once BASE_PATH . '/src/Providers/StaticDataServiceProvider.php';
$staticDataProvider = new \IslamWiki\Providers\StaticDataServiceProvider();
$staticDataProvider->register($container);

// Register SkinServiceProvider
require_once BASE_PATH . '/src/Providers/SkinServiceProvider.php';
$skinProvider = new \IslamWiki\Providers\SkinServiceProvider();
$skinProvider->register($container);

// Boot all service providers
error_log("MAIN ENTRY POINT: Booting service providers");
$sessionProvider->boot($container);
$authProvider->boot($container);
$viewProvider->boot($container);
$staticDataProvider->boot($container);
$skinProvider->boot($container);
$languageProvider->boot($container);

// Initialize and register controller factory
$controllerFactory = new \IslamWiki\Core\Routing\ControllerFactory($db, $simpleLogger, $container);
$container->instance('controller.factory', $controllerFactory);

// Initialize router
$router = new SabilRouting($container);

// Temporarily disable LanguageMiddleware to get the system working
// TODO: Fix middleware system and re-enable
/*
// Add LanguageMiddleware to handle language detection and context
try {
    $languageMiddleware = $container->get(\IslamWiki\Core\Http\Middleware\LanguageMiddleware::class);
    $router->addMiddleware($languageMiddleware);
    error_log("Successfully added LanguageMiddleware to router");
} catch (\Exception $e) {
    error_log("Could not add LanguageMiddleware: " . $e->getMessage());
}
*/

// Load routes
require_once BASE_PATH . '/public/routes.php';

// Get current request
$request = Request::capture();

// Handle the request
try {
    $response = $router->handle($request);

    // Send response
    http_response_code($response->getStatusCode());

    // Set headers
    foreach ($response->getHeaders() as $name => $values) {
        if (is_array($values)) {
            foreach ($values as $value) {
                header("$name: $value");
            }
        } else {
            header("$name: $values");
        }
    }

    // Output content
    echo $response->getBody();
} catch (\Exception $e) {
    // Handle errors
    http_response_code(500);
    echo '<h1>Application Error</h1>';
    echo '<p>An error occurred while processing your request.</p>';
    if (ini_get('display_errors')) {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
}
