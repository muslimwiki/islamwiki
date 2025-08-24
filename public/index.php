<?php

declare(strict_types=1);

/**
 * IslamWiki Beautiful Islamic Design Entry Point
 * 
 * Main application entry point for local.islam.wiki
 * 
 * @package IslamWiki\Public
 * @version 0.0.2.2
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

// Initialize error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to prevent premature output
ob_start();

try {
    // Load the autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    // Load environment variables from .env file
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($envLines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    // Load application configuration and bootstrap
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');

    // Load and register routes
    $routes = require __DIR__ . '/../config/routes.php';
    $routes($app);

    // Create request from globals
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    
    // Parse POST data for POST requests
    $parsedBody = null;
    if ($method === 'POST') {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            $parsedBody = $_POST;
        } else {
            // For other content types, read from php://input
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                if (strpos($contentType, 'application/json') !== false) {
                    $parsedBody = json_decode($input, true);
                } else {
                    parse_str($input, $parsedBody);
                }
            }
        }
    }
    
    $request = new \IslamWiki\Core\Http\Request($method, $path, getallheaders(), null, '1.1', $_SERVER);
    
    // Set the parsed body if we have POST data
    if ($parsedBody !== null) {
        $request = $request->withParsedBody($parsedBody);
    }

    // Handle the request
    $response = $app->handleRequest($request);

    // Send the response
    $app->sendResponse($response);

} catch (\Exception $e) {
    // Handle HTTP exceptions and other application errors
    ob_end_clean(); // Clear the output buffer
    
    // Try to determine the appropriate status code
    $statusCode = 500;
    if (method_exists($e, 'getStatusCode')) {
        $statusCode = $e->getStatusCode();
    } elseif ($e instanceof \IslamWiki\Core\Http\Exceptions\HttpException) {
        $statusCode = $e->getStatusCode();
    }
    
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=UTF-8');
    
    // Use Logging error handling system
    try {
        // Try to use our enhanced error templates
        $response = handleErrorWithLogging($e, $statusCode);
        $response->send();
    } catch (\Throwable $templateError) {
        // Fallback to basic error display if template rendering fails
        displayBasicError($e, $statusCode);
    }
    exit;

} catch (\Throwable $e) {
    // Handle critical application errors
    ob_end_clean(); // Clear the output buffer
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    
    // In development, show detailed error information
    if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>An error occurred while processing your request.</p>";
    }
    exit;
}

// End output buffering and send content
ob_end_flush();

/**
 * Handle error with Logging system
 */
function handleErrorWithLogging(\Throwable $e, int $statusCode) {
    // Create a simple response with our enhanced error template
    $templatePath = __DIR__ . '/../resources/views/errors/';
    
    if ($statusCode === 404 && file_exists($templatePath . '404.twig')) {
        $content = file_get_contents($templatePath . '404.twig');
        
        // Generate comprehensive debug information
        $debugInfo = generateDebugInfo($e, $statusCode);
        $requestId = 'req_' . uniqid() . '.' . mt_rand(10000000, 99999999);
        $timestamp = date('Y-m-d H:i:s T');
        
        // Replace all template variables
        $replacements = [
            '{{ status_code }}' => $statusCode,
            '{{ error }}' => $e->getMessage(),
            '{{ request_id }}' => $requestId,
            '{{ "now"|date("Y-m-d H:i:s T") }}' => $timestamp,
            '{{ app.request.uri }}' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
            '{{ app.request.method }}' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            '{{ exception_class }}' => get_class($e),
            '{{ exception_file }}' => $e->getFile(),
            '{{ exception_line }}' => $e->getLine(),
            '{{ exception_message }}' => $e->getMessage(),
            '{{ exception_code }}' => $e->getCode(),
            '{{ exception_trace }}' => $e->getTraceAsString(),
            '{{ debug_timestamp }}' => $debugInfo['timestamp'],
            '{{ debug_context }}' => $debugInfo['context'],
            '{{ debug_error_type }}' => $debugInfo['error_type'],
            '{{ debug_error_message }}' => $debugInfo['error_message'],
            '{{ debug_error_code }}' => $debugInfo['error_code'],
            '{{ debug_file }}' => $debugInfo['file'],
            '{{ debug_line }}' => $debugInfo['line'],
            '{{ debug_request_method }}' => $debugInfo['request_info']['method'],
            '{{ debug_request_uri }}' => $debugInfo['request_info']['uri'],
            '{{ debug_user_agent }}' => $debugInfo['request_info']['user_agent'],
            '{{ debug_remote_addr }}' => $debugInfo['request_info']['remote_addr'],
            '{{ debug_http_host }}' => $debugInfo['request_info']['http_host'],
            '{{ debug_session_id }}' => $debugInfo['session_info']['session_id'],
            '{{ debug_session_status }}' => $debugInfo['session_info']['session_status'],
            '{{ debug_session_data }}' => json_encode($debugInfo['session_info']['session_data']),
            '{{ debug_session_name }}' => $debugInfo['session_info']['session_name'],
            '{{ debug_memory_usage }}' => $debugInfo['memory_usage']['memory_usage'],
            '{{ debug_memory_peak }}' => $debugInfo['memory_usage']['memory_peak'],
            '{{ debug_memory_limit }}' => $debugInfo['memory_usage']['memory_limit'],
            '{{ debug_php_version }}' => $debugInfo['php_info']['php_version'],
            '{{ debug_extensions }}' => implode(', ', $debugInfo['php_info']['extensions']),
            '{{ debug_error_reporting }}' => $debugInfo['php_info']['error_reporting'],
            '{{ debug_display_errors }}' => $debugInfo['php_info']['display_errors'],
            '{{ debug_log_errors }}' => $debugInfo['php_info']['log_errors'],
            '{{ debug_stack_trace }}' => $debugInfo['stack_trace']
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        return new \IslamWiki\Core\Http\Response($statusCode, ['Content-Type' => 'text/html; charset=utf-8'], $content);
    }
    
    // Fallback to basic error
    throw new \Exception('Template rendering failed');
}

/**
 * Generate comprehensive debug information
 */
function generateDebugInfo(\Throwable $e, int $statusCode): array {
    return [
        'timestamp' => date('Y-m-d H:i:s T'),
        'context' => 'Error Handler',
        'error_type' => get_class($e),
        'error_message' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'request_info' => [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'http_host' => $_SERVER['HTTP_HOST'] ?? 'N/A'
        ],
        'session_info' => [
            'session_id' => session_id() ?: 'N/A',
            'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive',
            'session_data' => isset($_SESSION) ? $_SESSION : [],
            'session_name' => session_name()
        ],
        'memory_usage' => [
            'memory_usage' => memory_get_usage(),
            'memory_peak' => memory_get_peak_usage(),
            'memory_limit' => ini_get('memory_limit')
        ],
        'php_info' => [
            'php_version' => PHP_VERSION,
            'extensions' => get_loaded_extensions(),
            'error_reporting' => error_reporting(),
            'display_errors' => ini_get('display_errors'),
            'log_errors' => ini_get('log_errors')
        ],
        'stack_trace' => $e->getTraceAsString()
    ];
}

/**
 * Display basic error as fallback
 */
function displayBasicError(\Throwable $e, int $statusCode) {
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=UTF-8');
    
    echo "<h1>{$statusCode} - Error</h1>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>An error occurred while processing your request.</p>";
}
