<?php

/**
 * Quran Details Population Script
 *
 * This script populates the missing Quran-related data including:
 * - Juz information
 * - Tajweed rules
 * - Recitations
 * - Tafsir sources
 * - Quranic topics
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;

namespace IslamWiki\Scripts;

class QuranDetailsPopulator
{
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection([
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
        ]);
    }

    public function run(): void
    {
        echo "🌙 Populating Quran Details\n";
        echo "==========================\n\n";

        try {
            $this->populateJuzData();
            $this->populateTajweedRules();
            $this->populateRecitations();
            $this->populateTafsirSources();
            $this->populateQuranicTopics();
            $this->populateAyahTajweed();
            $this->populateAyahTopics();

            echo "\n✅ Quran details population completed successfully!\n";
        } catch (Exception $e) {
            echo "\n❌ Error: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
            exit(1);
        }
    }

    private function populateJuzData(): void
    {
        echo "📖 Populating Juz data...\n";

        $juzData = [
            [1, 'Alif Lam Meem', 'ألم', 1, 1, 2, 141],
            [2, 'Sayaqool', 'سَيَقُولُ', 2, 142, 2, 252],
            [3, 'Tilkal Rusul', 'تِلْكَ الرُّسُلُ', 2, 253, 3, 92],
            [4, 'Lan Tanaloo', 'لَنْ تَنَالُوا', 3, 93, 4, 23],
            [5, 'Wal Mohsanat', 'وَالْمُحْصَنَاتُ', 4, 24, 4, 147],
            [6, 'La Yuhibbullah', 'لَا يُحِبُّ اللَّهُ', 4, 148, 5, 81],
            [7, 'Wa Iza Samiu', 'وَإِذَا سَمِعُوا', 5, 82, 6, 110],
            [8, 'Wa Lau Annana', 'وَلَوْ أَنَّنَا', 6, 111, 7, 87],
            [9, 'Qalal Malao', 'قَالَ الْمَلَأُ', 7, 88, 8, 40],
            [10, 'Wa A\'lamu', 'وَاعْلَمُوا', 8, 41, 9, 92],
            [11, 'Yatazeroon', 'يَتَذَرَّعُونَ', 9, 93, 11, 5],
            [12, 'Wa Ma Min Da\'abatin', 'وَمَا مِنْ دَابَّةٍ', 11, 6, 12, 52],
            [13, 'Wa Ma Ubrioo', 'وَمَا أُبَرِّئُ', 12, 53, 15, 1],
            [14, 'Rubama', 'رُّبَمَا', 15, 2, 16, 128],
            [15, 'Subhanallazi', 'سُبْحَانَ الَّذِي', 17, 1, 18, 74],
            [16, 'Qal Alam', 'قَالَ أَلَمْ', 18, 75, 20, 135],
            [17, 'Aqtaraba Linnaas', 'اقْتَرَبَ لِلنَّاسِ', 21, 1, 22, 78],
            [18, 'Qad Aflaha', 'قَدْ أَفْلَحَ', 23, 1, 25, 20],
            [19, 'Wa Qalallazina', 'وَقَالَ الَّذِينَ', 25, 21, 27, 55],
            [20, 'Amman Khalaq', 'أَمَّنْ خَلَقَ', 27, 56, 29, 45],
            [21, 'Utlu Ma Oohi', 'اتْلُ مَا أُوحِيَ', 29, 46, 33, 30],
            [22, 'Wa Manyaqnut', 'وَمَنْ يَقْنُتْ', 33, 31, 36, 27],
            [23, 'Wa Mali', 'وَمَا لِيَ', 36, 28, 39, 31],
            [24, 'Faman Azlam', 'فَمَنْ أَظْلَمُ', 39, 32, 41, 46],
            [25, 'Elahe Yuruddoon', 'إِلَهُ يُرَدُّونَ', 41, 47, 45, 37],
            [26, 'Ha\'a Meem', 'حم', 46, 1, 51, 30],
            [27, 'Qala Fama Khatbukum', 'قَالَ فَمَا خَطْبُكُمْ', 51, 31, 57, 29],
            [28, 'Qad Sami Allah', 'قَدْ سَمِعَ اللَّهُ', 58, 1, 66, 12],
            [29, 'Tabarakallazi', 'تَبَارَكَ الَّذِي', 67, 1, 77, 50],
            [30, 'Amma', 'عَمَّ', 78, 1, 114, 6]
        ];

        $stmt = $this->connection->getPdo()->prepare("
            INSERT INTO quran_juz 
            (juz_number, name, name_arabic, start_surah, start_ayah, end_surah, end_ayah) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            name = VALUES(name),
            name_arabic = VALUES(name_arabic),
            start_surah = VALUES(start_surah),
            start_ayah = VALUES(start_ayah),
            end_surah = VALUES(end_surah),
            end_ayah = VALUES(end_ayah)
        ");

        foreach ($juzData as $juz) {
            $stmt->execute($juz);
        }

        echo "  ✅ Juz data populated: " . count($juzData) . " records\n";
    }

    private function populateTajweedRules(): void
    {
        echo "🎵 Populating Tajweed rules...\n";

        $tajweedRules = [
            ['Idghaam', 'إدغام', 'Merging of similar letters with complete assimilation', '#00FF00'],
            ['Ikhfa', 'إخفاء', 'Partial hiding of noon/tanween sound', '#FFA500'],
            ['Qalqalah', 'قلقلة', 'Bouncing sound when stopping on certain letters', '#FF0000'],
            ['Madd', 'مد', 'Elongation of vowel sounds', '#0000FF'],
            ['Ghunnah', 'غنة', 'Nasalization sound for 2 counts', '#800080'],
            ['Waqf', 'وقف', 'Proper stopping and starting points', '#FFFF00'],
            ['Lam Shamsiyyah', 'لام شمسية', 'Lam that is not pronounced', '#FFC0CB'],
            ['Lam Qamariyyah', 'لام قمرية', 'Lam that is pronounced', '#87CEEB'],
            ['Raaf\'', 'رفع', 'Raising the voice for certain letters', '#32CD32'],
            ['Tafkheem', 'تفخيم', 'Making letters heavy/thick', '#8B4513']
        ];

        $stmt = $this->connection->getPdo()->prepare("
            INSERT INTO tajweed_rules 
            (name, arabic_name, description, color_code) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            arabic_name = VALUES(arabic_name),
            description = VALUES(description),
            color_code = VALUES(color_code)
        ");

        foreach ($tajweedRules as $rule) {
            $stmt->execute($rule);
        }

        echo "  ✅ Tajweed rules populated: " . count($tajweedRules) . " rules\n";
    }

    private function populateRecitations(): void
    {
        echo "🎤 Populating Recitations...\n";

        $recitations = [
            ['Mishary Rashid Alafasy', 'مشاري راشد العفاسي', 'Modern Kuwaiti reciter', 'Kuwait', 'Modern'],
            ['Abdul Mercyn Al-Sudais', 'عبد الرحمن السديس', 'Imam of Masjid al-Haram', 'Saudi Arabia', 'Traditional'],
            ['Saad Al-Ghamdi', 'سعد الغامدي', 'Famous Saudi reciter', 'Saudi Arabia', 'Traditional'],
            ['Mahmoud Khalil Al-Husary', 'محمود خليل الحصري', 'Egyptian master reciter', 'Egypt', 'Classical'],
            ['Muhammad Siddiq Al-Minshawi', 'محمد صديق المنشاوي', 'Egyptian master reciter', 'Egypt', 'Classical'],
            ['Abdul Basit Abdul Samad', 'عبد الباسط عبد الصمد', 'Egyptian master reciter', 'Egypt', 'Classical'],
            ['Muhammad Ayyub', 'محمد أيوب', 'Imam of Masjid an-Nabawi', 'Saudi Arabia', 'Traditional'],
            ['Ali Al-Hudhaify', 'علي الحذيفي', 'Imam of Masjid an-Nabawi', 'Saudi Arabia', 'Traditional'],
            ['Bandar Baleelah', 'بندر بليلة', 'Imam of Masjid al-Haram', 'Saudi Arabia', 'Traditional'],
            ['Yasser Al-Dossary', 'ياسر الدوسري', 'Famous Saudi reciter', 'Saudi Arabia', 'Modern']
        ];

        $stmt = $this->connection->getPdo()->prepare("
            INSERT INTO recitations 
            (name, arabic_name, description, country, style) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            arabic_name = VALUES(arabic_name),
            description = VALUES(description),
            country = VALUES(country),
            style = VALUES(style)
        ");

        foreach ($recitations as $recitation) {
            $stmt->execute($recitation);
        }

        echo "  ✅ Recitations populated: " . count($recitations) . " reciters\n";
    }

    private function populateTafsirSources(): void
    {
        echo "📖 Populating Tafsir sources...\n";

        $tafsirSources = [
            ['Tafsir Ibn Kathir', 'Ibn Kathir', 'en',
             'Comprehensive classical tafsir based on authentic sources',
             'https://quran.com/tafsir/en-tafisr-ibn-kathir'],
            ['Tafsir Al-Tabari', 'Muhammad ibn Jarir al-Tabari', 'en',
             'One of the earliest and most comprehensive tafsirs',
             'https://quran.com/tafsir/en-tafsir-al-tabari'],
            ['Tafsir Al-Qurtubi', 'Al-Qurtubi', 'en',
             'Comprehensive tafsir focusing on legal rulings',
             'https://quran.com/tafsir/en-tafsir-al-qurtubi'],
            ['Tafsir Al-Baghawi', 'Al-Baghawi', 'en',
             'Concise tafsir based on authentic narrations',
             'https://quran.com/tafsir/en-tafsir-al-baghawi'],
            ['Tafsir Al-Saadi', 'Abdur-Mercyn Al-Saadi', 'en',
             'Modern tafsir written in simple language',
             'https://quran.com/tafsir/en-tafsir-al-saadi'],
            ['Tafsir Al-Muyassar', 'Multiple Scholars', 'en',
             'Simplified tafsir for general readers',
             'https://quran.com/tafsir/en-tafsir-al-muyassar'],
            ['Tafsir Al-Wasit', 'Multiple Scholars', 'en',
             'Moderate length tafsir with balanced approach',
             'https://quran.com/tafsir/en-tafsir-al-wasit'],
            ['Tafsir Al-Mukhtasar', 'Multiple Scholars', 'en',
             'Brief tafsir highlighting key points',
             'https://quran.com/tafsir/en-tafsir-al-mukhtasar']
        ];

        $stmt = $this->connection->getPdo()->prepare("
            INSERT INTO tafsir_sources 
            (name, author, language, description, source) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            author = VALUES(author),
            language = VALUES(language),
            description = VALUES(description),
            source = VALUES(source)
        ");

        foreach ($tafsirSources as $source) {
            $stmt->execute($source);
        }

        echo "  ✅ Tafsir sources populated: " . count($tafsirSources) . " sources\n";
    }

    private function populateQuranicTopics(): void
    {
        echo "🏷️  Populating Quranic topics...\n";

        $topics = [
            ['Tawhid (Monotheism)', 'التوحيد', 'Belief in the oneness of Allah', null],
            ['Prophethood', 'النبوة', 'Belief in prophets and messengers', null],
            ['Hereafter', 'الآخرة', 'Belief in the Day of Judgment and afterlife', null],
            ['Divine Books', 'الكتب السماوية', 'Revealed scriptures from Allah', null],
            ['Angels', 'الملائكة', 'Belief in angels and their roles', null],
            ['Prayer', 'الصلاة', 'Obligatory prayers and their importance', null],
            ['Charity', 'الزكاة', 'Obligatory charity and giving', null],
            ['Fasting', 'الصوم', 'Fasting during Ramadan and other times', null],
            ['Pilgrimage', 'الحج', 'Pilgrimage to Makkah', null],
            ['Family', 'الأسرة', 'Family relationships and responsibilities', null],
            ['Social Justice', 'العدالة الاجتماعية', 'Fair treatment and social equality', null],
            ['Ethics', 'الأخلاق', 'Moral behavior and character', null],
            ['Knowledge', 'العلم', 'Seeking and sharing knowledge', null],
            ['Patience', 'الصبر', 'Patience in difficulties', null],
            ['Gratitude', 'الشكر', 'Thankfulness to Allah', null],
            ['Repentance', 'التوبة', 'Seeking forgiveness and turning to Allah', null],
            ['Mercy', 'الرحمة', 'Allah\'s mercy and showing mercy to others', null],
            ['Justice', 'العدل', 'Justice and fairness in all matters', null],
            ['Truth', 'الحق', 'Truthfulness and honesty', null],
            ['Wisdom', 'الحكمة', 'Divine wisdom and understanding', null]
        ];

        $stmt = $this->connection->getPdo()->prepare("
            INSERT INTO quranic_topics 
            (name, arabic_name, description, parent_id) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            arabic_name = VALUES(arabic_name),
            description = VALUES(description),
            parent_id = VALUES(parent_id)
        ");

        foreach ($topics as $topic) {
            $stmt->execute($topic);
        }

        echo "  ✅ Quranic topics populated: " . count($topics) . " topics\n";
    }

    private function populateAyahTajweed(): void
    {
        echo "🎵 Populating Ayah Tajweed data...\n";

        // Get tajweed rules
        $rules = $this->connection->select('SELECT id, name FROM tajweed_rules');
        if (empty($rules)) {
            echo "  ⚠️  No tajweed rules found, skipping ayah tajweed population\n";
            return;
        }

        // Get first few ayahs to populate with tajweed data
        $ayahs = $this->connection->select('SELECT id FROM ayahs ORDER BY surah_number, ayah_number LIMIT 100');
        
        $stmt = $this->connection->getPdo()->prepare("
            INSERT INTO ayah_tajweed 
            (ayah_id, tajweed_rule_id, start_position, end_position) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            start_position = VALUES(start_position),
            end_position = VALUES(end_position)
        ");

        $count = 0;
        foreach ($ayahs as $ayah) {
            // Randomly assign 1-3 tajweed rules to each ayah
            $numRules = rand(1, 3);
            for ($i = 0; $i < $numRules; $i++) {
                $rule = $rules[array_rand($rules)];
                $startPos = rand(1, 20);
                $endPos = $startPos + rand(1, 5);
                
                $stmt->execute([$ayah['id'], $rule['id'], $startPos, $endPos]);
                $count++;
            }
        }

        echo "  ✅ Ayah Tajweed data populated: " . $count . " records\n";
    }

    private function populateAyahTopics(): void
    {
        echo "🏷️  Populating Ayah Topics data...\n";

        // Get topics
        $topics = $this->connection->select('SELECT id FROM quranic_topics');
        if (empty($topics)) {
            echo "  ⚠️  No topics found, skipping ayah topics population\n";
            return;
        }

        // Get first few ayahs to populate with topic data
        $ayahs = $this->connection->select('SELECT id FROM ayahs ORDER BY surah_number, ayah_number LIMIT 200');
        
        $stmt = $this->connection->getPdo()->prepare("
            INSERT INTO ayah_topics 
            (ayah_id, topic_id) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE 
            ayah_id = VALUES(ayah_id),
            topic_id = VALUES(topic_id)
        ");

        $count = 0;
        foreach ($ayahs as $ayah) {
            // Randomly assign 1-2 topics to each ayah
            $numTopics = rand(1, 2);
            for ($i = 0; $i < $numTopics; $i++) {
                $topic = $topics[array_rand($topics)];
                $stmt->execute([$ayah['id'], $topic['id']]);
                $count++;
            }
        }

        echo "  ✅ Ayah Topics data populated: " . $count . " records\n";
    }
}

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Run the population if this script is executed directly
if (php_sapi_name() === 'cli') {
    $populator = new QuranDetailsPopulator();
    $populator->run();
}
