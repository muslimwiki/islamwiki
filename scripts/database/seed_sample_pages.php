<?php

/**
 * Seed Sample Pages
 * 
 * This script adds sample pages to the newly created pages table
 * so the application can work properly.
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

try {
    $db = new Connection();
    echo "✅ Database connection successful\n";
    
    // Check if pages table exists and has data
    $result = $db->select("SELECT COUNT(*) as count FROM pages");
    $pageCount = $result[0]['count'] ?? 0;
    echo "Current pages in database: $pageCount\n";
    
    if ($pageCount > 0) {
        echo "Pages table already has data. No action needed.\n";
        exit(0);
    }
    
    echo "🔧 Adding sample pages...\n";
    
    // Sample pages data
    $samplePages = [
        [
            'title' => 'Welcome to IslamWiki',
            'slug' => 'welcome',
            'content' => '# Welcome to IslamWiki

IslamWiki is a comprehensive Islamic knowledge platform that provides authentic information about Islam, including:

- **Quran**: Complete text with translations and tafsir
- **Hadith**: Authentic collections and commentaries  
- **Islamic Sciences**: Various branches of Islamic knowledge
- **Prayer Times**: Accurate salah times and qibla direction
- **Islamic Calendar**: Hijri dates and important events

This platform is designed to serve Muslims and anyone interested in learning about Islam.',
            'content_format' => 'markdown',
            'namespace' => '',
            'view_count' => 0
        ],
        [
            'title' => 'About Islam',
            'slug' => 'about-islam',
            'content' => '# About Islam

Islam is a monotheistic Abrahamic religion that originated in the 7th century CE in the Arabian Peninsula. The word "Islam" means "submission to God" in Arabic.

## Core Beliefs

- **Tawhid**: Belief in the oneness of Allah
- **Prophethood**: Belief in all prophets including Muhammad (ﷺ)
- **Hereafter**: Belief in life after death and judgment

## Five Pillars

1. **Shahada**: Declaration of faith
2. **Salah**: Five daily prayers
3. **Zakat**: Charity to the poor
4. **Sawm**: Fasting during Ramadan
5. **Hajj**: Pilgrimage to Mecca',
            'content_format' => 'markdown',
            'namespace' => '',
            'view_count' => 0
        ],
        [
            'title' => 'Getting Started',
            'slug' => 'getting-started',
            'content' => '# Getting Started with IslamWiki

Welcome to IslamWiki! Here\'s how to get started:

## Navigation

- **Home**: Start here to explore the platform
- **Quran**: Read and search the Holy Quran
- **Hadith**: Access authentic hadith collections
- **Prayer Times**: Get accurate salah times for your location
- **Calendar**: View Islamic events and important dates

## Features

- **Search**: Use the powerful Iqra search to find Islamic content
- **User Accounts**: Create an account to save preferences and track progress
- **Mobile Friendly**: Access from any device with responsive design

## Support

If you need help or have questions, please refer to our documentation or contact support.',
            'content_format' => 'markdown',
            'namespace' => '',
            'view_count' => 0
        ]
    ];
    
    // Insert sample pages
    foreach ($samplePages as $page) {
        $sql = "INSERT INTO pages (title, slug, content, content_format, namespace, view_count, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $db->statement($sql, [
            $page['title'],
            $page['slug'],
            $page['content'],
            $page['content_format'],
            $page['namespace'],
            $page['view_count']
        ]);
        
        echo "  ✅ Added page: {$page['title']}\n";
    }
    
    // Verify pages were added
    $result = $db->select("SELECT COUNT(*) as count FROM pages");
    $finalCount = $result[0]['count'];
    echo "\n🎉 Sample pages added successfully!\n";
    echo "Total pages in database: $finalCount\n";
    
    // Show the pages
    echo "\nSample pages created:\n";
    $pages = $db->select("SELECT title, slug, created_at FROM pages ORDER BY id");
    foreach ($pages as $page) {
        echo "  - {$page['title']} (/{$page['slug']}) - {$page['created_at']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 