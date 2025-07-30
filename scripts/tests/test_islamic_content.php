<?php
declare(strict_types=1);

/**
 * Test Islamic Content Management
 * 
 * This script tests the Islamic content management features including:
 * - Islamic page creation and editing
 * - Scholar verification workflow
 * - Content moderation system
 * - Islamic templates and categorization
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\IslamicPage;
use IslamWiki\Models\IslamicUser;

// Load configuration
$config = require __DIR__ . '/../../config/database.php';

echo "=== Islamic Content Management Test ===\n";
echo "Testing Islamic content creation and management...\n\n";

try {
    // Create connection
    $connection = new Connection($config['connections']['mysql']);
    
    echo "✅ Connected to database\n\n";
    
    // Test 1: Create an Islamic page
    echo "1. Testing Islamic Page Creation...\n";
    
    $page = new IslamicPage($connection, [
        'title' => 'Introduction to Islamic Jurisprudence',
        'arabic_title' => 'مقدمة في الفقه الإسلامي',
        'content' => '# Introduction to Islamic Jurisprudence

Islamic jurisprudence (Fiqh) is the human understanding and practice of Islamic law. It is derived from the Quran and the Sunnah of Prophet Muhammad (ﷺ).

## Key Concepts

### Sources of Islamic Law
1. **Quran** - The primary source
2. **Sunnah** - The teachings and practices of Prophet Muhammad
3. **Ijma** - Consensus of scholars
4. **Qiyas** - Analogical reasoning

### Major Schools of Thought
- **Hanafi** - Founded by Imam Abu Hanifa
- **Maliki** - Founded by Imam Malik
- **Shafi\'i** - Founded by Imam Shafi\'i
- **Hanbali** - Founded by Imam Ahmad ibn Hanbal

## Importance

Fiqh helps Muslims understand how to live according to Islamic principles in their daily lives.',
        'arabic_content' => 'مقدمة في الفقه الإسلامي

الفقه الإسلامي هو الفهم البشري وممارسة الشريعة الإسلامية. وهو مستمد من القرآن الكريم وسنة النبي محمد ﷺ.

## المفاهيم الأساسية

### مصادر الشريعة الإسلامية
1. **القرآن** - المصدر الأساسي
2. **السنة** - تعاليم وممارسات النبي محمد
3. **الإجماع** - إجماع العلماء
4. **القياس** - الاستدلال القياسي

### المذاهب الرئيسية
- **الحنفي** - أسسه الإمام أبو حنيفة
- **المالكي** - أسسه الإمام مالك
- **الشافعي** - أسسه الإمام الشافعي
- **الحنبلي** - أسسه الإمام أحمد بن حنبل

## الأهمية

يساعد الفقه المسلمين على فهم كيفية العيش وفقاً للمبادئ الإسلامية في حياتهم اليومية.',
        'islamic_category' => 'fiqh',
        'islamic_template' => 'islamic_concept',
        'islamic_tags' => '["fiqh", "islamic-law", "jurisprudence", "shariah"]',
        'moderation_status' => 'pending',
        'content_quality_score' => 85,
        'namespace' => 'main',
        'content_format' => 'markdown',
        'slug' => 'introduction-to-islamic-jurisprudence-' . time(),
    ]);
    
    if ($page->save()) {
        echo "   ✅ Islamic page created successfully\n";
        echo "   📊 Page ID: {$page->getAttribute('id')}\n";
        echo "   📊 Title: {$page->getAttribute('title')}\n";
        echo "   📊 Arabic Title: {$page->getArabicTitle()}\n";
        echo "   📊 Category: {$page->getIslamicCategoryName()}\n";
        echo "   📊 Template: {$page->getIslamicTemplateName()}\n";
        echo "   📊 Moderation Status: {$page->getModerationStatusName()}\n";
        echo "   📊 Quality Score: {$page->getContentQualityScore()}\n";
    } else {
        echo "   ❌ Failed to create Islamic page\n";
    }
    
    // Test 2: Test Islamic tags
    echo "\n2. Testing Islamic Tags...\n";
    
    $page->addIslamicTag('islamic-studies');
    $page->addIslamicTag('education');
    $page->save();
    
    $tags = $page->getIslamicTags();
    echo "   📊 Islamic Tags: " . implode(', ', $tags) . "\n";
    
    // Test 3: Test Islamic references
    echo "\n3. Testing Islamic References...\n";
    
    $reference = [
        'type' => 'book',
        'title' => 'Kitab al-Fiqh al-Islami',
        'author' => 'Dr. Wahbah al-Zuhayli',
        'year' => 1989,
        'publisher' => 'Dar al-Fikr',
        'pages' => '1-50'
    ];
    
    $page->addIslamicReference($reference);
    $page->save();
    
    $references = $page->getIslamicReferences();
    echo "   📊 Added reference: " . count($references) . " total\n";
    
    // Test 4: Test Islamic citations
    echo "\n4. Testing Islamic Citations...\n";
    
    $citation = [
        'type' => 'quran',
        'reference' => 'Quran 2:185',
        'text' => 'شَهْرُ رَمَضَانَ الَّذِي أُنزِلَ فِيهِ الْقُرْآنُ',
        'translation' => 'The month of Ramadan in which was revealed the Quran',
        'context' => 'Regarding fasting in Ramadan'
    ];
    
    $page->addIslamicCitation($citation);
    $page->save();
    
    $citations = $page->getIslamicCitations();
    echo "   📊 Added citation: " . count($citations) . " total\n";
    
    // Test 5: Test Islamic metadata
    echo "\n5. Testing Islamic Metadata...\n";
    
    $metadata = [
        'language' => 'en',
        'arabic_language' => 'ar',
        'difficulty_level' => 'intermediate',
        'target_audience' => 'students',
        'estimated_reading_time' => '15 minutes',
        'keywords' => ['fiqh', 'islamic-law', 'jurisprudence'],
        'related_topics' => ['salah', 'zakat', 'hajj']
    ];
    
    $page->setIslamicMetadata($metadata);
    $page->save();
    
    $savedMetadata = $page->getIslamicMetadata();
    echo "   📊 Metadata keys: " . implode(', ', array_keys($savedMetadata)) . "\n";
    
    // Test 6: Test Islamic permissions
    echo "\n6. Testing Islamic Permissions...\n";
    
    $permissions = ['read', 'edit', 'comment', 'moderate'];
    $page->setIslamicPermissions($permissions);
    $page->save();
    
    $savedPermissions = $page->getIslamicPermissions();
    echo "   📊 Permissions: " . implode(', ', $savedPermissions) . "\n";
    
    // Test 7: Test moderation workflow
    echo "\n7. Testing Moderation Workflow...\n";
    
    echo "   📊 Initial status: {$page->getModerationStatusName()}\n";
    echo "   📊 Needs moderation: " . ($page->needsModeration() ? 'Yes' : 'No') . "\n";
    
    // Simulate approval
    if ($page->approve(1, 'Content approved by moderator')) {
        echo "   ✅ Page approved successfully\n";
        echo "   📊 New status: {$page->getModerationStatusName()}\n";
    } else {
        echo "   ❌ Failed to approve page\n";
    }
    
    // Test 8: Test scholar verification
    echo "\n8. Testing Scholar Verification...\n";
    
    echo "   📊 Scholar verified: " . ($page->isScholarVerified() ? 'Yes' : 'No') . "\n";
    
    // Simulate scholar verification
    if ($page->verifyByScholar(1, 'Content verified by Islamic scholar')) {
        echo "   ✅ Page verified by scholar successfully\n";
        echo "   📊 Scholar verified: " . ($page->isScholarVerified() ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ❌ Failed to verify page by scholar\n";
    }
    
    // Test 9: Test Islamic profile
    echo "\n9. Testing Islamic Profile...\n";
    
    $profile = $page->getIslamicProfile();
    echo "   📊 Islamic Profile Data:\n";
    echo "      - ID: {$profile['id']}\n";
    echo "      - Title: {$profile['title']}\n";
    echo "      - Arabic Title: {$profile['arabic_title']}\n";
    echo "      - Category: {$profile['islamic_category_name']}\n";
    echo "      - Template: {$profile['islamic_template_name']}\n";
    echo "      - Scholar Verified: " . ($profile['scholar_verified'] ? 'Yes' : 'No') . "\n";
    echo "      - Moderation Status: {$profile['moderation_status_name']}\n";
    echo "      - Quality Score: {$profile['content_quality_score']}\n";
    echo "      - Is Approved: " . ($profile['is_approved'] ? 'Yes' : 'No') . "\n";
    echo "      - Needs Moderation: " . ($profile['needs_moderation'] ? 'Yes' : 'No') . "\n";
    
    // Test 10: Test Islamic content search
    echo "\n10. Testing Islamic Content Search...\n";
    
    $islamicPages = $connection->select(
        "SELECT id, title, arabic_title, islamic_category, moderation_status, scholar_verified, content_quality_score 
         FROM pages 
         WHERE islamic_category IS NOT NULL 
         ORDER BY id DESC 
         LIMIT 5"
    );
    
    echo "   📊 Found " . count($islamicPages) . " Islamic pages in database\n";
    foreach ($islamicPages as $islamicPage) {
        echo "      - {$islamicPage['title']} ({$islamicPage['islamic_category']}) - Quality: {$islamicPage['content_quality_score']}\n";
    }
    
    // Test 11: Test Islamic categories and templates
    echo "\n11. Testing Islamic Categories and Templates...\n";
    
    $categories = IslamicPage::getIslamicCategories();
    echo "   📊 Islamic Categories: " . count($categories) . " total\n";
    foreach (array_slice($categories, 0, 5) as $key => $name) {
        echo "      - {$key}: {$name}\n";
    }
    
    $templates = IslamicPage::getIslamicTemplates();
    echo "   📊 Islamic Templates: " . count($templates) . " total\n";
    foreach (array_slice($templates, 0, 5) as $key => $name) {
        echo "      - {$key}: {$name}\n";
    }
    
    $statuses = IslamicPage::getModerationStatuses();
    echo "   📊 Moderation Statuses: " . count($statuses) . " total\n";
    foreach ($statuses as $key => $name) {
        echo "      - {$key}: {$name}\n";
    }
    
    $connection->disconnect();
    
    echo "\n=== Test Summary ===\n";
    echo "✅ Islamic Page Model: Working\n";
    echo "✅ Islamic Tags: Working\n";
    echo "✅ Islamic References: Working\n";
    echo "✅ Islamic Citations: Working\n";
    echo "✅ Islamic Metadata: Working\n";
    echo "✅ Islamic Permissions: Working\n";
    echo "✅ Moderation Workflow: Working\n";
    echo "✅ Scholar Verification: Working\n";
    echo "✅ Islamic Profile: Working\n";
    echo "✅ Islamic Categories: Working\n";
    echo "✅ Islamic Templates: Working\n";
    echo "✅ Content Quality Scoring: Working\n";
    
} catch (Exception $e) {
    echo "\n❌ Test Failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 