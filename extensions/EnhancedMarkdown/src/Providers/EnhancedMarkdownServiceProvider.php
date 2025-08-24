<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Providers;

use Container;\Container
use IslamWiki\Extensions\EnhancedMarkdown\EnhancedMarkdown;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\TemplateManager;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\CategoryManager;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\ReferenceManager;

/**
 * Enhanced Markdown Service Provider
 * 
 * Registers Enhanced Markdown services with the application container
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class EnhancedMarkdownServiceProvider
{
    /**
     * Register services with the container
     */
    public function register(Container $container): void
    {
        // Register TemplateManager
        $container->register('EnhancedMarkdown.TemplateManager', function($container) {
            $connection = $container->resolve('IslamWiki\Core\Database\Connection');
            return new TemplateManager($connection);
        });
        
        // Register CategoryManager
        $container->register('EnhancedMarkdown.CategoryManager', function($container) {
            $connection = $container->resolve('IslamWiki\Core\Database\Connection');
            return new CategoryManager($connection);
        });
        
        // Register ReferenceManager
        $container->register('EnhancedMarkdown.ReferenceManager', function($container) {
            $connection = $container->resolve('IslamWiki\Core\Database\Connection');
            return new ReferenceManager($connection);
        });
        
        // Register main EnhancedMarkdown service
        $container->register('EnhancedMarkdown', function($container) {
            $connection = $container->resolve('IslamWiki\Core\Database\Connection');
            return new EnhancedMarkdown($connection);
        });
        
        // Register EnhancedMarkdown with managers
        $container->register('EnhancedMarkdown.WithManagers', function($container) {
            $templateManager = $container->resolve('EnhancedMarkdown.TemplateManager');
            $categoryManager = $container->resolve('EnhancedMarkdown.CategoryManager');
            $referenceManager = $container->resolve('EnhancedMarkdown.ReferenceManager');
            
            return new EnhancedMarkdown(null, $templateManager, $categoryManager, $referenceManager);
        });
    }
    
    /**
     * Boot the service provider
     */
    public function boot(Container $container): void
    {
        // Any initialization that needs to happen after all services are registered
        
        // Register template routes if needed
        $this->registerTemplateRoutes($container);
        
        // Initialize template cache
        $this->initializeTemplateCache($container);
    }
    
    /**
     * Register template-related routes
     */
    private function registerTemplateRoutes(Container $container): void
    {
        // This would register routes like:
        // GET /wiki/Template:{name} - View template
        // GET /wiki/Template:{name}?action=edit - Edit template
        // POST /wiki/Template:{name} - Save template
        // GET /wiki/Special:Templates - List all templates
        
        // Routes would be registered with the router if available
        // For now, this is a placeholder for future implementation
    }
    
    /**
     * Initialize template cache
     */
    private function initializeTemplateCache(Container $container): void
    {
        try {
            $templateManager = $container->resolve('EnhancedMarkdown.TemplateManager');
            
            // Pre-load commonly used templates
            $commonTemplates = [
                'Good article',
                'About',
                'Infobox',
                'Stub',
                'Warning',
                'Note'
            ];
            
            foreach ($commonTemplates as $templateName) {
                $templateManager->loadTemplate($templateName);
            }
            
        } catch (\Exception $e) {
            // Log error but don't fail boot process
            error_log("Failed to initialize template cache: " . $e->getMessage());
        }
    }
    
    /**
     * Get service provider info
     */
    public function getInfo(): array
    {
        return [
            'name' => 'EnhancedMarkdownServiceProvider',
            'version' => '0.0.3.0',
            'description' => 'Provides Enhanced Markdown with Template System services',
            'services' => [
                'EnhancedMarkdown.TemplateManager',
                'EnhancedMarkdown.CategoryManager', 
                'EnhancedMarkdown.ReferenceManager',
                'EnhancedMarkdown',
                'EnhancedMarkdown.WithManagers'
            ],
            'dependencies' => [
                'IslamWiki\Core\Database\Connection'
            ]
        ];
    }
} 