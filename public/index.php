<?php

declare(strict_types=1);

/**
 * IslamWiki Beautiful Islamic Design Entry Point
 * 
 * Main application entry point for local.islam.wiki
 * 
 * @package IslamWiki\Public
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Start session for authentication
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Load Composer autoloader
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Load environment configuration
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }
    
    // Initialize the Asas container
    $container = new IslamWiki\Core\Container\AsasContainer();
    
    // Bootstrap the application
    $bootstrap = new IslamWiki\Core\AsasBootstrap($container);
    $bootstrap->bootstrap();
    
    // Get the view service
    $view = $container->get('view');
    
    // Get the current path
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($path, PHP_URL_PATH);
    
    // Get the request method
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Route to the appropriate template based on path and method
    switch ($path) {
        case '/':
            $template = 'pages/home.twig';
            $title = 'IslamWiki - Authentic Islamic Knowledge';
            break;
        case '/quran':
            $template = 'quran/index.twig';
            $title = 'Quran - IslamWiki';
            break;
        case '/hadith':
            $template = 'hadith/index.twig';
            $title = 'Hadith - IslamWiki';
            break;
        case '/wiki':
            $template = 'pages/index.twig';
            $title = 'Wiki - IslamWiki';
            break;
        case '/search':
            $template = 'search/index.twig';
            $title = 'Search - IslamWiki';
            break;
        case '/admin':
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                // User not logged in, redirect to login
                header("Location: /auth/login?redirect=" . urlencode('/admin'));
                exit;
            }
            
            // Redirect to appropriate dashboard based on user role
            if ($_SESSION['is_admin'] ?? false) {
                header("Location: /dashboard/admin");
                exit;
            } else {
                header("Location: /dashboard/user");
                exit;
            }
            break;
        case '/dashboard':
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                // User not logged in, redirect to login
                header("Location: /auth/login?redirect=" . urlencode('/dashboard'));
                exit;
            }
            
            // Redirect to appropriate dashboard based on user role
            if ($_SESSION['is_admin'] ?? false) {
                header("Location: /dashboard/admin");
                exit;
            } else {
                header("Location: /dashboard/user");
                exit;
            }
            break;
        case '/dashboard/admin':
            // Admin dashboard - check if user is admin
            if (!isset($_SESSION['user_id']) || !($_SESSION['is_admin'] ?? false)) {
                // User not logged in or not admin, redirect to login
                header("Location: /auth/login?redirect=" . urlencode('/dashboard/admin'));
                exit;
            }
            
            $template = 'dashboard/admin_dashboard.twig';
            $title = 'Admin Dashboard - IslamWiki';
            break;
        case '/dashboard/user':
            // User dashboard - check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                // User not logged in, redirect to login
                header("Location: /auth/login?redirect=" . urlencode('/dashboard/user'));
                exit;
            }
            
            $template = 'dashboard/user_dashboard.twig';
            $title = 'User Dashboard - IslamWiki';
            break;
        case '/login':
            if ($method === 'POST') {
                // Handle login POST request
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if (empty($username) || empty($password)) {
                    $error = 'Username and password are required';
                    $template = 'auth/login.twig';
                    $title = 'Login - IslamWiki';
                } else {
                    // Database authentication
                    try {
                        $pdo = new PDO('mysql:host=127.0.0.1;dbname=islamwiki', 'root', '');
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        $stmt = $pdo->prepare('SELECT id, username, password, is_admin, is_active FROM users WHERE username = ? AND is_active = 1');
                        $stmt->execute([$username]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($user && password_verify($password, $user['password'])) {
                            // Set user data in existing session
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['is_admin'] = (bool)$user['is_admin'];
                            
                            // Update last login
                            $updateStmt = $pdo->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
                            $updateStmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'unknown', $user['id']]);
                            
                            // Debug logging
                            error_log("Login successful for user: {$user['username']}, Session ID: " . session_id());
                            
                            // Redirect to appropriate dashboard or last page
                            $redirect = $_POST['redirect'] ?? '/dashboard';
                            header("Location: $redirect");
                            exit;
                        } else {
                            $error = 'Invalid username or password';
                            $template = 'auth/login.twig';
                            $title = 'Login - IslamWiki';
                            error_log("Login failed for user: $username");
                        }
                    } catch (PDOException $e) {
                        error_log("Database error during login: " . $e->getMessage());
                        $error = 'Database connection error. Please try again.';
                        $template = 'auth/login.twig';
                        $title = 'Login - IslamWiki';
                    }
                }
            } else {
                // GET request - check if user is already logged in
                if (isset($_SESSION['user_id'])) {
                    // User is already logged in, redirect to dashboard
                    header("Location: /dashboard");
                    exit;
                }
                
                // Show login form
                $template = 'auth/login.twig';
                $title = 'Login - IslamWiki';
            }
            break;
        case '/auth/login':
            if ($method === 'POST') {
                // Handle login POST request
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                // Debug logging
                error_log("Login attempt - Username: $username, Method: $method");
                
                if (empty($username) || empty($password)) {
                    $error = 'Username and password are required';
                    $template = 'auth/login.twig';
                    $title = 'Login - IslamWiki';
                } else {
                    // Database authentication
                    try {
                        $pdo = new PDO('mysql:host=127.0.0.1;dbname=islamwiki', 'root', '');
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        $stmt = $pdo->prepare('SELECT id, username, password, is_admin, is_active FROM users WHERE username = ? AND is_active = 1');
                        $stmt->execute([$username]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($user && password_verify($password, $user['password'])) {
                            // Set user data in existing session
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['is_admin'] = (bool)$user['is_admin'];
                            
                            // Update last login
                            $updateStmt = $pdo->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
                            $updateStmt->execute([$_SERVER['REMOTE_ADDR'] ?? 'unknown', $user['id']]);
                            
                            // Debug logging
                            error_log("Login successful for user: {$user['username']}, Session ID: " . session_id());
                            
                            // Redirect to admin dashboard or last page
                            $redirect = $_POST['redirect'] ?? '/admin';
                            header("Location: $redirect");
                            exit;
                        } else {
                            $error = 'Invalid username or password';
                            $template = 'auth/login.twig';
                            $title = 'Login - IslamWiki';
                            error_log("Login failed for user: $username");
                        }
                    } catch (PDOException $e) {
                        error_log("Database error during login: " . $e->getMessage());
                        $error = 'Database connection error. Please try again.';
                        $template = 'auth/login.twig';
                        $title = 'Login - IslamWiki';
                    }
                }
            } else {
                // GET request - check if user is already logged in
                if (isset($_SESSION['user_id'])) {
                    // User is already logged in, redirect to admin
                    header("Location: /admin");
                    exit;
                }
                
                // Show login form
                $template = 'auth/login.twig';
                $title = 'Login - IslamWiki';
            }
            break;
        case '/logout':
            // Handle logout
            session_destroy();
            header("Location: /");
            exit;
        case '/auth/logout':
            // Handle POST logout request from form
            if ($method === 'POST') {
                // Clear session data
                session_destroy();
                
                // Clear session cookie
                if (ini_get('session.use_cookies')) {
                    $params = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        '',
                        time() - 42000,
                        $params['path'],
                        $params['domain'],
                        $params['secure'],
                        $params['httponly']
                    );
                }
                
                // Redirect to home page
                header("Location: /");
                exit;
            } else {
                // GET request - redirect to home
                header("Location: /");
                exit;
            }
        case '/register':
            $template = 'auth/register.twig';
            $title = 'Register - IslamWiki';
            break;
        case '/settings':
            $template = 'settings/index.twig';
            $title = 'Settings - IslamWiki';
            break;
        default:
            // Check if it's a static file request
            if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
                // Serve static file
                $filePath = __DIR__ . '/..' . $path;
                if (file_exists($filePath)) {
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                    $contentTypes = [
                        'css' => 'text/css',
                        'js' => 'application/javascript',
                        'png' => 'image/png',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'ico' => 'image/x-icon',
                        'svg' => 'image/svg+xml',
                        'woff' => 'font/woff',
                        'woff2' => 'font/woff2',
                        'ttf' => 'font/ttf',
                        'eot' => 'application/vnd.ms-fontobject'
                    ];
                    
                    $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
                    header("Content-Type: $contentType");
                    header("Content-Length: " . filesize($filePath));
                    readfile($filePath);
                    exit;
                }
            }
            
            // 404 - Page not found
            http_response_code(404);
            $template = 'errors/404.twig';
            $title = 'Page Not Found - IslamWiki';
            break;
    }
    
    // Render the template
    $html = $view->render($template, [
        'title' => $title,
        'current_language' => 'en',
        'error' => $error ?? null,
        'user' => isset($_SESSION['user_id']) ? [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'User',
            'is_admin' => $_SESSION['is_admin'] ?? false
        ] : null
    ]);
    
    // Output the HTML
    echo $html;
    
} catch (Exception $e) {
    // Log the error
    error_log("IslamWiki Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    // Show 500 error page
    http_response_code(500);
    
    // Try to render error page, fallback to simple error if that fails
    try {
        if (isset($view)) {
            $html = $view->render('errors/500.twig', [
                'title' => 'Server Error - IslamWiki',
                'error' => $e->getMessage(),
                'current_language' => 'en'
            ]);
            echo $html;
        } else {
            throw new Exception("View service not available");
        }
    } catch (Exception $renderError) {
        // Fallback to simple error page
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - IslamWiki</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d32f2f; text-align: center; }
        .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { background: #e3f2fd; color: #1565c0; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 Server Error - IslamWiki</h1>
        
        <div class="error">
            <strong>❌ Error:</strong>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
        </div>
        
        <div class="info">
            <strong>ℹ️ Information:</strong>
            <p>An error occurred while processing your request. Please try again later or contact the administrator.</p>
        </div>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="/" style="background: #1976d2; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">🏠 Return to Home</a>
        </p>
    </div>
</body>
</html>';
    }
}
