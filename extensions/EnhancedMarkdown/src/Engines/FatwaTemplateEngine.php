<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Engines;

/**
 * Fatwa Template Engine
 * 
 * Handles Fatwa-specific template rendering for IslamWiki including:
 * - Fatwa templates: {{Fatwa|scholar=Name}}
 * - Fatwa information and legal opinions
 * - Islamic legal rulings and guidance
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class FatwaTemplateEngine
{
    private array $fatwaTypes = [];
    private array $fatwaTopics = [];
    
    public function __construct()
    {
        $this->initializeFatwaTypes();
        $this->initializeFatwaTopics();
    }
    
    /**
     * Render Fatwa template
     * 
     * @param array $params Template parameters
     * @return string Rendered HTML content
     */
    public function render(array $params): string
    {
        $scholar = $params['scholar'] ?? null;
        $topic = $params['topic'] ?? null;
        $date = $params['date'] ?? null;
        $type = $params['type'] ?? null;
        $format = $params['format'] ?? 'full';
        
        if (!$scholar) {
            return $this->renderError('Scholar name is required');
        }
        
        return $this->renderFatwa($scholar, $topic, $date, $type, $format);
    }
    
    /**
     * Render Fatwa information
     */
    private function renderFatwa(string $scholar, ?string $topic, ?string $date, ?string $type, string $format): string
    {
        $html = '<div class="fatwa" data-scholar="' . htmlspecialchars($scholar) . '">';
        
        if ($format === 'full') {
            $html .= '<div class="fatwa-header">';
            $html .= '<h4>Fatwa by ' . htmlspecialchars($scholar) . '</h4>';
            if ($topic) {
                $html .= '<div class="fatwa-topic">Topic: ' . htmlspecialchars($topic) . '</div>';
            }
            if ($date) {
                $html .= '<div class="fatwa-date">Date: ' . htmlspecialchars($date) . '</div>';
            }
            if ($type) {
                $html .= '<div class="fatwa-type">Type: ' . htmlspecialchars($type) . '</div>';
            }
            $html .= '</div>';
            
            $html .= '<div class="fatwa-content">';
            $html .= '<div class="fatwa-summary">';
            $html .= '<p>This is a fatwa (Islamic legal opinion) issued by ' . htmlspecialchars($scholar);
            if ($topic) {
                $html .= ' regarding ' . htmlspecialchars($topic);
            }
            $html .= '.</p>';
            $html .= '</div>';
            
            if ($type) {
                $typeInfo = $this->getTypeInfo($type);
                if ($typeInfo) {
                    $html .= '<div class="fatwa-type-info">';
                    $html .= '<h5>Fatwa Type: ' . htmlspecialchars($type) . '</h5>';
                    $html .= '<p>' . htmlspecialchars($typeInfo['description']) . '</p>';
                    $html .= '</div>';
                }
            }
            
            $html .= '<div class="fatwa-guidance">';
            $html .= '<h5>Guidance:</h5>';
            $html .= '<p>This fatwa provides guidance on Islamic matters. Please consult with qualified scholars for specific situations.</p>';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '<div class="fatwa-footer">';
            $html .= '<a href="/fatwa/' . urlencode($scholar) . '" class="fatwa-link">View Full Fatwa</a>';
            $html .= '</div>';
        } else {
            // Compact format
            $html .= '<span class="fatwa-reference">' . htmlspecialchars($scholar);
            if ($topic) {
                $html .= ' on ' . htmlspecialchars($topic);
            }
            $html .= '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get type information
     */
    private function getTypeInfo(string $type): ?array
    {
        return $this->fatwaTypes[$type] ?? null;
    }
    
    /**
     * Render error message
     */
    private function renderError(string $message): string
    {
        return '<div class="fatwa-error">
            <span class="error-icon">⚠️</span>
            <span class="error-message">' . htmlspecialchars($message) . '</span>
        </div>';
    }
    
    /**
     * Initialize fatwa types
     */
    private function initializeFatwaTypes(): void
    {
        $this->fatwaTypes = [
            'Halal' => ['description' => 'Permissible according to Islamic law'],
            'Haram' => ['description' => 'Forbidden according to Islamic law'],
            'Makruh' => ['description' => 'Disliked but not forbidden'],
            'Mustahabb' => ['description' => 'Recommended but not obligatory'],
            'Mubah' => ['description' => 'Neutral, neither recommended nor discouraged'],
            'Wajib' => ['description' => 'Obligatory according to Islamic law']
        ];
    }
    
    /**
     * Initialize fatwa topics
     */
    private function initializeFatwaTopics(): void
    {
        $this->fatwaTopics = [
            'Prayer' => 'Salah and worship',
            'Fasting' => 'Sawm and dietary restrictions',
            'Charity' => 'Zakat and Sadaqah',
            'Marriage' => 'Nikah and family matters',
            'Business' => 'Trade and commerce',
            'Food' => 'Halal food and beverages',
            'Clothing' => 'Modest dress and appearance',
            'Travel' => 'Travel and transportation',
            'Technology' => 'Modern technology and devices',
            'Finance' => 'Islamic banking and finance'
        ];
    }
    
    /**
     * Get available fatwa types
     */
    public function getAvailableTypes(): array
    {
        return $this->fatwaTypes;
    }
    
    /**
     * Get available fatwa topics
     */
    public function getAvailableTopics(): array
    {
        return $this->fatwaTopics;
    }
    
    /**
     * Add a new fatwa type
     */
    public function addFatwaType(string $type, array $info): void
    {
        $this->fatwaTypes[$type] = $info;
    }
    
    /**
     * Remove a fatwa type
     */
    public function removeFatwaType(string $type): bool
    {
        if (isset($this->fatwaTypes[$type])) {
            unset($this->fatwaTypes[$type]);
            return true;
        }
        return false;
    }
    
    /**
     * Add a new fatwa topic
     */
    public function addFatwaTopic(string $topic, string $description): void
    {
        $this->fatwaTopics[$topic] = $description;
    }
    
    /**
     * Remove a fatwa topic
     */
    public function removeFatwaTopic(string $topic): bool
    {
        if (isset($this->fatwaTopics[$topic])) {
            unset($this->fatwaTopics[$topic]);
            return true;
        }
        return false;
    }
    
    /**
     * Search fatwas by topic
     */
    public function searchFatwasByTopic(string $topic): array
    {
        // Placeholder - in real implementation, this would search the database
        return [
            'scholar' => 'Sample Scholar',
            'topic' => $topic,
            'summary' => 'Sample fatwa summary for ' . $topic
        ];
    }
    
    /**
     * Get fatwa statistics
     */
    public function getFatwaStats(): array
    {
        return [
            'types' => count($this->fatwaTypes),
            'topics' => count($this->fatwaTopics),
            'available_types' => array_keys($this->fatwaTypes),
            'available_topics' => array_keys($this->fatwaTopics)
        ];
    }
} 