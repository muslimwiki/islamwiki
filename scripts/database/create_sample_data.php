<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\User;
use IslamWiki\Models\Page;

/**
 * Sample Data Creation Script
 * 
 * This script creates sample data for IslamWiki development.
 */

class SampleDataCreator
{
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection([
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
        ]);
    }

    /**
     * Run the sample data creation
     */
    public function run(): void
    {
        echo "🌱 Creating Sample Data for IslamWiki\n";
        echo "=====================================\n\n";

        try {
            // Create sample user
            $this->createSampleUser();
            
            // Create sample pages
            $this->createSamplePages();
            
            echo "\n✅ Sample data created successfully!\n";
            echo "You can now login with:\n";
            echo "Username: admin\n";
            echo "Password: password\n";
            
        } catch (Exception $e) {
            echo "\n❌ Error: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
            exit(1);
        }
    }

    /**
     * Create a sample admin user
     */
    private function createSampleUser(): void
    {
        echo "👤 Creating sample admin user...\n";
        
        // Check if admin user already exists
        $existingUser = User::findByUsername('admin', $this->connection);
        if ($existingUser) {
            echo "✅ Admin user already exists\n";
            return;
        }

        $user = new User($this->connection, [
            'username' => 'admin',
            'email' => 'admin@islamwiki.local',
            'password' => 'password',
            'display_name' => 'Administrator',
            'bio' => 'System administrator for IslamWiki',
            'is_admin' => true,
            'is_active' => true,
        ]);

        if ($user->save()) {
            echo "✅ Admin user created successfully\n";
        } else {
            throw new Exception("Failed to create admin user");
        }
    }

    /**
     * Create sample wiki pages
     */
    private function createSamplePages(): void
    {
        echo "📄 Creating sample wiki pages...\n";
        
        $pages = [
            [
                'title' => 'Welcome to IslamWiki',
                'slug' => 'welcome',
                'content' => "# Welcome to IslamWiki\n\nThis is a collaborative platform for Islamic knowledge and research.\n\n## Features\n\n- **Collaborative Editing**: Multiple users can contribute to pages\n- **Version History**: Track changes and revert when needed\n- **Rich Content**: Support for markdown formatting\n- **Search**: Find information quickly\n\n## Getting Started\n\n1. Create an account or login\n2. Start editing pages\n3. Contribute your knowledge\n4. Help build the community\n\n*May Allah guide us all to the truth.*",
                'namespace' => '',
            ],
            [
                'title' => 'About Islam',
                'slug' => 'about-islam',
                'content' => "# About Islam\n\nIslam is a monotheistic Abrahamic religion that originated in the 7th century CE in the Arabian Peninsula.\n\n## Core Beliefs\n\n- **Tawhid**: Belief in the oneness of Allah\n- **Prophethood**: Belief in the messengers of Allah\n- **Hereafter**: Belief in the Day of Judgment\n- **Divine Books**: Belief in the revealed scriptures\n\n## Five Pillars\n\n1. **Shahada**: Declaration of faith\n2. **Salah**: Daily prayers\n3. **Zakat**: Charity to the poor\n4. **Sawm**: Fasting during Ramadan\n5. **Hajj**: Pilgrimage to Mecca\n\n## Sources of Islamic Law\n\n- **Quran**: The holy book of Islam\n- **Sunnah**: The teachings and practices of Prophet Muhammad (ﷺ)\n- **Ijma**: Consensus of scholars\n- **Qiyas**: Analogical reasoning",
                'namespace' => '',
            ],
            [
                'title' => 'Islamic History',
                'slug' => 'islamic-history',
                'content' => "# Islamic History\n\n## Early Period\n\n### Pre-Islamic Arabia\n- Tribal society in the Arabian Peninsula\n- Trade routes and cultural exchange\n- Religious diversity\n\n### The Prophet Muhammad (ﷺ)\n- Born in Mecca in 570 CE\n- Received revelation at age 40\n- Established the first Muslim community in Medina\n- Conquest of Mecca in 630 CE\n\n## Rashidun Caliphate (632-661)\n\n### Abu Bakr (632-634)\n- First caliph after Prophet Muhammad (ﷺ)\n- Consolidated Muslim community\n- Fought against apostasy\n\n### Umar ibn al-Khattab (634-644)\n- Expanded Islamic empire\n- Established administrative systems\n- Conquest of Jerusalem\n\n### Uthman ibn Affan (644-656)\n- Standardized Quran\n- Continued expansion\n- Internal conflicts\n\n### Ali ibn Abi Talib (656-661)\n- Fourth caliph\n- Internal strife and civil war\n- Assassination in 661\n\n## Umayyad Caliphate (661-750)\n\n- Capital moved to Damascus\n- Continued expansion\n- Cultural and architectural achievements\n- Internal divisions\n\n## Abbasid Caliphate (750-1258)\n\n- Golden Age of Islam\n- Baghdad as capital\n- Scientific and cultural achievements\n- Translation movement\n\n## Later Periods\n\n- Ottoman Empire (1299-1922)\n- Safavid Empire (1501-1736)\n- Mughal Empire (1526-1857)\n- Modern period",
                'namespace' => '',
            ],
            [
                'title' => 'Islamic Sciences',
                'slug' => 'islamic-sciences',
                'content' => "# Islamic Sciences\n\n## Quranic Sciences\n\n### Tafsir (Exegesis)\n- Interpretation of the Quran\n- Various methodologies\n- Classical and modern approaches\n\n### Qiraat (Recitation)\n- Different ways of reciting the Quran\n- Seven canonical readings\n- Preservation of oral tradition\n\n## Hadith Sciences\n\n### Mustalah al-Hadith\n- Study of hadith terminology\n- Classification of narrations\n- Authentication methods\n\n### Rijal (Biography)\n- Study of hadith narrators\n- Reliability assessment\n- Chain of transmission\n\n## Islamic Law (Fiqh)\n\n### Usul al-Fiqh\n- Principles of Islamic jurisprudence\n- Sources of law\n- Legal reasoning\n\n### Madhahib (Schools of Thought)\n- Hanafi, Maliki, Shafi'i, Hanbali\n- Differences in methodology\n- Regional distribution\n\n## Theology (Aqeedah)\n\n### Kalam\n- Islamic theology\n- Philosophical approaches\n- Debates and schools\n\n### Tawhid\n- Study of divine unity\n- Attributes of Allah\n- Avoiding shirk\n\n## Sufism (Tasawwuf)\n\n- Spiritual dimension of Islam\n- Inner purification\n- Various orders and practices\n\n## Islamic Philosophy\n\n- Integration of Greek philosophy\n- Islamic metaphysics\n- Ethics and politics",
                'namespace' => '',
            ],
            [
                'title' => 'Contributing Guidelines',
                'slug' => 'contributing',
                'content' => "# Contributing to IslamWiki\n\n## How to Contribute\n\n### Creating an Account\n1. Click on 'Register' in the top navigation\n2. Fill in your details\n3. Verify your email address\n4. Start contributing!\n\n### Editing Pages\n1. Navigate to any page\n2. Click the 'Edit' button\n3. Make your changes using markdown\n4. Add a summary of your changes\n5. Save the page\n\n### Creating New Pages\n1. Use the search function to see if a page exists\n2. If not found, you'll see a link to create it\n3. Write your content in markdown\n4. Add appropriate categories\n5. Save the page\n\n## Content Guidelines\n\n### Accuracy\n- Ensure information is accurate and well-sourced\n- Cite reliable Islamic sources\n- Avoid speculation and personal opinions\n\n### Respect\n- Be respectful of different Islamic schools of thought\n- Avoid sectarian language\n- Maintain a scholarly tone\n\n### Formatting\n- Use clear headings and structure\n- Include relevant categories\n- Add internal links to related pages\n\n## Markdown Guide\n\n### Headers\n```\n# Main heading\n## Subheading\n### Section heading\n```\n\n### Lists\n```\n- Bullet points\n1. Numbered lists\n```\n\n### Links\n```\n[Link text](page-slug)\n```\n\n### Bold and Italic\n```\n**Bold text**\n*Italic text*\n```\n\n### Code\n```\n`Inline code`\n```\n\n## Getting Help\n\n- Check existing documentation\n- Ask questions in the discussion pages\n- Contact administrators if needed\n\nThank you for contributing to Islamic knowledge!",
                'namespace' => '',
            ],
        ];

        foreach ($pages as $pageData) {
            // Check if page already exists
            $existingPage = Page::findBySlug($pageData['slug'], $this->connection);
            if ($existingPage) {
                echo "✅ Page '{$pageData['title']}' already exists\n";
                continue;
            }

            $page = new Page($this->connection, $pageData);
            
            if ($page->save()) {
                echo "✅ Created page: {$pageData['title']}\n";
            } else {
                echo "⚠️  Failed to create page: {$pageData['title']}\n";
            }
        }
    }
}

// Run the script
$creator = new SampleDataCreator();
$creator->run(); 