<?php
/**
 * Simple Index File for IslamWiki
 * 
 * This file provides a basic entry point that bypasses the complex routing system
 * and focuses on core functionality like the Iqra search engine.
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Set up error logging
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/php_errors.log');

// Set custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $errorMsg = sprintf(
        "[%s] ERROR: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $errstr,
        $errfile,
        $errline
    );
    error_log($errorMsg);
    
    if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        http_response_code(500);
        echo '<h1>Application Error</h1>';
        echo '<p>A fatal error occurred. Please check the error log for details.</p>';
        if (ini_get('display_errors')) {
            echo '<pre>' . htmlspecialchars($errorMsg) . '</pre>';
        }
        exit(1);
    }
    return false;
});

// Set exception handler
set_exception_handler(function(\Throwable $e) {
    $errorMsg = sprintf(
        "[%s] UNCAUGHT EXCEPTION: %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    error_log($errorMsg);
    
    http_response_code(500);
    echo '<h1>Application Error</h1>';
    echo '<p>An uncaught exception occurred. Please check the error log for details.</p>';
    if (ini_get('display_errors')) {
        echo '<pre>' . htmlspecialchars($errorMsg) . '</pre>';
    }
    exit(1);
});

// Shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        $errorMsg = sprintf(
            "[%s] FATAL ERROR: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        error_log($errorMsg);
        
        if (ini_get('display_errors')) {
            echo '<pre>' . htmlspecialchars($errorMsg) . '</pre>';
        } else {
            echo '<h1>Fatal Error</h1>';
            echo '<p>A fatal error occurred. Please check the error log for details.</p>';
        }
    }
});

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include necessary files
require_once BASE_PATH . '/src/Core/Asas.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';
require_once BASE_PATH . '/src/Core/Logging/Logger.php';
require_once BASE_PATH . '/src/Core/Search/IqraSearchEngine.php';
require_once BASE_PATH . '/src/Http/Controllers/Controller.php';
require_once BASE_PATH . '/src/Http/Controllers/IqraSearchController.php';

use IslamWiki\Core\Asas;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Search\IqraSearchEngine;
use IslamWiki\Http\Controllers\IqraSearchController;

try {
    
    // Get the request URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Simple routing based on URI
    if ($requestUri === '/' || $requestUri === '/index.php') {
        // Show main page
        showMainPage();
    } elseif (strpos($requestUri, '/iqra-search') === 0) {
        // Handle Iqra search
        handleIqraSearch();
    } elseif (strpos($requestUri, '/test-iqra') === 0) {
        // Handle Iqra test
        handleIqraTest();
    } else {
        // Show 404 page
        show404Page($requestUri);
    }
    
} catch (Throwable $e) {
    // This will be handled by the exception handler
    throw $e;
}

/**
 * Show the main page
 */
function showMainPage(): void
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>IslamWiki - Islamic Knowledge Platform</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            
            .header {
                text-align: center;
                margin-bottom: 40px;
                color: white;
            }
            
            .header h1 {
                font-size: 3rem;
                margin-bottom: 10px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
            
            .header p {
                font-size: 1.2rem;
                opacity: 0.9;
            }
            
            .content {
                background: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                margin-bottom: 30px;
            }
            
            .feature-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 30px;
            }
            
            .feature-card {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 10px;
                border-left: 4px solid #667eea;
                transition: transform 0.2s ease;
            }
            
            .feature-card:hover {
                transform: translateY(-2px);
            }
            
            .feature-card h3 {
                color: #333;
                margin-bottom: 15px;
                font-size: 1.2rem;
            }
            
            .feature-card p {
                color: #666;
                margin-bottom: 15px;
            }
            
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: transform 0.2s ease;
            }
            
            .btn:hover {
                transform: translateY(-1px);
            }
            
            .status {
                background: #e8f5e8;
                color: #2e7d32;
                padding: 15px;
                border-radius: 10px;
                border-left: 4px solid #4caf50;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🌙 IslamWiki</h1>
                <p>Advanced Islamic Knowledge Platform</p>
            </div>
            
            <div class="content">
                <div class="status">
                    <strong>✅ System Status:</strong> IslamWiki is running successfully with Iqra Search Engine.
                </div>
                
                <h2>Welcome to IslamWiki</h2>
                <p>IslamWiki is a comprehensive Islamic knowledge management platform built with modern web technologies. 
                Our advanced Iqra search engine provides intelligent, context-aware search capabilities for Islamic content.</p>
                
                <div class="feature-grid">
                    <div class="feature-card">
                        <h3>🔍 Iqra Search Engine</h3>
                        <p>Advanced Islamic-content-optimized search system with multi-content type support, Arabic text handling, and intelligent relevance scoring.</p>
                        <a href="/iqra-search" class="btn">Try Iqra Search</a>
                    </div>
                    
                    <div class="feature-card">
                        <h3>📖 Islamic Content</h3>
                        <p>Search across Quran verses, Hadith collections, Islamic calendar events, prayer times, and scholarly works.</p>
                        <a href="/test-iqra" class="btn">Test Search</a>
                    </div>
                    
                    <div class="feature-card">
                        <h3>🎯 Smart Features</h3>
                        <p>Islamic term recognition, Arabic text support, relevance scoring, search analytics, and intelligent suggestions.</p>
                        <a href="/iqra-search.php" class="btn">Advanced Search</a>
                    </div>
                </div>
                
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="/iqra-search">🔍 Iqra Search Interface</a></li>
                    <li><a href="/test-iqra-search.php">🧪 Search Engine Test</a></li>
                    <li><a href="/iqra-search.php">📊 Advanced Search with Analytics</a></li>
                </ul>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Handle Iqra search requests
 */
function handleIqraSearch(): void
{
    // Redirect to the Iqra search page
    header('Location: /iqra-search.php');
    exit;
}

/**
 * Handle Iqra test requests
 */
function handleIqraTest(): void
{
    // Redirect to the test page
    header('Location: /test-iqra-search.php');
    exit;
}

/**
 * Show 404 page
 */
function show404Page(string $requestUri): void
{
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Page Not Found</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-align: center;
                padding: 50px;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                max-width: 600px;
            }
            h1 { font-size: 4rem; margin-bottom: 20px; }
            p { font-size: 1.2rem; margin-bottom: 30px; }
            a { color: white; text-decoration: underline; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>404</h1>
            <p>Page not found: <?= htmlspecialchars($requestUri) ?></p>
            <p><a href="/">Return to Home</a></p>
        </div>
    </body>
    </html>
    <?php
} 