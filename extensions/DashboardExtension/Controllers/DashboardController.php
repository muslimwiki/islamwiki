<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\DashboardExtension\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\View\TwigRenderer;
use IslamWiki\Extensions\DashboardExtension\Config\DashboardConfig;
use IslamWiki\Extensions\DashboardExtension\Services\DashboardService;

/**
 * Dashboard Controller
 * 
 * Handles dashboard logic and routing for different user roles
 */
class DashboardController
{
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    /**
     * @var TwigRenderer
     */
    private TwigRenderer $view;

    /**
     * Constructor
     *
     * @param DashboardService $dashboardService
     * @param TwigRenderer $view
     */
    public function __construct(DashboardService $dashboardService, TwigRenderer $view)
    {
        $this->dashboardService = $dashboardService;
        $this->view = $view;
    }

    /**
     * Show the main dashboard based on user role
     *
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        // For extension purposes, use placeholder user data
        $user = ['id' => 1, 'username' => 'User', 'is_admin' => false];
        $role = $this->determineUserRole($user);
        
        // Get role-specific dashboard configuration
        $dashboardConfig = DashboardConfig::getRoleConfig($role);
        
        // Get dashboard data for the user
        $dashboardData = $this->dashboardService->getDashboardData($user, $role);
        
        // Render the appropriate template
        $template = $dashboardConfig['template'];
        $content = $this->view->render($template, [
            'user' => $user,
            'role' => $role,
            'config' => $dashboardConfig,
            'data' => $dashboardData,
            'widgets' => $dashboardConfig['widgets'],
            'layout' => $dashboardConfig['layout'],
            'theme' => $dashboardConfig['theme']
        ]);

        return new Response($content);
    }

    /**
     * Show admin dashboard
     *
     * @param Request $request
     * @return Response
     */
    public function showAdmin(Request $request): Response
    {
        // For extension purposes, use placeholder admin user data
        $user = ['id' => 1, 'username' => 'Admin', 'is_admin' => true];
        
        if (!$this->hasPermission($user, 'system_admin')) {
            return $this->redirectToLogin();
        }

        $dashboardConfig = DashboardConfig::getRoleConfig('admin');
        $dashboardData = $this->dashboardService->getAdminDashboardData($user);
        
        $content = $this->view->render('admin_dashboard.twig', [
            'user' => $user,
            'role' => 'admin',
            'config' => $dashboardConfig,
            'data' => $dashboardData,
            'widgets' => $dashboardConfig['widgets'],
            'layout' => $dashboardConfig['layout'],
            'theme' => $dashboardConfig['theme']
        ]);

        return new Response($content);
    }

    /**
     * Show scholar dashboard
     *
     * @param Request $request
     * @return Response
     */
    public function showScholar(Request $request): Response
    {
        // For extension purposes, use placeholder scholar user data
        $user = ['id' => 2, 'username' => 'Scholar', 'is_admin' => false, 'can_review' => true];
        
        if (!$this->hasPermission($user, 'review_content')) {
            return $this->redirectToLogin();
        }

        $dashboardConfig = DashboardConfig::getRoleConfig('scholar');
        $dashboardData = $this->dashboardService->getScholarDashboardData($user);
        
        $content = $this->view->render('scholar_dashboard.twig', [
            'user' => $user,
            'role' => 'scholar',
            'config' => $dashboardConfig,
            'data' => $dashboardData,
            'widgets' => $dashboardConfig['widgets'],
            'layout' => $dashboardConfig['layout'],
            'theme' => $dashboardConfig['theme']
        ]);

        return new Response($content);
    }

    /**
     * Show contributor dashboard
     *
     * @param Request $request
     * @return Response
     */
    public function showContributor(Request $request): Response
    {
        // For extension purposes, use placeholder contributor user data
        $user = ['id' => 3, 'username' => 'Contributor', 'is_admin' => false, 'can_create' => true];
        
        if (!$this->hasPermission($user, 'create_articles')) {
            return $this->redirectToLogin();
        }

        $dashboardConfig = DashboardConfig::getRoleConfig('contributor');
        $dashboardData = $this->dashboardService->getContributorDashboardData($user);
        
        $content = $this->view->render('contributor_dashboard.twig', [
            'user' => $user,
            'role' => 'contributor',
            'config' => $dashboardConfig,
            'data' => $dashboardData,
            'widgets' => $dashboardConfig['widgets'],
            'layout' => $dashboardConfig['layout'],
            'theme' => $dashboardConfig['theme']
        ]);

        return new Response($content);
    }

    /**
     * Show basic user dashboard
     *
     * @param Request $request
     * @return Response
     */
    public function showUser(Request $request): Response
    {
        // For extension purposes, use placeholder user data
        $user = ['id' => 4, 'username' => 'User', 'is_admin' => false];
        
        $dashboardConfig = DashboardConfig::getRoleConfig('user');
        $dashboardData = $this->dashboardService->getUserDashboardData($user);
        
        $content = $this->view->render('user_dashboard.twig', [
            'user' => $user,
            'role' => 'user',
            'config' => $dashboardConfig,
            'data' => $dashboardData,
            'widgets' => $dashboardConfig['widgets'],
            'layout' => $dashboardConfig['layout'],
            'theme' => $dashboardConfig['theme']
        ]);

        return new Response($content);
    }

