<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\IqraSearchExtension;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Extensions\IqraSearchExtension\Services\IqraSearchEngine;
use IslamWiki\Extensions\IqraSearchExtension\Services\SearchIndexer;
use IslamWiki\Extensions\IqraSearchExtension\Services\SearchRelevance;
use IslamWiki\Extensions\IqraSearchExtension\Services\SearchSuggestions;

/**
 * IqraSearchExtension - Advanced Islamic search system for IslamWiki
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */
class IqraSearchExtension extends Extension
{
    /**
     * Called when the extension is initialized
     */
    protected function onInitialize(): void
    {
        $this->registerHooks();
        $this->loadResources();
        $this->registerServices();
        error_log('IqraSearchExtension initialized successfully');
    }
    
    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        // Register search-specific hooks
        if ($this->hookManager) {
            $this->hookManager->register('SearchQuery', [$this, 'onSearchQuery']);
            $this->hookManager->register('SearchResult', [$this, 'onSearchResult']);
            $this->hookManager->register('ContentIndexed', [$this, 'onContentIndexed']);
        }
    }
    
    /**
     * Register extension services
     */
    protected function registerServices(): void
    {
        if ($this->container) {
            // Register core search services
            $this->container->set('iqra.search_engine', function($container) {
                return new IqraSearchEngine(
                    $container->get('database'),
                    $container->get('logger')
                );
            });
            
            $this->container->set('iqra.search_indexer', function($container) {
                return new SearchIndexer(
                    $container->get('database'),
                    $container->get('logger')
                );
            });
            
            $this->container->set('iqra.search_relevance', function($container) {
                return new SearchRelevance(
                    $container->get('database'),
                    $container->get('logger')
                );
            });
            
            $this->container->set('iqra.search_suggestions', function($container) {
                return new SearchSuggestions(
                    $container->get('database'),
                    $container->get('logger')
                );
            });
            
            // Alias for backward compatibility
            $this->container->alias('iqra.search', 'iqra.search_engine');
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
     * Hook: Search query executed
     */
    public function onSearchQuery(array $data): void
    {
        error_log('Search query executed: ' . ($data['query'] ?? 'Unknown'));
        // Track search analytics
        if (isset($this->container)) {
            $analytics = $this->container->get('iqra.search_analytics');
            $analytics->trackQuery($data);
        }
    }
    
    /**
     * Hook: Search result returned
     */
    public function onSearchResult(array $data): void
    {
        error_log('Search result returned: ' . ($data['result_count'] ?? '0') . ' results');
        // Track result analytics
        if (isset($this->container)) {
            $analytics = $this->container->get('iqra.search_analytics');
            $analytics->trackResult($data);
        }
    }
    
    /**
     * Hook: Content indexed for search
     */
    public function onContentIndexed(array $data): void
    {
        error_log('Content indexed: ' . ($data['content_type'] ?? 'Unknown') . ' - ' . ($data['content_id'] ?? 'Unknown'));
        // Update search index
        if (isset($this->container)) {
            $indexer = $this->container->get('iqra.search_indexer');
            $indexer->indexContent($data);
        }
    }
} 