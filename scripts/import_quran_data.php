<?php

/**
 * Simple Quran Data Import Script
 * 
 * This script populates the Quran tables with basic data for testing
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Database connection
$host = '127.0.0.1';
$database = 'islamwiki';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // Import Surah data
    echo "Importing Surah data...\n";
    
    $surahData = [
        [1, 'الفاتحة', 'Al-Fatiha', 'The Opening', 'Meccan', 7, 1, ''],
        [2, 'البقرة', 'Al-Baqarah', 'The Cow', 'Medinan', 286, 40, ''],
        [3, 'آل عمران', 'Aal-Imran', 'The Family of Imran', 'Medinan', 200, 20, ''],
        [4, 'النساء', 'An-Nisa', 'The Women', 'Medinan', 176, 24, ''],
        [5, 'المائدة', 'Al-Ma\'idah', 'The Table Spread', 'Medinan', 120, 16, ''],
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO quran_surahs 
        (surah_number, name_arabic, name_english, name_translation, revelation_type, verses_count, rukus_count, sajda_ayahs) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        name_arabic = VALUES(name_arabic),
        name_english = VALUES(name_english),
        name_translation = VALUES(name_translation),
        revelation_type = VALUES(revelation_type),
        verses_count = VALUES(verses_count),
        rukus_count = VALUES(rukus_count),
        sajda_ayahs = VALUES(sajda_ayahs)
    ");
    
    foreach ($surahData as $surah) {
        $stmt->execute($surah);
    }
    
    echo "Surah data imported successfully\n";
    
    // Import Ayah data for first few surahs
    echo "Importing Ayah data...\n";
    
    $ayahData = [
        // Al-Fatiha (Surah 1)
        [1, 1, 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ', 'In the name of Allah, the Entirely Merciful, the Especially Merciful', 1, 1, 1, 1, 1, false],
        [1, 2, 'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ', 'All praise is due to Allah, Lord of the worlds', 1, 1, 1, 1, 1, false],
        [1, 3, 'الرَّحْمَٰنِ الرَّحِيمِ', 'The Entirely Merciful, the Especially Merciful', 1, 1, 1, 1, 1, false],
        [1, 4, 'مَالِكِ يَوْمِ الدِّينِ', 'Sovereign of the Day of Recompense', 1, 1, 1, 1, 1, false],
        [1, 5, 'إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ', 'It is You we worship and You we ask for help', 1, 1, 1, 1, 1, false],
        [1, 6, 'اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ', 'Guide us to the straight path', 1, 1, 1, 1, 1, false],
        [1, 7, 'صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ', 'The path of those upon whom You have bestowed favor, not of those who have evoked Your anger or of those who are astray', 1, 1, 1, 1, 1, false],
        
        // Al-Baqarah (Surah 2) - first few ayahs
        [2, 1, 'الٓمٓ', 'Alif, Lam, Meem', 1, 1, 1, 1, 1, false],
        [2, 2, 'ذَٰلِكَ الْكِتَابُ لَا رَيْبَ ۛ فِيهِ ۛ هُدًى لِّلْمُتَّقِينَ', 'This is the Book about which there is no doubt, a guidance for those conscious of Allah', 1, 1, 1, 1, 1, false],
        [2, 3, 'الَّذِينَ يُؤْمِنُونَ بِالْغَيْبِ وَيُقِيمُونَ الصَّلَاةَ وَمِمَّا رَزَقْنَاهُمْ يُنفِقُونَ', 'Who believe in the unseen, establish prayer, and spend out of what We have provided for them', 1, 1, 1, 1, 1, false],
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO quran_ayahs 
        (surah_number, ayah_number, text, translation, juz, page, hizb, ruku, sajda) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        text = VALUES(text),
        translation = VALUES(translation),
        juz = VALUES(juz),
        page = VALUES(page),
        hizb = VALUES(hizb),
        ruku = VALUES(ruku),
        sajda = VALUES(sajda)
    ");
    
    foreach ($ayahData as $ayah) {
        // Ensure bound params match 9 placeholders (drop any trailing fixture values)
        $stmt->execute(array_slice($ayah, 0, 9));
    }
    
    echo "Ayah data imported successfully\n";

    // Import Translations into quran_translations for existing ayahs (language: en, translator: Saheeh International)
    echo "Importing Translations (en - Saheeh International) into quran_translations...\n";
    // Build a lightweight map of (surah, ayah) -> translation from the same $ayahData used above
    $translations = [];
    foreach ($ayahData as $row) {
        [$s, $a, $textAr, $transEn] = [$row[0], $row[1], $row[2], $row[3]];
        $translations[] = [$s, $a, $transEn];
    }

    // Prepare statements
    $selAyahId = $pdo->prepare("SELECT id FROM quran_ayahs WHERE surah_number = ? AND ayah_number = ? LIMIT 1");
    // Assume unique key on (ayah_id, language, translator); fallback to update if exists
    $insTrans = $pdo->prepare("
        INSERT INTO quran_translations (ayah_id, language, translator, translation)
        VALUES (?, 'en', 'Saheeh International', ?)
        ON DUPLICATE KEY UPDATE translation = VALUES(translation)
    ");

    $countInserted = 0;
    foreach ($translations as [$s, $a, $t]) {
        $selAyahId->execute([$s, $a]);
        $ayahRow = $selAyahId->fetch(PDO::FETCH_ASSOC);
        if ($ayahRow && isset($ayahRow['id'])) {
            $insTrans->execute([(int)$ayahRow['id'], $t]);
            $countInserted++;
        }
    }
    echo "Translations imported/updated: {$countInserted}\n";
    
    // Import Juz data
    echo "Importing Juz data...\n";
    
    $juzData = [
        [1, 'Alif Lam Meem', 1, 1, 2, 141],
        [2, 'Sayaqool', 2, 142, 2, 252],
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO quran_juz 
        (juz_number, name, start_surah, start_ayah, end_surah, end_ayah) 
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        name = VALUES(name),
        start_surah = VALUES(start_surah),
        start_ayah = VALUES(start_ayah),
        end_surah = VALUES(end_surah),
        end_ayah = VALUES(end_ayah)
    ");
    
    foreach ($juzData as $juz) {
        $stmt->execute($juz);
    }
    
    echo "Juz data imported successfully\n";
    
    echo "\nQuran data import completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
