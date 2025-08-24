<?php

/**
 * Seed Sample Data Script
 *
 * This script adds sample data to the pages table
 * so the homepage has content to display.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

echo "Seeding Sample Data\n";
echo "==================\n\n";

try {
    // Create database connection
    $dbConfig = [
        'driver' => getenv('DB_CONNECTION') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ];

    $connection = new Connection($dbConfig);
    echo "✅ Database connection created\n";

    // Check if pages table has data
    $result = $connection->select('SELECT COUNT(*) as count FROM pages');
    $count = $result[0]['count'];

    if ($count > 0) {
        echo "⚠️  Pages table already has $count records, skipping...\n";
    } else {
        echo "Adding sample pages...\n";

        // Sample pages data
        $pages = [
            [
                'title' => 'Welcome to IslamWiki',
                'slug' => 'welcome',
                'content' => "# Welcome to IslamWiki\n\nIslamWiki is your comprehensive Islamic knowledge base and resource center. " .
                    "Here you can find authentic information about Islam, including Quran, Hadith, Islamic history, and more.\n\n" .
                    "## Features\n\n- **Quran Studies**: Access Quranic text, translations, and tafsir\n" .
                    "- **Hadith Collections**: Browse authentic hadith with proper chains\n" .
                    "- **Islamic Calendar**: Track important Islamic dates and events\n" .
                    "- **Prayer Times**: Get accurate prayer times for your location\n" .
                    "- **Community**: Connect with other Muslims and scholars\n\n" .
                    "## Getting Started\n\nStart exploring by browsing our main categories or use the search function to find specific topics.",
                'content_format' => 'markdown',
                'namespace' => '',
                'parent_id' => null,
                'is_locked' => false,
                'view_count' => 0
            ],
            [
                'title' => 'About Islam',
                'slug' => 'about-islam',
                'content' => "# About Islam\n\nIslam is a monotheistic Abrahamic religion that originated in the 7th century CE in the Arabian Peninsula. " .
                    "The word \"Islam\" means submission to the will of God (Allah).\n\n" .
                    "## Core Beliefs\n\n- **Tawhid**: Belief in the oneness of God\n" .
                    "- **Prophethood**: Belief in all prophets including Muhammad (ﷺ)\n" .
                    "- **Hereafter**: Belief in life after death and judgment\n" .
                    "- **Divine Books**: Belief in revealed scriptures\n\n" .
                    "## Five Pillars\n\n1. **Shahada**: Declaration of faith\n" .
                    "2. **Salah**: Five daily prayers\n" .
                    "3. **Zakat**: Charity to the poor\n" .
                    "4. **Sawm**: Fasting during Ramadan\n" .
                    "5. **Hajj**: Pilgrimage to Mecca\n\n" .
                    "## Sources of Islamic Law\n\n- **Quran**: The holy book of Islam\n" .
                    "- **Sunnah**: The teachings and practices of Prophet Muhammad (ﷺ)\n" .
                    "- **Ijma**: Consensus of scholars\n" .
                    "- **Qiyas**: Analogical reasoning",
                'content_format' => 'markdown',
                'namespace' => '',
                'parent_id' => null,
                'is_locked' => false,
                'view_count' => 0
            ],
            [
                'title' => 'Quran Studies',
                'slug' => 'quran-studies',
                'content' => "# Quran Studies\n\nThe Quran is the holy book of Islam, believed by Muslims to be the word of God as revealed to Prophet Muhammad (ﷺ) " .
                    "through the Angel Gabriel.\n\n" .
                    "## Structure\n\n- **114 Surahs** (chapters)\n" .
                    "- **6,236 Ayahs** (verses)\n" .
                    "- **30 Juz** (parts)\n\n" .
                    "## Key Themes\n\n- **Tawhid**: Monotheism\n" .
                    "- **Akhirah**: Afterlife\n" .
                    "- **Justice**: Justice\n" .
                    "- **Ihsan**: Excellence\n" .
                    "- **Piety**: God-consciousness\n\n" .
                    "## Study Methods\n\n- **Tajweed**: Proper recitation\n" .
                    "- **Tafsir**: Interpretation and commentary\n" .
                    "- **Memorization**: Hifz\n" .
                    "- **Reflection**: Tadabbur\n\n" .
                    "## Online Resources\n\n- Complete Quran text with translations\n" .
                    "- Audio recitations by famous Qaris\n" .
                    "- Tafsir from various scholars\n" .
                    "- Search and navigation tools",
                'content_format' => 'markdown',
                'namespace' => '',
                'parent_id' => null,
                'is_locked' => false,
                'view_count' => 0
            ],
            [
                'title' => 'Hadith Collections',
                'slug' => 'hadith-collections',
                'content' => "# Hadith Collections\n\nHadith are the recorded sayings, actions, and approvals of Prophet Muhammad (ﷺ), which serve as a major source of Islamic law and guidance alongside the Quran.\n\n## Major Collections\n\n### Sahih Bukhari\n- **Compiler**: Imam Bukhari (810-870 CE)\n- **Status**: Most authentic collection\n- **Contents**: 7,275 hadith\n\n### Sahih Muslim\n- **Compiler**: Imam Muslim (817-875 CE)\n- **Status**: Second most authentic\n- **Contents**: 7,500+ hadith\n\n### Other Collections\n- **Abu Dawud**: Focus on legal hadith\n- **Tirmidhi**: Includes authenticity ratings\n- **Nasai**: Emphasizes prayer and fasting\n- **Ibn Majah**: Comprehensive collection\n\n## Hadith Sciences\n\n- **Rijal**: Study of narrators\n- **Mustalah**: Hadith terminology\n- **Jarh wa Ta'dil**: Criticism and praise of narrators\n- **Asma al-Rijal**: Biographies of narrators",
                'content_format' => 'markdown',
                'namespace' => '',
                'parent_id' => null,
                'is_locked' => false,
                'view_count' => 0
            ],
            [
                'title' => 'Islamic Calendar',
                'slug' => 'islamic-calendar',
                'content' => "# Islamic Calendar (Hijri Calendar)\n\nThe Islamic calendar is a lunar calendar used by Muslims to determine the dates of religious observances and events.\n\n## Structure\n\n- **12 Lunar Months**\n- **354-355 Days** per year\n- **Based on**: Lunar cycles\n\n## Important Months\n\n- **Ramadan**: Month of fasting\n- **Dhul Hijjah**: Month of Hajj\n- **Muharram**: Islamic New Year\n- **Rajab**: Sacred month\n- **Sha'ban**: Month before Ramadan\n\n## Key Dates\n\n- **1st Muharram**: Islamic New Year\n- **10th Muharram**: Day of Ashura\n- **12th Rabi al-Awwal**: Mawlid al-Nabi\n- **27th Ramadan**: Laylat al-Qadr\n- **1st Shawwal**: Eid al-Fitr\n- **10th Dhul Hijjah**: Eid al-Adha\n\n## Conversion\n\nOur calendar converter helps you convert between Gregorian and Hijri dates, and provides prayer times for important Islamic events.",
                'content_format' => 'markdown',
                'namespace' => '',
                'parent_id' => null,
                'is_locked' => false,
                'view_count' => 0
            ]
        ];

        // Insert each page
        foreach ($pages as $page) {
            $connection->statement(
                "INSERT INTO pages (title, slug, content, content_format, namespace, parent_id, is_locked, view_count, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $page['title'],
                    $page['slug'],
                    $page['content'],
                    $page['content_format'],
                    $page['namespace'],
                    $page['parent_id'],
                    $page['is_locked'],
                    $page['view_count']
                ]
            );
            echo "✅ Added page: {$page['title']}\n";
        }

        echo "\n🎉 Sample data added successfully!\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";
