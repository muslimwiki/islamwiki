<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Widgets;

use IslamWiki\Models\Hadith;

/**
 * Hadith Collections Widget
 *
 * Displays a list of available hadith collections with statistics and navigation.
 */
class HadithCollectionsWidget
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
        try {
            $collections = $this->hadithModel->getCollections();
            $showStats = $context['show_stats'] ?? true;
            $showDescription = $context['show_description'] ?? false;
            $compactMode = $context['compact_mode'] ?? false;

            $html = "<div class='hadith-collections-widget'>";
            $html .= "<div class='widget-header'>";
            $html .= "<h3>Hadith Collections</h3>";
            $html .= "</div>";

            $html .= "<div class='collections-list'>";
            foreach ($collections as $collection) {
                if ($compactMode) {
                    $html .= $this->renderCompactCollection($collection, $showStats);
                } else {
                    $html .= $this->renderDetailedCollection($collection, $showStats, $showDescription);
                }
            }
            $html .= "</div>";

            $html .= "<div class='widget-footer'>";
            $html .= "<a href='/hadith' class='view-all-collections'>View All Collections</a>";
            $html .= "</div>";

            $html .= "</div>";

            return $html;
        } catch (\Exception $e) {
            error_log("Hadith collections widget error: " . $e->getMessage());
            return $this->renderErrorMessage();
        }
    }

    /**
     * Render compact collection display
     *
     * @param array $collection Collection data
     * @param bool $showStats Whether to show collection statistics
     * @return string HTML for compact collection
     */
    private function renderCompactCollection(array $collection, bool $showStats): string
    {
        $id = $collection['id'] ?? 0;
        $name = $collection['name'] ?? 'Unknown';
        $arabicName = $collection['arabic_name'] ?? '';
        $hadithCount = $collection['hadith_count'] ?? 0;

        $html = "<div class='collection-item compact'>";
        $html .= "<div class='collection-header'>";
        $html .= "<a href='/hadith/collection/{$id}' class='collection-name'>{$name}</a>";
        if ($showStats) {
            $html .= "<span class='hadith-count'>{$hadithCount} hadiths</span>";
        }
        $html .= "</div>";

        if ($arabicName) {
            $html .= "<div class='arabic-name'>{$arabicName}</div>";
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * Render detailed collection display
     *
     * @param array $collection Collection data
     * @param bool $showStats Whether to show collection statistics
     * @param bool $showDescription Whether to show collection description
     * @return string HTML for detailed collection
     */
    private function renderDetailedCollection(array $collection, bool $showStats, bool $showDescription): string
    {
        $id = $collection['id'] ?? 0;
        $name = $collection['name'] ?? 'Unknown';
        $arabicName = $collection['arabic_name'] ?? '';
        $hadithCount = $collection['hadith_count'] ?? 0;
        $description = $collection['description'] ?? '';
        $compiler = $collection['compiler'] ?? '';
        $period = $collection['period'] ?? '';

        $html = "<div class='collection-item detailed'>";
        $html .= "<div class='collection-header'>";
        $html .= "<h4 class='collection-name'>";
        $html .= "<a href='/hadith/collection/{$id}'>{$name}</a>";
        $html .= "</h4>";

        if ($showStats) {
            $html .= "<div class='collection-stats'>";
            $html .= "<span class='hadith-count'>{$hadithCount} hadiths</span>";
            $html .= "</div>";
        }

        $html .= "</div>";

        if ($arabicName) {
            $html .= "<div class='arabic-name'>{$arabicName}</div>";
        }

        if ($compiler) {
            $html .= "<div class='collection-compiler'>Compiler: {$compiler}</div>";
        }

        if ($period) {
            $html .= "<div class='collection-period'>Period: {$period}</div>";
        }

        if ($showDescription && $description) {
            $html .= "<div class='collection-description'>{$description}</div>";
        }

        $html .= "<div class='collection-actions'>";
        $html .= "<a href='/hadith/collection/{$id}' class='browse-collection'>Browse Collection</a>";
        $html .= "<a href='/hadith/search?collection={$id}' class='search-collection'>Search in Collection</a>";
        $html .= "</div>";

        $html .= "</div>";

        return $html;
    }

    /**
     * Render error message
     *
     * @return string HTML for error message
     */
    private function renderErrorMessage(): string
    {
        return "<div class='hadith-collections-widget error'>
                    <div class='widget-header'>
                        <h3>Hadith Collections</h3>
                    </div>
                    <div class='collections-content'>
                        <p>Unable to load hadith collections. Please try again later.</p>
                    </div>
                </div>";
    }
}
