<?php

declare(strict_types=1);

/**
 * Seed Search Data for IqraSearchExtension
 * 
 * @package IslamWiki\Extensions\IqraSearchExtension\Database
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

class SearchDataSeeder
{
    private array $sampleContent = [];

    public function __construct()
    {
        $this->initializeSampleContent();
    }

    /**
     * Initialize sample content for search testing
     */
    private function initializeSampleContent(): void
    {
        $this->sampleContent = [
            // Wiki Content
            [
                'content_type' => 'wiki',
                'content_id' => 1,
                'title' => 'Islamic Principles and Beliefs',
                'content' => 'Islam is based on five fundamental principles known as the Five Pillars of Islam. These include Shahada (declaration of faith), Salah (prayer), Zakat (charity), Sawm (fasting during Ramadan), and Hajj (pilgrimage to Mecca). These principles form the foundation of Islamic faith and practice.',
                'excerpt' => 'Learn about the Five Pillars of Islam and the fundamental principles that guide Muslim life and practice.',
                'url' => '/wiki/islamic-principles',
                'metadata' => json_encode([
                    'author' => 'Islamic Scholars',
                    'tags' => ['islam', 'principles', 'five-pillars', 'faith'],
                    'category' => 'Aqeedah',
                    'language' => 'en'
                ]),
                'relevance_score' => 95.00,
                'view_count' => 1250,
                'rating' => 4.85
            ],
            [
                'content_type' => 'wiki',
                'content_id' => 2,
                'title' => 'Quran Recitation and Tajweed',
                'content' => 'Tajweed is the science of reciting the Quran with proper pronunciation, intonation, and rhythm. It involves learning the correct articulation of Arabic letters, understanding the rules of elongation, and mastering the various types of stops and pauses. Proper tajweed enhances the beauty and meaning of Quranic recitation.',
                'excerpt' => 'Master the art of Quran recitation with proper tajweed rules and beautiful pronunciation techniques.',
                'url' => '/wiki/quran-tajweed',
                'metadata' => json_encode([
                    'author' => 'Quran Teachers',
                    'tags' => ['quran', 'tajweed', 'recitation', 'arabic'],
                    'category' => 'Quran Studies',
                    'language' => 'en'
                ]),
                'relevance_score' => 92.00,
                'view_count' => 890,
                'rating' => 4.78
            ],
            [
                'content_type' => 'wiki',
                'content_id' => 3,
                'title' => 'Hadith Authentication and Sciences',
                'content' => 'The science of Hadith involves the critical examination and authentication of prophetic traditions. Scholars use various criteria to determine the authenticity of hadith, including chain of narrators (isnad), narrator reliability, and text analysis. This science ensures the preservation of authentic Islamic teachings.',
                'excerpt' => 'Understand how Islamic scholars authenticate hadith and preserve the authenticity of prophetic traditions.',
                'url' => '/wiki/hadith-sciences',
                'metadata' => json_encode([
                    'author' => 'Hadith Scholars',
                    'tags' => ['hadith', 'authentication', 'isnad', 'prophetic-traditions'],
                    'category' => 'Hadith Studies',
                    'language' => 'en'
                ]),
                'relevance_score' => 88.00,
                'view_count' => 675,
                'rating' => 4.65
            ],

            // Quran Content
            [
                'content_type' => 'quran',
                'content_id' => 1,
                'title' => 'Surah Al-Fatiha - The Opening',
                'content' => 'Bismillahi ar-rahmani ar-raheem. Al-hamdu lillahi rabbi al-alameen. Ar-rahmani ar-raheem. Maliki yawmi ad-deen. Iyyaka na\'budu wa iyyaka nasta\'een. Ihdina as-sirata al-mustaqeem. Sirata allatheena an\'amta alayhim ghayri al-maghdoobi alayhim wa la ad-dalleen.',
                'excerpt' => 'The opening chapter of the Quran, containing seven verses that are recited in every prayer.',
                'url' => '/quran/1',
                'metadata' => json_encode([
                    'author' => 'Allah (SWT)',
                    'tags' => ['quran', 'surah', 'fatiha', 'opening'],
                    'category' => 'Quran',
                    'language' => 'ar',
                    'juz' => 1,
                    'page' => 1
                ]),
                'relevance_score' => 100.00,
                'view_count' => 5000,
                'rating' => 5.00
            ],
            [
                'content_type' => 'quran',
                'content_id' => 2,
                'title' => 'Surah Al-Baqarah - The Cow',
                'content' => 'Alif-Lam-Meem. Thalika al-kitabu la rayba feehi hudan lil-muttaqeen. Alladheena yu\'minoona bil-ghaybi wa yuqeemoona as-salah wa mimma razaqnahum yunfiqoon. Wa alladheena yu\'minoona bima unzila ilayka wa ma unzila min qablika wa bil-akhirati hum yooqinoon.',
                'excerpt' => 'The longest chapter of the Quran, containing guidance for believers and stories of previous nations.',
                'url' => '/quran/2',
                'metadata' => json_encode([
                    'author' => 'Allah (SWT)',
                    'tags' => ['quran', 'surah', 'baqarah', 'guidance'],
                    'category' => 'Quran',
                    'language' => 'ar',
                    'juz' => 1,
                    'page' => 2
                ]),
                'relevance_score' => 98.00,
                'view_count' => 3200,
                'rating' => 4.95
            ],

            // Hadith Content
            [
                'content_type' => 'hadith',
                'content_id' => 1,
                'title' => 'Hadith of Gabriel - Definition of Islam',
                'content' => 'Narrated by Umar ibn al-Khattab: While we were sitting with the Messenger of Allah (ﷺ), a man came to him and asked, "What is Islam?" The Messenger of Allah (ﷺ) said, "Islam is to testify that there is no god but Allah and that Muhammad is the Messenger of Allah, to establish prayer, to give charity, to fast during Ramadan, and to perform pilgrimage to the House if you are able."',
                'excerpt' => 'The famous Hadith of Gabriel that defines the five pillars of Islam and explains the religion.',
                'url' => '/hadith/1',
                'metadata' => json_encode([
                    'author' => 'Prophet Muhammad (ﷺ)',
                    'narrator' => 'Umar ibn al-Khattab',
                    'tags' => ['hadith', 'gabriel', 'islam', 'five-pillars'],
                    'category' => 'Aqeedah',
                    'language' => 'en',
                    'authenticity' => 'Sahih',
                    'collection' => 'Sahih Muslim'
                ]),
                'relevance_score' => 96.00,
                'view_count' => 2100,
                'rating' => 4.90
            ],
            [
                'content_type' => 'hadith',
                'content_id' => 2,
                'title' => 'Hadith of Abu Huraira - Actions by Intentions',
                'content' => 'Narrated by Abu Huraira: The Messenger of Allah (ﷺ) said, "Actions are judged by intentions, and every person will be rewarded according to what they intended. So whoever emigrated for Allah and His Messenger, his emigration will be for Allah and His Messenger. And whoever emigrated for worldly gain or to marry a woman, his emigration will be for what he emigrated for."',
                'excerpt' => 'The fundamental hadith about intentions and how they determine the value of our actions.',
                'url' => '/hadith/2',
                'metadata' => json_encode([
                    'author' => 'Prophet Muhammad (ﷺ)',
                    'narrator' => 'Abu Huraira',
                    'tags' => ['hadith', 'intentions', 'actions', 'rewards'],
                    'category' => 'Akhlaq',
                    'language' => 'en',
                    'authenticity' => 'Sahih',
                    'collection' => 'Sahih Bukhari'
                ]),
                'relevance_score' => 94.00,
                'view_count' => 1800,
                'rating' => 4.88
            ],

            // Article Content
            [
                'content_type' => 'article',
                'content_id' => 1,
                'title' => 'The Importance of Islamic Education in Modern Times',
                'content' => 'In today\'s rapidly changing world, Islamic education plays a crucial role in providing moral guidance and spiritual foundation. Modern challenges require Muslims to understand their faith deeply while engaging constructively with contemporary issues. Islamic education helps develop critical thinking, moral reasoning, and spiritual awareness.',
                'excerpt' => 'Explore why Islamic education is essential in modern times and how it addresses contemporary challenges.',
                'url' => '/articles/islamic-education-modern-times',
                'metadata' => json_encode([
                    'author' => 'Dr. Ahmed Al-Rashid',
                    'tags' => ['education', 'islam', 'modern-times', 'morality'],
                    'category' => 'Education',
                    'language' => 'en',
                    'publish_date' => '2025-01-15',
                    'read_time' => '8 minutes'
                ]),
                'relevance_score' => 87.00,
                'view_count' => 450,
                'rating' => 4.60
            ],
            [
                'content_type' => 'article',
                'content_id' => 2,
                'title' => 'Islamic Finance: Principles and Modern Applications',
                'content' => 'Islamic finance is based on principles that prohibit interest (riba), excessive uncertainty (gharar), and gambling (maysir). Modern Islamic banking offers alternatives like profit-sharing, asset-backed financing, and ethical investment. These principles ensure financial transactions are fair, transparent, and beneficial to society.',
                'excerpt' => 'Learn about Islamic finance principles and how they apply to modern banking and investment.',
                'url' => '/articles/islamic-finance-principles',
                'metadata' => json_encode([
                    'author' => 'Ustadh Fatima Zahra',
                    'tags' => ['finance', 'islamic-banking', 'riba', 'ethical-investment'],
                    'category' => 'Finance',
                    'language' => 'en',
                    'publish_date' => '2025-01-18',
                    'read_time' => '12 minutes'
                ]),
                'relevance_score' => 85.00,
                'view_count' => 380,
                'rating' => 4.55
            ],

            // Scholar Content
            [
                'content_type' => 'scholar',
                'content_id' => 1,
                'title' => 'Imam Abu Hanifa - Founder of Hanafi School',
                'content' => 'Imam Abu Hanifa (699-767 CE) was one of the most influential Islamic scholars and the founder of the Hanafi school of Islamic jurisprudence. Known for his logical reasoning and systematic approach to Islamic law, he emphasized the use of qiyas (analogical reasoning) and istihsan (juristic preference) in deriving legal rulings.',
                'excerpt' => 'Biography of Imam Abu Hanifa, the great Islamic scholar and founder of the Hanafi school of thought.',
                'url' => '/scholars/abu-hanifa',
                'metadata' => json_encode([
                    'author' => 'Islamic Historians',
                    'tags' => ['scholar', 'imam', 'hanafi', 'fiqh', 'jurisprudence'],
                    'category' => 'Biography',
                    'language' => 'en',
                    'birth_year' => 699,
                    'death_year' => 767,
                    'school' => 'Hanafi'
                ]),
                'relevance_score' => 89.00,
                'view_count' => 620,
                'rating' => 4.70
            ]
        ];
    }

    /**
     * Seed the search index with sample content
     */
    public function seedSearchIndex(): void
    {
        foreach ($this->sampleContent as $content) {
            $this->insertSearchIndex($content);
        }
        
        echo "Search index seeded with " . count($this->sampleContent) . " content items.\n";
    }

    /**
     * Seed search suggestions
     */
    public function seedSearchSuggestions(): void
    {
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
            $this->insertSearchSuggestion($suggestion);
        }
        
        echo "Search suggestions seeded with " . count($suggestions) . " suggestions.\n";
    }

    /**
     * Insert content into search index
     */
    private function insertSearchIndex(array $content): void
    {
        // Create search vector for full-text search
        $searchVector = $this->createSearchVector($content['title'], $content['content']);
        
        $sql = "INSERT INTO iqra_search_index (
            content_type, content_id, title, content, excerpt, url, metadata,
            search_vector, relevance_score, view_count, rating
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $content['content_type'],
            $content['content_id'],
            $content['title'],
            $content['content'],
            $content['excerpt'],
            $content['url'],
            $content['metadata'],
            $searchVector,
            $content['relevance_score'],
            $content['view_count'],
            $content['rating']
        ];
        
        // TODO: Implement actual database insertion
        error_log("Would insert search index: " . json_encode($content['title']));
    }

    /**
     * Insert search suggestion
     */
    private function insertSearchSuggestion(array $suggestion): void
    {
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
        
        // TODO: Implement actual database insertion
        error_log("Would insert suggestion: " . json_encode($suggestion));
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
     * Run all seeding operations
     */
    public function run(): void
    {
        echo "Starting search data seeding...\n";
        
        $this->seedSearchIndex();
        $this->seedSearchSuggestions();
        
        echo "Search data seeding completed successfully!\n";
    }
}

// Run seeder if called directly
if (php_sapi_name() === 'cli') {
    $seeder = new SearchDataSeeder();
    $seeder->run();
} 