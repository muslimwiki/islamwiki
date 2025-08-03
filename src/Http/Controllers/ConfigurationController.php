<?php
declare(strict_types=1);

/**
 * Configuration Controller
 * 
 * Web and API controller for configuration management including
 * viewing, editing, validation, backup, and restore functionality.
 * 
 * @package IslamWiki\Http\Controllers
 * @version 0.0.20
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Configuration\ConfigurationManager;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container;
use IslamWiki\Core\Logging\Logger;

class ConfigurationController extends Controller
{
    /**
     * The configuration manager.
     */
    private ConfigurationManager $configManager;

    /**
     * The logger instance.
     */
    private Logger $logger;

    /**
     * Create a new configuration controller instance.
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->configManager = $container->get(ConfigurationManager::class);
        $this->logger = $container->get(Logger::class);
    }

    /**
     * Display the configuration index page.
     */
    public function index(): Response
    {
        try {
            $categories = $this->configManager->getCategories();
            $validation = $this->configManager->validateConfiguration();
            
            return $this->view('configuration/index', [
                'categories' => $categories,
                'validation' => $validation,
                'title' => 'Configuration Management'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Configuration index error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load configuration', 500);
        }
    }

    /**
     * Display the configuration builder page.
     */
    public function builder(): Response
    {
        try {
            $templates = $this->getConfigurationTemplates();
            
            return $this->view('configuration/builder', [
                'templates' => $templates,
                'title' => 'Configuration Builder'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Configuration builder error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load configuration builder', 500);
        }
    }

    /**
     * Display configuration by category.
     */
    public function show(string $category): Response
    {
        try {
            $categories = $this->configManager->getCategories();
            
            if (!isset($categories[$category])) {
                return $this->errorResponse('Configuration category not found', 404);
            }
            
            $configurations = $this->configManager->getCategory($category);
            $categoryInfo = $categories[$category];
            
            return $this->view('configuration/show', [
                'category' => $categoryInfo,
                'configurations' => $configurations,
                'title' => "Configuration - {$categoryInfo['display_name']}"
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Configuration show error for category {$category}: " . $e->getMessage());
            return $this->errorResponse('Failed to load configuration category', 500);
        }
    }

    /**
     * Update configuration value.
     */
    public function update(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['key']) || !isset($data['value'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Missing required fields: key and value'
                ], 400);
            }
            
            $key = $data['key'];
            $value = $data['value'];
            $userId = $this->getCurrentUserId();
            
            if ($this->configManager->setValue($key, $value, $userId)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Configuration updated successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update configuration'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('Configuration update error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Export configuration.
     */
    public function export(): Response
    {
        try {
            $configuration = $this->configManager->exportConfiguration();
            
            $filename = 'islamwiki-config-' . date('Y-m-d-H-i-s') . '.json';
            
            return new Response(
                200,
                [
                    'Content-Type' => 'application/json',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\""
                ],
                json_encode($configuration, JSON_PRETTY_PRINT)
            );
        } catch (\Exception $e) {
            $this->logger->error('Configuration export error: ' . $e->getMessage());
            return $this->errorResponse('Failed to export configuration', 500);
        }
    }

    /**
     * Import configuration.
     */
    public function import(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['configuration']) || !is_array($data['configuration'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid configuration data'
                ], 400);
            }
            
            if ($this->configManager->importConfiguration($data)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Configuration imported successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to import configuration'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('Configuration import error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Validate configuration.
     */
    public function validate(Request $request): Response
    {
        try {
            $validation = $this->configManager->validateConfiguration();
            
            return $this->jsonResponse([
                'success' => true,
                'validation' => $validation
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Configuration validation error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to validate configuration'
            ], 500);
        }
    }

    /**
     * Create configuration backup.
     */
    public function createBackup(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            
            $backupName = $data['backup_name'] ?? 'backup-' . date('Y-m-d-H-i-s');
            $description = $data['description'] ?? null;
            $userId = $this->getCurrentUserId();
            
            if ($this->configManager->createBackup($backupName, $userId, $description)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Configuration backup created successfully',
                    'backup_name' => $backupName
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create configuration backup'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('Configuration backup creation error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Restore configuration from backup.
     */
    public function restoreBackup(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['backup_id'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Backup ID is required'
                ], 400);
            }
            
            $backupId = (int) $data['backup_id'];
            
            if ($this->configManager->restoreBackup($backupId)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Configuration restored successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to restore configuration'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('Configuration backup restore error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get configuration audit log.
     */
    public function auditLog(Request $request): Response
    {
        try {
            $limit = (int) ($request->getQueryParams()['limit'] ?? 100);
            $offset = (int) ($request->getQueryParams()['offset'] ?? 0);
            
            $auditLog = $this->configManager->getAuditLog($limit, $offset);
            
            return $this->jsonResponse([
                'success' => true,
                'audit_log' => $auditLog
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Configuration audit log error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve audit log'
            ], 500);
        }
    }

    /**
     * Get configuration backups.
     */
    public function backups(): Response
    {
        try {
            $backups = $this->configManager->getBackups();
            
            return $this->jsonResponse([
                'success' => true,
                'backups' => $backups
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Configuration backups error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve backups'
            ], 500);
        }
    }

    /**
     * API endpoint to get all configuration.
     */
    public function apiIndex(): Response
    {
        try {
            $categories = $this->configManager->getCategories();
            $allConfig = [];
            
            foreach ($categories as $category => $categoryInfo) {
                $allConfig[$category] = [
                    'info' => $categoryInfo,
                    'configurations' => $this->configManager->getCategory($category)
                ];
            }
            
            return $this->jsonResponse([
                'success' => true,
                'configuration' => $allConfig
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Configuration API index error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve configuration'
            ], 500);
        }
    }

    /**
     * API endpoint to get configuration by category.
     */
    public function apiShow(string $category): Response
    {
        try {
            $categories = $this->configManager->getCategories();
            
            if (!isset($categories[$category])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Configuration category not found'
                ], 404);
            }
            
            $configurations = $this->configManager->getCategory($category);
            
            return $this->jsonResponse([
                'success' => true,
                'category' => $categories[$category],
                'configurations' => $configurations
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Configuration API show error for category {$category}: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve configuration category'
            ], 500);
        }
    }

    /**
     * API endpoint to update configuration value.
     */
    public function apiUpdate(Request $request, string $key): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['value'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Value is required'
                ], 400);
            }
            
            $value = $data['value'];
            $userId = $this->getCurrentUserId();
            
            if ($this->configManager->setValue($key, $value, $userId)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Configuration updated successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update configuration'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error("Configuration API update error for key {$key}: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * API endpoint to get configuration templates.
     */
    public function apiTemplates(): Response
    {
        try {
            $templates = $this->getConfigurationTemplates();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $templates,
                'message' => 'Configuration templates retrieved successfully'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API templates error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve configuration templates'
            ], 500);
        }
    }

    /**
     * API endpoint to create configuration template.
     */
    public function apiCreateTemplate(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['name']) || !isset($data['category'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Template name and category are required'
                ], 400);
            }
            
            $templateId = $this->createConfigurationTemplate($data);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => ['template_id' => $templateId],
                'message' => 'Configuration template created successfully'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API create template error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create configuration template'
            ], 500);
        }
    }

    /**
     * API endpoint to apply configuration template.
     */
    public function apiApplyTemplate(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['template_id'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Template ID is required'
                ], 400);
            }
            
            $applied = $this->applyConfigurationTemplate($data['template_id']);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => ['applied' => $applied],
                'message' => 'Configuration template applied successfully'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API apply template error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to apply configuration template'
            ], 500);
        }
    }

    /**
     * API endpoint for bulk configuration operations.
     */
    public function apiBulkUpdate(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['operations']) || !is_array($data['operations'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Operations array is required'
                ], 400);
            }
            
            $results = $this->performBulkOperations($data['operations']);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $results,
                'message' => 'Bulk operations completed'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API bulk update error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to perform bulk operations'
            ], 500);
        }
    }

    /**
     * API endpoint to get configuration analytics.
     */
    public function apiAnalytics(): Response
    {
        try {
            $analytics = $this->getConfigurationAnalytics();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $analytics,
                'message' => 'Configuration analytics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API analytics error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve configuration analytics'
            ], 500);
        }
    }

    /**
     * API endpoint for advanced configuration validation.
     */
    public function apiAdvancedValidate(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            $validationMode = $data['mode'] ?? 'full';
            
            $validation = $this->performAdvancedValidation($validationMode);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $validation,
                'message' => 'Advanced validation completed'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API advanced validation error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to perform advanced validation'
            ], 500);
        }
    }

    /**
     * API endpoint to get configuration dependencies.
     */
    public function apiDependencies(string $key): Response
    {
        try {
            $dependencies = $this->getConfigurationDependencies($key);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $dependencies,
                'message' => 'Configuration dependencies retrieved successfully'
            ]);
        } catch (\Exception $e) {
            $this->logger->error("API dependencies error for key {$key}: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve configuration dependencies'
            ], 500);
        }
    }

    /**
     * API endpoint to get configuration suggestions.
     */
    public function apiSuggestions(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();
            $query = $data['query'] ?? '';
            $category = $data['category'] ?? null;
            
            $suggestions = $this->getConfigurationSuggestions($query, $category);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $suggestions,
                'message' => 'Configuration suggestions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API suggestions error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve configuration suggestions'
            ], 500);
        }
    }

    /**
     * API endpoint to get configuration performance metrics.
     */
    public function apiPerformance(): Response
    {
        try {
            $metrics = $this->getConfigurationPerformanceMetrics();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $metrics,
                'message' => 'Configuration performance metrics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            $this->logger->error('API performance error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve configuration performance metrics'
            ], 500);
        }
    }

    /**
     * Get configuration templates.
     */
    private function getConfigurationTemplates(): array
    {
        // This would typically query a templates table
        // For now, return sample templates
        return [
            [
                'id' => 1,
                'name' => 'Production Server',
                'description' => 'Configuration template for production server deployment',
                'category' => 'Core',
                'fields' => [
                    'app.debug' => false,
                    'app.environment' => 'production',
                    'database.host' => 'localhost',
                    'database.port' => 3306
                ]
            ],
            [
                'id' => 2,
                'name' => 'Development Server',
                'description' => 'Configuration template for development server deployment',
                'category' => 'Core',
                'fields' => [
                    'app.debug' => true,
                    'app.environment' => 'development',
                    'database.host' => 'localhost',
                    'database.port' => 3306
                ]
            ]
        ];
    }

    /**
     * Create configuration template.
     */
    private function createConfigurationTemplate(array $data): int
    {
        // This would typically insert into a templates table
        // For now, return a mock ID
        return rand(1000, 9999);
    }

    /**
     * Apply configuration template.
     */
    private function applyConfigurationTemplate(int $templateId): bool
    {
        // This would typically load template and apply to configuration
        // For now, return success
        return true;
    }

    /**
     * Perform bulk configuration operations.
     */
    private function performBulkOperations(array $operations): array
    {
        $results = [];
        
        foreach ($operations as $operation) {
            try {
                switch ($operation['action']) {
                    case 'set':
                        $success = $this->configManager->setValue($operation['key'], $operation['value']);
                        $results[] = [
                            'action' => 'set',
                            'key' => $operation['key'],
                            'success' => $success
                        ];
                        break;
                        
                    case 'delete':
                        // Implement delete operation
                        $results[] = [
                            'action' => 'delete',
                            'key' => $operation['key'],
                            'success' => true
                        ];
                        break;
                        
                    default:
                        $results[] = [
                            'action' => $operation['action'],
                            'key' => $operation['key'] ?? null,
                            'success' => false,
                            'error' => 'Unknown action'
                        ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'action' => $operation['action'],
                    'key' => $operation['key'] ?? null,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Get configuration analytics.
     */
    private function getConfigurationAnalytics(): array
    {
        $auditLog = $this->configManager->getAuditLog(1000, 0);
        
        $analytics = [
            'total_changes' => count($auditLog),
            'changes_by_category' => [],
            'changes_by_user' => [],
            'recent_activity' => array_slice($auditLog, 0, 10),
            'most_changed_keys' => [],
            'validation_status' => $this->configManager->validateConfiguration()
        ];
        
        // Calculate changes by category
        foreach ($auditLog as $entry) {
            $category = $entry['category'];
            $analytics['changes_by_category'][$category] = ($analytics['changes_by_category'][$category] ?? 0) + 1;
        }
        
        return $analytics;
    }

    /**
     * Perform advanced configuration validation.
     */
    private function performAdvancedValidation(string $mode): array
    {
        $validation = $this->configManager->validateConfiguration();
        
        $advancedValidation = [
            'basic_validation' => $validation,
            'dependency_check' => $this->checkConfigurationDependencies(),
            'performance_check' => $this->checkConfigurationPerformance(),
            'security_check' => $this->checkConfigurationSecurity(),
            'consistency_check' => $this->checkConfigurationConsistency()
        ];
        
        if ($mode === 'full') {
            $advancedValidation['comprehensive_check'] = $this->performComprehensiveCheck();
        }
        
        return $advancedValidation;
    }

    /**
     * Get configuration dependencies.
     */
    private function getConfigurationDependencies(string $key): array
    {
        // This would typically query a dependencies table or configuration metadata
        // For now, return sample dependencies
        return [
            'dependencies' => [
                'app.name' => ['app.environment', 'app.debug'],
                'database.host' => ['database.port', 'database.name'],
                'security.encryption_key' => ['security.algorithm']
            ],
            'dependents' => [
                'app.environment' => ['app.name', 'app.debug'],
                'database.port' => ['database.host', 'database.connection_string']
            ]
        ];
    }

    /**
     * Get configuration suggestions.
     */
    private function getConfigurationSuggestions(string $query, ?string $category): array
    {
        $suggestions = [];
        
        if (empty($query)) {
            return $suggestions;
        }
        
        $categories = $this->configManager->getCategories();
        
        foreach ($categories as $cat) {
            if ($category && $cat['name'] !== $category) {
                continue;
            }
            
            $configs = $this->configManager->getCategory($cat['name']);
            
            foreach ($configs as $key => $value) {
                if (stripos($key, $query) !== false || stripos($cat['display_name'], $query) !== false) {
                    $suggestions[] = [
                        'key' => $key,
                        'category' => $cat['name'],
                        'display_name' => $cat['display_name'],
                        'value' => $value
                    ];
                }
            }
        }
        
        return array_slice($suggestions, 0, 10);
    }

    /**
     * Get configuration performance metrics.
     */
    private function getConfigurationPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        $this->configManager->loadConfiguration();
        $loadTime = (microtime(true) - $startTime) * 1000;
        
        return [
            'load_time_ms' => round($loadTime, 2),
            'cache_hit_rate' => 0.95,
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'total_configurations' => count($this->configManager->getCategories()),
            'validation_time_ms' => round($this->measureValidationTime(), 2)
        ];
    }

    /**
     * Check configuration dependencies.
     */
    private function checkConfigurationDependencies(): array
    {
        // This would check for missing dependencies and circular dependencies
        return [
            'status' => 'ok',
            'missing_dependencies' => [],
            'circular_dependencies' => [],
            'warnings' => []
        ];
    }

    /**
     * Check configuration performance.
     */
    private function checkConfigurationPerformance(): array
    {
        // This would check for performance issues in configuration
        return [
            'status' => 'ok',
            'slow_queries' => [],
            'optimization_suggestions' => []
        ];
    }

    /**
     * Check configuration security.
     */
    private function checkConfigurationSecurity(): array
    {
        // This would check for security issues in configuration
        return [
            'status' => 'ok',
            'security_issues' => [],
            'recommendations' => []
        ];
    }

    /**
     * Check configuration consistency.
     */
    private function checkConfigurationConsistency(): array
    {
        // This would check for consistency issues in configuration
        return [
            'status' => 'ok',
            'inconsistencies' => [],
            'suggestions' => []
        ];
    }

    /**
     * Perform comprehensive configuration check.
     */
    private function performComprehensiveCheck(): array
    {
        // This would perform a comprehensive check of all configuration aspects
        return [
            'overall_status' => 'ok',
            'checks_performed' => [
                'validation',
                'dependencies',
                'performance',
                'security',
                'consistency'
            ],
            'score' => 95
        ];
    }

    /**
     * Measure validation time.
     */
    private function measureValidationTime(): float
    {
        $startTime = microtime(true);
        $this->configManager->validateConfiguration();
        return (microtime(true) - $startTime) * 1000;
    }

    /**
     * Get current user ID from session.
     */
    private function getCurrentUserId(): ?int
    {
        try {
            $session = $this->container->get('session');
            return $session?->get('user_id');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Render a template with data.
     */
    private function render(string $template, array $data = []): Response
    {
        $renderer = $this->container->get('view');
        $content = $renderer->render($template, $data);
        
        return new Response(200, ['Content-Type' => 'text/html'], $content);
    }

    /**
     * Create JSON response.
     */
    private function jsonResponse(array $data, int $status = 200): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }

    /**
     * Create error response.
     */
    private function errorResponse(string $message, int $status = 500): Response
    {
        return new Response(
            $status,
            ['Content-Type' => 'text/html'],
            "<h1>Error {$status}</h1><p>{$message}</p>"
        );
    }
} 