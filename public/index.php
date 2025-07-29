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
$errorLogPath = $logDir . '/php_errors.log';
ini_set('error_log', $errorLogPath);

// Enable detailed error logging
ini_set('log_errors', '1');
ini_set('error_log', $errorLogPath);

// Set custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $errorType = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSING ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT NOTICE',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER DEPRECATED',
    ][$errno] ?? 'UNKNOWN ERROR';
    
    $errorMsg = sprintf(
        "[%s] %s: %s in %s on line %d\nStack trace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        $errorType,
        $errstr,
        $errfile,
        $errline,
        (new \Exception())->getTraceAsString()
    );
    
    error_log($errorMsg);
    
    if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        http_response_code(500);
        if (ini_get('display_errors')) {
            echo '<pre>' . htmlspecialchars($errorMsg) . '</pre>';
        } else {
            echo 'A server error occurred. Please check the error log for details.';
        }
        exit(1);
    }
    
    return false; // Let the default error handler run as well
});

// Set exception handler
set_exception_handler(function(\Throwable $e) {
    $errorMsg = sprintf(
        "[%s] UNCAUGHT EXCEPTION: %s in %s on line %d\nStack trace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    
    error_log($errorMsg);
    
    http_response_code(500);
    if (ini_get('display_errors')) {
        echo '<pre>' . htmlspecialchars($errorMsg) . '</pre>';
    } else {
        echo 'An uncaught exception occurred. Please check the error log for details.';
    }
    exit(1);
});

// Shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR])) {
        $errorMsg = sprintf(
            "[%s] FATAL ERROR: %s in %s on line %d\n\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        error_log($errorMsg);
        
        if (ini_get('display_errors')) {
            echo '<pre>' . htmlspecialchars($errorMsg) . '</pre>';
        } else {
            echo 'A fatal error occurred. Please check the error log for details.';
        }
    }
});

