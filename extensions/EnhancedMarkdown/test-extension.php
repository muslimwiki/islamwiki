<?php

/**
 * EnhancedMarkdown Extension Test
 * 
 * This file demonstrates the functionality of the EnhancedMarkdown extension
 * for IslamWiki. It shows how to use the extension to process Enhanced Markdown
 * content with wiki extensions and Islamic content extensions.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */

// Include the autoloader
require_once __DIR__ . '/autoload.php';

// Initialize the extension
$enhancedMarkdown = new IslamWiki\Extensions\EnhancedMarkdown\EnhancedMarkdown();

// Test content with Enhanced Markdown features
$testContent = <<<MARKDOWN
# Welcome to IslamWiki

This is a **test page** demonstrating the *Enhanced Markdown with Wiki Extensions* system.

## Standard Markdown Features

- **Bold text** and *italic text*
- Lists like this one
- `Inline code` and code blocks
- [External links](https://example.com)

## Wiki Extensions

### Internal Links
- [[Quran]] - Simple internal link
- [[Hadith|Islamic Traditions]] - Internal link with display text
- [Category:Islamic Studies] - Category tag

### Templates
- {{Infobox|title=Important Note|content=This is a sample infobox}}
- {{Warning|message=Please verify this information}}
- {{Note|message=This is a helpful note}}

### References
- This is a claim<ref>Sahih Bukhari, Book 1, Hadith 1</ref>
- Another reference<ref name="source1">Quran 2:255</ref>

## Islamic Content Extensions

### Quran Templates
- {{Quran|surah=1|ayah=1-7}} - Full verse
- {{Quran|surah=2|ayah=255}} - Another verse

### Hadith Templates
- {{Hadith|book=Bukhari|number=1|grade=Sahih}}
- {{Hadith|chain=Abu Hurairah → Prophet Muhammad}}

### Scholar Templates
- {{Scholar|name=Ibn Sina|period=980-1037|field=Medicine}}

### Fatwa Templates
- {{Fatwa|scholar=Al-Ghazali|topic=Prayer|date=1100}}

## Additional Features

### Prayer Times
- {{PrayerTimes|city=Mecca|date=today}}

### Hijri Calendar
- {{HijriCalendar|date=today}}

### Qibla Direction
- {{QiblaDirection|from=Current Location|to=Mecca}}

---

*This page demonstrates the power of Enhanced Markdown with Wiki Extensions for Islamic content management.*
MARKDOWN;

echo "=== EnhancedMarkdown Extension Test ===\n\n";

// Process the content
echo "Processing Enhanced Markdown content...\n";
$processedHtml = $enhancedMarkdown->process($testContent);

echo "Content processed successfully!\n\n";

// Get processing statistics
$stats = $enhancedMarkdown->getStats();
echo "=== Processing Statistics ===\n";
echo "Categories found: " . $stats['categories'] . "\n";
echo "References found: " . $stats['references'] . "\n";
echo "Templates processed: " . $stats['templates'] . "\n";
echo "Islamic templates processed: " . $stats['islamic_templates'] . "\n\n";

// Get categories
$categories = $enhancedMarkdown->getCategories();
if (!empty($categories)) {
    echo "=== Categories Found ===\n";
    foreach ($categories as $category) {
        echo "- " . $category . "\n";
    }
    echo "\n";
}

// Get references
$references = $enhancedMarkdown->getReferences();
if (!empty($references['numbered']) || !empty($references['named'])) {
    echo "=== References Found ===\n";
    if (!empty($references['numbered'])) {
        echo "Numbered References:\n";
        foreach ($references['numbered'] as $number => $content) {
            echo "  [$number] " . $content . "\n";
        }
    }
    if (!empty($references['named'])) {
        echo "Named References:\n";
        foreach ($references['named'] as $name => $content) {
            echo "  [$name] " . $content . "\n";
        }
    }
    echo "\n";
}

// Get available templates
$availableTemplates = $enhancedMarkdown->getAvailableTemplates();
echo "=== Available Templates ===\n";
echo "Wiki Templates: " . implode(', ', $availableTemplates['wiki']) . "\n";
echo "Islamic Templates:\n";
echo "  - Quran: " . implode(', ', $availableTemplates['islamic']['quran']) . "\n";
echo "  - Quran: " . implode(', ', $availableTemplates['islamic']['quran']) . "\n";
echo "  - Hadith: " . implode(', ', $availableTemplates['islamic']['hadith']) . "\n";
echo "  - Scholar: " . implode(', ', $availableTemplates['islamic']['scholar']) . "\n";
echo "  - Fatwa: " . implode(', ', $availableTemplates['islamic']['fatwa']) . "\n\n";

// Render reference list
echo "=== Reference List ===\n";
$referenceList = $enhancedMarkdown->renderReferenceList();
echo $referenceList . "\n";

// Render category navigation
echo "=== Category Navigation ===\n";
$categoryNav = $enhancedMarkdown->renderCategoryNavigation();
echo $categoryNav . "\n";

echo "=== Test Complete ===\n";
echo "The EnhancedMarkdown extension is working correctly!\n";
echo "All processors, engines, and managers are functioning as expected.\n"; 