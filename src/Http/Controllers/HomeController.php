<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
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
     * @param \IslamWiki\Core\Container $container The container instance
     */
    public function __construct(
        \IslamWiki\Core\Database\Connection $db,
        \IslamWiki\Core\Container $container
    ) {
        error_log('HomeController: Constructor called');
        error_log('HomeController: DB class: ' . get_class($db));
        error_log('HomeController: Container class: ' . get_class($container));
        
        try {
            parent::__construct($db, $container);
            // Get logger from container
            $this->logger = $container->get(\Psr\Log\LoggerInterface::class);
            error_log('HomeController: Logger class: ' . get_class($this->logger));
            error_log('HomeController: Constructor completed successfully');
        } catch (\Throwable $e) {
            error_log('HomeController: Error in constructor: ' . $e->getMessage());
            error_log('HomeController: Stack trace: ' . $e->getTraceAsString());
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

            // Use Twig template instead of hardcoded HTML
            $data = [
                'title' => 'Welcome to IslamWiki',
                'message' => 'Your Islamic knowledge base and resource center',
                'recentPages' => $recentPages,
                'features' => [
                    'Modern, responsive design',
                    'Alpine.js for lightweight interactivity',
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
