<?php

/**
 * IslamWiki Main Application Entry Point - Debug Version
 *
 * This file handles all application routes including authentication,
 * dashboard, profile, settings, and the homepage.
 *
 * @package IslamWiki
 * @version 0.0.34
 * @license AGPL-3.0-only
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/islamwiki-debug.log');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Use statements
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Routing\IslamRouter;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

echo "<h1>Debug: Starting Application</h1>";

try {
    // Include Composer autoloader
    echo "<p>Loading autoloader...</p>";
    require_once BASE_PATH . '/vendor/autoload.php';
    echo "<p>✅ Autoloader loaded</p>";

    // Include necessary files
    echo "<p>Loading core files...</p>";
    require_once BASE_PATH . '/src/Core/Container/AsasContainer.php';
    require_once BASE_PATH . '/src/Core/Database/Connection.php';
    require_once BASE_PATH . '/src/Core/Routing/IslamRouter.php';
    require_once BASE_PATH . '/src/Core/Auth/AmanSecurity.php';
    require_once BASE_PATH . '/src/Core/Session/Wisal.php';
    require_once BASE_PATH . '/src/Core/Routing/ControllerFactory.php';
    require_once BASE_PATH . '/src/Core/Auth/AmanSecurity.php';
    require_once BASE_PATH . '/src/Providers/SkinServiceProvider.php';
    require_once BASE_PATH . '/src/Core/NizamApplication.php';
    require_once BASE_PATH . '/src/Http/Controllers/Auth/AuthController.php';
    require_once BASE_PATH . '/src/Http/Controllers/DashboardController.php';
    require_once BASE_PATH . '/src/Http/Controllers/ProfileController.php';
    require_once BASE_PATH . '/src/Http/Controllers/SettingsController.php';
    require_once BASE_PATH . '/src/Http/Controllers/HomeController.php';
    require_once BASE_PATH . '/src/Http/Controllers/PageController.php';
    require_once BASE_PATH . '/src/Core/View/TwigRenderer.php';
    require_once BASE_PATH . '/src/Http/Controllers/SearchController.php';
    require_once BASE_PATH . '/src/Http/Controllers/IqraSearchController.php';
    require_once BASE_PATH . '/src/Core/Search/IqraSearch.php';
    require_once BASE_PATH . '/src/Models/Page.php';
    require_once BASE_PATH . '/src/Models/QuranVerse.php';
    require_once BASE_PATH . '/src/Models/Hadith.php';
    require_once BASE_PATH . '/src/Models/IslamicCalendar.php';
    require_once BASE_PATH . '/src/Models/PrayerTime.php';
    echo "<p>✅ Core files loaded</p>";

    // Initialize Application (which creates its own container)
    echo "<p>Initializing application...</p>";
    $app = new \IslamWiki\Core\Application(BASE_PATH);
    echo "<p>✅ Application created</p>";

    $container = $app->getContainer();
    echo "<p>✅ Container retrieved</p>";

    // Register the application in the container
    $container->instance('app', $app);
    echo "<p>✅ Application registered in container</p>";

    // Get the database connection from the application's container
    echo "<p>Getting database connection...</p>";
    $db = $container->get('db');
    echo "<p>✅ Database connection retrieved</p>";

    // Initialize and register Wisal connection manager
    echo "<p>Initializing session manager...</p>";
    $sessionManager = new \IslamWiki\Core\Session\Wisal();
    $sessionManager->start(); // Start the session
    $container->instance('session', $sessionManager);
    echo "<p>✅ Session manager initialized</p>";

    // Initialize and register Aman
    echo "<p>Initializing auth manager...</p>";
    $authManager = new \IslamWiki\Core\Auth\AmanSecurity($sessionManager, $db);
    $container->instance('auth', $authManager);
    echo "<p>✅ Auth manager initialized</p>";

    // Create a simple logger (since we don't have a proper logger yet)
    echo "<p>Creating logger...</p>";
    $logger = new class implements \Psr\Log\LoggerInterface {
        public function emergency($message, array $context = [])
        {
            error_log("EMERGENCY: $message");
        }
        public function alert($message, array $context = [])
        {
            error_log("ALERT: $message");
        }
        public function critical($message, array $context = [])
        {
            error_log("CRITICAL: $message");
        }
        public function error($message, array $context = [])
        {
            error_log("ERROR: $message");
        }
        public function warning($message, array $context = [])
        {
            error_log("WARNING: $message");
        }
        public function notice($message, array $context = [])
        {
            error_log("NOTICE: $message");
        }
        public function info($message, array $context = [])
        {
            error_log("INFO: $message");
        }
        public function debug($message, array $context = [])
        {
            error_log("DEBUG: $message");
        }
        public function log($level, $message, array $context = [])
        {
            error_log("LOG[$level]: $message");
        }
    };

    // Register logger in container
    $container->instance(\Psr\Log\LoggerInterface::class, $logger);
    echo "<p>✅ Logger created and registered</p>";

    // Initialize and register TwigRenderer
    echo "<p>Initializing Twig renderer...</p>";
    $twigRenderer = new \IslamWiki\Core\View\TwigRenderer(
        BASE_PATH . '/resources/views',
        BASE_PATH . '/storage/framework/views',
        true // debug mode
    );
    $container->instance('view', $twigRenderer);
    echo "<p>✅ Twig renderer initialized</p>";

    // Initialize and register controller factory
    echo "<p>Initializing controller factory...</p>";
    $controllerFactory = new \IslamWiki\Core\Routing\ControllerFactory($db, $logger, $container);
    $container->instance('controller.factory', $controllerFactory);
    echo "<p>✅ Controller factory initialized</p>";

    // Initialize router
    echo "<p>Initializing router...</p>";
    $router = new IslamRouter($container);
    echo "<p>✅ Router initialized</p>";

    // Load routes
    echo "<p>Loading routes...</p>";
    require_once BASE_PATH . '/routes/web.php';
    echo "<p>✅ Routes loaded</p>";

    // Get current request
    echo "<p>Capturing request...</p>";
    $request = Request::capture();
    echo "<p>✅ Request captured</p>";
    echo "<p>Request URI: " . $request->getUri() . "</p>";
    echo "<p>Request Method: " . $request->getMethod() . "</p>";

    // Handle the request
    echo "<p>Handling request...</p>";
    $response = $router->handle($request);
    echo "<p>✅ Request handled</p>";
    echo "<p>Response Status: " . $response->getStatusCode() . "</p>";

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
    echo "<h1>❌ Application Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<h2>Stack Trace:</h2>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";

    http_response_code(500);
}
