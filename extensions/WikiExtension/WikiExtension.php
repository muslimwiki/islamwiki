<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\WikiExtension;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Extensions\WikiExtension\Services\PerformanceMonitor;
use IslamWiki\Extensions\WikiExtension\Services\SupportSystem;
use IslamWiki\Extensions\WikiExtension\Services\SuccessMetrics;

/**
 * WikiExtension - Unified wiki system for IslamWiki
 * 
 * @package IslamWiki\Extensions\WikiExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class WikiExtension extends Extension
{
    /**
     * Called when the extension is initialized
     */
    protected function onInitialize(): void
    {
        $this->registerHooks();
        $this->loadResources();
        $this->registerServices();
        error_log('WikiExtension initialized successfully');
    }
    
    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        // Register wiki-specific hooks
        if ($this->hookManager) {
            $this->hookManager->register('WikiPageCreate', [$this, 'onWikiPageCreate']);
            $this->hookManager->register('WikiPageUpdate', [$this, 'onWikiPageUpdate']);
            $this->hookManager->register('WikiPageDelete', [$this, 'onWikiPageDelete']);
        }
    }
    
    /**
     * Register extension services
     */
    protected function registerServices(): void
    {
        if ($this->container) {
            // Register Phase 8 services
            $this->container->set('wiki.performance_monitor', function($container) {
                return new PerformanceMonitor(
                    $container->get('database'),
                    $container->get('logger')
                );
            });
            
            $this->container->set('wiki.support_system', function($container) {
                return new SupportSystem($container->get('database'));
            });
            
            $this->container->set('wiki.success_metrics', function($container) {
                return new SuccessMetrics($container->get('database'));
            });
        }
    }
    
    /**
     * Load extension resources
     */
    protected function loadResources(): void
    {
        $extensionPath = $this->getExtensionPath();
        
        // Load CSS files
        $cssPath = $extensionPath . '/assets/css';
        if (is_dir($cssPath)) {
            $this->loadCssFiles($cssPath);
        }
        
        // Load JS files
        $jsPath = $extensionPath . '/assets/js';
        if (is_dir($jsPath)) {
            $this->loadJsFiles($jsPath);
        }
    }
    
    /**
     * Hook: Wiki page created
     */
    public function onWikiPageCreate(array $data): void
    {
        error_log('Wiki page created: ' . ($data['title'] ?? 'Unknown'));
    }
    
    /**
     * Hook: Wiki page updated
     */
    public function onWikiPageUpdate(array $data): void
    {
        error_log('Wiki page updated: ' . ($data['title'] ?? 'Unknown'));
    }
    
    /**
     * Hook: Wiki page deleted
     */
    public function onWikiPageDelete(array $data): void
    {
        error_log('Wiki page deleted: ' . ($data['title'] ?? 'Unknown'));
    }
} 