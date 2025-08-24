<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\TemplateManagementExtension;

use IslamWiki\Core\Extensions\ExtensionInterface;
use IslamWiki\Core\Container\Container;
use IslamWiki\Core\Logging\Logger;

/**
 * Template Management Extension
 * 
 * Centralized template management system for all template types:
 * - Error templates (404, 500, 403, etc.)
 * - Wiki templates
 * - Dashboard templates
 * - Custom templates
 * 
 * @package IslamWiki\Extensions\TemplateManagementExtension
 * @version 0.0.1.0
 */
class TemplateManagementExtension implements ExtensionInterface
{
    /**
     * Extension metadata
     */
    public const EXTENSION_NAME = 'Template Management Extension';
    public const EXTENSION_VERSION = '0.0.1.0';
    public const EXTENSION_DESCRIPTION = 'Centralized template management system for all template types';
    public const EXTENSION_AUTHOR = 'IslamWiki Team';
    public const EXTENSION_WEBSITE = 'https://islam.wiki';

    /**
     * Template types supported by this extension
     */
    private const SUPPORTED_TEMPLATE_TYPES = [
        'error' => [
            'name' => 'Error Templates',
            'description' => 'HTTP error page templates (404, 500, 403, etc.)',
            'path' => 'resources/views/errors',
            'extension' => '.twig',
            'icon' => '🚨'
        ],
        'wiki' => [
            'name' => 'Wiki Templates',
            'description' => 'Wiki page templates and layouts',
            'path' => 'resources/views/wiki',
            'extension' => '.twig',
            'icon' => '📚'
        ],
        'dashboard' => [
            'name' => 'Dashboard Templates',
            'description' => 'Admin and user dashboard templates',
            'path' => 'resources/views/dashboard',
            'extension' => '.twig',
            'icon' => '📊'
        ],
        'auth' => [
            'name' => 'Authentication Templates',
            'description' => 'Login, register, and auth-related templates',
            'path' => 'resources/views/auth',
            'extension' => '.twig',
            'icon' => '🔐'
        ],
        'components' => [
            'name' => 'Component Templates',
            'description' => 'Reusable UI component templates',
            'path' => 'resources/views/components',
            'extension' => '.twig',
            'icon' => '🧩'
        ]
    ];

    /**
     * @var Container
     */
    private Container $container;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var string
     */
    private string $basePath;

    /**
     * Initialize the extension
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->basePath = dirname(__DIR__, 2); // Go up to project root
        $this->logger = $container->get(Logger::class);
    }

    /**
     * Get extension name
     */
    public function getName(): string
    {
        return self::EXTENSION_NAME;
    }

    /**
     * Get extension version
     */
    public function getVersion(): string
    {
        return self::EXTENSION_VERSION;
    }

    /**
     * Get extension description
     */
    public function getDescription(): string
    {
        return self::EXTENSION_DESCRIPTION;
    }

