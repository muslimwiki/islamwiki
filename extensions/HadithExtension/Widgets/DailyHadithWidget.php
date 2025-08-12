<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Widgets;

use IslamWiki\Models\Hadith;

/**
 * Daily Hadith Widget
 *
 * Displays a random hadith for daily reading with options for customization.
 */
class DailyHadithWidget
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
            $hadiths = $this->hadithModel->getDailyHadiths(1);
            $hadith = $hadiths[0] ?? null;

            if (!$hadith) {
                return $this->renderNoHadithMessage();
            }

            return $this->renderHadithDisplay($hadith, $context);
        } catch (\Exception $e) {
            error_log("Daily hadith widget error: " . $e->getMessage());
            return $this->renderErrorMessage();
        }
    }

    /**
     * Render hadith display
     *
     * @param array $hadith Hadith data
     * @param array $context Widget context
     * @return string HTML for hadith display
     */
    private function renderHadithDisplay(array $hadith, array $context): string
    {
        $collection = $hadith['collection_name'] ?? 'Unknown';
        $number = $hadith['hadith_number'] ?? '';
        $grade = $hadith['grade'] ?? '';
        $text = $hadith['english_text'] ?? '';
        $arabicText = $hadith['arabic_text'] ?? '';

        $gradeClass = $this->getGradeClass($grade);
        $showArabic = $context['show_arabic'] ?? true;
        $showGrade = $context['show_grade'] ?? true;

        $html = "<div class='daily-hadith-widget {$gradeClass}'>";
        $html .= "<div class='widget-header'>";
        $html .= "<h3>Daily Hadith</h3>";
        $html .= "<div class='hadith-reference'>";
        $html .= "<span class='collection'>{$collection}</span>";
        $html .= "<span class='number'>{$number}</span>";
        if ($showGrade && $grade) {
            $html .= "<span class='grade'>{$grade}</span>";
        }
        $html .= "</div>";
        $html .= "</div>";

        $html .= "<div class='hadith-content'>";
        if ($showArabic && $arabicText) {
            $html .= "<div class='arabic-text'>{$arabicText}</div>";
        }
        $html .= "<div class='english-text'>{$text}</div>";
        $html .= "</div>";

        $html .= "<div class='widget-footer'>";
        $html .= "<a href='/hadith/{$collection}/{$number}' class='read-more'>Read Full Hadith</a>";
        $html .= "<button class='refresh-hadith' onclick='refreshDailyHadith()'>New Hadith</button>";
        $html .= "</div>";

        $html .= "</div>";

        return $html;
    }

    /**
     * Render no hadith message
     *
     * @return string HTML for no hadith message
     */
    private function renderNoHadithMessage(): string
    {
        return "<div class='daily-hadith-widget no-hadith'>
                    <div class='widget-header'>
                        <h3>Daily Hadith</h3>
                    </div>
                    <div class='hadith-content'>
                        <p>No hadith available for today. Please check back later.</p>
                    </div>
                </div>";
    }

    /**
     * Render error message
     *
     * @return string HTML for error message
     */
    private function renderErrorMessage(): string
    {
        return "<div class='daily-hadith-widget error'>
                    <div class='widget-header'>
                        <h3>Daily Hadith</h3>
                    </div>
                    <div class='hadith-content'>
                        <p>Unable to load daily hadith. Please try again later.</p>
                    </div>
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
}
