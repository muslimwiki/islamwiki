<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Processors;

use IslamWiki\Extensions\EnhancedMarkdown\Engines\QuranTemplateEngine;
use IslamWiki\Extensions\EnhancedMarkdown\Engines\HadithTemplateEngine;
use IslamWiki\Extensions\EnhancedMarkdown\Engines\ScholarTemplateEngine;
use IslamWiki\Extensions\EnhancedMarkdown\Engines\FatwaTemplateEngine;

/**
 * Islamic Extension Processor
 * 
 * Handles Islamic-specific content extensions for IslamWiki including:
 * - Quran templates: {{Quran|surah=1|ayah=1-7}}
 * - Hadith templates: {{Hadith|book=Bukhari|number=1}}
 * - Scholar templates: {{Scholar|name=Ibn Sina}}
 * - Fatwa templates: {{Fatwa|scholar=Name}}
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class IslamicExtensionProcessor
{
    private QuranTemplateEngine $quranEngine;
    private HadithTemplateEngine $hadithEngine;
    private ScholarTemplateEngine $scholarEngine;
    private FatwaTemplateEngine $fatwaEngine;
    private bool $enabled = true;
    
    public function __construct()
    {
        $this->quranEngine = new QuranTemplateEngine();
        $this->hadithEngine = new HadithTemplateEngine();
        $this->scholarEngine = new ScholarTemplateEngine();
        $this->fatwaEngine = new FatwaTemplateEngine();
    }
    
    /**
     * Process Islamic content extensions in HTML content
     * 
     * @param string $html The HTML content to process
     * @return string Processed HTML content
     */
    public function process(string $html): string
    {
        if (!$this->enabled) {
            return $html;
        }
        
        // Process Quran templates
        $html = $this->processQuranTemplates($html);
        
        // Process Hadith templates
        $html = $this->processHadithTemplates($html);
        
        // Process Scholar templates
        $html = $this->processScholarTemplates($html);
        
        // Process Fatwa templates
        $html = $this->processFatwaTemplates($html);
        
        // Process other Islamic templates
        $html = $this->processOtherIslamicTemplates($html);
        
        return $html;
    }
    
    /**
     * Process Quran templates {{Quran|surah=1|ayah=1-7}}
     */
    private function processQuranTemplates(string $html): string
    {
        $html = preg_replace_callback(
            '/\{\{Quran\|([^}]+)\}\}/',
            function ($matches) {
                $params = $this->parseTemplateParams($matches[1]);
                return $this->quranEngine->render($params);
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Process Hadith templates {{Hadith|book=Bukhari|number=1}}
     */
    private function processHadithTemplates(string $html): string
    {
        $html = preg_replace_callback(
            '/\{\{Hadith\|([^}]+)\}\}/',
            function ($matches) {
                $params = $this->parseTemplateParams($matches[1]);
                return $this->hadithEngine->render($params);
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Process Scholar templates {{Scholar|name=Ibn Sina}}
     */
    private function processScholarTemplates(string $html): string
    {
        $html = preg_replace_callback(
            '/\{\{Scholar\|([^}]+)\}\}/',
            function ($matches) {
                $params = $this->parseTemplateParams($matches[1]);
                return $this->scholarEngine->render($params);
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Process Fatwa templates {{Fatwa|scholar=Name}}
     */
    private function processFatwaTemplates(string $html): string
    {
        $html = preg_replace_callback(
            '/\{\{Fatwa\|([^}]+)\}\}/',
            function ($matches) {
                $params = $this->parseTemplateParams($matches[1]);
                return $this->fatwaEngine->render($params);
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Process other Islamic templates (PrayerTimes, HijriCalendar, etc.)
     */
    private function processOtherIslamicTemplates(string $html): string
    {
        // Prayer Times
        $html = preg_replace_callback(
            '/\{\{PrayerTimes\|([^}]+)\}\}/',
            function ($matches) {
                $params = $this->parseTemplateParams($matches[1]);
                return $this->renderPrayerTimes($params);
            },
            $html
        );
        
        // Hijri Calendar
        $html = preg_replace_callback(
            '/\{\{HijriCalendar\|([^}]+)\}\}/',
            function ($matches) {
                $params = $this->parseTemplateParams($matches[1]);
                return $this->renderHijriCalendar($params);
            },
            $html
        );
        
        // Qibla Direction
        $html = preg_replace_callback(
            '/\{\{QiblaDirection\|([^}]+)\}\}/',
            function ($matches) {
                $params = $this->parseTemplateParams($matches[1]);
                return $this->renderQiblaDirection($params);
            },
            $html
        );
        
        return $html;
    }
    
    /**
     * Parse template parameters (param1=value1|param2=value2)
     */
    private function parseTemplateParams(string $paramString): array
    {
        $params = [];
        $pairs = explode('|', $paramString);
        
        foreach ($pairs as $pair) {
            if (strpos($pair, '=') !== false) {
                list($key, $value) = explode('=', $pair, 2);
                $params[trim($key)] = trim($value);
            } else {
                // Handle parameters without values
                $params[trim($pair)] = '';
            }
        }
        
        return $params;
    }
    
    /**
     * Render Prayer Times template
     */
    private function renderPrayerTimes(array $params): string
    {
        $city = $params['city'] ?? 'Mecca';
        $date = $params['date'] ?? 'today';
        
        return '<div class="prayer-times" data-city="' . htmlspecialchars($city) . '" data-date="' . htmlspecialchars($date) . '">
            <h4>Prayer Times - ' . htmlspecialchars($city) . '</h4>
            <div class="prayer-times-content">
                <p>Loading prayer times...</p>
            </div>
        </div>';
    }
    
    /**
     * Render Hijri Calendar template
     */
    private function renderHijriCalendar(array $params): string
    {
        $date = $params['date'] ?? 'today';
        
        return '<div class="hijri-calendar" data-date="' . htmlspecialchars($date) . '">
            <h4>Hijri Calendar</h4>
            <div class="hijri-calendar-content">
                <p>Loading Hijri date...</p>
            </div>
        </div>';
    }
    
    /**
     * Render Qibla Direction template
     */
    private function renderQiblaDirection(array $params): string
    {
        $from = $params['from'] ?? 'Current Location';
        $to = $params['to'] ?? 'Mecca';
        
        return '<div class="qibla-direction" data-from="' . htmlspecialchars($from) . '" data-to="' . htmlspecialchars($to) . '">
            <h4>Qibla Direction</h4>
            <div class="qibla-direction-content">
                <p>From: ' . htmlspecialchars($from) . '</p>
                <p>To: ' . htmlspecialchars($to) . '</p>
                <p>Loading direction...</p>
            </div>
        </div>';
    }
    
    /**
     * Enable or disable the processor
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
    
    /**
     * Check if processor is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    /**
     * Disable the processor
     */
    public function disable(): void
    {
        $this->enabled = false;
    }
    
    /**
     * Enable the processor
     */
    public function enable(): void
    {
        $this->enabled = true;
    }
    
    /**
     * Get the Quran template engine
     */
    public function getQuranEngine(): QuranTemplateEngine
    {
        return $this->quranEngine;
    }
    
    /**
     * Get the Hadith template engine
     */
    public function getHadithEngine(): HadithTemplateEngine
    {
        return $this->hadithEngine;
    }
    
    /**
     * Get the Scholar template engine
     */
    public function getScholarEngine(): ScholarTemplateEngine
    {
        return $this->scholarEngine;
    }
    
    /**
     * Get the Fatwa template engine
     */
    public function getFatwaEngine(): FatwaTemplateEngine
    {
        return $this->fatwaEngine;
    }
} 