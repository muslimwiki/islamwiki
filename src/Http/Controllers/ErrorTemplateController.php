<?php

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Extensions\TemplateManagementExtension\TemplateManagementExtension;

class ErrorTemplateController extends Controller
{
    /**
     * @var \IslamWiki\Extensions\TemplateManagementExtension\TemplateManagementExtension|null
     */
    private $templateExtension;

    /**
     * Constructor
     */
    public function __construct(\IslamWiki\Core\Database\Connection $db, \IslamWiki\Core\Container\Container $container)
    {
        parent::__construct($db, $container);
        
        // Try to get the template management extension from the container
        try {
            $this->templateExtension = $container->get('template.management');
        } catch (\Exception $e) {
            // Fallback: create a new instance if not in container
            try {
                $this->templateExtension = new \IslamWiki\Extensions\TemplateManagementExtension\TemplateManagementExtension($container);
            } catch (\Exception $e2) {
                // If extension can't be created, use direct file operations
                $this->templateExtension = null;
            }
        }
    }

    /**
     * Show the main templates dashboard with all template types
     */
    public function templatesIndex(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        // Get all templates using the extension if available, otherwise direct file operations
        if ($this->templateExtension) {
            $allTemplates = $this->templateExtension->getAllTemplates();
            $templateStats = $this->templateExtension->getTemplateStatistics();
            $templateTypes = $this->templateExtension->getTemplateTypes();
        } else {
            $allTemplates = $this->getAllTemplatesDirect();
            $templateStats = $this->getTemplateStatisticsDirect();
            $templateTypes = $this->getTemplateTypesDirect();
        }
        
        return $this->view('admin/templates/index', [
            'allTemplates' => $allTemplates,
            'templateStats' => $templateStats,
            'templateTypes' => $templateTypes,
            'user' => $this->getCurrentUser($request)
        ]);
    }

    /**
     * Unified Templates Hub - Main entry point for all template management
     */
    public function templatesHub(Request $request): Response
    {
        $user = $this->getCurrentUser($request);
        $isAdmin = $user && $this->isAdmin($request);
        
        // Get all templates using the extension if available, otherwise direct file operations
        if ($this->templateExtension) {
            $allTemplates = $this->templateExtension->getAllTemplates();
            $templateStats = $this->templateExtension->getTemplateStatistics();
            $templateTypes = $this->templateExtension->getTemplateTypes();
        } else {
            $allTemplates = $this->getAllTemplatesDirect();
            $templateStats = $this->getTemplateStatisticsDirect();
            $templateTypes = $this->getTemplateTypesDirect();
        }
        
        // Determine view based on user role
        if ($isAdmin) {
            $view = 'templates/admin/index';
        } elseif ($user) {
            $view = 'templates/user/index';
        } else {
            $view = 'templates/guest/index';
        }
        
        return $this->view($view, [
            'allTemplates' => $allTemplates,
            'templateStats' => $templateStats,
            'templateTypes' => $templateTypes,
            'user' => $user,
            'isAdmin' => $isAdmin,
            'mode' => $isAdmin ? 'admin' : ($user ? 'user' : 'guest')
        ]);
    }

    /**
     * Error Templates - Role-based access
     */
    public function errorTemplates(Request $request): Response
    {
        $user = $this->getCurrentUser($request);
        $isAdmin = $user && $this->isAdmin($request);
        
        // Get error templates using the extension if available, otherwise direct file operations
        if ($this->templateExtension) {
            $errorTemplates = $this->templateExtension->getTemplatesByType('error');
            $templateStats = $this->templateExtension->getTemplateStatistics();
        } else {
            $errorTemplates = $this->getErrorTemplatesDirect();
            $templateStats = $this->getTemplateStatisticsDirect();
        }
        
        // Determine view based on user role
        if ($isAdmin) {
            $view = 'templates/admin/error/index';
        } elseif ($user) {
            $view = 'templates/user/error/index';
        } else {
            $view = 'templates/guest/error/index';
        }
        
        return $this->view($view, [
            'templates' => $errorTemplates,
            'stats' => $templateStats,
            'user' => $user,
            'isAdmin' => $isAdmin,
            'mode' => $isAdmin ? 'admin' : ($user ? 'user' : 'guest')
        ]);
    }