    /**
     * Get widget data via AJAX
     *
     * @param Request $request
     * @return Response
     */
    public function getWidgetData(Request $request): Response
    {
        // For extension purposes, use placeholder user data
        $user = ['id' => 1, 'username' => 'User', 'is_admin' => false];
        $widgetId = $request->getQueryParam('widget_id');
        $role = $this->determineUserRole($user);
        
        if (!$widgetId) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode(['error' => 'Widget ID required']));
        }

        $widgetData = $this->dashboardService->getWidgetData($user, $role, $widgetId);
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($widgetData));
    }

    /**
     * Update user dashboard preferences
     *
     * @param Request $request
     * @return Response
     */
    public function updatePreferences(Request $request): Response
    {
        // For extension purposes, use placeholder user data
        $user = ['id' => 1, 'username' => 'User', 'is_admin' => false];
        $preferences = $request->getParsedBody()['preferences'] ?? [];
        
        $success = $this->dashboardService->updateUserPreferences($user, $preferences);
        
        if ($success) {
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['success' => true]));
        }
        
        return new Response(500, ['Content-Type' => 'application/json'], json_encode(['error' => 'Failed to update preferences']));
    }

    /**
     * Get dashboard configuration
     *
     * @param Request $request
     * @return Response
     */
    public function getConfig(Request $request): Response
    {
        // For extension purposes, use placeholder user data
        $user = ['id' => 1, 'username' => 'User', 'is_admin' => false];
        $role = $this->determineUserRole($user);
        
        $config = DashboardConfig::getRoleConfig($role);
        $layouts = DashboardConfig::getAvailableLayouts();
        $themes = DashboardConfig::getAvailableThemes();
        $widgets = DashboardConfig::getWidgetDefinitions();
        
        $responseData = [
            'config' => $config,
            'layouts' => $layouts,
            'themes' => $themes,
            'widgets' => $widgets
        ];
        
        return new Response(json_encode($responseData), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Determine user role based on permissions and attributes
     *
     * @param array $user
     * @return string
     */
    private function determineUserRole(array $user): string
    {
        if (!$user) {
            return 'user';
        }

        // Check for admin role
        if ($this->hasPermission($user, 'system_admin')) {
            return 'admin';
        }

        // Check for scholar role
        if ($this->hasPermission($user, 'review_content')) {
            return 'scholar';
        }

        // Check for contributor role
        if ($this->hasPermission($user, 'create_articles')) {
            return 'contributor';
        }

        // Default to basic user
        return 'user';
    }

    /**
     * Check if user has specific permission
     *
     * @param array $user
     * @param string $permission
     * @return bool
     */
    private function hasPermission(array $user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        // Check admin status
        if ($user['is_admin'] ?? false) {
            return true;
        }

        // Check specific permissions
        $userPermissions = $user['permissions'] ?? [];
        
        return in_array($permission, $userPermissions);
    }

    /**
     * Redirect to login page
     *
     * @return Response
     */
    private function redirectToLogin(): Response
    {
        return new Response('', 302, [
            'Location' => '/auth/login'
        ]);
    }

    /**
     * Handle widget refresh
     *
     * @param Request $request
     * @return Response
     */
    public function refreshWidget(Request $request): Response
    {
        // For extension purposes, use placeholder user data
        $user = ['id' => 1, 'username' => 'User', 'is_admin' => false];
        $widgetId = $request->getQueryParam('widget_id');
        $role = $this->determineUserRole($user);
        
        if (!$widgetId) {
            return new Response(json_encode(['error' => 'Widget ID required']), 400);
        }

        $widgetData = $this->dashboardService->refreshWidget($user, $role, $widgetId);
        
        return new Response(json_encode($widgetData), 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Handle dashboard export
     *
     * @param Request $request
     * @return Response
     */
    public function exportDashboard(Request $request): Response
    {
        // For extension purposes, use placeholder user data
        $user = ['id' => 1, 'username' => 'User', 'is_admin' => false];
        $role = $this->determineUserRole($user);
        $format = $request->getQueryParam('format', 'json');
        
        $dashboardData = $this->dashboardService->exportDashboard($user, $role, $format);
        
        $headers = [];
        switch ($format) {
            case 'csv':
                $headers['Content-Type'] = 'text/csv';
                $headers['Content-Disposition'] = 'attachment; filename="dashboard.csv"';
                break;
            case 'pdf':
                $headers['Content-Type'] = 'application/pdf';
                $headers['Content-Disposition'] = 'attachment; filename="dashboard.pdf"';
                break;
            default:
                $headers['Content-Type'] = 'application/json';
                break;
        }
        
        return new Response($dashboardData, 200, $headers);
    }

    /**
     * Handle dashboard import
     *
     * @param Request $request
     * @return Response
     */
    public function importDashboard(Request $request): Response
    {
        // For extension purposes, use placeholder user data
        $user = ['id' => 1, 'username' => 'User', 'is_admin' => false];
        $importData = $request->getParsedBody()['import_data'] ?? [];
        
        $success = $this->dashboardService->importDashboard($user, $importData);
        
        if ($success) {
            return new Response(json_encode(['success' => true]), 200, [
                'Content-Type' => 'application/json'
            ]);
        }
        
        return new Response(json_encode(['error' => 'Failed to import dashboard']), 500, [
            'Content-Type' => 'application/json'
        ]);
    }
} 