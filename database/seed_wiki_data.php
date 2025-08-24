<?php

declare(strict_types=1);

/**
 * Wiki Data Seeder
 * 
 * This script populates the wiki tables with sample data for testing.
 * Run with: php database/seed_wiki_data.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Database configuration
$config = [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

try {
    // Connect to database
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    echo "Connected to database: {$config['database']}\n";
    echo "Seeding wiki tables with sample data...\n\n";
    
    $pdo->beginTransaction();
    
    // 1. Create sample categories
    echo "1️⃣ Creating sample categories...\n";
    
    $categories = [
        [
            'name' => 'Islamic History',
            'slug' => 'islamic-history',
            'description' => 'Articles about Islamic history and civilization',
            'icon' => 'fas fa-landmark',
            'color' => '#007cba',
            'is_featured' => true
        ],
        [
            'name' => 'Islamic Sciences',
            'slug' => 'islamic-sciences',
            'description' => 'Islamic contributions to science and knowledge',
            'icon' => 'fas fa-microscope',
            'color' => '#28a745',
            'is_featured' => true
        ],
        [
            'name' => 'Islamic Art & Architecture',
            'slug' => 'islamic-art-architecture',
            'description' => 'Beautiful Islamic art, calligraphy, and architecture',
            'icon' => 'fas fa-mosque',
            'color' => '#ffc107',
            'is_featured' => true
        ],
        [
            'name' => 'Islamic Ethics',
            'slug' => 'islamic-ethics',
            'description' => 'Islamic moral teachings and ethical principles',
            'icon' => 'fas fa-heart',
            'color' => '#dc3545',
            'is_featured' => false
        ],
        [
            'name' => 'Islamic Literature',
            'slug' => 'islamic-literature',
            'description' => 'Classical and modern Islamic literature',
            'icon' => 'fas fa-book-open',
            'color' => '#6f42c1',
            'is_featured' => false
        ]
    ];
    
    $categoryIds = [];
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("
            INSERT INTO wiki_categories (name, slug, description, icon, color, is_featured, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $category['name'],
            $category['slug'],
            $category['description'],
            $category['icon'],
            $category['color'],
            $category['is_featured'] ? 1 : 0
        ]);
        $categoryIds[] = $pdo->lastInsertId();
        echo "   ✅ Created category: {$category['name']}\n";
    }
    
    // 2. Create sample wiki pages
    echo "\n2️⃣ Creating sample wiki pages...\n";
    
    $pages = [
        [
            'title' => 'The Golden Age of Islam',
            'slug' => 'golden-age-of-islam',
            'content' => "# The Golden Age of Islam\n\n## Overview\n\nThe Golden Age of Islam, also known as the Islamic Golden Age, was a period of cultural, economic, and scientific flourishing in the history of Islam, traditionally dated from the 8th century to the 14th century.\n\n## Key Achievements\n\n### Science and Mathematics\n- Development of algebra and trigonometry\n- Advances in astronomy and medicine\n- Translation and preservation of ancient knowledge\n\n### Art and Architecture\n- Beautiful mosques and palaces\n- Intricate geometric patterns\n- Calligraphy as an art form\n\n### Literature and Philosophy\n- Works of Ibn Sina (Avicenna)\n- Poetry of Rumi and Hafez\n- Philosophical treatises\n\n## Legacy\n\nThis period laid the foundation for the European Renaissance and continues to influence modern science and culture.",
            'meta_description' => 'Explore the remarkable achievements of the Islamic Golden Age, from science and mathematics to art and architecture.',
            'content_type' => 'article',
            'category_id' => $categoryIds[0], // Islamic History
            'is_featured' => true
        ],
        [
            'title' => 'Islamic Contributions to Mathematics',
            'slug' => 'islamic-contributions-mathematics',
            'content' => "# Islamic Contributions to Mathematics\n\n## Introduction\n\nIslamic scholars made significant contributions to mathematics during the Golden Age, building upon Greek, Indian, and Persian mathematical traditions.\n\n## Key Developments\n\n### Algebra\n- Al-Khwarizmi's work on algebra (from which we get the word 'algorithm')\n- Systematic solutions to linear and quadratic equations\n- Development of algebraic notation\n\n### Geometry\n- Advanced geometric constructions\n- Work on conic sections\n- Geometric proofs and theorems\n\n### Number Systems\n- Introduction of the decimal system\n- Development of Arabic numerals\n- Concept of zero\n\n## Famous Mathematicians\n\n1. **Al-Khwarizmi** (780-850 CE)\n2. **Omar Khayyam** (1048-1131 CE)\n3. **Al-Kashi** (1380-1429 CE)\n\n## Impact\n\nThese contributions formed the basis for modern mathematics and were transmitted to Europe through translations.",
            'meta_description' => 'Discover how Islamic mathematicians revolutionized algebra, geometry, and number systems during the Golden Age.',
            'content_type' => 'article',
            'category_id' => $categoryIds[1], // Islamic Sciences
            'is_featured' => true
        ],
        [
            'title' => 'The Beauty of Islamic Calligraphy',
            'slug' => 'islamic-calligraphy',
            'content' => "# The Beauty of Islamic Calligraphy\n\n## Introduction\n\nIslamic calligraphy is one of the most revered art forms in Islamic culture, combining aesthetic beauty with religious significance.\n\n## Styles of Calligraphy\n\n### Kufic\n- Oldest form of Arabic calligraphy\n- Angular and geometric\n- Used in early Quran manuscripts\n\n### Naskh\n- Most readable style\n- Used in printed materials\n- Balanced proportions\n\n### Thuluth\n- Large, decorative style\n- Used in architectural inscriptions\n- Elegant and flowing\n\n### Diwani\n- Highly ornamental\n- Used in official documents\n- Complex and artistic\n\n## Materials and Tools\n\n- **Qalam**: Traditional reed pen\n- **Ink**: Made from natural materials\n- **Paper**: High-quality, often decorated\n- **Ruler and compass**: For geometric layouts\n\n## Spiritual Significance\n\nCalligraphy is considered a form of worship, as it involves writing the words of Allah and Islamic texts with beauty and reverence.",
            'meta_description' => 'Explore the art of Islamic calligraphy, from its various styles to its spiritual significance in Islamic culture.',
            'content_type' => 'article',
            'category_id' => $categoryIds[2], // Islamic Art & Architecture
            'is_featured' => true
        ],
        [
            'title' => 'Islamic Ethics: The Path to Virtue',
            'slug' => 'islamic-ethics-path-virtue',
            'content' => "# Islamic Ethics: The Path to Virtue\n\n## Core Principles\n\nIslamic ethics is based on the Quran and the teachings of Prophet Muhammad (peace be upon him), emphasizing moral character and righteous behavior.\n\n## Key Virtues\n\n### Piety (God-Consciousness)\n- Awareness of Allah in all actions\n- Container of Islamic ethics\n- Guides moral decision-making\n\n### Justice (Justice)\n- Fair treatment of all people\n- Standing for truth\n- Opposing oppression\n\n### Ihsan (Excellence)\n- Doing things in the best way\n- Striving for perfection\n- Beautiful character\n\n### Mercy (Mercy)\n- Compassion for all creation\n- Kindness to others\n- Forgiving nature\n\n## Practical Applications\n\n- **Business Ethics**: Honest trade, fair pricing\n- **Social Ethics**: Helping neighbors, caring for family\n- **Environmental Ethics**: Respecting nature, avoiding waste\n- **Personal Ethics**: Self-discipline, continuous improvement\n\n## Benefits\n\nFollowing Islamic ethics leads to:\n- Inner peace and contentment\n- Strong community bonds\n- Divine pleasure and reward\n- Success in this life and the next",
            'meta_description' => 'Learn about Islamic ethics and how they provide a comprehensive framework for virtuous living and moral excellence.',
            'content_type' => 'article',
            'category_id' => $categoryIds[3], // Islamic Ethics
            'is_featured' => false
        ],
        [
            'title' => 'Classical Islamic Literature',
            'slug' => 'classical-islamic-literature',
            'content' => "# Classical Islamic Literature\n\n## Overview\n\nClassical Islamic literature encompasses a vast body of works spanning poetry, prose, philosophy, and religious texts, reflecting the rich cultural heritage of the Islamic world.\n\n## Major Genres\n\n### Poetry\n- **Qasida**: Long poems with complex meters\n- **Ghazal**: Lyrical love poetry\n- **Rubai**: Four-line philosophical verses\n\n### Prose\n- **Adab**: Literature of manners and culture\n- **Tafsir**: Quranic commentary\n- **Hadith**: Collections of prophetic sayings\n\n### Philosophy\n- **Kalam**: Islamic theology\n- **Falsafa**: Islamic philosophy\n- **Sufism**: Mystical literature\n\n## Famous Works\n\n1. **The Quran**: The holy book of Islam\n2. **The Masnavi**: Rumi's spiritual masterpiece\n3. **The Rubaiyat**: Omar Khayyam's philosophical verses\n4. **The Thousand and One Nights**: Collection of folk tales\n5. **The Book of Kings**: Persian epic poetry\n\n## Themes\n\n- Love and devotion\n- Wisdom and knowledge\n- Justice and morality\n- Beauty and nature\n- Spiritual journey\n\n## Legacy\n\nClassical Islamic literature continues to inspire readers worldwide and has influenced many other literary traditions.",
            'meta_description' => 'Discover the rich world of classical Islamic literature, from poetry and prose to philosophy and religious texts.',
            'content_type' => 'article',
            'category_id' => $categoryIds[4], // Islamic Literature
            'is_featured' => false
        ]
    ];
    
    $pageIds = [];
    foreach ($pages as $page) {
        $stmt = $pdo->prepare("
            INSERT INTO wiki_pages (title, slug, content, meta_description, content_type, is_featured, status, published_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'published', NOW(), NOW(), NOW())
        ");
        $stmt->execute([
            $page['title'],
            $page['slug'],
            $page['content'],
            $page['meta_description'],
            $page['content_type'],
            $page['is_featured'] ? 1 : 0
        ]);
        $pageIds[] = $pdo->lastInsertId();
            echo "   ✅ Created page: {$page['title']}\n";
}

// 2.5. Link pages to categories
echo "\n2.5️⃣ Linking pages to categories...\n";

$pageCategories = [
    [$pageIds[0], $categoryIds[0]], // Golden Age - Islamic History
    [$pageIds[1], $categoryIds[1]], // Mathematics - Islamic Sciences
    [$pageIds[2], $categoryIds[2]], // Calligraphy - Islamic Art & Architecture
    [$pageIds[3], $categoryIds[3]], // Ethics - Islamic Ethics
    [$pageIds[4], $categoryIds[4]]  // Literature - Islamic Literature
];

foreach ($pageCategories as $pageCategory) {
    $stmt = $pdo->prepare("
        INSERT INTO wiki_page_categories (page_id, category_id, created_at, updated_at)
        VALUES (?, ?, NOW(), NOW())
    ");
    $stmt->execute($pageCategory);
}
echo "   ✅ Linked pages to categories\n";
    
// 3. Create sample tags
    echo "\n3️⃣ Creating sample tags...\n";
    
    $tags = [
        ['name' => 'History', 'slug' => 'history', 'color' => '#007cba'],
        ['name' => 'Science', 'slug' => 'science', 'color' => '#28a745'],
        ['name' => 'Art', 'slug' => 'art', 'color' => '#ffc107'],
        ['name' => 'Culture', 'slug' => 'culture', 'color' => '#6f42c1'],
        ['name' => 'Philosophy', 'slug' => 'philosophy', 'color' => '#dc3545'],
        ['name' => 'Literature', 'slug' => 'literature', 'color' => '#fd7e14'],
        ['name' => 'Architecture', 'slug' => 'architecture', 'color' => '#20c997'],
        ['name' => 'Mathematics', 'slug' => 'mathematics', 'color' => '#e83e8c']
    ];
    
    $tagIds = [];
    foreach ($tags as $tag) {
        $stmt = $pdo->prepare("
            INSERT INTO wiki_tags (name, slug, color, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$tag['name'], $tag['slug'], $tag['color']]);
        $tagIds[] = $pdo->lastInsertId();
        echo "   ✅ Created tag: {$tag['name']}\n";
    }
    
    // 4. Link pages to tags
    echo "\n4️⃣ Linking pages to tags...\n";
    
    $pageTags = [
        [$pageIds[0], $tagIds[0]], // Golden Age - History
        [$pageIds[0], $tagIds[2]], // Golden Age - Art
        [$pageIds[0], $tagIds[3]], // Golden Age - Culture
        [$pageIds[1], $tagIds[1]], // Mathematics - Science
        [$pageIds[1], $tagIds[7]], // Mathematics - Mathematics
        [$pageIds[2], $tagIds[2]], // Calligraphy - Art
        [$pageIds[2], $tagIds[3]], // Calligraphy - Culture
        [$pageIds[3], $tagIds[4]], // Ethics - Philosophy
        [$pageIds[3], $tagIds[3]], // Ethics - Culture
        [$pageIds[4], $tagIds[5]], // Literature - Literature
        [$pageIds[4], $tagIds[3]]  // Literature - Culture
    ];
    
    foreach ($pageTags as $pageTag) {
        $stmt = $pdo->prepare("
            INSERT INTO wiki_page_tags (page_id, tag_id, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
        ");
        $stmt->execute($pageTag);
    }
    echo "   ✅ Linked pages to tags\n";
    
    // 5. Create sample revisions
    echo "\n5️⃣ Creating sample revisions...\n";
    
    foreach ($pageIds as $pageId) {
        $stmt = $pdo->prepare("
            INSERT INTO wiki_revisions (page_id, revision_number, content, is_current, created_at, updated_at)
            SELECT ?, 1, content, 1, created_at, updated_at
            FROM wiki_pages WHERE id = ?
        ");
        $stmt->execute([$pageId, $pageId]);
    }
    echo "   ✅ Created initial revisions for all pages\n";
    
    // 6. Create sample page views
    echo "\n6️⃣ Creating sample page views...\n";
    
    foreach ($pageIds as $pageId) {
        // Create 5-15 views per page
        $viewCount = rand(5, 15);
        for ($i = 0; $i < $viewCount; $i++) {
            $stmt = $pdo->prepare("
                INSERT INTO wiki_page_views (page_id, viewed_at)
                VALUES (?, DATE_SUB(NOW(), INTERVAL ? HOUR))
            ");
            $stmt->execute([$pageId, rand(1, 168)]); // Random time in last week
        }
    }
    echo "   ✅ Created sample page views\n";
    
    $pdo->commit();
    
    echo "\n🎉 Wiki data seeding completed successfully!\n";
    echo "\n📊 Summary:\n";
    echo "   - Categories: " . count($categories) . "\n";
    echo "   - Pages: " . count($pages) . "\n";
    echo "   - Tags: " . count($tags) . "\n";
    echo "   - Page-Tag Links: " . count($pageTags) . "\n";
    echo "   - Revisions: " . count($pageIds) . "\n";
    echo "   - Page Views: " . (array_sum(array_map(fn($id) => rand(5, 15), $pageIds))) . "\n";
    
    echo "\n🚀 Your wiki is now ready with sample content!\n";
    echo "   Visit /wiki to see the homepage\n";
    echo "   Visit /wiki/golden-age-of-islam to see a sample page\n";
    echo "   Visit /wiki/categories to browse categories\n";
    echo "   Visit /wiki/search to test search functionality\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    echo "❌ Seeding failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} 