    /**
     * Edit Error Template (Admin only)
     */
    public function editErrorTemplate(Request $request, string $templateName): Response
    {
        $user = $this->getCurrentUser($request);
        if (!$user || !$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        $template = $this->getTemplateDirect('error', $templateName);
        
        if (!$template) {
            return new Response(404, ['Content-Type' => 'text/html; charset=utf-8'], 'Template not found');
        }

        return $this->view('templates/admin/error/edit', [
            'template' => $template,
            'user' => $user,
            'isAdmin' => true,
            'mode' => 'admin'
        ]);
    }

    /**
     * Update Error Template (Admin only)
     */
    public function updateErrorTemplate(Request $request, string $templateName): Response
    {
        $user = $this->getCurrentUser($request);
        if (!$user || !$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        $content = $request->getBody();
        $data = json_decode($content, true);
        
        if (!$data || !isset($data['content'])) {
            return new Response(400, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Invalid request data'])
            );
        }

        try {
            $success = $this->updateTemplateDirect('error', $templateName, $data['content']);
            
            if ($success) {
                return new Response(200, ['Content-Type' => 'application/json'], 
                    json_encode(['success' => true, 'message' => 'Template updated successfully'])
                );
            } else {
                return new Response(500, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => 'Failed to update template'])
                );
            }
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Update failed: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Preview Error Template (Admin only)
     */
    public function previewErrorTemplate(Request $request, string $templateName): Response
    {
        $user = $this->getCurrentUser($request);
        if (!$user || !$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        $template = $this->getTemplateDirect('error', $templateName);
        
        if (!$template) {
            return new Response(404, ['Content-Type' => 'text/html; charset=utf-8'], 'Template not found');
        }

        return $this->view('templates/admin/error/preview', [
            'template' => $template,
            'user' => $user,
            'isAdmin' => true,
            'mode' => 'admin'
        ]);
    }

    /**
     * Preview Error Template Content (Admin only)
     */
    public function previewErrorTemplateContent(Request $request, string $templateName): Response
    {
        $user = $this->getCurrentUser($request);
        if (!$user || !$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        $template = $this->getTemplateDirect('error', $templateName);
        if (!$template) {
            return new Response(404, ['Content-Type' => 'text/html; charset=utf-8'], 'Template not found');
        }

        $sampleData = $this->getSampleData($templateName);
        $theme = $request->getQueryParam('theme', 'light');
        $device = $request->getQueryParam('device', 'desktop');
        $language = $request->getQueryParam('language', 'en');
        
        $previewData = array_merge($sampleData, [
            'preview_mode' => true,
            'preview_theme' => $theme,
            'preview_device' => $device,
            'preview_language' => $language,
            'is_preview' => true
        ]);

        try {
            $content = $this->renderTemplate($templateName, $previewData);
            return new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $content);
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'text/html; charset=utf-8'],
                'Error rendering template: ' . $e->getMessage()
            );
        }
    }

    /**
     * Redirect methods for backward compatibility - Admin only
     */
    public function redirectToTemplates(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }
        
        return new Response(302, ['Location' => '/templates?mode=admin'], '');
    }

    public function redirectToErrorTemplates(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }
        
