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
     * @param \IslamWiki\Core\Container\AsasContainer $container The container instance
     */
    public function __construct(
        \IslamWiki\Core\Database\Connection $db,
        \IslamWiki\Core\Container\AsasContainer $container
    ) {
        // Constructor completed successfully

        try {
            parent::__construct($db, $container);

            // Get logger from container
            $this->logger = $container->get(\Psr\Log\LoggerInterface::class);
        } catch (\Throwable $e) {
            $this->logger = null;
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
        try {
            // Log the request
            $logData = [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $request->getHeader('User-Agent'),
                'referer' => $request->getHeader('Referer'),
                'request_uri' => $request->getUri()->getPath(),
                'method' => $request->getMethod(),
            ];

            // error_log('HomeController: Log data: ' . print_r($logData, true));

            if ($this->logger) {
                $this->logger->info('Home page accessed', $logData);
            } else {
                // error_log('HomeController: Logger not available');
            }

            // Fetch recent pages from database
            $recentPages = [];
            try {
                            $recentPages = $this->db->select(
                'SELECT id, title, slug, content, created_at 
                 FROM pages 
                 ORDER BY created_at DESC 
                 LIMIT 5'
            );
                // error_log('HomeController: Fetched ' . count($recentPages) . ' recent pages');
            } catch (\Exception $e) {
                // error_log('HomeController: Error fetching pages: ' . $e->getMessage());
                $recentPages = [];
            }

            // Get current user from Aman
            $user = null;
            try {
                $auth = $this->container->get('auth');
                $user = $auth->user();
            } catch (\Exception $e) {
                // error_log('HomeController: Error getting user: ' . $e->getMessage());
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

            // error_log('HomeController@index: Rendering Twig template');
            $response = $this->view('pages/home', $data);
            // error_log('HomeController@index: Response generated successfully');
            return $response;
        } catch (\Throwable $e) {
            // error_log('HomeController@index: Exception: ' . $e->getMessage());
            // error_log('HomeController@index: Stack trace: ' . $e->getTraceAsString());
            $debugLog = BASE_PATH . '/storage/logs/debug.log';
            file_put_contents($debugLog, "\n[" . date('Y-m-d H:i:s') . "] Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($debugLog, "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            if (isset($user)) {
                // error_log('HomeController@index: $user type: ' . gettype($user));
                // error_log('HomeController@index: $user value: ' . var_export($user, true));
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
