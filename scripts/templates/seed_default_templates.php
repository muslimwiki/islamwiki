<?php

declare(strict_types=1);

/**
 * Seed Default Templates Script
 * 
 * This script creates default Islamic templates in the database
 * for the MediaWiki-style template system.
 * 
 * Usage: php scripts/templates/seed_default_templates.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\Template;

// Initialize database connection
$config = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];

try {
    $connection = new Connection($config);
    echo "✅ Database connection established\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Default template categories
$categories = [
    [
        'name' => 'Islamic',
        'description' => 'Islamic content templates (Quran, Hadith, etc.)',
        'icon' => '🕌',
        'sort_order' => 1,
    ],
    [
        'name' => 'General',
        'description' => 'General purpose templates',
        'icon' => '📄',
        'sort_order' => 2,
    ],
    [
        'name' => 'Navigation',
        'description' => 'Navigation and structure templates',
        'icon' => '🧭',
        'sort_order' => 3,
    ],
];

// Insert categories
echo "📝 Inserting template categories...\n";
foreach ($categories as $category) {
    try {
        $connection->table('template_categories')->insert(array_merge($category, [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
        echo "  ✅ Category '{$category['name']}' created\n";
    } catch (Exception $e) {
        echo "  ⚠️  Category '{$category['name']}' already exists or failed: " . $e->getMessage() . "\n";
    }
}

// Default Islamic templates
$templates = [
    [
        'name' => 'QuranVerse',
        'content' => '<div class="quran-verse" data-surah="{{surah}}" data-ayah="{{ayah}}">
            <div class="verse-header">
                <span class="surah-name">{{surah_name|Surah {{surah}}}}</span>
                <span class="ayah-number">Ayah {{ayah}}</span>
            </div>
            <div class="verse-content">
                <div class="arabic-text">{{arabic_text|Loading...}}</div>
                <div class="translation">{{translation|Loading...}}</div>
            </div>
            <div class="verse-footer">
                <a href="/quran/{{surah}}/{{ayah}}" class="verse-link">View Full Context</a>
            </div>
        </div>',
        'parameters' => [
            'surah' => ['type' => 'integer', 'required' => true, 'description' => 'Surah number'],
            'ayah' => ['type' => 'integer', 'required' => true, 'description' => 'Ayah number'],
            'surah_name' => ['type' => 'string', 'required' => false, 'description' => 'Surah name'],
            'arabic_text' => ['type' => 'string', 'required' => false, 'description' => 'Arabic text'],
            'translation' => ['type' => 'string', 'required' => false, 'description' => 'Translation text']
        ],
        'description' => 'Display a Quran verse with Arabic text and translation',
        'category' => 'Islamic',
        'author' => 'System',
        'is_system' => true,
    ],
    [
        'name' => 'HadithCitation',
        'content' => '<div class="hadith-citation" data-collection="{{collection}}" data-book="{{book}}" data-hadith="{{hadith}}">
            <div class="hadith-header">
                <span class="collection-name">{{collection_name|{{collection}}}}</span>
                <span class="reference">Book {{book}}, Hadith {{hadith}}</span>
            </div>
            <div class="hadith-content">
                <div class="arabic-text">{{arabic_text|Loading...}}</div>
                <div class="translation">{{translation|Loading...}}</div>
            </div>
            <div class="hadith-footer">
                <span class="narrator">{{narrator|Narrated by {{narrator_name}}}}</span>
                <a href="/hadith/{{collection}}/{{book}}/{{hadith}}" class="hadith-link">View Full Hadith</a>
            </div>
        </div>',
        'parameters' => [
            'collection' => ['type' => 'string', 'required' => true, 'description' => 'Hadith collection name'],
            'book' => ['type' => 'integer', 'required' => true, 'description' => 'Book number'],
            'hadith' => ['type' => 'integer', 'required' => true, 'description' => 'Hadith number'],
            'collection_name' => ['type' => 'string', 'required' => false, 'description' => 'Display name for collection'],
            'arabic_text' => ['type' => 'string', 'required' => false, 'description' => 'Arabic text'],
            'translation' => ['type' => 'string', 'required' => false, 'description' => 'Translation text'],
            'narrator' => ['type' => 'string', 'required' => false, 'description' => 'Narrator information'],
            'narrator_name' => ['type' => 'string', 'required' => false, 'description' => 'Narrator name']
        ],
        'description' => 'Display a Hadith citation with Arabic text and translation',
        'category' => 'Islamic',
        'author' => 'System',
        'is_system' => true,
    ],
    [
        'name' => 'ScholarProfile',
        'content' => '<div class="scholar-profile" data-scholar="{{name}}">
            <div class="scholar-header">
                <h3 class="scholar-name">{{display_name|{{name}}}}</h3>
                <span class="scholar-era">{{era|Unknown Era}}</span>
            </div>
            <div class="scholar-content">
                <div class="scholar-bio">{{biography|Biography not available}}</div>
                <div class="scholar-works">
                    <h4>Notable Works</h4>
                    <ul>{{works|No works listed}}</ul>
                </div>
            </div>
            <div class="scholar-footer">
                <a href="/scholars/{{name}}" class="scholar-link">View Full Profile</a>
            </div>
        </div>',
        'parameters' => [
            'name' => ['type' => 'string', 'required' => true, 'description' => 'Scholar name'],
            'display_name' => ['type' => 'string', 'required' => false, 'description' => 'Display name'],
            'era' => ['type' => 'string', 'required' => false, 'description' => 'Historical era'],
            'biography' => ['type' => 'string', 'required' => false, 'description' => 'Scholar biography'],
            'works' => ['type' => 'string', 'required' => false, 'description' => 'Notable works list']
        ],
        'description' => 'Display a scholar profile with biography and works',
        'category' => 'Islamic',
        'author' => 'System',
        'is_system' => true,
    ],
    [
        'name' => 'PrayerTimes',
        'content' => '<div class="prayer-times" data-location="{{location}}">
            <div class="prayer-header">
                <h3>Prayer Times for {{location_name|{{location}}}}</h3>
                <span class="date">{{date|Today}}</span>
            </div>
            <div class="prayer-schedule">
                <div class="prayer-time">
                    <span class="prayer-name">Fajr</span>
                    <span class="prayer-time">{{fajr|--:--}}</span>
                </div>
                <div class="prayer-time">
                    <span class="prayer-name">Dhuhr</span>
                    <span class="prayer-time">{{dhuhr|--:--}}</span>
                </div>
                <div class="prayer-time">
                    <span class="prayer-name">Asr</span>
                    <span class="prayer-time">{{asr|--:--}}</span>
                </div>
                <div class="prayer-time">
                    <span class="prayer-name">Maghrib</span>
                    <span class="prayer-time">{{maghrib|--:--}}</span>
                </div>
                <div class="prayer-time">
                    <span class="prayer-name">Isha</span>
                    <span class="prayer-time">{{isha|--:--}}</span>
                </div>
            </div>
            <div class="prayer-footer">
                <a href="/prayer-times/{{location}}" class="prayer-link">View Full Schedule</a>
            </div>
        </div>',
        'parameters' => [
            'location' => ['type' => 'string', 'required' => true, 'description' => 'Location identifier'],
            'location_name' => ['type' => 'string', 'required' => false, 'description' => 'Display name for location'],
            'date' => ['type' => 'string', 'required' => false, 'description' => 'Date for prayer times'],
            'fajr' => ['type' => 'string', 'required' => false, 'description' => 'Fajr prayer time'],
            'dhuhr' => ['type' => 'string', 'required' => false, 'description' => 'Dhuhr prayer time'],
            'asr' => ['type' => 'string', 'required' => false, 'description' => 'Asr prayer time'],
            'maghrib' => ['type' => 'string', 'required' => false, 'description' => 'Maghrib prayer time'],
            'isha' => ['type' => 'string', 'required' => false, 'description' => 'Isha prayer time']
        ],
        'description' => 'Display prayer times for a specific location',
        'category' => 'Islamic',
        'author' => 'System',
        'is_system' => true,
    ],
    [
        'name' => 'InfoBox',
        'content' => '<div class="infobox" style="border: 1px solid #ccc; padding: 10px; margin: 10px; float: right; width: 300px;">
            <div class="infobox-header">
                <h3>{{title|Information}}</h3>
            </div>
            <div class="infobox-content">
                {{content|No content provided}}
            </div>
            {{#if:image|{{image}}}}
            {{#if:caption|{{caption}}}}
        </div>',
        'parameters' => [
            'title' => ['type' => 'string', 'required' => false, 'description' => 'Infobox title'],
            'content' => ['type' => 'string', 'required' => false, 'description' => 'Infobox content'],
            'image' => ['type' => 'string', 'required' => false, 'description' => 'Image URL'],
            'caption' => ['type' => 'string', 'required' => false, 'description' => 'Image caption']
        ],
        'description' => 'Create an information box with title, content, and optional image',
        'category' => 'General',
        'author' => 'System',
        'is_system' => true,
    ],
    [
        'name' => 'Navigation',
        'content' => '<div class="navigation-box">
            <div class="nav-header">{{title|Navigation}}</div>
            <div class="nav-content">
                <ul>
                    {{#each:links|{{link}}}}
                </ul>
            </div>
        </div>',
        'parameters' => [
            'title' => ['type' => 'string', 'required' => false, 'description' => 'Navigation title'],
            'links' => ['type' => 'array', 'required' => false, 'description' => 'Array of navigation links']
        ],
        'description' => 'Create a navigation box with links',
        'category' => 'Navigation',
        'author' => 'System',
        'is_system' => true,
    ],
];

// Insert templates
echo "\n📝 Inserting default templates...\n";
foreach ($templates as $template) {
    try {
        $templateData = array_merge($template, [
            'parameters' => json_encode($template['parameters']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        $connection->table('templates')->insert($templateData);
        echo "  ✅ Template '{$template['name']}' created\n";
    } catch (Exception $e) {
        echo "  ⚠️  Template '{$template['name']}' already exists or failed: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Default templates seeding completed!\n";
echo "\n📋 Summary:\n";
echo "  • Template categories: " . count($categories) . "\n";
echo "  • Default templates: " . count($templates) . "\n";
echo "\n🔧 Next steps:\n";
echo "  1. Run the database migration: php scripts/database/run_migrations.php\n";
echo "  2. Test templates in wiki pages\n";
echo "  3. Create custom templates as needed\n"; 