<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/php_errors.log');

// Simple autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Simple request handler
$request = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
    'query' => $_GET,
    'body' => file_get_contents('php://input'),
];

// Debug output
error_log('Request: ' . print_r($request, true));

// Simple router
$routes = [
    'GET /' => ['App\Http\Controllers\PageController', 'home'],
    'GET /pages/{slug}' => ['App\Http\Controllers\PageController', 'show'],
];

// Match route
$handler = null;
$params = [];

error_log('Available routes: ' . print_r($routes, true));

foreach ($routes as $pattern => $handlerInfo) {
    list($method, $path) = explode(' ', $pattern, 2);
    
    if ($method !== $request['method']) {
        continue;
    }
    
    // Convert route pattern to regex
    $pattern = '#^' . preg_replace('/\{([^\}]+)\}/', '(?P<$1>[^/]+)', $path) . '$#';
    
    if (preg_match($pattern, $request['uri'], $matches)) {
        $handler = $handlerInfo;
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        break;
    }
}

// Handle request
if ($handler) {
    list($controllerClass, $method) = $handler;
    error_log("Calling handler: $controllerClass::$method");
    
    try {
        // Try to create controller instance
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller class not found: $controllerClass");
        }
        
        $controller = new $controllerClass();
        
        // Check if method exists
        if (!method_exists($controller, $method)) {
            throw new \Exception("Method not found: $controllerClass::$method");
        }
        
        // Call the controller method with parameters
        $response = call_user_func_array([$controller, $method], $params);
        
        // Output the response
        if (is_array($response) || is_object($response)) {
            header('Content-Type: application/json');
            echo json_encode($response, JSON_PRETTY_UNESCAPED_SLASHES);
        } else {
            echo $response;
        }
    } catch (\Exception $e) {
        error_log('Error handling request: ' . $e->getMessage());
        error_log($e->getTraceAsString());
        
        http_response_code(500);
        echo 'Internal Server Error: ' . $e->getMessage();
    }
} else {
    // 404 Not Found
    http_response_code(404);
    echo '404 Not Found';
}