// Debug: Log that the script has started
error_log('========================================');
error_log('=== NEW REQUEST ' . date('Y-m-d H:i:s') . ' ===');
error_log('=== index.php started ===');
error_log('PHP Version: ' . phpversion());
error_log('Request URI: ' . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
error_log('Script name: ' . ($_SERVER['SCRIPT_NAME'] ?? 'N/A'));
error_log('Document root: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A'));
error_log('Current working directory: ' . getcwd());
error_log('Memory usage: ' . memory_get_usage() . ' bytes');
error_log('Memory peak usage: ' . memory_get_peak_usage() . ' bytes');
error_log('Error log path: ' . $errorLogPath);
error_log('Included files: ' . implode("\n", get_included_files()));

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
 
 // Define the application start time for performance measurement
 define('APP_START', microtime(true));
 
 // Define the application's base path
 define('BASE_PATH', dirname(__DIR__));
 
 // Ensure logs directory exists
 $logDir = BASE_PATH . '/logs';
 if (!is_dir($logDir)) {
     mkdir($logDir, 0755, true);
 }
 
 // Set up error logging first
 ini_set('log_errors', '1');
 ini_set('error_log', $logDir . '/php_errors.log');
 ini_set('display_errors', '1');
 ini_set('display_startup_errors', '1');
 error_reporting(E_ALL);
 
 // Set error handler to catch all errors
 set_error_handler(function($errno, $errstr, $errfile, $errline) {
     error_log(sprintf('Error [%d] %s in %s on line %d', $errno, $errstr, $errfile, $errline));
     return false; // Let the normal error handler run
 });
 
 // Set exception handler
 set_exception_handler(function($e) {
     error_log(sprintf('Uncaught Exception: %s in %s on line %d', 
         $e->getMessage(), 
         $e->getFile(), 
         $e->getLine()
     ));
     error_log('Stack trace: ' . $e->getTraceAsString());
     
     if (!headers_sent()) {
         header('Content-Type: text/plain; charset=utf-8');
         http_response_code(500);
     }
     
     echo 'An error occurred. Please check the error log for details.';
     exit(1);
 });
 
 // Debug: Log the request details
 error_log('Request URI: ' . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
 error_log('Script name: ' . ($_SERVER['SCRIPT_NAME'] ?? 'N/A'));
 error_log('Document root: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A'));
 error_log('Current working directory: ' . getcwd());
 
 // Simple error handler for early initialization
 set_error_handler(function($errno, $errstr, $errfile, $errline) {
     $message = sprintf(
         'Error [%d] %s in %s on line %d',
         $errno,
         $errstr,
         $errfile,
         $errline
     );
     error_log($message);
     return false; // Let the default error handler run
 });
 
 // Exception handler for uncaught exceptions
 set_exception_handler(function(Throwable $e) {
     $message = sprintf(
         "Uncaught %s: %s in %s on line %d\nStack trace:\n%s",
         get_class($e),
         $e->getMessage(),
         $e->getFile(),
         $e->getLine(),
         $e->getTraceAsString()
     );
     error_log($message);
     
     if (getenv('APP_ENV') !== 'production') {
         echo "<h1>Application Error</h1>";
         echo "<pre>{$message}</pre>";
     } else {
         echo "<h1>500 Internal Server Error</h1>";
         echo "<p>An error occurred while processing your request.</p>";
     }
     exit(1);
 });
 
 // Shutdown function to catch fatal errors
 register_shutdown_function(function() {
     $error = error_get_last();
     if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
         $message = sprintf(
             'Fatal error [%d] %s in %s on line %d',
             $error['type'],
             $error['message'],
             $error['file'],
             $error['line']
         );
         error_log($message);
     }
 });
 
 try {
     // Enable output buffering
     if (ob_get_level() === 0) {
         ob_start();
     }
     
     error_log('Starting application bootstrap...');
     error_log('Current working directory: ' . getcwd());
     error_log('BASE_PATH: ' . BASE_PATH);
     error_log('PHP Version: ' . phpversion());
     error_log('Memory Limit: ' . ini_get('memory_limit'));
     
     // Log environment variables
     error_log('Environment: ' . getenv('APP_ENV') ?: 'not set');
     error_log('Debug Mode: ' . (getenv('APP_DEBUG') ? 'true' : 'false'));
 
     // Load Composer's autoloader
     $autoloadPath = BASE_PATH . '/vendor/autoload.php';
     error_log('Autoload path: ' . $autoloadPath);

     if (file_exists($autoloadPath)) {
         error_log('Autoload file exists, including...');
         require_once $autoloadPath;
         error_log('Autoload file included successfully');
     } else {
         $error = 'Autoload file not found. Please run `composer install` to install the project dependencies.';
         error_log($error);
         die($error);
     }
 
     // Load environment variables from .env file
     $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
     $dotenv->load();
 
     // Helper function to get environment variables with default value
     if (!function_exists('env')) {
         function env($key, $default = null) {
             $value = getenv($key);
             if ($value === false) {
                 return $default;
             }
             
             // Convert string values to appropriate types
             switch (strtolower($value)) {
                 case 'true':
                 case '(true)':
                     return true;
                 case 'false':
                 case '(false)':
                     return false;
                 case 'empty':
                 case '(empty)':
                     return '';
                 case 'null':
                 case '(null)':
                     return null;
             }
             
             // Remove quotes if present
             if (strlen($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
                 return substr($value, 1, -1);
             }
             
             return $value;
         }
     }
 
     // Set default timezone
     date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
 
     // Set error reporting based on environment
     if (env('APP_DEBUG', false)) {
         ini_set('display_errors', '1');
         ini_set('display_startup_errors', '1');
         error_reporting(E_ALL);
     } else {
         ini_set('display_errors', '0');
         ini_set('display_startup_errors', '0');
         error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
     }
 
     // Bootstrap the application
     error_log('Creating Application instance...');
     $app = new IslamWiki\Core\Application(BASE_PATH);
     error_log('Application instance created');
    
     // Create FastRouter instance
     error_log('Initializing FastRouter...');
     $fastRouter = new IslamWiki\Core\Routing\FastRouter($app->getContainer());
    
     // Set the router instance in the application
     $app->setRouter($fastRouter);
    
     // Set the application instance for static access (for backward compatibility)
     error_log('Setting application instance for Router...');
     IslamWiki\Core\Routing\Router::setApplication($app);
    
     // Log the current state
     error_log('Router instance: ' . get_class($fastRouter));
    
     // Load the web routes
     $webRoutesPath = BASE_PATH . '/routes/web.php';
     error_log('Loading web routes from: ' . $webRoutesPath);
    
     if (file_exists($webRoutesPath)) {
         error_log('Web routes file exists, including it...');
        
         // Store the router in a local variable for use in the routes file
         $router = $fastRouter;
        
         // Load routes
         $result = require $webRoutesPath;
         error_log('Web routes loaded, require returned: ' . var_export($result, true));
        
         // Log registered routes for debugging
         error_log('Registered routes:');
         $reflection = new \ReflectionProperty($fastRouter, 'routes');
         $reflection->setAccessible(true);
         $routes = $reflection->getValue($fastRouter);
         foreach ($routes as $route) {
             error_log(sprintf(
                 '  %s %s -> %s',
                 implode('|', $route['methods']),
                 $route['route'],
                 is_string($route['handler']) ? $route['handler'] : gettype($route['handler'])
             ));
         }
     } else {
         error_log('ERROR: Web routes file not found at: ' . $webRoutesPath);
         throw new \RuntimeException('Web routes file not found');
     }
    
     // Handle the request and send the response
     error_log('About to handle the request with FastRouter...');
    
     // Create PSR-7 request from globals
     $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
    
     // Handle the request and get the response
     error_log('Calling $fastRouter->handle()');
     $response = $fastRouter->handle($request);
     error_log('$fastRouter->handle() returned');
    
     // Send the response
     error_log('Sending response...');
     http_response_code($response->getStatusCode());
    
     // Set headers
     foreach ($response->getHeaders() as $name => $values) {
         foreach ($values as $value) {
             header(sprintf('%s: %s', $name, $value), false);
         }
     }
    
     // Send the body
     echo $response->getBody();
    
     error_log('Response sent successfully');
 
 } catch (Throwable $e) {
     // This will be handled by the exception handler
     throw $e;
 }
