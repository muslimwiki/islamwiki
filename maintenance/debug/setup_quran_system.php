<?php

/**
 * Setup Quran System
 * 
 * This script sets up the Quran system by creating necessary database tables
 * and importing basic Quran data so that /quran/1/1 works properly.
 * 
 * @package IslamWiki\Maintenance\Debug
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

require_once __DIR__ . '/../../src/helpers.php';

// Set up basic environment
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup Quran System - IslamWiki</title>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        h1 {
            text-align: center;
            color: #2d3748;
            margin-bottom: 40px;
        }
        .setup-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        .setup-section h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .setup-section pre {
            background: #1a202c;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin: 10px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }
        .btn-success {
            background: #10b981;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-warning {
            background: #f59e0b;
        }
        .btn-warning:hover {
            background: #d97706;
        }
        .info-box {
            background: #e8f4fd;
            border-left: 4px solid #17a2b8;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .info-box h3 {
            color: #0c5460;
            margin-bottom: 15px;
        }
        .success-box {
            background: #e8f5e9;
            border-left: 4px solid #10b981;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .success-box h3 {
            color: #059669;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Setup Quran System</h1>
        
        <div class='info-box'>
            <h3>🎯 What This Script Does</h3>
            <p>This script will set up the complete Quran system so that <code>/quran/1/1</code> displays Quran content instead of an error page. It will:</p>
            <ul>
                <li>Check database connection</li>
                <li>Create Quran database tables if they don't exist</li>
                <li>Import basic Quran data (Surah Al-Fatiha)</li>
                <li>Test the system functionality</li>
            </ul>
        </div>
        
        <div class='setup-section'>
            <h3>📊 Step 1: Database Connection Test</h3>
            <p>First, let's verify the database connection is working:</p>
            <div id='db-test-results'></div>
            <a href='?setup=db_test' class='btn'>Test Database Connection</a>
        </div>
        
        <div class='setup-section'>
            <h3>📋 Step 2: Create Quran Tables</h3>
            <p>Create the necessary Quran database tables:</p>
            <div id='tables-setup-results'></div>
            <a href='?setup=create_tables' class='btn btn-warning'>Create Quran Tables</a>
        </div>
        
        <div class='setup-section'>
            <h3>📖 Step 3: Import Quran Data</h3>
            <p>Import basic Quran data including Surah Al-Fatiha:</p>
            <div id='import-results'></div>
            <a href='?setup=import_data' class='btn btn-warning'>Import Quran Data</a>
        </div>
        
        <div class='setup-section'>
            <h3>🧪 Step 4: Test Quran System</h3>
            <p>Test if the Quran system is now working:</p>
            <div id='test-results'></div>
            <a href='?setup=test_system' class='btn btn-success'>Test Quran System</a>
        </div>
        
        <div class='setup-section'>
            <h3>🎯 Step 5: Verify Quran Page</h3>
            <p>After setup, test the actual Quran page:</p>
            <div style='text-align: center; margin-top: 20px;'>
                <a href='/quran/1/1' class='btn btn-success' target='_blank'>🎯 Test Quran 1:1</a>
                <a href='/quran/1' class='btn' target='_blank'>📄 Test Surah 1</a>
                <a href='/quran' class='btn' target='_blank'>🏠 Test Quran Home</a>
            </div>
        </div>
        
        <div class='success-box'>
            <h3>🎉 Expected Result</h3>
            <p>After running this setup, <code>/quran/1/1</code> should display:</p>
            <ul>
                <li>📖 Surah Al-Fatiha (The Opening)</li>
                <li>🕋 First ayah: "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ"</li>
                <li>🌐 Translation: "In the name of Allah, the Entirely Merciful, the Especially Merciful"</li>
                <li>⬅️➡️ Navigation controls</li>
                <li>📚 Breadcrumb navigation</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Handle setup requests via AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const setup = urlParams.get('setup');
            
            if (setup) {
                runSetup(setup);
            }
        });
        
        function runSetup(type) {
            const resultsDiv = document.getElementById(type + '-results');
            if (resultsDiv) {
                resultsDiv.innerHTML = '<p>Running setup...</p>';
                
                fetch('?setup=' + type)
                    .then(response => response.text())
                    .then(data => {
                        resultsDiv.innerHTML = '<pre>' + data + '</pre>';
                    })
                    .catch(error => {
                        resultsDiv.innerHTML = '<p>Error running setup: ' + error.message + '</p>';
                    });
            }
        }
    </script>
</body>
</html>";

// Handle setup requests
if (isset($_GET['setup'])) {
    $setup = $_GET['setup'];
    
    switch ($setup) {
        case 'db_test':
            echo "=== Database Connection Test ===\n\n";
            
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=islamwiki;charset=utf8mb4", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo "✅ Database connection successful!\n";
                echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
                echo "Database: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n\n";
                
            } catch (PDOException $e) {
                echo "❌ Database connection failed: " . $e->getMessage() . "\n";
                echo "Error code: " . $e->getCode() . "\n\n";
                echo "Please check your database configuration.\n";
            }
            break;
            
        case 'create_tables':
            echo "=== Creating Quran Tables ===\n\n";
            
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=islamwiki;charset=utf8mb4", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo "Creating Quran tables...\n\n";
                
                // Create quran_surahs table
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
                
                // Create quran_ayahs table
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
                
                // Create quran_translations table
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
                
                echo "🎉 All Quran tables created successfully!\n";
                
            } catch (PDOException $e) {
                echo "❌ Error creating tables: " . $e->getMessage() . "\n";
            }
            break;
            
        case 'import_data':
            echo "=== Importing Quran Data ===\n\n";
            
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=islamwiki;charset=utf8mb4", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo "Importing Surah Al-Fatiha data...\n\n";
                
                // Import Surah Al-Fatiha
                $pdo->exec("
                    INSERT INTO quran_surahs (surah_number, name_arabic, name_english, name_translation, revelation_type, ayahs_count, juz_start, juz_end, description) 
                    VALUES (1, 'الفاتحة', 'Al-Fatiha', 'The Opening', 'Meccan', 7, 1, 1, 'The first chapter of the Quran, consisting of seven verses')
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
                echo "✅ Surah Al-Fatiha imported\n";
                
                // Import Ayahs of Al-Fatiha
                $ayahs = [
                    [1, 1, 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ', 1, 1, 1, 1, 1],
                    [1, 2, 'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ', 1, 1, 1, 1, 1],
                    [1, 3, 'الرَّحْمَٰنِ الرَّحِيمِ', 1, 1, 1, 1, 1],
                    [1, 4, 'مَالِكِ يَوْمِ الدِّينِ', 1, 1, 1, 1, 1],
                    [1, 5, 'إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ', 1, 1, 1, 1, 1],
                    [1, 6, 'اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ', 1, 1, 1, 1, 1],
                    [1, 7, 'صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ', 1, 1, 1, 1, 1]
                ];
                
                foreach ($ayahs as $ayah) {
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
                    $stmt->execute($ayah);
                }
                echo "✅ 7 Ayahs of Al-Fatiha imported\n";
                
                // Import English translations
                $translations = [
                    [1, 1, 'In the name of Allah, the Entirely Merciful, the Especially Merciful', 'Saheeh International', 'en'],
                    [1, 2, 'All praise is due to Allah, Lord of the worlds', 'Saheeh International', 'en'],
                    [1, 3, 'The Entirely Merciful, the Especially Merciful', 'Saheeh International', 'en'],
                    [1, 4, 'Sovereign of the Day of Recompense', 'Saheeh International', 'en'],
                    [1, 5, 'It is You we worship and You we ask for help', 'Saheeh International', 'en'],
                    [1, 6, 'Guide us to the straight path', 'Saheeh International', 'en'],
                    [1, 7, 'The path of those upon whom You have bestowed favor, not of those who have evoked Your anger or of those who are astray', 'Saheeh International', 'en']
                ];
                
                foreach ($translations as $translation) {
                    $stmt = $pdo->prepare("
                        INSERT INTO quran_translations (surah_number, ayah_number, translation_text, translator, language) 
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        translation_text = VALUES(translation_text),
                        translator = VALUES(translator),
                        language = VALUES(language)
                    ");
                    $stmt->execute($translation);
                }
                echo "✅ English translations imported\n\n";
                
                echo "🎉 Quran data import completed successfully!\n";
                echo "Surah Al-Fatiha with 7 ayahs and English translations is now available.\n";
                
            } catch (PDOException $e) {
                echo "❌ Error importing data: " . $e->getMessage() . "\n";
            }
            break;
            
        case 'test_system':
            echo "=== Testing Quran System ===\n\n";
            
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=islamwiki;charset=utf8mb4", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
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
                
                echo "🎯 Quran system test completed!\n";
                echo "Now try visiting /quran/1/1 to see the Quran content.\n";
                
            } catch (PDOException $e) {
                echo "❌ Error testing system: " . $e->getMessage() . "\n";
            }
            break;
            
        default:
            echo "Unknown setup type: $setup\n";
    }
}

echo "\n\n<!-- Quran System Setup Complete -->\n";
echo "<!-- Run each setup step to get the Quran system working -->\n"; 