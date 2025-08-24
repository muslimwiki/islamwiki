<?php

/**
 * Dashboard Controller
 *
 * Handles dashboard functionality for IslamWiki.
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Container;

/**
 * Dashboard Controller - Handles Dashboard Functionality
 */
class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     * 
     * SECURITY: This method requires authentication. Unauthenticated users
     * are redirected to the login page.
     *
     * @param Request $request The incoming request
     * @return Response
     */
    public function index(Request $request): Response
    {
        try {
            // Check if user is authenticated
            $session = $this->container->get('session');
            if (!$session->isLoggedIn()) {
                // Return authentication required response
                return new Response(401, [
                    'Content-Type' => 'text/html; charset=UTF-8'
                ], 
                '<!DOCTYPE html>
                <html>
                <head>
                    <title>Authentication Required - IslamWiki</title>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <meta http-equiv="refresh" content="3;url=/login">
                </head>
                <body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
                    <h1>🔒 Authentication Required</h1>
                    <p>You must be logged in to access the dashboard.</p>
                    <p>Redirecting to login page in 3 seconds...</p>
                    <p><a href="/login">Click here to login now</a></p>
                    <p><a href="/wiki/Home">← Back to Home Page</a></p>
                </body>
                </html>'
                );
            }

            // Get current user
            $user = $this->getCurrentUser();
            
            // Get dashboard data
            $dashboardData = $this->getDashboardData();
            
            return $this->view('dashboard/user_dashboard', [
                'user' => $user,
                'dashboard_data' => $dashboardData,
                'title' => 'Dashboard - IslamWiki'
            ]);
            
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Show the admin dashboard.
     */
    public function admin(Request $request): Response
    {
        try {
            // Check if user is admin
            $session = $this->container->get('session');
            if (!$session->isLoggedIn() || !$session->isAdmin()) {
                return new Response(403, [], 'Access Denied');
            }
            
            $user = $this->getCurrentUser();
            $adminData = $this->getAdminData();
            
            return $this->view('dashboard/admin_dashboard', [
                'user' => $user,
                'admin_data' => $adminData,
                'title' => 'Admin Dashboard - IslamWiki'
            ]);
            
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Get current user from container.
     */
    private function getCurrentUser(): ?array
    {
        try {
            if ($this->container && $this->container->has('auth')) {
                $auth = $this->container->get('auth');
                if (method_exists($auth, 'user')) {
                    return $auth->user();
                }
            }
        } catch (\Exception $e) {
            // Log error if logger is available
            if (isset($this->logger)) {
                $this->logger->warning('Failed to get current user', ['error' => $e->getMessage()]);
            }
        }
        
        return null;
    }

    /**
     * Get dashboard data.
     */
    private function getDashboardData(): array
    {
        return [
            'statistics' => [
                'total_pages' => 0,
                'total_users' => 0,
                'recent_activity' => []
            ],
            'quick_actions' => [
                'create_page' => '/wiki/create',
                'edit_profile' => '/profile/edit',
                'view_watchlist' => '/watchlist'
            ]
        ];
    }

    /**
     * Get admin dashboard data.
     */
    private function getAdminData(): array
    {
        return [
            'system_stats' => [
                'total_pages' => 0,
                'total_users' => 0,
                'active_sessions' => 0
            ],
            'recent_activity' => [],
            'system_health' => 'Good'
        ];
    }
}
