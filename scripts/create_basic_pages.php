<?php
/**
 * Create Basic Pages Script
 * 
 * Creates the basic pages that should exist on IslamWiki
 * 
 * @package IslamWiki
 * @version 0.0.34
 * @license AGPL-3.0-only
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include necessary files
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';

use IslamWiki\Core\Database\Connection;

try {
    // Initialize database connection
    $db = new Connection();
    
    // Basic pages to create
    $basicPages = [
        [
            'title' => 'Welcome to IslamWiki',
            'slug' => 'welcome',
            'content' => "# Welcome to IslamWiki\n\nThis is your comprehensive resource for Islamic knowledge, history, and sciences.\n\n## Getting Started\n\n- Browse existing pages\n- Create new content\n- Contribute to the community\n\n## Features\n\n- Islamic content search\n- User profiles and settings\n- Community features\n\n*Welcome to the journey of Islamic knowledge!*"
        ],
        [
            'title' => 'About IslamWiki',
            'slug' => 'about',
            'content' => "# About IslamWiki\n\nIslamWiki is a comprehensive platform dedicated to Islamic knowledge, scholarship, and community.\n\n## Our Mission\n\nTo provide accurate, comprehensive, and accessible Islamic knowledge to everyone.\n\n## Features\n\n- **Islamic Sciences**: Quran, Hadith, Islamic Law, Theology\n- **Community**: User contributions and collaboration\n- **Search**: Advanced Islamic content search\n- **Multilingual**: Support for Arabic and English\n\n## Contributing\n\nWe welcome contributions from scholars, students, and anyone interested in Islamic knowledge.\n\n*May Allah guide us all to the straight path.*"
        ],
        [
            'title' => 'Islamic Sciences',
            'slug' => 'islamic-sciences',
            'content' => "# Islamic Sciences\n\nExplore the rich tradition of Islamic scholarship and academic disciplines.\n\n## Major Disciplines\n\n### 1. Quranic Sciences (Ulum al-Quran)\n- Tafsir (Exegesis)\n- Asbab al-Nuzul (Reasons for Revelation)\n- Muhkam and Mutashabih\n- Nasikh and Mansukh\n\n### 2. Hadith Sciences (Ulum al-Hadith)\n- Rijal (Biography of Narrators)\n- Matn (Text Analysis)\n- Sanad (Chain of Transmission)\n- Classification of Hadith\n\n### 3. Islamic Law (Fiqh)\n- Usul al-Fiqh (Principles of Jurisprudence)\n- Madhahib (Schools of Thought)\n- Contemporary Issues\n\n### 4. Islamic Theology (Aqidah)\n- Tawhid (Monotheism)\n- Attributes of Allah\n- Prophethood\n- Hereafter\n\n### 5. Islamic History\n- Sirah (Biography of Prophet Muhammad ﷺ)\n- Khulafa al-Rashidun\n- Islamic Empires\n- Golden Age of Islam\n\n*Knowledge is the life of the mind.*"
        ],
        [
            'title' => 'Contributing Guidelines',
            'slug' => 'contributing',
            'content' => "# Contributing to IslamWiki\n\nThank you for your interest in contributing to IslamWiki!\n\n## How to Contribute\n\n### 1. Creating Pages\n- Use clear, descriptive titles\n- Follow Islamic scholarly standards\n- Include proper citations\n- Write in accessible language\n\n### 2. Editing Existing Pages\n- Improve accuracy and clarity\n- Add missing information\n- Fix grammatical errors\n- Update outdated content\n\n### 3. Content Guidelines\n- Ensure accuracy and authenticity\n- Use reliable sources\n- Respect different scholarly opinions\n- Maintain respectful tone\n\n## Quality Standards\n\n- **Accuracy**: Verify information from reliable sources\n- **Completeness**: Provide comprehensive coverage\n- **Clarity**: Write in clear, accessible language\n- **Respect**: Honor Islamic traditions and scholarship\n\n## Getting Help\n\n- Check existing documentation\n- Ask questions in discussions\n- Review similar pages for examples\n\n*Your contributions help build a better resource for everyone.*"
        ],
        [
            'title' => 'Islamic History',
            'slug' => 'islamic-history',
            'content' => "# Islamic History\n\nExplore the rich history of Islam from the time of Prophet Muhammad ﷺ to the present day.\n\n## Early Islamic Period\n\n### The Prophet Muhammad ﷺ\n- Birth and early life in Mecca\n- Revelation and prophethood\n- Migration to Medina (Hijra)\n- Conquest of Mecca\n- Final years and passing\n\n### The Rightly Guided Caliphs (Khulafa al-Rashidun)\n- Abu Bakr al-Siddiq (632-634 CE)\n- Umar ibn al-Khattab (634-644 CE)\n- Uthman ibn Affan (644-656 CE)\n- Ali ibn Abi Talib (656-661 CE)\n\n## Islamic Empires\n\n### Umayyad Caliphate (661-750 CE)\n- Expansion and conquests\n- Administrative reforms\n- Cultural achievements\n\n### Abbasid Caliphate (750-1258 CE)\n- Golden Age of Islam\n- Scientific and cultural achievements\n- Translation movement\n- House of Wisdom\n\n### Ottoman Empire (1299-1922 CE)\n- Rise and expansion\n- Administrative system\n- Cultural contributions\n- Decline and fall\n\n## Modern Period\n\n- Colonial period\n- Independence movements\n- Contemporary challenges\n- Revival and renewal\n\n*History is the teacher of life.*"
        ]
    ];
    
    $stmt = $db->prepare("
        INSERT INTO pages (title, slug, content, content_format, namespace, created_at, updated_at) 
        VALUES (?, ?, ?, 'markdown', '', NOW(), NOW())
    ");
    
    $createdCount = 0;
    foreach ($basicPages as $page) {
        // Check if page already exists
        $checkStmt = $db->prepare("SELECT id FROM pages WHERE slug = ?");
        $checkStmt->execute([$page['slug']]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            echo "⏭️  Page already exists: {$page['title']}\n";
            continue;
        }
        
        $result = $stmt->execute([
            $page['title'],
            $page['slug'],
            $page['content']
        ]);
        
        if ($result) {
            echo "✅ Created page: {$page['title']}\n";
            $createdCount++;
        } else {
            echo "❌ Failed to create page: {$page['title']}\n";
        }
    }
    
    if ($createdCount > 0) {
        echo "\n🎉 Successfully created {$createdCount} basic pages!\n";
    } else {
        echo "\n📝 All basic pages already exist!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating basic pages: " . $e->getMessage() . "\n";
    exit(1);
}
?> 