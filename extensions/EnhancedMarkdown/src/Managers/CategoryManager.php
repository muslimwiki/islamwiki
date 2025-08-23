<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Managers;

/**
 * Category Manager
 * 
 * Handles category management and rendering for IslamWiki including:
 * - Category processing: [Category:Name]
 * - Category display and organization
 * - Category page generation
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class CategoryManager
{
    private array $categories = [];
    private array $categoryPages = [];
    
    /**
     * Render a category tag
     * 
     * @param string $categoryName The category name
     * @return string Rendered HTML for the category
     */
    public function renderCategory(string $categoryName): string
    {
        // Store category for later processing
        $this->categories[] = $categoryName;
        
        // Generate category link
        $categoryUrl = $this->generateCategoryUrl($categoryName);
        
        return '<span class="category-tag">
            <a href="' . $categoryUrl . '" class="category-link">
                <i class="fas fa-tag"></i> ' . htmlspecialchars($categoryName) . '
            </a>
        </span>';
    }
    
    /**
     * Generate category URL
     */
    private function generateCategoryUrl(string $categoryName): string
    {
        $encodedName = str_replace(' ', '_', $categoryName);
        $encodedName = urlencode($encodedName);
        
        return '/wiki/Category:' . $encodedName;
    }
    
    /**
     * Get all categories from current content
     */
    public function getCategories(): array
    {
        return array_unique($this->categories);
    }
    
    /**
     * Clear categories (for new content processing)
     */
    public function clearCategories(): void
    {
        $this->categories = [];
    }
    
    /**
     * Add a page to a category
     */
    public function addPageToCategory(string $categoryName, string $pageName, string $pageUrl): void
    {
        if (!isset($this->categoryPages[$categoryName])) {
            $this->categoryPages[$categoryName] = [];
        }
        
        $this->categoryPages[$categoryName][] = [
            'name' => $pageName,
            'url' => $pageUrl,
            'added' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get all pages in a category
     */
    public function getPagesInCategory(string $categoryName): array
    {
        return $this->categoryPages[$categoryName] ?? [];
    }
    
    /**
     * Get category statistics
     */
    public function getCategoryStats(): array
    {
        $stats = [];
        
        foreach ($this->categoryPages as $category => $pages) {
            $stats[$category] = [
                'count' => count($pages),
                'pages' => $pages
            ];
        }
        
        return $stats;
    }
    
    /**
     * Render category page content
     */
    public function renderCategoryPage(string $categoryName): string
    {
        $pages = $this->getPagesInCategory($categoryName);
        
        $html = '<div class="category-page">';
        $html .= '<h1>Category: ' . htmlspecialchars($categoryName) . '</h1>';
        
        if (empty($pages)) {
            $html .= '<p>No pages found in this category.</p>';
        } else {
            $html .= '<p>Found ' . count($pages) . ' page(s) in this category:</p>';
            $html .= '<ul class="category-pages">';
            
            foreach ($pages as $page) {
                $html .= '<li><a href="' . htmlspecialchars($page['url']) . '">' . 
                        htmlspecialchars($page['name']) . '</a></li>';
            }
            
            $html .= '</ul>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render category navigation
     */
    public function renderCategoryNavigation(): string
    {
        $categories = $this->getCategories();
        
        if (empty($categories)) {
            return '';
        }
        
        $html = '<div class="category-navigation">';
        $html .= '<h4>Categories:</h4>';
        $html .= '<div class="category-tags">';
        
        foreach ($categories as $category) {
            $categoryUrl = $this->generateCategoryUrl($category);
            $html .= '<a href="' . $categoryUrl . '" class="category-link">' . 
                    htmlspecialchars($category) . '</a>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Check if a category exists
     */
    public function categoryExists(string $categoryName): bool
    {
        return isset($this->categoryPages[$categoryName]);
    }
    
    /**
     * Get category count
     */
    public function getCategoryCount(): int
    {
        return count($this->categories);
    }
    
    /**
     * Get page count in all categories
     */
    public function getTotalPageCount(): int
    {
        $total = 0;
        foreach ($this->categoryPages as $pages) {
            $total += count($pages);
        }
        return $total;
    }
} 