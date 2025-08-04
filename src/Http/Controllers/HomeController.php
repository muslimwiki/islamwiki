<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Skins\SkinManager;
use Psr\Log\LoggerInterface;
use function error_log;

class HomeController extends Controller
{
    /**
     * @var LoggerInterface Logger instance
     */
    private $logger;
    
    /**
     * Create a new controller instance.
     *
     * @param \IslamWiki\Core\Database\Connection $db Database connection
     * @param \IslamWiki\Core\Container\Asas $container The container instance
     */
    public function __construct(
        \IslamWiki\Core\Database\Connection $db,
        \IslamWiki\Core\Container\Asas $container
    ) {
        $debugLog = BASE_PATH . '/storage/logs/debug.log';
        file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] Entered HomeController::__construct\n", FILE_APPEND);
        file_put_contents($debugLog, "db type: " . gettype($db) . ", value: " . var_export($db, true) . "\n", FILE_APPEND);
        file_put_contents($debugLog, "container type: " . gettype($container) . ", value: " . var_export($container, true) . "\n", FILE_APPEND);
        error_log('HomeController: Constructor called');
        error_log('HomeController: DB class: ' . get_class($db));
        error_log('HomeController: Container class: ' . get_class($container));
        
        try {
            file_put_contents($debugLog, "before parent::__construct\n", FILE_APPEND);
            parent::__construct($db, $container);
            file_put_contents($debugLog, "after parent::__construct\n", FILE_APPEND);
            file_put_contents($debugLog, "before logger assignment\n", FILE_APPEND);
            // Get logger from container
            file_put_contents($debugLog, "about to get logger from container\n", FILE_APPEND);
            try {
                $this->logger = $container->get(\Psr\Log\LoggerInterface::class);
                file_put_contents($debugLog, "logger assignment successful\n", FILE_APPEND);
            } catch (\Throwable $e) {
                file_put_contents($debugLog, "logger assignment failed: " . $e->getMessage() . "\n", FILE_APPEND);
                throw $e;
            }
            file_put_contents($debugLog, "after logger assignment\n", FILE_APPEND);
            file_put_contents($debugLog, "logger type: " . gettype($this->logger) . ", value: " . var_export($this->logger, true) . "\n", FILE_APPEND);
            if (is_object($this->logger)) {
                file_put_contents($debugLog, "about to call get_class on logger\n", FILE_APPEND);
                error_log('HomeController: Logger class: ' . get_class($this->logger));
                file_put_contents($debugLog, "get_class on logger successful\n", FILE_APPEND);
            } else {
                error_log('HomeController: Logger type: ' . gettype($this->logger));
            }
            error_log('HomeController: Constructor completed successfully');
        } catch (\Throwable $e) {
            error_log('HomeController: Constructor error: ' . $e->getMessage());
            error_log('HomeController: Constructor error trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Show the application home page.
     *
     * @param Request $request The HTTP request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $debugLog = BASE_PATH . '/storage/logs/debug.log';
        file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] Entered HomeController@index\n", FILE_APPEND);
        file_put_contents($debugLog, "request type: " . gettype($request) . ", value: " . var_export($request, true) . "\n", FILE_APPEND);
        error_log('HomeController@index called');
        
        try {
            error_log('HomeController@index: Request class: ' . get_class($request));
            
            // Log the request
            $logData = [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $request->getHeader('User-Agent'),
                'referer' => $request->getHeader('Referer'),
                'request_uri' => $request->getUri()->getPath(),
                'method' => $request->getMethod(),
            ];
            
            error_log('HomeController: Log data: ' . print_r($logData, true));
            
            if ($this->logger) {
                $this->logger->info('Home page accessed', $logData);
            } else {
                error_log('HomeController: Logger not available');
            }
            
            // Fetch recent pages from database
            $recentPages = [];
            try {
                $recentPages = $this->db->select(
                    'SELECT id, title, slug, content, created_at FROM pages ORDER BY created_at DESC LIMIT 5'
                );
                error_log('HomeController: Fetched ' . count($recentPages) . ' recent pages');
            } catch (\Exception $e) {
                error_log('HomeController: Error fetching pages: ' . $e->getMessage());
                $recentPages = [];
            }

            // Get current user from Aman
            $user = null;
            try {
                $auth = $this->container->get('auth');
                $user = $auth->user();
                file_put_contents($debugLog, "user after auth->user(): type=" . gettype($user) . ", value=" . var_export($user, true) . "\n", FILE_APPEND);
                error_log('HomeController: User authenticated: ' . ($user ? 'yes' : 'no'));
                if ($user) {
                    if (is_object($user)) {
                        error_log('HomeController: User class: ' . get_class($user));
                    } elseif (is_array($user)) {
                        error_log('HomeController: User is array, username: ' . ($user['username'] ?? 'N/A'));
                    } else {
                        error_log('HomeController: User type: ' . gettype($user));
                    }
                }
            } catch (\Exception $e) {
                error_log('HomeController: Error getting user: ' . $e->getMessage());
            }

            // Use Twig template instead of hardcoded HTML
            $data = [
                'title' => 'Welcome to IslamWiki',
                'message' => 'Your Islamic knowledge base and resource center',
                'recentPages' => $recentPages,
                'user' => $user,
                'app' => [
                    'debug' => $_ENV['APP_DEBUG'] ?? false
                ],
                'features' => [
                    'Modern, responsive design',
                    'ZamZam.js for lightweight interactivity',
                    'Twig templating for server-side rendering',
                    'PSR-7 compatible HTTP handling',
                    'Dependency injection container',
                    'Comprehensive error handling and logging'
                ]
            ];
            
            error_log('HomeController@index: Rendering Twig template');
            $response = $this->view('pages/home', $data);
            error_log('HomeController@index: Response generated successfully');
            return $response;
            
        } catch (\Throwable $e) {
            error_log('HomeController@index: Exception: ' . $e->getMessage());
            error_log('HomeController@index: Stack trace: ' . $e->getTraceAsString());
            $debugLog = BASE_PATH . '/storage/logs/debug.log';
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($debugLog, "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            if (isset($user)) {
                error_log('HomeController@index: $user type: ' . gettype($user));
                error_log('HomeController@index: $user value: ' . var_export($user, true));
                file_put_contents($debugLog, "user type: " . gettype($user) . "\n", FILE_APPEND);
                file_put_contents($debugLog, "user value: " . var_export($user, true) . "\n", FILE_APPEND);
            }
            if ($this->logger) {
                $this->logger->error('Error in HomeController@index', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            
            return new Response(
                status: 500,
                headers: ['Content-Type' => 'text/plain'],
                body: 'Internal Server Error: ' . $e->getMessage()
            );
        }
    }
}
