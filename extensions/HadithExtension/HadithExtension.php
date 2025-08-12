<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;
use IslamWiki\Models\Hadith;
use IslamWiki\Extensions\HadithExtension\Widgets\DailyHadithWidget;
use IslamWiki\Extensions\HadithExtension\Widgets\HadithSearchWidget;
use IslamWiki\Extensions\HadithExtension\Widgets\HadithCollectionsWidget;

/**
 * Hadith System Extension
 *
 * Provides comprehensive Hadith functionality including search, display,
 * management, and integration with the wiki system.
 */
class HadithExtension extends Extension
{
    /**
     * @var Hadith Hadith model instance
     */
    private Hadith $hadithModel;

    /**
     * @var array Available hadith collections
     */
    private array $collections = [];

    /**
     * @var array Hadith widgets
     */
    private array $widgets = [];

    /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->loadHadithModel();
        $this->loadCollections();
        // Don't load widgets during initialization to avoid autoloader issues
        // $this->loadWidgets();
        $this->registerHooks();
        $this->loadResources();
    }

    /**
     * Load Hadith model instance
     */
    private function loadHadithModel(): void
    {
        $this->hadithModel = new Hadith();
    }

    /**
     * Load available hadith collections
     */
    private function loadCollections(): void
    {
        try {
            $this->collections = $this->hadithModel->getCollections();
        } catch (\Exception $e) {
            // Log error but don't fail extension loading
            error_log("Failed to load hadith collections: " . $e->getMessage());
            $this->collections = [];
        }
    }

    /**
     * Load hadith widgets
     */
    private function loadWidgets(): void
    {
        $this->widgets = [
            'daily_hadith' => new DailyHadithWidget($this->hadithModel),
            'hadith_search' => new HadithSearchWidget($this->hadithModel),
            'hadith_collections' => new HadithCollectionsWidget($this->hadithModel)
        ];
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();

        // Content parsing hook for hadith syntax
        $hookManager->register('ContentParse', [$this, 'onContentParse'], 10);

        // Page display hook for hadith content
        $hookManager->register('PageDisplay', [$this, 'onPageDisplay'], 10);

        // Search indexing hook
        $hookManager->register('SearchIndex', [$this, 'onSearchIndex'], 10);

        // Widget rendering hook
        $hookManager->register('WidgetRender', [$this, 'onWidgetRender'], 10);

        // Template loading hook
        $hookManager->register('TemplateLoad', [$this, 'onTemplateLoad'], 10);

        // Admin menu hook
        $hookManager->register('AdminMenu', [$this, 'onAdminMenu'], 10);

        // User profile hook
        $hookManager->register('UserProfile', [$this, 'onUserProfile'], 10);
    }

    /**
     * Content parsing hook for hadith syntax
     *
     * @param string $content The content to parse
     * @param string $format The content format
     * @return string The parsed content
     */
    public function onContentParse(string $content, string $format = 'markdown'): string
    {
        if ($format !== 'markdown') {
            return $content;
        }

        // Parse hadith citations: {{hadith:collection:number}}
        $content = $this->parseHadithCitations($content);

        // Parse hadith references: [hadith:collection:number]
        $content = $this->parseHadithReferences($content);

        return $content;
    }

    /**
     * Parse hadith citations in content
     *
     * @param string $content Content to parse
     * @return string Parsed content
     */
    private function parseHadithCitations(string $content): string
    {
        $pattern = '/\{\{hadith:([^:]+):(\d+)\}\}/';
        
        return preg_replace_callback($pattern, function ($matches) {
            $collection = $matches[1];
            $number = $matches[2];

            try {
                $hadith = $this->hadithModel->getByReference($collection, $number);
                if ($hadith) {
                    return $this->renderHadithCitation($hadith);
                }
            } catch (\Exception $e) {
                // Log error but don't break content
                error_log("Failed to parse hadith citation: " . $e->getMessage());
            }

            return "<span class='hadith-citation-error'>Hadith not found: {$collection} {$number}</span>";
        }, $content);
    }

    /**
     * Parse hadith references in content
     *
     * @param string $content Content to parse
     * @return string Parsed content
     */
    private function parseHadithReferences(string $content): string
    {
        $pattern = '/\[hadith:([^:]+):(\d+)\]/';
        
        return preg_replace_callback($pattern, function ($matches) {
            $collection = $matches[1];
            $number = $matches[2];

            return "<a href='/hadith/{$collection}/{$number}' class='hadith-reference'>{$collection} {$number}</a>";
        }, $content);
    }

    /**
     * Render hadith citation HTML
     *
     * @param array $hadith Hadith data
     * @return string HTML for citation
     */
    private function renderHadithCitation(array $hadith): string
    {
        $collection = $hadith['collection_name'] ?? 'Unknown';
        $number = $hadith['hadith_number'] ?? '';
        $grade = $hadith['grade'] ?? '';
        
        $gradeClass = $this->getGradeClass($grade);
        
        return "<div class='hadith-citation {$gradeClass}'>
                    <div class='hadith-header'>
                        <span class='collection'>{$collection}</span>
                        <span class='number'>{$number}</span>
                        <span class='grade'>{$grade}</span>
                    </div>
                    <div class='hadith-text'>{$hadith['english_text']}</div>
                </div>";
    }

    /**
     * Get CSS class for hadith grade
     *
     * @param string $grade Hadith grade
     * @return string CSS class
     */
    private function getGradeClass(string $grade): string
    {
        $gradeMap = [
            'sahih' => 'grade-sahih',
            'hasan' => 'grade-hasan',
            'daif' => 'grade-daif',
            'mawdu' => 'grade-mawdu'
        ];
        
        return $gradeMap[strtolower($grade)] ?? 'grade-unknown';
    }

    /**
     * Page display hook
     *
     * @param array $pageData Page data
     * @param array $context Display context
     * @return array Modified page data
     */
    public function onPageDisplay(array $pageData, array $context = []): array
    {
        // Add hadith-related metadata if page contains hadith content
        if ($this->containsHadithContent($pageData['content'] ?? '')) {
            $pageData['hadith_metadata'] = $this->extractHadithMetadata($pageData['content']);
        }
        
        return $pageData;
    }

    /**
     * Check if content contains hadith references
     *
     * @param string $content Content to check
     * @return bool True if contains hadith content
     */
    private function containsHadithContent(string $content): bool
    {
        return preg_match('/\{\{hadith:|\[hadith:/', $content) === 1;
    }

    /**
     * Extract hadith metadata from content
     *
     * @param string $content Content to analyze
     * @return array Hadith metadata
     */
    private function extractHadithMetadata(string $content): array
    {
        $metadata = [];

        // Extract hadith citations
        preg_match_all('/\{\{hadith:([^:]+):(\d+)\}\}/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $metadata['citations'][] = [
                'collection' => $match[1],
                'number' => $match[2]
            ];
        }

        // Extract hadith references
        preg_match_all('/\[hadith:([^:]+):(\d+)\]/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $metadata['references'][] = [
                'collection' => $match[1],
                'number' => $match[2]
            ];
        }

        return $metadata;
    }

    /**
     * Search indexing hook
     *
     * @param array $content Content to index
     * @param array $context Search context
     * @return array Modified content
     */
    public function onSearchIndex(array $content, array $context = []): array
    {
        // Add hadith-related search terms
        if (isset($content['hadith_metadata'])) {
            $content['search_terms'][] = 'hadith';
            $content['search_terms'][] = 'sunnah';
            
            foreach ($content['hadith_metadata']['citations'] ?? [] as $citation) {
                $content['search_terms'][] = $citation['collection'];
                $content['search_terms'][] = $citation['collection'] . ' ' . $citation['number'];
            }
        }
        
        return $content;
    }

    /**
     * Widget rendering hook
     *
     * @param string $widgetName Widget name
     * @param array $context Widget context
     * @return string|null Widget HTML or null if not handled
     */
    public function onWidgetRender(string $widgetName, array $context = []): ?string
    {
        // Load widgets lazily when needed
        if (empty($this->widgets)) {
            $this->loadWidgets();
        }
        
        if (isset($this->widgets[$widgetName])) {
            return $this->widgets[$widgetName]->render($context);
        }

        return null;
    }

    /**
     * Template loading hook
     *
     * @param string $templateName Template name
     * @return string|null Template content or null if not found
     */
    public function onTemplateLoad(string $templateName): ?string
    {
        $templatePath = $this->getExtensionPath() . '/templates/' . $templateName;
        
        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }
        
        return null;
    }

    /**
     * Admin menu hook
     *
     * @param array $menuItems Admin menu items
     * @return array Modified menu items
     */
    public function onAdminMenu(array $menuItems): array
    {
        $menuItems['hadith'] = [
            'title' => 'Hadith System',
            'url' => '/admin/hadith',
            'icon' => 'book-open',
            'permission' => 'hadith_manage'
        ];
        
        return $menuItems;
    }

    /**
     * User profile hook
     *
     * @param array $profileData User profile data
     * @param array $context Profile context
     * @return array Modified profile data
     */
    public function onUserProfile(array $profileData, array $context = []): array
    {
        // Add hadith-related profile information
        $profileData['hadith_stats'] = [
            'favorites' => $this->getUserFavoriteHadiths($profileData['user_id'] ?? 0),
            'recently_viewed' => $this->getUserRecentlyViewedHadiths($profileData['user_id'] ?? 0)
        ];
        
        return $profileData;
    }

    /**
     * Get user's favorite hadiths
     *
     * @param int $userId User ID
     * @return array Favorite hadiths
     */
    private function getUserFavoriteHadiths(int $userId): array
    {
        // Implementation would depend on user preferences system
        return [];
    }

    /**
     * Get user's recently viewed hadiths
     *
     * @param int $userId User ID
     * @return array Recently viewed hadiths
     */
    private function getUserRecentlyViewedHadiths(int $userId): array
    {
        // Implementation would depend on user activity tracking
        return [];
    }

    /**
     * Get hadith collections
     *
     * @return array Collections
     */
    public function getCollections(): array
    {
        return $this->collections;
    }

    /**
     * Get hadith widgets
     *
     * @return array Widgets
     */
    public function getWidgets(): array
    {
        return $this->widgets;
    }

    /**
     * Search hadiths
     *
     * @param string $query Search query
     * @param array $filters Search filters
     * @return array Search results
     */
    public function searchHadiths(string $query, array $filters = []): array
    {
        try {
            return $this->hadithModel->search($query, $filters['language'] ?? 'en', $filters['limit'] ?? 50);
        } catch (\Exception $e) {
            error_log("Hadith search failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get hadith by reference
     *
     * @param string $collection Collection name
     * @param string $number Hadith number
     * @return array|null Hadith data or null if not found
     */
    public function getHadithByReference(string $collection, string $number): ?array
    {
        try {
            return $this->hadithModel->getByReference($collection, $number);
        } catch (\Exception $e) {
            error_log("Failed to get hadith by reference: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get daily hadith
     *
     * @param int $count Number of hadiths to return
     * @return array Daily hadiths
     */
    public function getDailyHadiths(int $count = 1): array
    {
        try {
            return $this->hadithModel->getDailyHadiths($count);
        } catch (\Exception $e) {
            error_log("Failed to get daily hadiths: " . $e->getMessage());
            return [];
        }
    }
}
