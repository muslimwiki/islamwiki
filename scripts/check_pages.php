<?php
/**
 * Check Pages Script
 * 
 * Lists all pages in the database
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
    
    // Check if pages table exists
    $stmt = $db->prepare("SHOW TABLES LIKE 'pages'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "❌ Pages table does not exist!\n";
        exit(1);
    }
    
    // Get all pages
    $stmt = $db->prepare("SELECT id, title, slug, namespace, created_at FROM pages ORDER BY created_at DESC");
    $stmt->execute();
    $pages = $stmt->fetchAll();
    
    if (empty($pages)) {
        echo "📝 No pages found in database\n";
        echo "Let's create some basic pages...\n";
        
        // Create basic pages
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
            ]
        ];
        
        $stmt = $db->prepare("
            INSERT INTO pages (title, slug, content, content_format, namespace, created_at, updated_at) 
            VALUES (?, ?, ?, 'markdown', '', NOW(), NOW())
        ");
        
        foreach ($basicPages as $page) {
            $result = $stmt->execute([
                $page['title'],
                $page['slug'],
                $page['content']
            ]);
            
            if ($result) {
                echo "✅ Created page: {$page['title']}\n";
            } else {
                echo "❌ Failed to create page: {$page['title']}\n";
            }
        }
        
        echo "\n🎉 Basic pages created successfully!\n";
        
    } else {
        echo "📚 Found " . count($pages) . " pages in database:\n\n";
        
        foreach ($pages as $page) {
            echo "- {$page->title} (/{$page->slug})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error checking pages: " . $e->getMessage() . "\n";
    exit(1);
}
?> 