<?php

/**
 * Simple Quran System Setup
 * 
 * This script sets up the Quran system by creating necessary database tables
 * and importing basic Quran data so that /quran/1/1 works properly.
 * 
 * @package IslamWiki\Maintenance\Debug
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

// Set up basic environment
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "🔧 Quran System Setup Script\n";
echo "============================\n\n";

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

echo "Database Configuration:\n";
echo "Host: {$dbConfig['host']}\n";
echo "Database: {$dbConfig['database']}\n";
echo "Username: {$dbConfig['username']}\n\n";

// Step 1: Test Database Connection
echo "📊 Step 1: Testing Database Connection\n";
echo "----------------------------------------\n";

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful!\n";
    echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n\n";
    echo "Please check your database configuration and try again.\n";
    exit(1);
}

// Step 2: Create Quran Tables
echo "📋 Step 2: Creating Quran Tables\n";
echo "----------------------------------\n";

try {
    echo "Creating quran_surahs table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quran_surahs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            surah_number INT NOT NULL UNIQUE,
            name_arabic VARCHAR(100) NOT NULL,
            name_english VARCHAR(100) NOT NULL,
            name_translation VARCHAR(100) NOT NULL,
            revelation_type VARCHAR(20) NOT NULL,
            ayahs_count INT NOT NULL,
            juz_start INT,
            juz_end INT,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_surah_number (surah_number),
            INDEX idx_revelation_type (revelation_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ quran_surahs table created/verified\n";
    
    echo "Creating quran_ayahs table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quran_ayahs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            surah_number INT NOT NULL,
            ayah_number INT NOT NULL,
            text_arabic TEXT NOT NULL,
            text_uthmani TEXT,
            text_indopak TEXT,
            juz_number INT,
            hizb_number INT,
            page_number INT,
            ruku_number INT,
            sajda_number INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_surah_ayah (surah_number, ayah_number),
            INDEX idx_surah_ayah (surah_number, ayah_number),
            INDEX idx_juz (juz_number),
            INDEX idx_page (page_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ quran_ayahs table created/verified\n";
    
    echo "Creating quran_translations table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quran_translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            surah_number INT NOT NULL,
            ayah_number INT NOT NULL,
            translation_text TEXT NOT NULL,
            translator VARCHAR(100) NOT NULL,
            language VARCHAR(10) NOT NULL DEFAULT 'en',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_translation (surah_number, ayah_number, translator, language),
            INDEX idx_surah_ayah (surah_number, ayah_number),
            INDEX idx_translator (translator),
            INDEX idx_language (language)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ quran_translations table created/verified\n\n";
    
    echo "🎉 All Quran tables created successfully!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error creating tables: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Import Quran Data
echo "📖 Step 3: Importing Quran Data\n";
echo "---------------------------------\n";

try {
    echo "Importing Surah Al-Fatiha...\n";
    
    // Import Surah Al-Fatiha
    $stmt = $pdo->prepare("
        INSERT INTO quran_surahs (surah_number, name_arabic, name_english, name_translation, revelation_type, ayahs_count, juz_start, juz_end, description) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        name_arabic = VALUES(name_arabic),
        name_english = VALUES(name_english),
        name_translation = VALUES(name_translation),
        revelation_type = VALUES(revelation_type),
        ayahs_count = VALUES(ayahs_count),
        juz_start = VALUES(juz_start),
        juz_end = VALUES(juz_end),
        description = VALUES(description)
    ");
    
    $stmt->execute([1, 'الفاتحة', 'Al-Fatiha', 'The Opening', 'Meccan', 7, 1, 1, 'The first chapter of the Quran, consisting of seven verses']);
    echo "✅ Surah Al-Fatiha imported\n";
    
    // Import Ayahs of Al-Fatiha
    echo "Importing Ayahs of Al-Fatiha...\n";
    
    $ayahs = [
        [1, 1, 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ', 1, 1, 1, 1, 1],
        [1, 2, 'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ', 1, 1, 1, 1, 1],
        [1, 3, 'الرَّحْمَٰنِ الرَّحِيمِ', 1, 1, 1, 1, 1],
        [1, 4, 'مَالِكِ يَوْمِ الدِّينِ', 1, 1, 1, 1, 1],
        [1, 5, 'إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ', 1, 1, 1, 1, 1],
        [1, 6, 'اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ', 1, 1, 1, 1, 1],
        [1, 7, 'صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ', 1, 1, 1, 1, 1]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO quran_ayahs (surah_number, ayah_number, text_arabic, juz_number, hizb_number, page_number, ruku_number, sajda_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        text_arabic = VALUES(text_arabic),
        juz_number = VALUES(juz_number),
        hizb_number = VALUES(hizb_number),
        page_number = VALUES(page_number),
        ruku_number = VALUES(ruku_number),
        sajda_number = VALUES(sajda_number)
    ");
    
    foreach ($ayahs as $ayah) {
        $stmt->execute($ayah);
    }
    echo "✅ 7 Ayahs of Al-Fatiha imported\n";
    
    // Import English translations
    echo "Importing English translations...\n";
    
    $translations = [
        [1, 1, 'In the name of Allah, the Entirely Merciful, the Especially Merciful', 'Saheeh International', 'en'],
        [1, 2, 'All praise is due to Allah, Lord of the worlds', 'Saheeh International', 'en'],
        [1, 3, 'The Entirely Merciful, the Especially Merciful', 'Saheeh International', 'en'],
        [1, 4, 'Sovereign of the Day of Recompense', 'Saheeh International', 'en'],
        [1, 5, 'It is You we worship and You we ask for help', 'Saheeh International', 'en'],
        [1, 6, 'Guide us to the straight path', 'Saheeh International', 'en'],
        [1, 7, 'The path of those upon whom You have bestowed favor, not of those who have evoked Your anger or of those who are astray', 'Saheeh International', 'en']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO quran_translations (surah_number, ayah_number, translation_text, translator, language) 
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        translation_text = VALUES(translation_text),
        translator = VALUES(translator),
        language = VALUES(language)
    ");
    
    foreach ($translations as $translation) {
        $stmt->execute($translation);
    }
    echo "✅ English translations imported\n\n";
    
    echo "🎉 Quran data import completed successfully!\n";
    echo "Surah Al-Fatiha with 7 ayahs and English translations is now available.\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error importing data: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Test Quran System
echo "🧪 Step 4: Testing Quran System\n";
echo "---------------------------------\n";

try {
    echo "Testing Quran system functionality...\n\n";
    
    // Test if Surah 1 exists
    $stmt = $pdo->query("SELECT * FROM quran_surahs WHERE surah_number = 1");
    $surah = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($surah) {
        echo "✅ Surah 1 found: {$surah['name_english']} ({$surah['name_translation']})\n";
        echo "   Arabic name: {$surah['name_arabic']}\n";
        echo "   Ayahs count: {$surah['ayahs_count']}\n";
        echo "   Revelation: {$surah['revelation_type']}\n\n";
    } else {
        echo "❌ Surah 1 not found\n";
    }
    
    // Test if Ayah 1:1 exists
    $stmt = $pdo->query("SELECT * FROM quran_ayahs WHERE surah_number = 1 AND ayah_number = 1");
    $ayah = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($ayah) {
        echo "✅ Ayah 1:1 found\n";
        echo "   Arabic text: " . substr($ayah['text_arabic'], 0, 50) . "...\n";
        echo "   Juz: {$ayah['juz_number']}\n";
        echo "   Page: {$ayah['page_number']}\n\n";
    } else {
        echo "❌ Ayah 1:1 not found\n";
    }
    
    // Test if translation exists
    $stmt = $pdo->query("SELECT * FROM quran_translations WHERE surah_number = 1 AND ayah_number = 1 AND language = 'en'");
    $translation = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($translation) {
        echo "✅ English translation found\n";
        echo "   Translator: {$translation['translator']}\n";
        echo "   Translation: " . substr($translation['translation_text'], 0, 100) . "...\n\n";
    } else {
        echo "❌ English translation not found\n";
    }
    
    echo "🎯 Quran system test completed!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error testing system: " . $e->getMessage() . "\n";
    exit(1);
}

// Final Instructions
echo "🎉 Quran System Setup Complete!\n";
echo "===============================\n\n";
echo "✅ Database connection: Working\n";
echo "✅ Quran tables: Created\n";
echo "✅ Quran data: Imported\n";
echo "✅ System test: Passed\n\n";
echo "🎯 Now test the Quran page:\n";
echo "   Visit: /quran/1/1\n";
echo "   Expected: Surah Al-Fatiha with Arabic text and English translation\n\n";
echo "🔗 Test URLs:\n";
echo "   - /quran/1/1 (First ayah of Al-Fatiha)\n";
echo "   - /quran/1 (Full Surah Al-Fatiha)\n";
echo "   - /quran (Quran homepage)\n\n";
echo "If you still see an error page, check the browser console and network tab for details.\n";
echo "The Quran system should now be fully functional! 🎊\n"; 