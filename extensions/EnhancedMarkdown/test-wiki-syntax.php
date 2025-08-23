<?php
/**
 * Test script for Enhanced Markdown Wiki Syntax
 * Tests all components from the wiki example
 */

require_once __DIR__ . '/autoload.php';

use IslamWiki\Extensions\EnhancedMarkdown\EnhancedMarkdown;

echo "=== Testing Enhanced Markdown Wiki Syntax ===\n\n";

// Initialize Enhanced Markdown
$enhancedMarkdown = new EnhancedMarkdown();

// Test content from the wiki example
$testContent = <<<'MARKDOWN'
{{About|the religion||Islam (disambiguation)}}
{{pp-semi-indef}}
{{pp-move}}
{{good article}}
{{Use dmy dates|date=March 2022}}
{{Use Oxford spelling|date=May 2022}}

{{Sidebar Islam}}

# Islam

'''Islam'''<ref>/ˈɪzlɑːm, ˈɪzlæm/ ''IZ-la(h)m''; Arabic: ٱلْإِسْلَام‎, <small>romanized:</small> ''al-Islām'', IPA: [alʔɪsˈlaːm], <abbr>lit.</abbr> 'submission [to the will of God]'</ref> is the final and complete way of life revealed by [[Allah]] (ﷻ) for all of humanity.

{{Cquote|Indeed, the religion in the sight of Allah is Islam.|{{qref|3|19|b=yl|c=y|y=si}}}

## Etymology
{{Further information|[[Muslims#Etymology]], [[S-L-M]]}}

The word '''Islam''' (Arabic: الإسلام) comes from the [[triliteral]] root '''س-ل-م''' (sīn-lām-mīm).

## Five Pillars
{{Main|Five Pillars of Islam}}

### Shahada
{{Main|Shahada}}
The ''[[shahadah]]'' ([[Arabic]]: الشهادة)<ref>[[Seyed Hossein Nasr|Nasr, Seyed Hossein]] (2003). pp. 3, 39, 85, 270–272. The Heart of Islam: Enduring Values for Humanity.</ref> is the fundamental declaration.

[[File:Mohammad_Al-Amin_Mosque_during_2019_Lebanese_revolution.jpg|alt=Interior of a large mosque with ornate chandeliers and carpets.|thumb|The mosque during the 2019 Lebanese revolution]]

## References
{{reflist|30em}}

{{Islam portal}}

[[Category:Islam]]
[[Category:Religions]]
[[Category:7th-century Islam]]
MARKDOWN;

echo "Processing test content...\n\n";

try {
    // Process the content
    $processedContent = $enhancedMarkdown->process($testContent);
    
    echo "✅ Content processed successfully!\n\n";
    
    // Display the processed HTML
    echo "=== Processed HTML Output ===\n";
    echo $processedContent;
    
    echo "\n\n=== Processing Statistics ===\n";
    echo "Content processed successfully with Enhanced Markdown!\n";
    
} catch (Exception $e) {
    echo "❌ Error processing content: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n"; 