<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Widgets;

use IslamWiki\Models\Hadith;

/**
 * Hadith Search Widget
 *
 * Provides a quick search interface for hadiths with filters and results display.
 */
class HadithSearchWidget
{
    /**
     * @var Hadith Hadith model instance
     */
    private Hadith $hadithModel;

    /**
     * Constructor
     *
     * @param Hadith $hadithModel Hadith model instance
     */
    public function __construct(Hadith $hadithModel)
    {
        $this->hadithModel = $hadithModel;
    }

    /**
     * Render the widget
     *
     * @param array $context Widget context
     * @return string Widget HTML
     */
    public function render(array $context = []): string
    {
        $searchQuery = $context['search_query'] ?? '';
        $searchResults = $context['search_results'] ?? [];
        $showFilters = $context['show_filters'] ?? true;
        $compactMode = $context['compact_mode'] ?? false;

        $html = "<div class='hadith-search-widget'>";
        $html .= "<div class='widget-header'>";
        $html .= "<h3>Hadith Search</h3>";
        $html .= "</div>";

        $html .= "<div class='search-form'>";
        $html .= $this->renderSearchForm($searchQuery, $showFilters);
        $html .= "</div>";

        if ($searchResults) {
            $html .= "<div class='search-results'>";
            $html .= $this->renderSearchResults($searchResults, $compactMode);
            $html .= "</div>";
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * Render search form
     *
     * @param string $searchQuery Current search query
     * @param bool $showFilters Whether to show advanced filters
     * @return string HTML for search form
     */
    private function renderSearchForm(string $searchQuery, bool $showFilters): string
    {
        $html = "<form class='hadith-search-form' onsubmit='return performHadithSearch(event)'>";
        $html .= "<div class='search-input-group'>";
        $html .= "<input type='text' name='query' value='{$searchQuery}' placeholder='Search hadiths...' class='search-input' required>";
        $html .= "<button type='submit' class='search-button'>Search</button>";
        $html .= "</div>";

        if ($showFilters) {
            $html .= $this->renderAdvancedFilters();
        }

        $html .= "</form>";

        return $html;
    }

    /**
     * Render advanced filters
     *
     * @return string HTML for advanced filters
     */
    private function renderAdvancedFilters(): string
    {
        $html = "<div class='advanced-filters'>";
        $html .= "<div class='filter-row'>";
        
        // Collection filter
        $html .= "<div class='filter-group'>";
        $html .= "<label for='collection-filter'>Collection:</label>";
        $html .= "<select name='collection' id='collection-filter' class='filter-select'>";
        $html .= "<option value=''>All Collections</option>";
        
        try {
            $collections = $this->hadithModel->getCollections();
            foreach ($collections as $collection) {
                $html .= "<option value='{$collection['id']}'>{$collection['name']}</option>";
            }
        } catch (\Exception $e) {
            // Log error but don't break widget
            error_log("Failed to load collections for search widget: " . $e->getMessage());
        }
        
        $html .= "</select>";
        $html .= "</div>";

        // Grade filter
        $html .= "<div class='filter-group'>";
        $html .= "<label for='grade-filter'>Grade:</label>";
        $html .= "<select name='grade' id='grade-filter' class='filter-select'>";
        $html .= "<option value=''>All Grades</option>";
        $html .= "<option value='sahih'>Sahih</option>";
        $html .= "<option value='hasan'>Hasan</option>";
        $html .= "<option value='daif'>Da'if</option>";
        $html .= "<option value='mawdu'>Mawdu'</option>";
        $html .= "</select>";
        $html .= "</div>";

        // Language filter
        $html .= "<div class='filter-group'>";
        $html .= "<label for='language-filter'>Language:</label>";
        $html .= "<select name='language' id='language-filter' class='filter-select'>";
        $html .= "<option value='en'>English</option>";
        $html .= "<option value='ar'>Arabic</option>";
        $html .= "<option value='ur'>Urdu</option>";
        $html .= "<option value='tr'>Turkish</option>";
        $html .= "<option value='id'>Indonesian</option>";
        $html .= "</select>";
        $html .= "</div>";

        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * Render search results
     *
     * @param array $results Search results
     * @param bool $compactMode Whether to show results in compact mode
     * @return string HTML for search results
     */
    private function renderSearchResults(array $results, bool $compactMode): string
    {
        $html = "<div class='results-header'>";
        $html .= "<h4>Search Results (" . count($results) . ")</h4>";
        $html .= "</div>";

        $html .= "<div class='results-list'>";
        
        foreach ($results as $hadith) {
            if ($compactMode) {
                $html .= $this->renderCompactResult($hadith);
            } else {
                $html .= $this->renderDetailedResult($hadith);
            }
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * Render compact search result
     *
     * @param array $hadith Hadith data
     * @return string HTML for compact result
     */
    private function renderCompactResult(array $hadith): string
    {
        $collection = $hadith['collection_name'] ?? 'Unknown';
        $number = $hadith['hadith_number'] ?? '';
        $grade = $hadith['grade'] ?? '';
        $text = $hadith['english_text'] ?? '';
        
        // Truncate text for compact display
        $truncatedText = strlen($text) > 100 ? substr($text, 0, 100) . '...' : $text;
        
        $gradeClass = $this->getGradeClass($grade);

        $html = "<div class='result-item compact {$gradeClass}'>";
        $html .= "<div class='result-header'>";
        $html .= "<span class='reference'>{$collection} {$number}</span>";
        if ($grade) {
            $html .= "<span class='grade'>{$grade}</span>";
        }
        $html .= "</div>";
        $html .= "<div class='result-text'>{$truncatedText}</div>";
        $html .= "<div class='result-actions'>";
        $html .= "<a href='/hadith/{$collection}/{$number}' class='view-hadith'>View Full</a>";
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }

    /**
     * Render detailed search result
     *
     * @param array $hadith Hadith data
     * @return string HTML for detailed result
     */
    private function renderDetailedResult(array $hadith): string
    {
        $collection = $hadith['collection_name'] ?? 'Unknown';
        $number = $hadith['hadith_number'] ?? '';
        $grade = $hadith['grade'] ?? '';
        $text = $hadith['english_text'] ?? '';
        $arabicText = $hadith['arabic_text'] ?? '';

        $gradeClass = $this->getGradeClass($grade);

        $html = "<div class='result-item detailed {$gradeClass}'>";
        $html .= "<div class='result-header'>";
        $html .= "<h5 class='reference'>{$collection} {$number}</h5>";
        if ($grade) {
            $html .= "<span class='grade'>{$grade}</span>";
        }
        $html .= "</div>";

        if ($arabicText) {
            $html .= "<div class='arabic-text'>{$arabicText}</div>";
        }

        $html .= "<div class='result-text'>{$text}</div>";

        $html .= "<div class='result-actions'>";
        $html .= "<a href='/hadith/{$collection}/{$number}' class='view-hadith'>View Full Hadith</a>";
        $html .= "<button class='bookmark-hadith' onclick='bookmarkHadith({$hadith['id']})'>Bookmark</button>";
        $html .= "</div>";

        $html .= "</div>";

        return $html;
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
}
