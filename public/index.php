<?php

/**
 * IslamWiki Main Application Entry Point
 *
 * This file handles all application routes including authentication,
 * dashboard, profile, settings, and the homepage.
 *
 * @category  Application
 * @package   IslamWiki
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.34
 */

file_put_contents(
    __DIR__ . '/../storage/logs/debug.log',
    "\n[" . date('Y-m-d H:i:s') . "] Entered index.php\n",
    FILE_APPEND
);

// Temporarily redirect error_log to a file to prevent output before headers
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Include helpers
require_once BASE_PATH . '/src/helpers.php';

// Load LocalSettings.php for configuration
require_once BASE_PATH . '/LocalSettings.php';

// Include necessary files
require_once BASE_PATH . '/src/Core/Container/AsasContainer.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';
require_once BASE_PATH . '/src/Core/Routing/SabilRouting.php';
require_once BASE_PATH . '/src/Core/Auth/AmanSecurity.php';
require_once BASE_PATH . '/src/Core/Session/WisalSession.php';
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
require_once BASE_PATH . '/src/Http/Controllers/Controller.php';
require_once BASE_PATH . '/src/Http/Controllers/SearchController.php';
require_once BASE_PATH . '/src/Http/Controllers/IqraSearchController.php';
require_once BASE_PATH . '/src/Http/Controllers/QuranController.php';
require_once BASE_PATH . '/src/Http/Controllers/HadithController.php';
require_once BASE_PATH . '/src/Http/Controllers/SalahTimeController.php';
require_once BASE_PATH . '/src/Http/Controllers/IslamicCalendarController.php';
require_once BASE_PATH . '/src/Http/Controllers/CommunityController.php';
require_once BASE_PATH . '/src/Http/Controllers/SciencesController.php';
require_once BASE_PATH . '/src/Core/Search/IqraSearch.php';
require_once BASE_PATH . '/src/Models/Page.php';
require_once BASE_PATH . '/src/Models/QuranVerse.php';
require_once BASE_PATH . '/src/Models/Hadith.php';
require_once BASE_PATH . '/src/Models/IslamicCalendar.php';
require_once BASE_PATH . '/src/Models/SalahTime.php';
require_once BASE_PATH . '/src/Http/Controllers/AssetController.php';
require_once BASE_PATH . '/src/Http/Controllers/WikiController.php';
require_once BASE_PATH . '/src/Http/Controllers/SpecialController.php';
require_once BASE_PATH . '/src/Core/Wiki/NamespaceManager.php';

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Routing\SabilRouting;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

// Initialize Application (which creates its own container)
error_log("MAIN ENTRY POINT: index.php is being executed");
$app = new \IslamWiki\Core\NizamApplication(BASE_PATH);

// Boot the application to start all systems including session
$app->boot();

$container = $app->getContainer();

// Get services from the application
$db = $container->get('db');
$sessionManager = $container->get('session');
$logger = $container->get('logger');
$view = $container->get('view');

// Initialize and register controller factory
$controllerFactory = new \IslamWiki\Core\Routing\ControllerFactory(
    $db,
    $logger,
    $container
);
$container->instance('controller.factory', $controllerFactory);

// Initialize router
$router = new SabilRouting($container);
global $router;

// Load routes
error_log("index.php: About to load routes");
require_once BASE_PATH . '/routes/web.php';
// error_log("index.php: Routes loaded, router class: " . get_class($router));

// Get current request
$request = Request::capture();

// Handle the request
// error_log("index.php: About to handle request with router: " . get_class($router));
// error_log("index.php: Request URI: " . $request->getUri()->getPath());
// error_log("index.php: Request method: " . $request->getMethod());
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
