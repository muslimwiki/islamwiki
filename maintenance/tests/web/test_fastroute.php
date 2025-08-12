<?php

declare(strict_types=1);

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Set error log path
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . '/php_errors.log');

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Simple response function
function sendResponse($status, $headers, $body)
{
    http_response_code($status);
    foreach ($headers as $name => $value) {
        header("$name: $value");
    }
    echo $body;
}

// Simple router implementation
function handleRequest()
{
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Get the script filename without path
    $scriptName = basename($_SERVER['SCRIPT_NAME']);

    // Handle direct script access (e.g., /test_fastroute.php)
    if ($requestUri === '/' . $scriptName || $requestUri === '/' . $scriptName . '/') {
        $path = '/';
    }
    // Handle script with path (e.g., /test_fastroute.php/test)
    elseif (strpos($requestUri, '/' . $scriptName . '/') === 0) {
        $path = substr($requestUri, strlen('/' . $scriptName));
    }
    // Handle direct path access (e.g., /test)
    else {
        $path = $requestUri;
    }

    // Clean up the path
    $path = '/' . ltrim($path, '/');
    $path = $path === '' ? '/' : $path;

    $basePath = ''; // Not used in this version

    // Define available routes
    $routes = [
        'GET' => [
            '/' => function () {
                return ['title' => 'Home', 'content' => '<h2>Welcome to the Home Page</h2>'];
            },
            '/test' => function () {
                return ['title' => 'Test', 'content' => '<h2>Test Route Works!</h2>'];
            },
            '/test/{id}' => function ($params) {
                $testId = $params['id'] ?? 'unknown';
                return ['title' => 'Test with ID', 'content' => "<h2>Test with ID: $testId</h2>"];
            },
            '/dashboard' => function () {
                return ['title' => 'Dashboard', 'content' => '<h2>Dashboard</h2><p>Welcome to your dashboard!</p>'];
            },
            '/user/{id}' => function ($params) {
                $userId = $params['id'] ?? 'unknown';
                return ['title' => 'User Profile', 'content' => "<h2>User Profile</h2><p>User ID: $userId</p>"];
            },
        ],
        'POST' => [
            '/submit' => function () {
                $data = $_POST;
                return [
                    'title' => 'Form Submitted',
                    'content' => '<h2>Form Submitted Successfully!</h2><pre>' .
                                htmlspecialchars(print_r($data, true)) . '</pre>'
                ];
            }
        ]
    ];

    // Debug information
    $debug = [
        'request_uri' => $requestUri,
        'base_path' => $basePath,
        'path' => $path,
        'request_method' => $requestMethod,
        'available_routes' => array_keys($routes['GET'] + $routes['POST']),
        'matched_route' => null,
        'route_params' => []
    ];

    // Check for matching route
    $matchedRoute = null;
    $routeParams = [];

    // Check if we have routes for this HTTP method
    if (isset($routes[$requestMethod])) {
        foreach ($routes[$requestMethod] as $route => $handler) {
            // Convert route to regex pattern
            // Escape forward slashes first
            $pattern = str_replace('/', '\/', $route);
            // Replace {param} with named capture group
            $pattern = preg_replace('/\{([^\/]+)\}/', '(?P<$1>[^\/]+)', $pattern);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                $matchedRoute = $route;
                $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                break;
            }
        }
    }

    $debug['matched_route'] = $matchedRoute;
    $debug['route_params'] = $routeParams;

    // Handle the matched route
    if ($matchedRoute !== null && isset($routes[$requestMethod][$matchedRoute])) {
        $handler = $routes[$requestMethod][$matchedRoute];
        try {
            $result = $handler($routeParams);

            $html = "<h1>{$result['title']}</h1>";
            $html .= $result['content'];

            // Add navigation
            $html .= "<hr><h3>Navigation</h3><ul>";
            $html .= "<li><a href='{$basePath}/'>Home</a></li>";
            $html .= "<li><a href='{$basePath}/test'>Test Route</a></li>";
            $html .= "<li><a href='{$basePath}/dashboard'>Dashboard</a></li>";
            $html .= "<li><a href='{$basePath}/user/123'>User Profile (123)</a></li>";
            $html .= "</ul>";

            // Add debug info
            $html .= "<hr><h3>Debug Information</h3>";
            $html .= "<pre>" . htmlspecialchars(print_r($debug, true)) . "</pre>";

            sendResponse(200, ['Content-Type' => 'text/html'], $html);
            return;
        } catch (\Exception $e) {
            error_log('Error in route handler: ' . $e->getMessage());
            sendResponse(500, ['Content-Type' => 'text/html'], '<h2>500 Internal Server Error</h2>');
            return;
        }
    }

    // 404 Not Found
    $html = "<h1>404 Not Found</h1>";
    $html .= "<p>The requested URL was not found on this server.</p>";
    $html .= "<p><a href='{$basePath}/'>Go to Homepage</a></p>";

    // Add debug info
    $html .= "<hr><h3>Debug Information</h3>";
    $html .= "<pre>" . htmlspecialchars(print_r($debug, true)) . "</pre>";

    sendResponse(404, ['Content-Type' => 'text/html'], $html);
}

// Handle the request
handleRequest();
