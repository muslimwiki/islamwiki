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
            // Redirect root to Main_Page as the main focus
            header("Location: /wiki/Main_Page", true, 301);
            exit;
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
            // Use the wiki template with proper skin integration
            $template = 'pages/wiki.twig';
            $title = 'Islamic Wiki - Discover Islamic Knowledge - IslamWiki';
            break;
        case '/wiki/Main_Page':
            // Main page - default landing page
            $template = 'wiki/main-page.twig';
            $title = 'Main Page - IslamWiki';
            
            // Get current date and time
            $currentTime = date('H:i, j F Y');
            $totalArticles = 4829; // Placeholder for now
            
            // Initialize extension data
            $hijriDate = null;
            $nextSalah = null;
            
            // For now, use fallback values while we work on extension integration
            // TODO: Integrate with HijriCalendar and SalahTime extensions
            $hijriDate = '28 Safar 1447 AH';
            $nextSalah = [
                'name' => 'Asr',
                'time' => new DateTime('15:45')
            ];
            
            // Store template variables for the main page
            $templateData = [
                'current_time' => $currentTime,
                'hijri_date' => $hijriDate,
                'next_salah' => $nextSalah,
                'total_articles' => $totalArticles,
                'title' => 'Main Page - IslamWiki'
            ];
            break;
        case '/wiki/create':
            // Wiki page creation form
            $template = 'wiki/create.twig';
            $title = 'Create New Page - Wiki - IslamWiki';
            
            // Get title from URL parameters if provided
            $pageTitle = $_GET['title'] ?? '';
            
            // Store template variables for the create form
            $templateData = [
                'title' => 'Create New Page - Wiki - IslamWiki',
                'pageTitle' => $pageTitle,
                'categories' => [
                    ['id' => 1, 'name' => 'Quranic Studies', 'slug' => 'quranic-studies'],
                    ['id' => 2, 'name' => 'Hadith Studies', 'slug' => 'hadith-studies'],
                    ['id' => 3, 'name' => 'Islamic History', 'slug' => 'islamic-history'],
                    ['id' => 4, 'name' => 'Fiqh & Jurisprudence', 'slug' => 'fiqh-jurisprudence'],
                    ['id' => 5, 'name' => 'Islamic Practices', 'slug' => 'islamic-practices'],
                    ['id' => 6, 'name' => 'Islamic Sciences', 'slug' => 'islamic-sciences']
                ],
                'templates' => [
                    ['id' => 1, 'name' => 'Standard Article', 'slug' => 'standard-article'],
                    ['id' => 2, 'name' => 'Biography', 'slug' => 'biography'],
                    ['id' => 3, 'name' => 'Historical Event', 'slug' => 'historical-event'],
                    ['id' => 4, 'name' => 'Religious Ruling', 'slug' => 'religious-ruling']
                ],
                'user' => [
                    'id' => 1,
                    'username' => 'Guest',
                    'role' => 'user'
                ]
            ];
            break;
        case '/wiki/search':
            // Redirect wiki search to unified search
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'all';
            $sort = $_GET['sort'] ?? 'relevance';
            $order = $_GET['order'] ?? 'desc';
            
            $redirectUrl = '/search';
            $params = [];
            if ($query) $params[] = 'q=' . urlencode($query);
            if ($type !== 'all') $params[] = 'sort=' . urlencode($sort);
            if ($order !== 'desc') $params[] = 'order=' . urlencode($order);
            
            if (!empty($params)) {
                $redirectUrl .= '?' . implode('&', $params);
            }
            
            header("Location: $redirectUrl", true, 301);
            exit;
            break;
        case (preg_match('/^\/wiki\/(.+)$/', $path, $matches) ? $path : null):
            // Dynamic wiki page request (/wiki/{page_name})
            $pageName = $matches[1];
            
            // Skip if it's a known wiki route
            if (in_array($pageName, ['create', 'search', 'categories', 'category'])) {
                // Let the SabilRouting system handle these
                break;
            }
            
            // This is a wiki page that doesn't exist - show create page
            $template = 'wiki/page-not-found.twig';
            $title = $pageName . ' - Wiki - IslamWiki';
            
            // Store template variables for the page not found view
            $templateData = [
                'pageName' => $pageName,
                'pageTitle' => ucfirst(str_replace(['-', '_'], ' ', $pageName)),
                'user' => [
                    'id' => 1,
                    'username' => 'Guest',
                    'role' => 'user'
                ]
            ];
            break;
        case '/search':
            // Unified search page - Now properly handled by IqraSearchExtension
            $template = 'iqra-search/index.twig';
            $title = 'Search Islamic Knowledge - IslamWiki';
            
            // Store template variables for the main rendering system
            $templateData = [
                'query' => $_GET['q'] ?? '',
                'type' => $_GET['type'] ?? 'all',
                'sort' => $_GET['sort'] ?? 'relevance',
                'order' => $_GET['order'] ?? 'desc',
                'results' => [],
                'totalResults' => 0,
                'currentPage' => 1,
                'totalPages' => 1,
                'searchStats' => [
                    'total_pages' => 0,
                    'total_quran' => 0,
                    'total_hadith' => 0,
                    'total_calendar' => 0,
                    'total_prayer' => 0
                ],
                'searchTime' => 0,
                'searchTypes' => [
                    'all' => 'All Content',
                    'wiki' => 'Wiki Pages',
                    'quran' => 'Quran',
                    'hadith' => 'Hadith',
                    'articles' => 'Articles',
                    'scholars' => 'Scholars'
                ],
                'sortOptions' => [
                    'relevance' => 'Relevance',
                    'date' => 'Date',
                    'title' => 'Title',
                    'popularity' => 'Popularity'
                ],
                'orderOptions' => [
                    'desc' => 'Descending',
                    'asc' => 'Ascending'
                ]
            ];
            break;
        case '/search/analytics':
            // Search analytics dashboard - Now handled by IqraSearchExtension
            $template = 'iqra-search/analytics.twig';
            $title = 'Search Analytics - IqraSearchExtension - IslamWiki';
            break;
        case '/search/api/suggestions':
            // Search suggestions API - Now handled by IqraSearchExtension
            header('Content-Type: application/json');
            $query = $_GET['q'] ?? '';
            
            if (empty($query) || strlen($query) < 2) {
                echo json_encode(['suggestions' => []]);
                exit;
            }
            
            // Use IqraSearch suggestions
            $suggestions = [
                $query . ' knowledge',
                $query . ' principles',
                $query . ' history',
                $query . ' teachings',
                $query . ' scholars'
            ];
            
            echo json_encode([
                'suggestions' => $suggestions,
                'query' => $query,
                'count' => count($suggestions)
            ]);
            exit;
            break;
        case '/iqra-search':
            // Iqra Search - Islamic search engine
            $template = 'iqra-search/index.twig';
            $title = 'Iqra Search - Islamic Knowledge Search - IslamWiki';
            break;
        case '/community':
            // Community page
            $template = 'community/index.twig';
            $title = 'Community - IslamWiki';
            break;
        case '/about':
            // About page
            $template = 'about/index.twig';
            $title = 'About - IslamWiki';
            break;
        case '/iqra-search/api/search':
            // Iqra Search API endpoint
            header('Content-Type: application/json');
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'all';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $sort = $_GET['sort'] ?? 'relevance';
            $order = $_GET['order'] ?? 'desc';
            
            if (empty($query)) {
                http_response_code(400);
                echo json_encode(['error' => 'Query parameter is required', 'code' => 'MISSING_QUERY']);
                exit;
            }
            
            // For now, return a mock response until we integrate with the full Iqra system
            $mockResults = [
                'success' => true,
                'query' => $query,
                'type' => $type,
                'results' => [
                    [
                        'title' => 'Islamic Knowledge Base',
                        'type' => 'wiki',
                        'snippet' => 'Comprehensive Islamic knowledge and information...',
                        'url' => '/wiki/islamic-knowledge',
                        'relevance' => 95
                    ]
                ],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 1,
                    'total_results' => 1,
                    'per_page' => 20
                ],
                'search_time' => 0.1,
                'engine' => 'Iqra Search Engine v1.0'
            ];
            
            echo json_encode($mockResults);
            exit;
            break;
        case '/iqra-search/api/suggestions':
            // Iqra Search Suggestions API endpoint
            header('Content-Type: application/json');
            $query = $_GET['q'] ?? '';
            
            if (empty($query) || strlen($query) < 2) {
                echo json_encode(['suggestions' => []]);
                exit;
            }
            
            // Mock suggestions for now
            $suggestions = [
                $query . ' knowledge',
                $query . ' principles',
                $query . ' history',
                $query . ' teachings',
                $query . ' scholars'
            ];
            
            echo json_encode([
                'suggestions' => $suggestions,
                'query' => $query,
                'count' => count($suggestions)
            ]);
            exit;
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
        case '/settings/skins':
            $template = 'settings/skins.twig';
            $title = 'Skin Settings - IslamWiki';
            
            try {
                // Get skin settings data
                $skinController = new \IslamWiki\Http\Controllers\SkinSettingsController($container);
                $settingsData = $skinController->index();
                
                $templateData = array_merge([
                    'title' => 'Skin Settings - IslamWiki'
                ], $settingsData);
            } catch (Exception $e) {
                error_log("Error loading skin settings: " . $e->getMessage());
                $templateData = [
                    'title' => 'Skin Settings - IslamWiki',
                    'error' => 'Failed to load skin settings',
                    'current_skin' => null,
                    'available_skins' => [],
                    'user_preferences' => [],
                    'customization_options' => [],
                    'layout_options' => [],
                    'component_options' => []
                ];
            }
            break;
        case '/api/settings/skins/switch':
            // API endpoint for switching skins
            header('Content-Type: application/json');
            
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }
            
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                $skinName = $input['skin'] ?? '';
                
                if (empty($skinName)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Skin name is required']);
                    exit;
                }
                
                $skinController = new \IslamWiki\Http\Controllers\SkinSettingsController($container);
                $result = $skinController->switchSkin($skinName);
                
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Error switching skin: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Internal server error']);
            }
            exit;
            break;
        case '/api/settings/skins/update':
            // API endpoint for updating theme and layout preferences
            header('Content-Type: application/json');
            
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }
            
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                $skinController = new \IslamWiki\Http\Controllers\SkinSettingsController($container);
                
                if (isset($input['theme_customization'])) {
                    $result = $skinController->updateTheme($input['theme_customization']);
                } elseif (isset($input['layout_preferences'])) {
                    $result = $skinController->updateLayout($input['layout_preferences']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid request data']);
                    exit;
                }
                
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Error updating preferences: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Internal server error']);
            }
            exit;
            break;
        case '/api/settings/skins/component':
            // API endpoint for toggling component visibility
            header('Content-Type: application/json');
            
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }
            
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                $componentName = $input['component'] ?? '';
                $visible = $input['visible'] ?? true;
                
                if (empty($componentName)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Component name is required']);
                    exit;
                }
                
                $skinController = new \IslamWiki\Http\Controllers\SkinSettingsController($container);
                $result = $skinController->toggleComponent($componentName, $visible);
                
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Error toggling component: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Internal server error']);
            }
            exit;
            break;
        case '/api/settings/skins/preview':
            // API endpoint for live skin preview
            header('Content-Type: application/json');
            
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }
            
            try {
                $skinName = $_GET['skin'] ?? 'current';
                $customization = [];
                
                if (isset($_GET['prefs'])) {
                    $customization = json_decode($_GET['prefs'], true) ?: [];
                }
                
                $skinController = new \IslamWiki\Http\Controllers\SkinSettingsController($container);
                $result = $skinController->getLivePreview($skinName, $customization);
                
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Error generating preview: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Internal server error']);
            }
            exit;
            break;
        case '/api/settings/skins/reset':
            // API endpoint for resetting preferences
            header('Content-Type: application/json');
            
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }
            
            try {
                $skinController = new \IslamWiki\Http\Controllers\SkinSettingsController($container);
                $result = $skinController->resetPreferences();
                
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Error resetting preferences: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Internal server error']);
            }
            exit;
            break;
        case '/api/settings/skins/export':
            // API endpoint for exporting preferences
            header('Content-Type: application/json');
            
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }
            
            try {
                $skinController = new \IslamWiki\Http\Controllers\SkinSettingsController($container);
                $result = $skinController->exportPreferences();
                
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Error exporting preferences: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Internal server error']);
            }
            exit;
            break;
        case '/api/settings/skins/import':
            // API endpoint for importing preferences
            header('Content-Type: application/json');
            
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }
            
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                $skinController = new \IslamWiki\Http\Controllers\SkinSettingsController($container);
                $result = $skinController->importPreferences($input);
                
                echo json_encode($result);
            } catch (Exception $e) {
                error_log("Error importing preferences: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Internal server error']);
            }
            exit;
            break;
        default:
            // Check if this is a wiki page request (/wiki/{page_name})
            if (preg_match('/^\/wiki\/(.+)$/', $path, $matches)) {
                $pageName = $matches[1];
                
                // Skip if it's a known wiki route
                if (in_array($pageName, ['create', 'search', 'categories', 'category'])) {
                    // Let the SabilRouting system handle these
                    break;
                }
                
                // This is a wiki page that doesn't exist - show create page
                $template = 'wiki/page-not-found.twig';
                $title = $pageName . ' - Wiki - IslamWiki';
                
                // Store template variables for the page not found view
                $templateData = [
                    'pageName' => $pageName,
                    'pageTitle' => ucfirst(str_replace(['-', '_'], ' ', $pageName)),
                    'user' => [
                        'id' => 1,
                        'username' => 'Guest',
                        'role' => 'user'
                    ]
                ];
                break;
            }
            
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
    $renderData = [
        'title' => $title,
        'current_language' => 'en',
        'error' => $error ?? null,
        'user' => isset($_SESSION['user_id']) ? [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'User',
            'is_admin' => $_SESSION['is_admin'] ?? false
        ] : null,
    ];
    
    // Add any additional template variables if they exist
    if (isset($templateData) && is_array($templateData)) {
        $renderData = array_merge($renderData, $templateData);
    }
    
    $html = $view->render($template, $renderData);
    
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
