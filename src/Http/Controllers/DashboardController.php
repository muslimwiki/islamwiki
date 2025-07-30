<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(\IslamWiki\Core\Database\Connection $db, \IslamWiki\Core\Container $container)
    {
        parent::__construct($db, $container);
    }

    /**
     * Show the application dashboard.
     */
    public function index(Request $request): Response
    {
        // Convert PSR-7 request to our Request class
        $convertedRequest = \IslamWiki\Core\Http\Request::capture();
        
        // Get current user from session
        $user = null;
        try {
            $session = $this->container->get('session');
            error_log('DashboardController: Session isLoggedIn: ' . ($session->isLoggedIn() ? 'true' : 'false'));
            
            if ($session->isLoggedIn()) {
                $userId = $session->getUserId();
                error_log('DashboardController: User ID from session: ' . $userId);
                $user = \IslamWiki\Models\User::find($userId, $this->db);
                error_log('DashboardController: User found: ' . ($user ? 'true' : 'false'));
                if ($user) {
                    error_log('DashboardController: Username: ' . $user->getAttribute('username'));
                }
            }
        } catch (\Exception $e) {
            error_log('DashboardController: Error getting user: ' . $e->getMessage());
        }
        
        // Use Twig template instead of hardcoded HTML
        $data = [
            'title' => 'Dashboard - IslamWiki',
            'message' => 'Welcome to your IslamWiki dashboard',
            'user' => $user,
            'stats' => [
                'totalPages' => 1234,
                'userEdits' => 42,
                'watchlist' => 12
            ],
            'activities' => [
                ['type' => 'edit', 'page' => 'Quran', 'time' => '2 hours ago', 'user' => 'You'],
                ['type' => 'create', 'page' => 'Hadith Collection', 'time' => '1 day ago', 'user' => 'You'],
                ['type' => 'edit', 'page' => 'Islamic History', 'time' => '3 days ago', 'user' => 'Admin']
            ],
            'watchlist' => [
                'Quran',
                'Hadith Collection', 
                'Islamic History',
                'Prayer Times',
                'Islamic Calendar'
            ]
        ];

        return $this->view('dashboard/index', $data);
    }
}
