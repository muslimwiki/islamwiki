<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Engines;

/**
 * Scholar Template Engine
 * 
 * Handles Scholar-specific template rendering for IslamWiki including:
 * - Scholar templates: {{Scholar|name=Ibn Sina}}
 * - Scholar information and biographical data
 * - Scholarly works and contributions
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class ScholarTemplateEngine
{
    private array $scholars = [];
    private array $scholarFields = [];
    
    public function __construct()
    {
        $this->initializeScholars();
        $this->initializeScholarFields();
    }
    
    /**
     * Render Scholar template
     * 
     * @param array $params Template parameters
     * @return string Rendered HTML content
     */
    public function render(array $params): string
    {
        $name = $params['name'] ?? null;
        $period = $params['period'] ?? null;
        $field = $params['field'] ?? null;
        $format = $params['format'] ?? 'full';
        
        if (!$name) {
            return $this->renderError('Scholar name is required');
        }
        
        return $this->renderScholar($name, $period, $field, $format);
    }
    
    /**
     * Render Scholar information
     */
    private function renderScholar(string $name, ?string $period, ?string $field, string $format): string
    {
        $scholarInfo = $this->getScholarInfo($name);
        
        $html = '<div class="scholar" data-name="' . htmlspecialchars($name) . '">';
        
        if ($format === 'full') {
            $html .= '<div class="scholar-header">';
            $html .= '<h4>Scholar: ' . htmlspecialchars($name) . '</h4>';
            if ($period) {
                $html .= '<div class="scholar-period">' . htmlspecialchars($period) . '</div>';
            }
            if ($field) {
                $html .= '<div class="scholar-field">' . htmlspecialchars($field) . '</div>';
            }
            $html .= '</div>';
            
            $html .= '<div class="scholar-content">';
            if ($scholarInfo) {
                $html .= '<div class="scholar-bio">' . htmlspecialchars($scholarInfo['biography']) . '</div>';
                if (!empty($scholarInfo['works'])) {
                    $html .= '<div class="scholar-works">';
                    $html .= '<h5>Major Works:</h5>';
                    $html .= '<ul>';
                    foreach ($scholarInfo['works'] as $work) {
                        $html .= '<li>' . htmlspecialchars($work) . '</li>';
                    }
                    $html .= '</ul>';
                    $html .= '</div>';
                }
            } else {
                $html .= '<div class="scholar-bio">Biographical information not available.</div>';
            }
            $html .= '</div>';
            
            $html .= '<div class="scholar-footer">';
            $html .= '<a href="/scholar/' . urlencode($name) . '" class="scholar-link">View Full Profile</a>';
            $html .= '</div>';
        } else {
            // Compact format
            $html .= '<span class="scholar-reference">' . htmlspecialchars($name);
            if ($period) {
                $html .= ' (' . htmlspecialchars($period) . ')';
            }
            $html .= '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get scholar information
     */
    private function getScholarInfo(string $name): ?array
    {
        return $this->scholars[$name] ?? null;
    }
    
    /**
     * Render error message
     */
    private function renderError(string $message): string
    {
        return '<div class="scholar-error">
            <span class="error-icon">⚠️</span>
            <span class="error-message">' . htmlspecialchars($message) . '</span>
        </div>';
    }
    
    /**
     * Initialize scholars data
     */
    private function initializeScholars(): void
    {
        $this->scholars = [
            'Ibn Sina' => [
                'biography' => 'Abu Ali al-Husayn ibn Abdullah ibn Sina, known as Ibn Sina or Avicenna, was a Persian polymath who is regarded as one of the most significant physicians, astronomers, thinkers and writers of the Islamic Golden Age.',
                'works' => ['The Canon of Medicine', 'The Book of Healing', 'The Book of Salvation']
            ],
            'Al-Ghazali' => [
                'biography' => 'Abu Hamid Muhammad ibn Muhammad al-Ghazali, known as Al-Ghazali, was a Persian theologian, jurist, philosopher, and mystic of Sunni Islam.',
                'works' => ['The Revival of Religious Sciences', 'The Incoherence of the Philosophers', 'The Alchemy of Happiness']
            ],
            'Ibn Rushd' => [
                'biography' => 'Abu al-Walid Muhammad ibn Ahmad ibn Rushd, known as Averroes, was an Andalusian polymath and Islamic philosopher.',
                'works' => ['The Incoherence of the Incoherence', 'Commentary on Aristotle', 'On the Harmony of Religions and Philosophy']
            ]
        ];
    }
    
    /**
     * Initialize scholar fields
     */
    private function initializeScholarFields(): void
    {
        $this->scholarFields = [
            'Medicine' => 'Medical sciences and healthcare',
            'Philosophy' => 'Philosophical thought and logic',
            'Theology' => 'Religious studies and theology',
            'Mathematics' => 'Mathematical sciences',
            'Astronomy' => 'Astronomical studies',
            'Law' => 'Islamic jurisprudence (Fiqh)',
            'Hadith' => 'Hadith studies and transmission',
            'Quran' => 'Quranic studies and interpretation'
        ];
    }
    
    /**
     * Get available scholars
     */
    public function getAvailableScholars(): array
    {
        return array_keys($this->scholars);
    }
    
    /**
     * Get available scholar fields
     */
    public function getAvailableFields(): array
    {
        return $this->scholarFields;
    }
    
    /**
     * Add a new scholar
     */
    public function addScholar(string $name, array $info): void
    {
        $this->scholars[$name] = $info;
    }
    
    /**
     * Remove a scholar
     */
    public function removeScholar(string $name): bool
    {
        if (isset($this->scholars[$name])) {
            unset($this->scholars[$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Search scholars by field
     */
    public function searchScholarsByField(string $field): array
    {
        // Placeholder - in real implementation, this would search the database
        return [
            'scholar' => 'Sample Scholar',
            'field' => $field,
            'biography' => 'Sample biography for ' . $field
        ];
    }
} 