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
            
            return $this->render('configuration/index.twig', [
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
            
            return $this->render('configuration/show.twig', [
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