<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Run this script from CLI.\n");
    exit(1);
}

// DB setup
$configs = [
    'quran' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'database' => getenv('DB_DATABASE') ?: 'islamwiki',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
];

try {
    $manager = new \IslamWiki\Core\Database\Islamic\IslamicDatabaseManager($configs);
    /** @var PDO $pdo */
    $pdo = $manager->getQuranPdo();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
} catch (Exception $e) {
    fwrite(STDERR, "Database connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

// Quran surahs data - all 114 surahs
$surahs = [
    [1, 'Al-Fatihah', 'الفاتحة', 'The Opener', 'Meccan', 7],
    [2, 'Al-Baqarah', 'البقرة', 'The Cow', 'Medinan', 286],
    [3, 'Aal-Imran', 'آل عمران', 'Family of Imran', 'Medinan', 200],
    [4, 'An-Nisa', 'النساء', 'The Women', 'Medinan', 176],
    [5, 'Al-Ma\'idah', 'المائدة', 'The Table Spread', 'Medinan', 120],
    [6, 'Al-An\'am', 'الأنعام', 'The Cattle', 'Meccan', 165],
    [7, 'Al-A\'raf', 'الأعراف', 'The Heights', 'Meccan', 206],
    [8, 'Al-Anfal', 'الأنفال', 'The Spoils of War', 'Medinan', 75],
    [9, 'At-Tawbah', 'التوبة', 'The Repentance', 'Medinan', 129],
    [10, 'Yunus', 'يونس', 'Jonah', 'Meccan', 109],
    [11, 'Hud', 'هود', 'Hud', 'Meccan', 123],
    [12, 'Yusuf', 'يوسف', 'Joseph', 'Meccan', 111],
    [13, 'Ar-Ra\'d', 'الرعد', 'The Thunder', 'Medinan', 43],
    [14, 'Ibrahim', 'إبراهيم', 'Abraham', 'Meccan', 52],
    [15, 'Al-Hijr', 'الحجر', 'The Rocky Tract', 'Meccan', 99],
    [16, 'An-Nahl', 'النحل', 'The Bee', 'Meccan', 128],
    [17, 'Al-Isra', 'الإسراء', 'The Night Journey', 'Meccan', 111],
    [18, 'Al-Kahf', 'الكهف', 'The Cave', 'Meccan', 110],
    [19, 'Maryam', 'مريم', 'Mary', 'Meccan', 98],
    [20, 'Ta-Ha', 'طه', 'Ta-Ha', 'Meccan', 135],
    [21, 'Al-Anbiya', 'الأنبياء', 'The Prophets', 'Meccan', 112],
    [22, 'Al-Hajj', 'الحج', 'The Pilgrimage', 'Medinan', 78],
    [23, 'Al-Mu\'minun', 'المؤمنون', 'The Believers', 'Meccan', 118],
    [24, 'An-Nur', 'النور', 'The Light', 'Medinan', 64],
    [25, 'Al-Furqan', 'الفرقان', 'The Criterion', 'Meccan', 77],
    [26, 'Ash-Shu\'ara', 'الشعراء', 'The Poets', 'Meccan', 227],
    [27, 'An-Naml', 'النمل', 'The Ant', 'Meccan', 93],
    [28, 'Al-Qasas', 'القصص', 'The Stories', 'Meccan', 88],
    [29, 'Al-Ankabut', 'العنكبوت', 'The Spider', 'Meccan', 69],
    [30, 'Ar-Rum', 'الروم', 'The Romans', 'Meccan', 60],
    [31, 'Luqman', 'لقمان', 'Luqman', 'Meccan', 34],
    [32, 'As-Sajdah', 'السجدة', 'The Prostration', 'Meccan', 30],
    [33, 'Al-Ahzab', 'الأحزاب', 'The Combined Forces', 'Medinan', 73],
    [34, 'Saba', 'سبإ', 'Sheba', 'Meccan', 54],
    [35, 'Fatir', 'فاطر', 'Originator', 'Meccan', 45],
    [36, 'Ya-Sin', 'يس', 'Ya-Sin', 'Meccan', 83],
    [37, 'As-Saffat', 'الصافات', 'Those Who Set The Ranks', 'Meccan', 182],
    [38, 'Sad', 'ص', 'The Letter "Saad"', 'Meccan', 88],
    [39, 'Az-Zumar', 'الزمر', 'The Troops', 'Meccan', 75],
    [40, 'Ghafir', 'غافر', 'The Forgiver', 'Meccan', 85],
    [41, 'Fussilat', 'فصلت', 'Explained in Detail', 'Meccan', 54],
    [42, 'Ash-Shura', 'الشورى', 'The Consultation', 'Meccan', 53],
    [43, 'Az-Zukhruf', 'الزخرف', 'The Ornaments of Gold', 'Meccan', 89],
    [44, 'Ad-Dukhan', 'الدخان', 'The Smoke', 'Meccan', 59],
    [45, 'Al-Jathiyah', 'الجاثية', 'The Kneeling', 'Meccan', 37],
    [46, 'Al-Ahqaf', 'الأحقاف', 'The Wind-Curved Sandhills', 'Meccan', 35],
    [47, 'Muhammad', 'محمد', 'Muhammad', 'Medinan', 38],
    [48, 'Al-Fath', 'الفتح', 'The Victory', 'Medinan', 29],
    [49, 'Al-Hujurat', 'الحجرات', 'The Private Apartments', 'Medinan', 18],
    [50, 'Qaf', 'ق', 'The Letter "Qaf"', 'Meccan', 45],
    [51, 'Adh-Dhariyat', 'الذاريات', 'The Winnowing Winds', 'Meccan', 60],
    [52, 'At-Tur', 'الطور', 'The Mount', 'Meccan', 49],
    [53, 'An-Najm', 'النجم', 'The Star', 'Meccan', 62],
    [54, 'Al-Qamar', 'القمر', 'The Moon', 'Meccan', 55],
    [55, 'Ar-Mercyn', 'الرحمن', 'The Beneficent', 'Medinan', 78],
    [56, 'Al-Waqi\'ah', 'الواقعة', 'The Inevitable', 'Meccan', 96],
    [57, 'Al-Hadid', 'الحديد', 'The Iron', 'Medinan', 29],
    [58, 'Al-Mujadila', 'المجادلة', 'The Pleading Woman', 'Medinan', 22],
    [59, 'Al-Hashr', 'الحشر', 'The Exile', 'Medinan', 24],
    [60, 'Al-Mumtahanah', 'الممتحنة', 'The Woman to be Examined', 'Medinan', 13],
    [61, 'As-Saf', 'الصف', 'The Ranks', 'Medinan', 14],
    [62, 'Al-Jumu\'ah', 'الجمعة', 'Friday', 'Medinan', 11],
    [63, 'Al-Munafiqun', 'المنافقون', 'The Hypocrites', 'Medinan', 11],
    [64, 'At-Taghabun', 'التغابن', 'The Mutual Disillusion', 'Medinan', 18],
    [65, 'At-Talaq', 'الطلاق', 'Divorce', 'Medinan', 12],
    [66, 'At-Tahrim', 'التحريم', 'The Prohibition', 'Medinan', 12],
    [67, 'Al-Mulk', 'الملك', 'The Sovereignty', 'Meccan', 30],
    [68, 'Al-Qalam', 'القلم', 'The Pen', 'Meccan', 52],
    [69, 'Al-Haqqah', 'الحاقة', 'The Reality', 'Meccan', 52],
    [70, 'Al-Ma\'arij', 'المعارج', 'The Ascending Stairways', 'Meccan', 44],
    [71, 'Nuh', 'نوح', 'Noah', 'Meccan', 28],
    [72, 'Al-Jinn', 'الجن', 'The Jinn', 'Meccan', 28],
    [73, 'Al-Muzzammil', 'المزمل', 'The Enshrouded One', 'Meccan', 20],
    [74, 'Al-Muddathir', 'المدثر', 'The Cloaked One', 'Meccan', 56],
    [75, 'Al-Qiyamah', 'القيامة', 'The Resurrection', 'Meccan', 40],
    [76, 'Al-Insan', 'الإنسان', 'Man', 'Medinan', 31],
    [77, 'Al-Mursalat', 'المرسلات', 'The Emissaries', 'Meccan', 50],
    [78, 'An-Naba', 'النبإ', 'The Tidings', 'Meccan', 40],
    [79, 'An-Nazi\'at', 'النازعات', 'Those Who Drag Forth', 'Meccan', 46],
    [80, 'Abasa', 'عبس', 'He Frowned', 'Meccan', 42],
    [81, 'At-Takwir', 'التكوير', 'The Overthrowing', 'Meccan', 29],
    [82, 'Al-Infitar', 'الانفطار', 'The Cleaving', 'Meccan', 19],
    [83, 'Al-Mutaffifin', 'المطففين', 'The Defrauding', 'Meccan', 36],
    [84, 'Al-Inshiqaq', 'الانشقاق', 'The Splitting Open', 'Meccan', 25],
    [85, 'Al-Buruj', 'البروج', 'The Mansions of the Stars', 'Meccan', 22],
    [86, 'At-Tariq', 'الطارق', 'The Morning Star', 'Meccan', 17],
    [87, 'Al-A\'la', 'الأعلى', 'The Most High', 'Meccan', 19],
    [88, 'Al-Ghashiyah', 'الغاشية', 'The Overwhelming', 'Meccan', 26],
    [89, 'Al-Fajr', 'الفجر', 'The Dawn', 'Meccan', 30],
    [90, 'Al-Balad', 'البلد', 'The City', 'Meccan', 20],
    [91, 'Ash-Shams', 'الشمس', 'The Sun', 'Meccan', 15],
    [92, 'Al-Layl', 'الليل', 'The Night', 'Meccan', 21],
    [93, 'Ad-Duha', 'الضحى', 'The Morning Hours', 'Meccan', 11],
    [94, 'Ash-Sharh', 'الشرح', 'The Relief', 'Meccan', 8],
    [95, 'At-Tin', 'التين', 'The Fig', 'Meccan', 8],
    [96, 'Al-\'Alaq', 'العلق', 'The Clot', 'Meccan', 19],
    [97, 'Al-Qadr', 'القدر', 'The Power', 'Meccan', 5],
    [98, 'Al-Bayyinah', 'البينة', 'The Clear Proof', 'Medinan', 8],
    [99, 'Az-Zalzalah', 'الزلزلة', 'The Earthquake', 'Medinan', 8],
    [100, 'Al-\'Adiyat', 'العاديات', 'The Coursers', 'Meccan', 11],
    [101, 'Al-Qari\'ah', 'القارعة', 'The Calamity', 'Meccan', 11],
    [102, 'At-Takathur', 'التكاثر', 'The Rivalry in World Increase', 'Meccan', 8],
    [103, 'Al-\'Asr', 'العصر', 'The Declining Day', 'Meccan', 3],
    [104, 'Al-Humazah', 'الهمزة', 'The Traducer', 'Meccan', 9],
    [105, 'Al-Fil', 'الفيل', 'The Elephant', 'Meccan', 5],
    [106, 'Quraish', 'قريش', 'Quraish', 'Meccan', 4],
    [107, 'Al-Ma\'un', 'الماعون', 'The Small Kindnesses', 'Meccan', 7],
    [108, 'Al-Kawthar', 'الكوثر', 'The Abundance', 'Meccan', 3],
    [109, 'Al-Kafirun', 'الكافرون', 'The Disbelievers', 'Meccan', 6],
    [110, 'An-Nasr', 'النصر', 'The Divine Support', 'Medinan', 3],
    [111, 'Al-Masad', 'المسد', 'The Palm Fiber', 'Meccan', 5],
    [112, 'Al-Ikhlas', 'الإخلاص', 'The Sincerity', 'Meccan', 4],
    [113, 'Al-Falaq', 'الفلق', 'The Daybreak', 'Meccan', 5],
    [114, 'An-Nas', 'الناس', 'Mankind', 'Meccan', 6]
];

echo "Starting to populate quran_surahs table with " . count($surahs) . " surahs...\n";

try {
    // First, clear existing data
    $pdo->exec("DELETE FROM quran_surahs");
    echo "Cleared existing surah data.\n";
    
    // Insert all surahs
    $stmt = $pdo->prepare("
        INSERT INTO quran_surahs 
        (surah_number, name_english, name_arabic, name_translation, revelation_type, verses_count, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $inserted = 0;
    foreach ($surahs as $surah) {
        $stmt->execute([
            $surah[0], // surah_number
            $surah[1], // name_english
            $surah[2], // name_arabic
            $surah[3], // name_translation
            $surah[4], // revelation_type
            $surah[5]  // verses_count
        ]);
        $inserted++;
        
        if ($inserted % 10 == 0) {
            echo "Inserted $inserted surahs...\n";
        }
    }
    
    echo "Successfully inserted $inserted surahs into quran_surahs table.\n";
    
    // Verify the count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM quran_surahs");
    $result = $stmt->fetch();
    echo "Total surahs in table: " . $result['count'] . "\n";
    
    if ($result['count'] == 114) {
        echo "✅ All 114 surahs have been successfully populated!\n";
    } else {
        echo "❌ Expected 114 surahs, but found " . $result['count'] . "\n";
    }
    
} catch (Exception $e) {
    fwrite(STDERR, "Error populating surahs table: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Surah population complete!\n";
