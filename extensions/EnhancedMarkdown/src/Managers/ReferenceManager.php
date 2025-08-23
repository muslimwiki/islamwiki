<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Managers;

/**
 * Reference Manager
 * 
 * Handles reference management and rendering for IslamWiki including:
 * - Reference processing: <ref>content</ref>
 * - Named references: <ref name="name">content</ref>
 * - Reference list generation
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class ReferenceManager
{
    private array $references = [];
    private array $namedReferences = [];
    private int $referenceCounter = 1;
    
    /**
     * Render a simple reference
     * 
     * @param string $content The reference content
     * @return string Rendered HTML for the reference
     */
    public function renderReference(string $content): string
    {
        $refNumber = $this->referenceCounter++;
        
        // Store reference for later processing
        $this->references[$refNumber] = $content;
        
        return '<sup class="reference">
            <a href="#ref-' . $refNumber . '" id="ref-link-' . $refNumber . '">[' . $refNumber . ']</a>
        </sup>';
    }
    
    /**
     * Render a named reference
     * 
     * @param string $name The reference name
     * @param string $content The reference content
     * @return string Rendered HTML for the reference
     */
    public function renderNamedReference(string $name, string $content): string
    {
        // Store named reference
        $this->namedReferences[$name] = $content;
        
        return '<sup class="reference">
            <a href="#ref-' . $name . '" id="ref-link-' . $name . '">[' . $name . ']</a>
        </sup>';
    }
    
    /**
     * Get all references
     */
    public function getReferences(): array
    {
        return $this->references;
    }
    
    /**
     * Get all named references
     */
    public function getNamedReferences(): array
    {
        return $this->namedReferences;
    }
    
    /**
     * Clear references (for new content processing)
     */
    public function clearReferences(): void
    {
        $this->references = [];
        $this->namedReferences = [];
        $this->referenceCounter = 1;
    }
    
    /**
     * Render the reference list
     */
    public function renderReferenceList(): string
    {
        if (empty($this->references) && empty($this->namedReferences)) {
            return '';
        }
        
        $html = '<div class="references-section">';
        $html .= '<h3>References</h3>';
        $html .= '<ol class="reference-list">';
        
        // Render numbered references
        foreach ($this->references as $number => $content) {
            $html .= '<li id="ref-' . $number . '">';
            $html .= '<a href="#ref-link-' . $number . '">↑</a> ';
            $html .= htmlspecialchars($content);
            $html .= '</li>';
        }
        
        // Render named references
        foreach ($this->namedReferences as $name => $content) {
            $html .= '<li id="ref-' . $name . '">';
            $html .= '<a href="#ref-link-' . $name . '">↑</a> ';
            $html .= '<strong>' . htmlspecialchars($name) . ':</strong> ';
            $html .= htmlspecialchars($content);
            $html .= '</li>';
        }
        
        $html .= '</ol>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get reference count
     */
    public function getReferenceCount(): int
    {
        return count($this->references) + count($this->namedReferences);
    }
    
    /**
     * Check if references exist
     */
    public function hasReferences(): bool
    {
        return !empty($this->references) || !empty($this->namedReferences);
    }
    
    /**
     * Get reference by number
     */
    public function getReference(int $number): ?string
    {
        return $this->references[$number] ?? null;
    }
    
    /**
     * Get named reference by name
     */
    public function getNamedReference(string $name): ?string
    {
        return $this->namedReferences[$name] ?? null;
    }
    
    /**
     * Add a reference manually
     */
    public function addReference(string $content): int
    {
        $refNumber = $this->referenceCounter++;
        $this->references[$refNumber] = $content;
        return $refNumber;
    }
    
    /**
     * Add a named reference manually
     */
    public function addNamedReference(string $name, string $content): void
    {
        $this->namedReferences[$name] = $content;
    }
    
    /**
     * Remove a reference
     */
    public function removeReference(int $number): bool
    {
        if (isset($this->references[$number])) {
            unset($this->references[$number]);
            return true;
        }
        return false;
    }
    
    /**
     * Remove a named reference
     */
    public function removeNamedReference(string $name): bool
    {
        if (isset($this->namedReferences[$name])) {
            unset($this->namedReferences[$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Get reference statistics
     */
    public function getReferenceStats(): array
    {
        return [
            'total' => $this->getReferenceCount(),
            'numbered' => count($this->references),
            'named' => count($this->namedReferences),
            'references' => $this->references,
            'named_references' => $this->namedReferences
        ];
    }

    /**
     * Process references in HTML content
     * 
     * @param string $html The HTML content to process
     * @return string Processed HTML content
     */
    public function process(string $html): string
    {
        // Process simple references <ref>content</ref>
        $html = $this->processSimpleReferences($html);
        
        // Process named references <ref name="name">content</ref>
        $html = $this->processNamedReferences($html);
        
        // Process Quran references {{qref|surah|ayah|b=yl|c=y|y=si}}
        $html = $this->processQuranReferences($html);
        
        // Process reference templates {{Reference|source=Source}}
        $html = $this->processReferenceTemplates($html);
        
        return $html;
    }
    
    /**
     * Process simple references <ref>content</ref>
     */
    private function processSimpleReferences(string $html): string
    {
        return preg_replace_callback(
            '/<ref>([^<]+)<\/ref>/',
            function ($matches) {
                $content = trim($matches[1]);
                $refId = $this->addReference($content);
                return '<sup class="reference"><a href="#ref' . $refId . '">[' . $refId . ']</a></sup>';
            },
            $html
        );
    }
    
    /**
     * Process named references <ref name="name">content</ref>
     */
    private function processNamedReferences(string $html): string
    {
        return preg_replace_callback(
            '/<ref name="([^"]+)">([^<]+)<\/ref>/',
            function ($matches) {
                $name = trim($matches[1]);
                $content = trim($matches[2]);
                $refId = $this->addNamedReference($name, $content);
                return '<sup class="reference"><a href="#ref' . $refId . '">[' . $refId . ']</a></sup>';
            },
            $html
        );
    }
    
    /**
     * Process Quran references {{qref|surah|ayah|b=yl|c=y|y=si}}
     */
    private function processQuranReferences(string $html): string
    {
        return preg_replace_callback(
            '/\{\{qref\|([^|]+)\|([^|]+)(?:\|([^}]+))?\}\}/',
            function ($matches) {
                $surah = trim($matches[1]);
                $ayah = trim($matches[2]);
                $params = isset($matches[3]) ? $this->parseQuranRefParams($matches[3]) : [];
                
                $refId = $this->addQuranReference($surah, $ayah, $params);
                return '<sup class="reference quran-ref"><a href="#ref' . $refId . '">[' . $refId . ']</a></sup>';
            },
            $html
        );
    }
    
    /**
     * Process reference templates {{Reference|source=Source}}
     */
    private function processReferenceTemplates(string $html): string
    {
        return preg_replace_callback(
            '/\{\{Reference\|([^}]+)\}\}/',
            function ($matches) {
                $params = $this->parseReferenceParams($matches[1]);
                $source = $params['source'] ?? 'Source';
                
                $refId = $this->addReference($source);
                return '<sup class="reference"><a href="#ref' . $refId . '">[' . $refId . ']</a></sup>';
            },
            $html
        );
    }
    
    /**
     * Parse Quran reference parameters (b=yl|c=y|y=si)
     */
    private function parseQuranRefParams(string $paramString): array
    {
        $params = [];
        $pairs = explode('|', $paramString);
        
        foreach ($pairs as $pair) {
            if (strpos($pair, '=') !== false) {
                list($key, $value) = explode('=', $pair, 2);
                $params[trim($key)] = trim($value);
            }
        }
        
        return $params;
    }
    
    /**
     * Parse reference template parameters
     */
    private function parseReferenceParams(string $paramString): array
    {
        $params = [];
        $pairs = explode('|', $paramString);
        
        foreach ($pairs as $pair) {
            if (strpos($pair, '=') !== false) {
                list($key, $value) = explode('=', $pair, 2);
                $params[trim($key)] = trim($value);
            }
        }
        
        return $params;
    }
    
    /**
     * Add a Quran reference
     */
    private function addQuranReference(string $surah, string $ayah, array $params): int
    {
        $content = "Quran " . $surah . ":" . $ayah;
        if (!empty($params)) {
            $content .= " (" . implode(', ', array_map(fn($k, $v) => "$k=$v", array_keys($params), $params)) . ")";
        }
        
        return $this->addReference($content);
    }
} 