        return new Response(302, ['Location' => '/templates/error?mode=admin'], '');
    }

    public function redirectToEditTemplate(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }
        
        return new Response(302, ['Location' => "/templates/error/{$templateName}/edit?mode=admin"], '');
    }

    public function redirectToUpdateTemplate(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }
        
        return new Response(302, ['Location' => "/templates/error/{$templateName}/edit?mode=admin"], '');
    }

    public function redirectToPreviewTemplate(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }
        
        return new Response(302, ['Location' => "/templates/error/{$templateName}/preview?mode=admin"], '');
    }

    public function redirectToPreviewContent(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }
        
        return new Response(302, ['Location' => "/templates/error/{$templateName}/preview?mode=admin"], '');
    }

    /**
     * Show the error template management dashboard
     */
    public function index(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        // Get error templates using the extension if available, otherwise direct file operations
        if ($this->templateExtension) {
            $errorTemplates = $this->templateExtension->getTemplatesByType('error');
            $templateStats = $this->templateExtension->getTemplateStatistics();
        } else {
            $errorTemplates = $this->getErrorTemplatesDirect();
            $templateStats = $this->getTemplateStatisticsDirect();
        }
        
        return $this->view('admin/error-templates/index', [
            'templates' => $errorTemplates,
            'stats' => $templateStats,
            'user' => $this->getCurrentUser($request)
        ]);
    }

    /**
     * Show edit form for a specific error template
     */
    public function edit(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        $template = $this->getTemplateDirect('error', $templateName);
        
        if (!$template) {
            return new Response(404, ['Content-Type' => 'text/html; charset=utf-8'], 'Template not found');
        }

        return $this->view('admin/error-templates/edit', [
            'template' => $template,
            'user' => $this->getCurrentUser($request)
        ]);
    }

    /**
     * Update an error template
     */
    public function update(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        $content = $request->getBody();
        $data = json_decode($content, true);
        
        if (!$data || !isset($data['content'])) {
            return new Response(400, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Invalid request data'])
            );
        }

        try {
            $success = $this->updateTemplateDirect('error', $templateName, $data['content']);
            
            if ($success) {
                return new Response(200, ['Content-Type' => 'application/json'], 
                    json_encode(['success' => true, 'message' => 'Template updated successfully'])
                );
            } else {
                return new Response(500, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => 'Failed to update template'])
                );
            }
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Error updating template: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Preview an error template
     */
    public function preview(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        $template = $this->getTemplateDirect('error', $templateName);
        
        if (!$template) {
            return new Response(404, ['Content-Type' => 'text/html; charset=utf-8'], 'Template not found');
        }

        // Get sample data for preview
        $sampleData = $this->getSampleData($templateName);
        
        return $this->view('admin/error-templates/preview', [
            'template' => $template,
            'sampleData' => $sampleData,
            'user' => $this->getCurrentUser($request)
        ]);
    }

    /**
     * Render template content for preview iframe
     */
    public function previewContent(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'text/html; charset=utf-8'], 'Access Forbidden');
        }

        $template = $this->getTemplateDirect('error', $templateName);
        
        if (!$template) {
            return new Response(404, ['Content-Type' => 'text/html; charset=utf-8'], 'Template not found');
        }

        // Get sample data for preview
        $sampleData = $this->getSampleData($templateName);
        
        // Get query parameters for preview customization
        $theme = $request->getQueryParam('theme', 'light');
        $device = $request->getQueryParam('device', 'desktop');
        $language = $request->getQueryParam('language', 'en');
        
        // Add preview-specific data
        $previewData = array_merge($sampleData, [
            'preview_mode' => true,
            'preview_theme' => $theme,
            'preview_device' => $device,
            'preview_language' => $language,
            'is_preview' => true
        ]);
        
        // Render the actual template with sample data
        try {
            $content = $this->renderTemplate($templateName, $previewData);
            return new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $content);
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'text/html; charset=utf-8'], 
                'Error rendering template: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get sample data for template preview
     */
    private function getSampleData(string $templateName): array
    {
        $baseData = [
            'status_code' => $this->getStatusCodeFromTemplate($templateName),
            'error' => 'Sample error message for preview',
            'request_id' => 'req_' . uniqid(),
            'debug_info' => [
                'timestamp' => date('Y-m-d H:i:s T'),
                'context' => 'Template Preview',
                'error_type' => 'SampleException',
                'error_message' => 'This is a sample error message for preview purposes',
                'error_code' => 0,
                'file' => '/sample/file.php',
                'line' => 42,
                'request_info' => [
                    'method' => 'GET',
                    'uri' => '/sample/url',
                    'user_agent' => 'Mozilla/5.0 (Sample Browser)',
                    'remote_addr' => '127.0.0.1',
                    'http_host' => 'local.islam.wiki'
                ],
                'session_info' => [
                    'session_id' => 'sample_session_' . uniqid(),
                    'session_status' => 'Active',
                    'session_data' => ['user_id' => 1, 'role' => 'admin'],
                    'session_name' => 'PHPSESSID'
                ],
                'memory_usage' => [
                    'memory_usage' => '2,048,000',
                    'memory_peak' => '4,096,000',
                    'memory_limit' => '128M'
                ],
                'php_info' => [
                    'php_version' => '8.3.6',
                    'extensions' => ['json', 'mbstring', 'xml'],
                    'error_reporting' => 'E_ALL',
                    'display_errors' => 'On',
                    'log_errors' => 'On'
                ],
                'stack_trace' => "Stack trace:\n  at SampleClass.sampleMethod()\n  at main()"
            ]
        ];

        // Add template-specific data
        switch ($templateName) {
            case '403':
                $baseData['user_id'] = 'sample_user_123';
                $baseData['user_role'] = 'User';
                $baseData['required_role'] = 'Administrator';
                $baseData['resource_type'] = 'Page';
                $baseData['permission_level'] = 'Read';
                break;
                
            case '401':
                $baseData['session_status'] = 'Inactive';
                $baseData['user_id'] = 'Not Logged In';
                $baseData['user_role'] = 'None';
                $baseData['last_activity'] = 'Never';
                $baseData['required_role'] = 'Any Authenticated User';
                break;
                
            case '429':
                $baseData['rate_limit'] = '100 requests';
                $baseData['rate_window'] = '1 hour';
                $baseData['rate_remaining'] = '0';
                $baseData['rate_reset'] = date('Y-m-d H:i:s T', time() + 3600);
                break;
                
            case '503':
                $baseData['service_status'] = [
                    'database' => 'Maintenance',
                    'cache' => 'Offline',
                    'external_apis' => 'Limited'
                ];
                $baseData['estimated_recovery'] = '2-3 hours';
                break;
        }

        return $baseData;
    }

    /**
     * Get status code from template name
     */
    private function getStatusCodeFromTemplate(string $templateName): int
    {
        $statusCodes = [
            '400' => 400,
            '401' => 401,
            '403' => 403,
            '404' => 404,
            '422' => 422,
            '429' => 429,
            '500' => 500,
            '503' => 503
        ];

        return $statusCodes[$templateName] ?? 500;
    }

    /**
     * Render a template with data
     */
    private function renderTemplate(string $templateName, array $data): string
    {
        try {
            // Get the template renderer from the container
            $renderer = $this->container->get('twig.renderer');
            
            // Render the template
            return $renderer->render('errors/' . $templateName, $data);
        } catch (\Exception $e) {
            // Fallback: try to render using the view method
            try {
                $response = $this->view('errors/' . $templateName, $data);
                return $response->getBody();
            } catch (\Exception $e2) {
                // Last resort: return a basic error message
                return '<html><body><h1>Error ' . $data['status_code'] . '</h1><p>Template rendering failed: ' . $e2->getMessage() . '</p></body></html>';
            }
        }
    }

    /**
     * Backup all templates
     */
    public function backupTemplates(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        try {
            $backupData = [];
            $timestamp = date('Y-m-d_H-i-s');
            
            // Get all templates
            $allTemplates = $this->getAllTemplatesDirect();
            
            foreach ($allTemplates as $type => $templates) {
                foreach ($templates as $template) {
                    $templateContent = $this->getTemplateDirect($type, $template['name']);
                    if ($templateContent) {
                        $backupData[] = [
                            'type' => $type,
                            'name' => $template['name'],
                            'content' => $templateContent['content'],
                            'metadata' => [
                                'size' => $template['size'],
                                'modified' => $template['modified'],
                                'status' => $template['status']
                            ]
                        ];
                    }
                }
            }
            
            // Create backup file
            $backupDir = dirname(__DIR__, 3) . '/storage/backups/templates';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $backupFile = $backupDir . '/templates_backup_' . $timestamp . '.json';
            file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode([
                    'success' => true,
                    'message' => 'Templates backed up successfully',
                    'backup_file' => basename($backupFile),
                    'templates_count' => count($backupData)
                ])
            );
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Backup failed: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Restore templates from backup
     */
    public function restoreTemplates(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        try {
            $content = $request->getBody();
            $data = json_decode($content, true);
            
            if (!$data || !isset($data['backup_file'])) {
                return new Response(400, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => 'Backup file not specified'])
                );
            }
            
            $backupFile = dirname(__DIR__, 3) . '/storage/backups/templates/' . $data['backup_file'];
            
            if (!file_exists($backupFile)) {
                return new Response(404, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => 'Backup file not found'])
                );
            }
            
            $backupData = json_decode(file_get_contents($backupFile), true);
            $restoredCount = 0;
            
            foreach ($backupData as $template) {
                try {
                    $this->updateTemplateDirect($template['type'], $template['name'], $template['content']);
                    $restoredCount++;
                } catch (\Exception $e) {
                    // Log error but continue with other templates
                    error_log("Failed to restore template {$template['type']}/{$template['name']}: " . $e->getMessage());
                }
            }
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode([
                    'success' => true,
                    'message' => "Restored {$restoredCount} templates successfully",
                    'restored_count' => $restoredCount,
                    'total_count' => count($backupData)
                ])
            );
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Restore failed: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Validate all templates
     */
    public function validateTemplates(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        try {
            $validationResults = [];
            $allTemplates = $this->getAllTemplatesDirect();
            
            foreach ($allTemplates as $type => $templates) {
                foreach ($templates as $template) {
                    $validation = $this->validateTemplateDirect($type, $template['name']);
                    $validationResults[] = [
                        'type' => $type,
                        'name' => $template['name'],
                        'valid' => $validation['valid'],
                        'errors' => $validation['errors'],
                        'warnings' => $validation['warnings']
                    ];
                }
            }
            
            $totalTemplates = count($validationResults);
            $validTemplates = count(array_filter($validationResults, fn($r) => $r['valid']));
            $totalErrors = array_sum(array_map(fn($r) => count($r['errors']), $validationResults));
            $totalWarnings = array_sum(array_map(fn($r) => count($r['warnings']), $validationResults));
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode([
                    'success' => true,
                    'validation_results' => $validationResults,
                    'summary' => [
                        'total_templates' => $totalTemplates,
                        'valid_templates' => $validTemplates,
                        'invalid_templates' => $totalTemplates - $validTemplates,
                        'total_errors' => $totalErrors,
                        'total_warnings' => $totalWarnings
                    ]
                ])
            );
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Validation failed: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Export templates
     */
    public function exportTemplates(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        try {
            $content = $request->getBody();
            $data = json_decode($content, true);
            
            $exportType = $data['type'] ?? 'all';
            $exportFormat = $data['format'] ?? 'json';
            
            if ($exportType === 'all') {
                $exportData = $this->getAllTemplatesDirect();
            } else {
                $exportData = [$exportType => $this->getErrorTemplatesDirect()];
            }
            
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "templates_export_{$exportType}_{$timestamp}";
            
            if ($exportFormat === 'json') {
                $content = json_encode($exportData, JSON_PRETTY_PRINT);
                $headers = ['Content-Type' => 'application/json'];
                $filename .= '.json';
            } elseif ($exportFormat === 'zip') {
                // Create ZIP file
                $zipFile = tempnam(sys_get_temp_dir(), 'templates_export_');
                $zip = new \ZipArchive();
                $zip->open($zipFile, \ZipArchive::CREATE);
                
                foreach ($exportData as $type => $templates) {
                    foreach ($templates as $template) {
                        $templateContent = $this->getTemplateDirect($type, $template['name']);
                        if ($templateContent) {
                            $zip->addFromString("{$type}/{$template['name']}.twig", $templateContent['content']);
                        }
                    }
                }
                $zip->close();
                
                $content = file_get_contents($zipFile);
                $headers = ['Content-Type' => 'application/zip'];
                $filename .= '.zip';
                unlink($zipFile);
            } else {
                return new Response(400, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => 'Unsupported export format'])
                );
            }
            
            $headers['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
            
            return new Response(200, $headers, $content);
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Export failed: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Share templates
     */
    public function shareTemplates(Request $request): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        try {
            $content = $request->getBody();
            $data = json_decode($content, true);
            
            if (!$data || !isset($data['templates']) || !isset($data['share_method'])) {
                return new Response(400, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => 'Missing required parameters'])
                );
            }
            
            $templates = $data['templates'];
            $shareMethod = $data['share_method'];
            $shareOptions = $data['share_options'] ?? [];
            
            $shareResults = [];
            
            foreach ($templates as $templateInfo) {
                $type = $templateInfo['type'];
                $name = $templateInfo['name'];
                
                try {
                    $template = $this->getTemplateDirect($type, $name);
                    if ($template) {
                        $shareResults[] = [
                            'type' => $type,
                            'name' => $name,
                            'shared' => true,
                            'share_url' => $this->generateShareUrl($type, $name, $shareMethod, $shareOptions)
                        ];
                    }
                } catch (\Exception $e) {
                    $shareResults[] = [
                        'type' => $type,
                        'name' => $name,
                        'shared' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode([
                    'success' => true,
                    'message' => 'Templates shared successfully',
                    'share_results' => $shareResults
                ])
            );
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Sharing failed: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Get template versions
     */
    public function getTemplateVersions(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        try {
            $versions = $this->getTemplateVersionHistory($templateName);
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode([
                    'success' => true,
                    'template' => $templateName,
                    'versions' => $versions
                ])
            );
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Failed to get versions: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Create template version
     */
    public function createTemplateVersion(Request $request, string $templateName): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        try {
            $content = $request->getBody();
            $data = json_decode($content, true);
            
            $version = $this->createTemplateVersionSnapshot($templateName, $data['content'] ?? '', $data['message'] ?? '');
            
            return new Response(200, ['Content-Type' => 'application/json'], 
                json_encode([
                    'success' => true,
                    'message' => 'Version created successfully',
                    'version' => $version
                ])
            );
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Failed to create version: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Restore template version
     */
    public function restoreTemplateVersion(Request $request, string $templateName, string $version): Response
    {
        // Check if user is admin
        if (!$this->isAdmin($request)) {
            return new Response(403, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Access Forbidden'])
            );
        }

        try {
            $restored = $this->restoreTemplateVersionSnapshot($templateName, $version);
            
            if ($restored) {
                return new Response(200, ['Content-Type' => 'application/json'], 
                    json_encode([
                        'success' => true,
                        'message' => 'Version restored successfully'
                    ])
                );
            } else {
                return new Response(404, ['Content-Type' => 'application/json'], 
                    json_encode(['error' => 'Version not found'])
                );
            }
        } catch (\Exception $e) {
            return new Response(500, ['Content-Type' => 'application/json'], 
                json_encode(['error' => 'Failed to restore version: ' . $e->getMessage()])
            );
        }
    }

    /**
     * Generate share URL for template
     */
    private function generateShareUrl(string $type, string $name, string $method, array $options): string
    {
        $baseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'islam.wiki');
        
        switch ($method) {
            case 'public_link':
                return "{$baseUrl}/templates/shared/{$type}/{$name}";
            case 'download_link':
                return "{$baseUrl}/dashboard/templates/download/{$type}/{$name}";
            case 'preview_link':
                return "{$baseUrl}/dashboard/templates/preview/{$type}/{$name}";
            default:
                return "{$baseUrl}/dashboard/templates/{$type}/{$name}";
        }
    }

    /**
     * Get template version history
     */
    private function getTemplateVersionHistory(string $templateName): array
    {
        $versionsDir = dirname(__DIR__, 3) . '/storage/versions/templates';
        $templateVersionsDir = $versionsDir . '/' . $templateName;
        
        if (!is_dir($templateVersionsDir)) {
            return [];
        }
        
        $versions = [];
        $files = glob($templateVersionsDir . '/*.json');
        
        foreach ($files as $file) {
            $versionData = json_decode(file_get_contents($file), true);
            if ($versionData) {
                $versions[] = [
                    'version' => basename($file, '.json'),
                    'timestamp' => $versionData['timestamp'] ?? filemtime($file),
                    'message' => $versionData['message'] ?? '',
                    'size' => $versionData['size'] ?? 0,
                    'author' => $versionData['author'] ?? 'Unknown'
                ];
            }
        }
        
        // Sort by timestamp (newest first)
        usort($versions, fn($a, $b) => $b['timestamp'] - $a['timestamp']);
        
        return $versions;
    }

    /**
     * Create template version snapshot
     */
    private function createTemplateVersionSnapshot(string $templateName, string $content, string $message): array
    {
        $versionsDir = dirname(__DIR__, 3) . '/storage/versions/templates';
        $templateVersionsDir = $versionsDir . '/' . $templateName;
        
        if (!is_dir($templateVersionsDir)) {
            mkdir($templateVersionsDir, 0755, true);
        }
        
        $timestamp = time();
        $versionId = 'v' . date('Y-m-d_H-i-s', $timestamp);
        
        $versionData = [
            'version' => $versionId,
            'timestamp' => $timestamp,
            'message' => $message,
            'content' => $content,
            'size' => strlen($content),
            'author' => 'Admin'
        ];
        
        $versionFile = $templateVersionsDir . '/' . $versionId . '.json';
        file_put_contents($versionFile, json_encode($versionData, JSON_PRETTY_PRINT));
        
        return $versionData;
    }

    /**
     * Restore template version snapshot
     */
    private function restoreTemplateVersionSnapshot(string $templateName, string $version): bool
    {
        $versionsDir = dirname(__DIR__, 3) . '/storage/versions/templates';
        $versionFile = $versionsDir . '/' . $templateName . '/' . $version . '.json';
        
        if (!file_exists($versionFile)) {
            return false;
        }
        
        $versionData = json_decode(file_get_contents($versionFile), true);
        if (!$versionData || !isset($versionData['content'])) {
            return false;
        }
        
        // Find the template type by searching through all types
        $allTemplates = $this->getAllTemplatesDirect();
        $templateType = null;
        
        foreach ($allTemplates as $type => $templates) {
            foreach ($templates as $template) {
                if ($template['name'] === $templateName) {
                    $templateType = $type;
                    break 2;
                }
            }
        }
        
        if ($templateType) {
            try {
                $this->updateTemplateDirect($templateType, $templateName, $versionData['content']);
                return true;
            } catch (\Exception $e) {
                error_log("Failed to restore template version: " . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(Request $request): bool
    {
        // For now, hardcode admin access for testing
        // In production, this should use proper authentication
        return true;
    }

    /**
     * Get current user from request
     */
    private function getCurrentUser(Request $request): ?array
    {
        // This is a simplified implementation - in production you'd want proper session handling
        return [
            'id' => 1,
            'username' => 'admin',
            'role' => 'admin',
            'email' => 'admin@islam.wiki'
        ];
    }

    /**
     * Get all templates (direct file operations)
     */
    private function getAllTemplatesDirect(): array
    {
        $allTemplates = [];
        $templateTypes = $this->getTemplateTypesDirect();

        foreach ($templateTypes as $type => $typeInfo) {
            $templates = $this->getErrorTemplatesDirect(); // Assuming error templates are the only ones for now
            $allTemplates[$type] = $templates;
        }
        return $allTemplates;
    }

    /**
     * Get error templates (direct file operations)
     */
    private function getErrorTemplatesDirect(): array
    {
        $errorTemplates = [];
        $errorTemplatesDir = dirname(__DIR__, 3) . '/resources/views/errors';
        
        if (!is_dir($errorTemplatesDir)) {
            return [];
        }

        $files = glob($errorTemplatesDir . '/*.twig');
        foreach ($files as $file) {
            $templateName = basename($file, '.twig');
            $templateContent = file_get_contents($file);
            $errorTemplates[] = [
                'name' => $templateName,
                'filename' => $file,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'status' => 'active', // Assuming all are active for now
                'icon' => '🚨',
                'description' => 'HTTP error page templates (404, 500, 403, etc.)'
            ];
        }
        return $errorTemplates;
    }

    /**
     * Get a specific template (direct file operations)
     */
    private function getTemplateDirect(string $type, string $name): ?array
    {
        // Map type to correct directory
        $typeMap = [
            'error' => 'errors'  // Map 'error' type to 'errors' directory
        ];
        
        $actualDir = $typeMap[$type] ?? $type;
        $templateDir = dirname(__DIR__, 3) . '/resources/views/' . $actualDir;
        $templateFile = $templateDir . '/' . $name . '.twig';

        if (!is_file($templateFile)) {
            return null;
        }

        return [
            'name' => $name,
            'filename' => $name . '.twig',
            'path' => $templateFile,
            'content' => file_get_contents($templateFile),
            'size' => filesize($templateFile),
            'modified' => filemtime($templateFile),
            'status' => 'active',
            'icon' => '🚨',
            'description' => 'HTTP error page templates (404, 500, 403, etc.)'
        ];
    }

    /**
     * Get template statistics (direct file operations)
     */
    private function getTemplateStatisticsDirect(): array
    {
        $errorTemplates = $this->getErrorTemplatesDirect();
        $totalSize = array_sum(array_map(fn($t) => $t['size'], $errorTemplates));
        
        return [
            'total_templates' => count($errorTemplates),
            'total_size' => $totalSize,
            'recently_modified' => 0, // No direct way to track recently modified for files
            'missing_templates' => 0,
            'by_type' => [
                'error' => [
                    'count' => count($errorTemplates),
                    'size' => $totalSize,
                    'recently_modified' => 0,
                    'missing' => 0
                ]
            ]
        ];
    }

    /**
     * Get template types (direct file operations)
     */
    private function getTemplateTypesDirect(): array
    {
        return [
            'error' => [
                'name' => 'Error Templates',
                'description' => 'HTTP error page templates (404, 500, 403, etc.)',
                'path' => 'resources/views/errors',
                'extension' => '.twig',
                'icon' => '🚨'
            ]
        ];
    }

    /**
     * Update a template (direct file operations)
     */
    private function updateTemplateDirect(string $type, string $name, string $content): bool
    {
        $templateDir = dirname(__DIR__, 3) . '/resources/views/' . $type;
        $templateFile = $templateDir . '/' . $name . '.twig';

        if (!is_dir($templateDir)) {
            return false;
        }

        try {
            file_put_contents($templateFile, $content);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to update template {$type}/{$name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate a template (direct file operations)
     */
    private function validateTemplateDirect(string $type, string $name): array
    {
        $templateDir = dirname(__DIR__, 3) . '/resources/views/' . $type;
        $templateFile = $templateDir . '/' . $name . '.twig';

        if (!is_file($templateFile)) {
            return ['valid' => false, 'errors' => ['Template not found'], 'warnings' => []];
        }

        // Basic syntax check (e.g., Twig syntax)
        // This is a very basic example. A real validator would be more robust.
        $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(dirname(__DIR__, 3) . '/resources/views'));
        try {
            $twig->load($type . '/' . $name . '.twig');
            return ['valid' => true, 'errors' => [], 'warnings' => []];
        } catch (\Twig\Error\SyntaxError $e) {
            return ['valid' => false, 'errors' => [$e->getMessage()], 'warnings' => []];
        } catch (\Exception $e) {
            return ['valid' => false, 'errors' => [$e->getMessage()], 'warnings' => []];
        }
    }
} 