<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request): Response
    {
        // Convert PSR-7 request to our Request class
        $convertedRequest = \IslamWiki\Core\Http\Request::capture();
        
        // Use Twig template instead of hardcoded HTML
        $data = [
            'title' => 'Dashboard - IslamWiki',
            'message' => 'Welcome to your IslamWiki dashboard',
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