    /**
     * Install the extension
     */
    public function install(): bool
    {
        try {
            $this->logger->info('Template Management Extension installed', [
                'extension' => self::EXTENSION_NAME,
                'version' => self::EXTENSION_VERSION
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to install Template Management Extension', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Uninstall the extension
     */
    public function uninstall(): bool
    {
        try {
            $this->logger->info('Template Management Extension uninstalled', [
                'extension' => self::EXTENSION_NAME
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to uninstall Template Management Extension', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Activate the extension
     */
    public function activate(): bool
    {
        try {
            $this->logger->info('Template Management Extension activated', [
                'extension' => self::EXTENSION_NAME
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to activate Template Management Extension', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Deactivate the extension
     */
    public function deactivate(): bool
    {
        try {
            $this->logger->info('Template Management Extension deactivated', [
                'extension' => self::EXTENSION_NAME
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to deactivate Template Management Extension', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Initialize the extension
     */
    public function init(): void
    {
        $this->register();
        $this->boot();
    }

    /**
     * Register the extension
     */
    public function register(): void
    {
        $this->logger->info('Template Management Extension registered', [
            'extension' => self::EXTENSION_NAME,
            'version' => self::EXTENSION_VERSION
        ]);

        // Register services
        $this->registerServices();
    }

    /**
     * Boot the extension
     */
    public function boot(): void
    {
        $this->logger->info('Template Management Extension booted', [
            'extension' => self::EXTENSION_NAME,
            'template_types' => array_keys(self::SUPPORTED_TEMPLATE_TYPES)
        ]);
    }

    /**
     * Get all available template types
     */
    public function getTemplateTypes(): array
    {
        return self::SUPPORTED_TEMPLATE_TYPES;
    }

    /**
     * Get templates of a specific type
     */
    public function getTemplatesByType(string $type): array
    {
        if (!isset(self::SUPPORTED_TEMPLATE_TYPES[$type])) {
            throw new \InvalidArgumentException("Unsupported template type: {$type}");
        }

        $config = self::SUPPORTED_TEMPLATE_TYPES[$type];
        $templatePath = $this->basePath . '/' . $config['path'];
        $templates = [];

        if (is_dir($templatePath)) {
            $files = glob($templatePath . '/*' . $config['extension']);
            foreach ($files as $file) {
                $name = basename($file, $config['extension']);
                $templates[] = [
                    'name' => $name,
                    'type' => $type,
                    'filename' => $name . $config['extension'],
                    'path' => $file,
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                    'status' => $this->getTemplateStatus($file),
                    'icon' => $config['icon'],
                    'description' => $config['description']
                ];
            }
        }

        return $templates;
    }

    /**
     * Get all templates across all types
     */
    public function getAllTemplates(): array
    {
        $allTemplates = [];
        
        foreach (array_keys(self::SUPPORTED_TEMPLATE_TYPES) as $type) {
            $allTemplates[$type] = $this->getTemplatesByType($type);
        }

        return $allTemplates;
    }

    /**
     * Get a specific template
     */
    public function getTemplate(string $type, string $name): ?array
    {
        if (!isset(self::SUPPORTED_TEMPLATE_TYPES[$type])) {
            throw new \InvalidArgumentException("Unsupported template type: {$type}");
        }

        $config = self::SUPPORTED_TEMPLATE_TYPES[$type];
        $templatePath = $this->basePath . '/' . $config['path'] . '/' . $name . $config['extension'];

        if (!file_exists($templatePath)) {
            return null;
        }

        return [
            'name' => $name,
            'type' => $type,
            'filename' => $name . $config['extension'],
            'path' => $templatePath,
            'content' => file_get_contents($templatePath),
            'size' => filesize($templatePath),
            'modified' => filemtime($templatePath),
            'status' => $this->getTemplateStatus($templatePath),
            'icon' => $config['icon'],
            'description' => $config['description']
        ];
    }

    /**
     * Update a template
     */
    public function updateTemplate(string $type, string $name, string $content): bool
    {
        if (!isset(self::SUPPORTED_TEMPLATE_TYPES[$type])) {
            throw new \InvalidArgumentException("Unsupported template type: {$type}");
        }

        $config = self::SUPPORTED_TEMPLATE_TYPES[$type];
        $templatePath = $this->basePath . '/' . $config['path'] . '/' . $name . $config['extension'];

        // Create backup
        $backupPath = $templatePath . '.backup.' . date('Y-m-d-H-i-s');
        if (file_exists($templatePath)) {
            copy($templatePath, $backupPath);
        }

        // Write new content
        $success = file_put_contents($templatePath, $content) !== false;

        if ($success) {
            $this->logger->info('Template updated successfully', [
                'type' => $type,
                'name' => $name,
                'backup_path' => $backupPath
            ]);
        } else {
            $this->logger->error('Failed to update template', [
                'type' => $type,
                'name' => $name,
                'template_path' => $templatePath
            ]);
        }

        return $success;
    }

    /**
     * Get template statistics
     */
    public function getTemplateStatistics(): array
    {
        $stats = [
            'total_templates' => 0,
            'total_size' => 0,
            'by_type' => [],
            'recently_modified' => 0,
            'missing_templates' => 0
        ];

        foreach (self::SUPPORTED_TEMPLATE_TYPES as $type => $config) {
            $templates = $this->getTemplatesByType($type);
            $typeStats = [
                'count' => count($templates),
                'size' => array_sum(array_column($templates, 'size')),
                'recently_modified' => 0,
                'missing' => 0
            ];

            foreach ($templates as $template) {
                if ($template['status'] === 'recently_modified') {
                    $typeStats['recently_modified']++;
                } elseif ($template['status'] === 'missing') {
                    $typeStats['missing']++;
                }
            }

            $stats['by_type'][$type] = $typeStats;
            $stats['total_templates'] += $typeStats['count'];
            $stats['total_size'] += $typeStats['size'];
            $stats['recently_modified'] += $typeStats['recently_modified'];
            $stats['missing_templates'] += $typeStats['missing'];
        }

        return $stats;
    }

    /**
     * Validate template syntax
     */
    public function validateTemplate(string $type, string $name): array
    {
        $template = $this->getTemplate($type, $name);
        if (!$template) {
            return ['valid' => false, 'errors' => ['Template not found']];
        }

        $errors = [];
        $warnings = [];

        // Basic Twig syntax validation
        if (strpos($template['content'], '{{') !== false && strpos($template['content'], '}}') === false) {
            $errors[] = 'Unclosed Twig variable';
        }

        if (strpos($template['content'], '{%') !== false && strpos($template['content'], '%}') === false) {
            $errors[] = 'Unclosed Twig tag';
        }

        // Check for common issues
        if (strpos($template['content'], '{% extends') === false) {
            $warnings[] = 'Template does not extend a layout';
        }

        if (strpos($template['content'], '{% block content') === false) {
            $warnings[] = 'Template missing content block';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Get template status
     */
    private function getTemplateStatus(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return 'missing';
        }

        // Check if template has been modified recently (within last 24 hours)
        if (time() - filemtime($filePath) < 86400) {
            return 'recently_modified';
        }

        return 'active';
    }

    /**
     * Register extension services
     */
    private function registerServices(): void
    {
        // Register the extension itself
        $this->container->set('template.management', $this);
        $this->container->alias('IslamWiki\Extensions\TemplateManagementExtension\TemplateManagementExtension', 'template.management');
    }
} 