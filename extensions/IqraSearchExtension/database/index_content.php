<?php

declare(strict_types=1);

/**
 * Content Indexer for IqraSearchExtension
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Database
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

require_once __DIR__ . '/connect.php';

class ContentIndexer
{
    private SearchDatabaseConnection $db;
    private array $indexedContent = [];
    private array $indexStats = [
        'total' => 0,
        'successful' => 0,
        'failed' => 0,
        'by_type' => []
    ];

    public function __construct()
    {
        $this->db = new SearchDatabaseConnection();
    }

    /**
     * Run the content indexing process
     */
    public function run(): void
    {
        echo "🚀 Starting Content Indexing for IqraSearchExtension\n";
        echo "==================================================\n\n";

        try {
            // Test database connection
            if (!$this->db->testConnection()) {
                throw new Exception("Database connection failed");
            }

            // Create tables if they don't exist
            $this->createTablesIfNotExist();

            // Index different content types
            $this->indexWikiContent();
            $this->indexQuranContent();
            $this->indexHadithContent();
            $this->indexArticleContent();
            $this->indexScholarContent();

            // Generate search suggestions
            $this->generateSearchSuggestions();

            // Display indexing statistics
            $this->displayIndexingStats();

            echo "\n✅ Content indexing completed successfully!\n";

        } catch (Exception $e) {
            echo "\n❌ Content indexing failed: " . $e->getMessage() . "\n";
            error_log("Content indexing error: " . $e->getMessage());
        }
    }

    /**
     * Create search tables if they don't exist
     */
    private function createTablesIfNotExist(): void
    {
        echo "📋 Creating search tables...\n";

        $tables = [
            'iqra_search_index' => "
                CREATE TABLE IF NOT EXISTS `iqra_search_index` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `content_type` VARCHAR(50) NOT NULL COMMENT 'Type of content',
                    `content_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID of the content',
                    `title` VARCHAR(500) NOT NULL COMMENT 'Content title',
                    `content` TEXT NOT NULL COMMENT 'Searchable content text',
                    `excerpt` VARCHAR(1000) NOT NULL COMMENT 'Short excerpt',
                    `url` VARCHAR(500) NOT NULL COMMENT 'URL to content',
                    `metadata` JSON COMMENT 'Additional metadata',
                    `search_vector` TEXT COMMENT 'Full-text search vector',
                    `relevance_score` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Relevance score',
                    `view_count` INT UNSIGNED DEFAULT 0 COMMENT 'View count',
                    `rating` DECIMAL(3,2) DEFAULT 0.00 COMMENT 'Content rating',
                    `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `indexed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Whether content is active',
                    
                    INDEX `idx_content_type` (`content_type`),
                    INDEX `idx_content_id` (`content_id`),
                    INDEX `idx_title` (`title`(100)),
                    INDEX `idx_search_vector` (`search_vector`(100)),
                    INDEX `idx_relevance` (`relevance_score`),
                    INDEX `idx_last_updated` (`last_updated`),
                    INDEX `idx_is_active` (`is_active`),
                    FULLTEXT `ft_search` (`title`, `content`, `excerpt`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'iqra_search_suggestions' => "
                CREATE TABLE IF NOT EXISTS `iqra_search_suggestions` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `query` VARCHAR(500) NOT NULL COMMENT 'Search query',
                    `suggestion` VARCHAR(500) NOT NULL COMMENT 'Suggested search term',
                    `type` ENUM('auto', 'manual', 'popular') DEFAULT 'auto' COMMENT 'Type of suggestion',
                    `usage_count` INT UNSIGNED DEFAULT 0 COMMENT 'Usage count',
                    `relevance_score` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Relevance score',
                    `is_active` BOOLEAN DEFAULT TRUE COMMENT 'Whether suggestion is active',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    
                    INDEX `idx_query` (`query`(100)),
                    INDEX `idx_suggestion` (`suggestion`(100)),
                    INDEX `idx_type` (`type`),
                    INDEX `idx_usage_count` (`usage_count`),
                    INDEX `idx_relevance` (`relevance_score`),
                    INDEX `idx_is_active` (`is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];

        foreach ($tables as $tableName => $sql) {
            try {
                $this->db->execute($sql);
                echo "  ✅ Table '{$tableName}' created/verified\n";
            } catch (Exception $e) {
                echo "  ❌ Failed to create table '{$tableName}': " . $e->getMessage() . "\n";
            }
        }
        echo "\n";
    }

    /**
     * Index wiki content
     */
    private function indexWikiContent(): void
    {
        echo "📚 Indexing Wiki Content...\n";

        $wikiContent = [
            [
                'id' => 1,
                'title' => 'Islamic Principles and Beliefs',
                'content' => 'Islam is based on five fundamental principles known as the Five Pillars of Islam. These include Shahada (declaration of faith), Salah (prayer), Zakat (charity), Sawm (fasting during Ramadan), and Hajj (pilgrimage to Mecca). These principles form the foundation of Islamic faith and practice.',
                'excerpt' => 'Learn about the Five Pillars of Islam and the fundamental principles that guide Muslim life and practice.',
                'url' => '/wiki/islamic-principles',
                'metadata' => ['author' => 'Islamic Scholars', 'tags' => ['islam', 'principles', 'five-pillars', 'faith'], 'category' => 'Aqeedah'],
                'relevance_score' => 95.00,
                'view_count' => 1250,
                'rating' => 4.85
            ],
            [
                'id' => 2,
                'title' => 'Quran Recitation and Tajweed',
                'content' => 'Tajweed is the science of reciting the Quran with proper pronunciation, intonation, and rhythm. It involves learning the correct articulation of Arabic letters, understanding the rules of elongation, and mastering the various types of stops and pauses. Proper tajweed enhances the beauty and meaning of Quranic recitation.',
                'excerpt' => 'Master the art of Quran recitation with proper tajweed rules and beautiful pronunciation techniques.',
                'url' => '/wiki/quran-tajweed',
                'metadata' => ['author' => 'Quran Teachers', 'tags' => ['quran', 'tajweed', 'recitation', 'arabic'], 'category' => 'Quran Studies'],
                'relevance_score' => 92.00,
                'view_count' => 890,
                'rating' => 4.78
            ],
            [
                'id' => 3,
                'title' => 'Hadith Authentication and Sciences',
                'content' => 'The science of Hadith involves the critical examination and authentication of prophetic traditions. Scholars use various criteria to determine the authenticity of hadith, including chain of narrators (isnad), narrator reliability, and text analysis. This science ensures the preservation of authentic Islamic teachings.',
                'excerpt' => 'Understand how Islamic scholars authenticate hadith and preserve the authenticity of prophetic traditions.',
                'url' => '/wiki/hadith-sciences',
                'metadata' => ['author' => 'Hadith Scholars', 'tags' => ['hadith', 'authentication', 'isnad', 'prophetic-traditions'], 'category' => 'Hadith Studies'],
                'relevance_score' => 88.00,
                'view_count' => 675,
                'rating' => 4.65
            ]
        ];

        $this->indexContentBatch('wiki', $wikiContent);
    }

    /**
     * Index Quran content
     */
    private function indexQuranContent(): void
    {
        echo "📖 Indexing Quran Content...\n";

        $quranContent = [
            [
                'id' => 1,
                'title' => 'Surah Al-Fatiha - The Opening',
                'content' => 'Bismillahi ar-rahmani ar-raheem. Al-hamdu lillahi rabbi al-alameen. Ar-rahmani ar-raheem. Maliki yawmi ad-deen. Iyyaka na\'budu wa iyyaka nasta\'een. Ihdina as-sirata al-mustaqeem. Sirata allatheena an\'amta alayhim ghayri al-maghdoobi alayhim wa la ad-dalleen.',
                'excerpt' => 'The opening chapter of the Quran, containing seven verses that are recited in every prayer.',
                'url' => '/quran/1',
                'metadata' => ['author' => 'Allah (SWT)', 'tags' => ['quran', 'surah', 'fatiha', 'opening'], 'category' => 'Quran', 'juz' => 1, 'page' => 1],
                'relevance_score' => 100.00,
                'view_count' => 5000,
                'rating' => 5.00
            ],
            [
                'id' => 2,
                'title' => 'Surah Al-Baqarah - The Cow',
                'content' => 'Alif-Lam-Meem. Thalika al-kitabu la rayba feehi hudan lil-muttaqeen. Alladheena yu\'minoona bil-ghaybi wa yuqeemoona as-salah wa mimma razaqnahum yunfiqoon. Wa alladheena yu\'minoona bima unzila ilayka wa ma unzila min qablika wa bil-akhirati hum yooqinoon.',
                'excerpt' => 'The longest chapter of the Quran, containing guidance for believers and stories of previous nations.',
                'url' => '/quran/2',
                'metadata' => ['author' => 'Allah (SWT)', 'tags' => ['quran', 'surah', 'baqarah', 'guidance'], 'category' => 'Quran', 'juz' => 1, 'page' => 2],
                'relevance_score' => 98.00,
                'view_count' => 3200,
                'rating' => 4.95
            ]
        ];

        $this->indexContentBatch('quran', $quranContent);
    }

    /**
     * Index Hadith content
     */
    private function indexHadithContent(): void
    {
        echo "📜 Indexing Hadith Content...\n";

        $hadithContent = [
            [
                'id' => 1,
                'title' => 'Hadith of Gabriel - Definition of Islam',
                'content' => 'Narrated by Umar ibn al-Khattab: While we were sitting with the Messenger of Allah (ﷺ), a man came to him and asked, "What is Islam?" The Messenger of Allah (ﷺ) said, "Islam is to testify that there is no god but Allah and that Muhammad is the Messenger of Allah, to establish prayer, to give charity, to fast during Ramadan, and to perform pilgrimage to the House if you are able."',
                'excerpt' => 'The famous Hadith of Gabriel that defines the five pillars of Islam and explains the religion.',
                'url' => '/hadith/1',
                'metadata' => ['author' => 'Prophet Muhammad (ﷺ)', 'narrator' => 'Umar ibn al-Khattab', 'tags' => ['hadith', 'gabriel', 'islam', 'five-pillars'], 'category' => 'Aqeedah', 'authenticity' => 'Sahih', 'collection' => 'Sahih Muslim'],
                'relevance_score' => 96.00,
                'view_count' => 2100,
                'rating' => 4.90
            ],
            [
                'id' => 2,
                'title' => 'Hadith of Abu Huraira - Actions by Intentions',
                'content' => 'Narrated by Abu Huraira: The Messenger of Allah (ﷺ) said, "Actions are judged by intentions, and every person will be rewarded according to what they intended. So whoever emigrated for Allah and His Messenger, his emigration will be for Allah and His Messenger. And whoever emigrated for worldly gain or to marry a woman, his emigration will be for what he emigrated for."',
                'excerpt' => 'The fundamental hadith about intentions and how they determine the value of our actions.',
                'url' => '/hadith/2',
                'metadata' => ['author' => 'Prophet Muhammad (ﷺ)', 'narrator' => 'Abu Huraira', 'tags' => ['hadith', 'intentions', 'actions', 'rewards'], 'category' => 'Akhlaq', 'authenticity' => 'Sahih', 'collection' => 'Sahih Bukhari'],
                'relevance_score' => 94.00,
                'view_count' => 1800,
                'rating' => 4.88
            ]
        ];

        $this->indexContentBatch('hadith', $hadithContent);
    }

    /**
     * Index article content
     */
    private function indexArticleContent(): void
    {
        echo "📝 Indexing Article Content...\n";

        $articleContent = [
            [
                'id' => 1,
                'title' => 'The Importance of Islamic Education in Modern Times',
                'content' => 'In today\'s rapidly changing world, Islamic education plays a crucial role in providing moral guidance and spiritual foundation. Modern challenges require Muslims to understand their faith deeply while engaging constructively with contemporary issues. Islamic education helps develop critical thinking, moral reasoning, and spiritual awareness.',
                'excerpt' => 'Explore why Islamic education is essential in modern times and how it addresses contemporary challenges.',
                'url' => '/articles/islamic-education-modern-times',
                'metadata' => ['author' => 'Dr. Ahmed Al-Rashid', 'tags' => ['education', 'islam', 'modern-times', 'morality'], 'category' => 'Education', 'publish_date' => '2025-01-15', 'read_time' => '8 minutes'],
                'relevance_score' => 87.00,
                'view_count' => 450,
                'rating' => 4.60
            ],
            [
                'id' => 2,
                'title' => 'Islamic Finance: Principles and Modern Applications',
                'content' => 'Islamic finance is based on principles that prohibit interest (riba), excessive uncertainty (gharar), and gambling (maysir). Modern Islamic banking offers alternatives like profit-sharing, asset-backed financing, and ethical investment. These principles ensure financial transactions are fair, transparent, and beneficial to society.',
                'excerpt' => 'Learn about Islamic finance principles and how they apply to modern banking and investment.',
                'url' => '/articles/islamic-finance-principles',
                'metadata' => ['author' => 'Ustadh Fatima Zahra', 'tags' => ['finance', 'islamic-banking', 'riba', 'ethical-investment'], 'category' => 'Finance', 'publish_date' => '2025-01-18', 'read_time' => '12 minutes'],
                'relevance_score' => 85.00,
                'view_count' => 380,
                'rating' => 4.55
            ]
        ];

        $this->indexContentBatch('article', $articleContent);
    }

    /**
     * Index scholar content
     */
    private function indexScholarContent(): void
    {
        echo "👨‍🏫 Indexing Scholar Content...\n";

        $scholarContent = [
            [
                'id' => 1,
                'title' => 'Imam Abu Hanifa - Founder of Hanafi School',
                'content' => 'Imam Abu Hanifa (699-767 CE) was one of the most influential Islamic scholars and the founder of the Hanafi school of Islamic jurisprudence. Known for his logical reasoning and systematic approach to Islamic law, he emphasized the use of qiyas (analogical reasoning) and istihsan (juristic preference) in deriving legal rulings.',
                'excerpt' => 'Biography of Imam Abu Hanifa, the great Islamic scholar and founder of the Hanafi school of thought.',
                'url' => '/scholars/abu-hanifa',
                'metadata' => ['author' => 'Islamic Historians', 'tags' => ['scholar', 'imam', 'hanafi', 'fiqh', 'jurisprudence'], 'category' => 'Biography', 'birth_year' => 699, 'death_year' => 767, 'school' => 'Hanafi'],
                'relevance_score' => 89.00,
                'view_count' => 620,
                'rating' => 4.70
            ]
        ];

        $this->indexContentBatch('scholar', $scholarContent);
    }

    /**
     * Index a batch of content
     */
    private function indexContentBatch(string $contentType, array $contentItems): void
    {
        $this->indexStats['by_type'][$contentType] = 0;

        foreach ($contentItems as $content) {
            try {
                $this->indexContent($contentType, $content);
                $this->indexStats['by_type'][$contentType]++;
                $this->indexStats['successful']++;
            } catch (Exception $e) {
                $this->indexStats['failed']++;
                echo "  ❌ Failed to index {$contentType} content '{$content['title']}': " . $e->getMessage() . "\n";
            }
        }

        $this->indexStats['total'] += count($contentItems);
        echo "  ✅ Indexed " . count($contentItems) . " {$contentType} content items\n";
    }

    /**
     * Index individual content item
     */
    private function indexContent(string $contentType, array $content): void
    {
        // Check if content already exists
        $existing = $this->db->queryOne(
            "SELECT id FROM iqra_search_index WHERE content_type = ? AND content_id = ?",
            [$contentType, $content['id']]
        );

        if ($existing) {
            // Update existing content
            $this->updateIndexedContent($contentType, $content);
        } else {
            // Insert new content
            $this->insertIndexedContent($contentType, $content);
        }
    }

    /**
     * Insert new content into search index
     */
    private function insertIndexedContent(string $contentType, array $content): void
    {
        $searchVector = $this->createSearchVector($content['title'], $content['content']);
        
        $sql = "INSERT INTO iqra_search_index (
            content_type, content_id, title, content, excerpt, url, metadata,
            search_vector, relevance_score, view_count, rating
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $contentType,
            $content['id'],
            $content['title'],
            $content['content'],
            $content['excerpt'],
            $content['url'],
            json_encode($content['metadata']),
            $searchVector,
            $content['relevance_score'],
            $content['view_count'],
            $content['rating']
        ];
        
        $this->db->execute($sql, $params);
    }

    /**
     * Update existing content in search index
     */
    private function updateIndexedContent(string $contentType, array $content): void
    {
        $searchVector = $this->createSearchVector($content['title'], $content['content']);
        
        $sql = "UPDATE iqra_search_index SET
            title = ?, content = ?, excerpt = ?, url = ?, metadata = ?,
            search_vector = ?, relevance_score = ?, view_count = ?, rating = ?,
            last_updated = CURRENT_TIMESTAMP
            WHERE content_type = ? AND content_id = ?";
        
        $params = [
            $content['title'],
            $content['content'],
            $content['excerpt'],
            $content['url'],
            json_encode($content['metadata']),
            $searchVector,
            $content['relevance_score'],
            $content['view_count'],
            $content['rating'],
            $contentType,
            $content['id']
        ];
        
        $this->db->execute($sql, $params);
    }

    /**
     * Create search vector for full-text search
     */
    private function createSearchVector(string $title, string $content): string
    {
        // Combine title and content with different weights
        $titleWeight = 3; // Title is more important
        $contentWeight = 1;
        
        $vector = str_repeat($title . ' ', $titleWeight) . str_repeat($content . ' ', $contentWeight);
        
        // Normalize the vector
        $vector = strtolower($vector);
        $vector = preg_replace('/\s+/', ' ', $vector);
        
        return trim($vector);
    }

    /**
     * Generate search suggestions
     */
    private function generateSearchSuggestions(): void
    {
        echo "\n💡 Generating Search Suggestions...\n";

        $suggestions = [
            ['query' => 'islam', 'suggestion' => 'islamic principles', 'type' => 'popular'],
            ['query' => 'islam', 'suggestion' => 'islamic education', 'type' => 'popular'],
            ['query' => 'quran', 'suggestion' => 'quran recitation', 'type' => 'popular'],
            ['query' => 'quran', 'suggestion' => 'quran translation', 'type' => 'popular'],
            ['query' => 'hadith', 'suggestion' => 'hadith authentication', 'type' => 'popular'],
            ['query' => 'hadith', 'suggestion' => 'hadith sciences', 'type' => 'popular'],
            ['query' => 'prayer', 'suggestion' => 'salah method', 'type' => 'auto'],
            ['query' => 'prayer', 'suggestion' => 'daily prayers', 'type' => 'auto'],
            ['query' => 'fasting', 'suggestion' => 'ramadan fasting', 'type' => 'auto'],
            ['query' => 'fasting', 'suggestion' => 'sawm rules', 'type' => 'auto'],
            ['query' => 'charity', 'suggestion' => 'zakat calculation', 'type' => 'auto'],
            ['query' => 'charity', 'suggestion' => 'sadaqah', 'type' => 'auto']
        ];

        foreach ($suggestions as $suggestion) {
            try {
                $this->insertSearchSuggestion($suggestion);
            } catch (Exception $e) {
                echo "  ❌ Failed to insert suggestion: " . $e->getMessage() . "\n";
            }
        }

        echo "  ✅ Generated " . count($suggestions) . " search suggestions\n";
    }

    /**
     * Insert search suggestion
     */
    private function insertSearchSuggestion(array $suggestion): void
    {
        // Check if suggestion already exists
        $existing = $this->db->queryOne(
            "SELECT id FROM iqra_search_suggestions WHERE query = ? AND suggestion = ?",
            [$suggestion['query'], $suggestion['suggestion']]
        );

        if (!$existing) {
            $sql = "INSERT INTO iqra_search_suggestions (
                query, suggestion, type, usage_count, relevance_score
            ) VALUES (?, ?, ?, ?, ?)";
            
            $params = [
                $suggestion['query'],
                $suggestion['suggestion'],
                $suggestion['type'],
                rand(10, 100), // Random usage count for demo
                rand(70, 95) / 10 // Random relevance score for demo
            ];
            
            $this->db->execute($sql, $params);
        }
    }

    /**
     * Display indexing statistics
     */
    private function displayIndexingStats(): void
    {
        echo "\n📊 Indexing Statistics\n";
        echo "=====================\n";
        echo "Total Content Items: {$this->indexStats['total']}\n";
        echo "Successfully Indexed: {$this->indexStats['successful']}\n";
        echo "Failed: {$this->indexStats['failed']}\n";
        echo "\nBy Content Type:\n";
        
        foreach ($this->indexStats['by_type'] as $type => $count) {
            echo "  {$type}: {$count} items\n";
        }
    }
}

// Run content indexing if called directly
if (php_sapi_name() === 'cli') {
    $indexer = new ContentIndexer();
    $indexer->run();
} 