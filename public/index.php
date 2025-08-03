<?php
file_put_contents(__DIR__ . '/../storage/logs/debug.log', "\n[" . date('Y-m-d H:i:s') . "] Entered index.php\n", FILE_APPEND);
/**
 * IslamWiki Main Application Entry Point
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

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Include necessary files
require_once BASE_PATH . '/src/Core/Container/Asas.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';
require_once BASE_PATH . '/src/Core/Routing/IslamRouter.php';
require_once BASE_PATH . '/src/Core/Auth/Aman.php';
require_once BASE_PATH . '/src/Core/Session/Wisal.php';
require_once BASE_PATH . '/src/Core/Routing/ControllerFactory.php';
require_once BASE_PATH . '/src/Core/Auth/Aman.php';
require_once BASE_PATH . '/src/Providers/SkinServiceProvider.php';
require_once BASE_PATH . '/src/Core/Application.php';
require_once BASE_PATH . '/src/Http/Controllers/Auth/AuthController.php';
require_once BASE_PATH . '/src/Http/Controllers/DashboardController.php';
require_once BASE_PATH . '/src/Http/Controllers/ProfileController.php';
require_once BASE_PATH . '/src/Http/Controllers/SettingsController.php';
require_once BASE_PATH . '/src/Http/Controllers/HomeController.php';
require_once BASE_PATH . '/src/Http/Controllers/PageController.php';
require_once BASE_PATH . '/src/Core/View/TwigRenderer.php';
require_once BASE_PATH . '/src/Http/Controllers/SearchController.php';
require_once BASE_PATH . '/src/Http/Controllers/IqraSearchController.php';
require_once BASE_PATH . '/src/Core/Search/IqraSearchEngine.php';
require_once BASE_PATH . '/src/Models/Page.php';
require_once BASE_PATH . '/src/Models/QuranVerse.php';
require_once BASE_PATH . '/src/Models/Hadith.php';
require_once BASE_PATH . '/src/Models/IslamicCalendar.php';
require_once BASE_PATH . '/src/Models/PrayerTime.php';

use IslamWiki\Core\Container\Asas;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Routing\IslamRouter;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

// Initialize Application (which creates its own container)
$app = new \IslamWiki\Core\Application(BASE_PATH);
$container = $app->getContainer();

// Register the application in the container
$container->instance('app', $app);



// Get the database connection from the application's container
$db = $container->get('db');

// Initialize and register Wisal connection manager
$sessionManager = new \IslamWiki\Core\Session\Wisal();
$sessionManager->start(); // Start the session
$container->instance('session', $sessionManager);

// Initialize and register Aman
$authManager = new \IslamWiki\Core\Auth\Aman($sessionManager, $db);
$container->instance('auth', $authManager);

// Create a simple logger (since we don't have a proper logger yet)
$logger = new class implements \Psr\Log\LoggerInterface {
    public function emergency($message, array $context = []) { error_log("EMERGENCY: $message"); }
    public function alert($message, array $context = []) { error_log("ALERT: $message"); }
    public function critical($message, array $context = []) { error_log("CRITICAL: $message"); }
    public function error($message, array $context = []) { error_log("ERROR: $message"); }
    public function warning($message, array $context = []) { error_log("WARNING: $message"); }
    public function notice($message, array $context = []) { error_log("NOTICE: $message"); }
    public function info($message, array $context = []) { error_log("INFO: $message"); }
    public function debug($message, array $context = []) { error_log("DEBUG: $message"); }
    public function log($level, $message, array $context = []) { error_log("LOG[$level]: $message"); }
};

// Register logger in container
$container->instance(\Psr\Log\LoggerInterface::class, $logger);

// Initialize and register TwigRenderer
$twigRenderer = new \IslamWiki\Core\View\TwigRenderer(
    BASE_PATH . '/resources/views',
    BASE_PATH . '/storage/framework/views',
    true // debug mode
);
$container->instance('view', $twigRenderer);

// Initialize and register controller factory
$controllerFactory = new \IslamWiki\Core\Routing\ControllerFactory($db, $logger, $container);
$container->instance('controller.factory', $controllerFactory);

// Initialize router
$router = new IslamRouter($container);

// Load routes
require_once BASE_PATH . '/routes/web.php';

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
?>


