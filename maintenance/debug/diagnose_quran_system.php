<?php

/**
 * Diagnose Quran System
 * 
 * This script diagnoses why the Quran system isn't working
 * and helps fix the underlying database and repository issues.
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
    <title>Diagnose Quran System - IslamWiki</title>
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
        .diagnostic-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        .diagnostic-section h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .diagnostic-section pre {
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
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .warning-box h3 {
            color: #92400e;
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
        <h1>🔍 Diagnose Quran System</h1>
        
        <div class='info-box'>
            <h3>🎯 What We're Diagnosing</h3>
            <p>The QuranController is now working without crashing, but <code>/quran/1/1</code> is still showing an error page instead of Quran content. This script will help identify and fix the underlying issues.</p>
        </div>
        
        <div class='diagnostic-section'>
            <h3>📊 Step 1: Database Connection Test</h3>
            <p>Check if the database connection is working and accessible:</p>
            <div id='db-test-results'></div>
            <a href='?diagnose=db_test' class='btn'>Test Database Connection</a>
        </div>
        
        <div class='diagnostic-section'>
            <h3>📋 Step 2: Database Tables Check</h3>
            <p>Verify that all required Quran database tables exist:</p>
            <div id='tables-test-results'></div>
            <a href='?diagnose=tables_test' class='btn'>Check Database Tables</a>
        </div>
        
        <div class='diagnostic-section'>
            <h3>🔧 Step 3: Repository Test</h3>
            <p>Test if the Quran repositories can be created and used:</p>
            <div id='repository-test-results'></div>
            <a href='?diagnose=repository_test' class='btn'>Test Repositories</a>
        </div>
        
        <div class='diagnostic-section'>
            <h3>🎮 Step 4: Controller Test</h3>
            <p>Test the full QuranController functionality:</p>
            <div id='controller-test-results'></div>
            <a href='?diagnose=controller_test' class='btn'>Test Controller</a>
        </div>
        
        <div class='diagnostic-section'>
            <h3>📖 Step 5: Quran Data Test</h3>
            <p>Check if Quran data exists and can be retrieved:</p>
            <div id='data-test-results'></div>
            <a href='?diagnose=data_test' class='btn'>Test Quran Data</a>
        </div>
        
        <div class='warning-box'>
            <h3>⚠️ Common Issues</h3>
            <ul>
                <li><strong>Missing Database Tables:</strong> Quran tables might not exist or be empty</li>
                <li><strong>Database Connection:</strong> Connection might be failing</li>
                <li><strong>Missing Data:</strong> Quran data might not be imported</li>
                <li><strong>Repository Errors:</strong> Repository classes might have issues</li>
                <li><strong>Configuration Problems:</strong> Database config might be incorrect</li>
            </ul>
        </div>
        
        <div class='success-box'>
            <h3>🎯 Expected Outcome</h3>
            <p>After fixing the issues, <code>/quran/1/1</code> should display:</p>
            <ul>
                <li>📖 Surah Al-Fatiha (The Opening)</li>
                <li>🕋 First ayah with Arabic text and translation</li>
                <li>🌐 Language and translator options</li>
                <li>⬅️➡️ Navigation to previous/next ayahs</li>
                <li>📚 Breadcrumb navigation</li>
            </ul>
        </div>
        
        <div class='diagnostic-section'>
            <h3>🧪 Manual Testing</h3>
            <p>After running the diagnostics, test these URLs:</p>
            <div style='text-align: center; margin-top: 20px;'>
                <a href='/quran/1/1' class='btn btn-success'>🎯 Test Quran 1:1</a>
                <a href='/quran/1' class='btn'>📄 Test Surah 1</a>
                <a href='/quran' class='btn'>🏠 Test Quran Home</a>
            </div>
        </div>
    </div>
    
    <script>
        // Handle diagnostic requests via AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const diagnose = urlParams.get('diagnose');
            
            if (diagnose) {
                runDiagnostic(diagnose);
            }
        });
        
        function runDiagnostic(type) {
            const resultsDiv = document.getElementById(type + '-test-results');
            if (resultsDiv) {
                resultsDiv.innerHTML = '<p>Running diagnostic...</p>';
                
                fetch('?diagnose=' + type)
                    .then(response => response.text())
                    .then(data => {
                        resultsDiv.innerHTML = '<pre>' + data + '</pre>';
                    })
                    .catch(error => {
                        resultsDiv.innerHTML = '<p>Error running diagnostic: ' + error.message + '</p>';
                    });
            }
        }
    </script>
</body>
</html>";

// Handle diagnostic requests
if (isset($_GET['diagnose'])) {
    $diagnose = $_GET['diagnose'];
    
    switch ($diagnose) {
        case 'db_test':
            echo "=== Database Connection Test ===\n\n";
            
            try {
                // Try to connect to database
                $dbConfig = [
                    'host' => 'localhost',
                    'database' => 'islamwiki',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8mb4'
                ];
                
                echo "Attempting database connection...\n";
                echo "Host: {$dbConfig['host']}\n";
                echo "Database: {$dbConfig['database']}\n";
                echo "Username: {$dbConfig['username']}\n\n";
                
                $pdo = new PDO(
                    "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
                    $dbConfig['username'],
                    $dbConfig['password']
                );
                
                echo "✅ Database connection successful!\n";
                echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
                echo "Client version: " . $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . "\n";
                echo "Database name: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n\n";
                
            } catch (PDOException $e) {
                echo "❌ Database connection failed: " . $e->getMessage() . "\n";
                echo "Error code: " . $e->getCode() . "\n\n";
                
                echo "🔧 Troubleshooting steps:\n";
                echo "1. Check if MySQL/MariaDB is running\n";
                echo "2. Verify database credentials\n";
                echo "3. Ensure database 'islamwiki' exists\n";
                echo "4. Check user permissions\n\n";
            }
            break;
            
        case 'tables_test':
            echo "=== Database Tables Check ===\n\n";
            
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=islamwiki;charset=utf8mb4", "root", "");
                
                echo "Checking for required Quran tables...\n\n";
                
                $requiredTables = [
                    'quran_ayahs' => 'Contains individual Quran verses',
                    'quran_surahs' => 'Contains Surah information',
                    'quran_translations' => 'Contains verse translations',
                    'quran_juz' => 'Contains Juz information',
                    'quran_pages' => 'Contains page information'
                ];
                
                foreach ($requiredTables as $table => $description) {
                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        echo "✅ Table '$table' exists - $description\n";
                        
                        // Check if table has data
                        $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                        $count = $countStmt->fetchColumn();
                        echo "   Records: $count\n";
                        
                        if ($count == 0) {
                            echo "   ⚠️  Table is empty - no data available\n";
                        }
                    } else {
                        echo "❌ Table '$table' does NOT exist - $description\n";
                    }
                    echo "\n";
                }
                
            } catch (PDOException $e) {
                echo "❌ Error checking tables: " . $e->getMessage() . "\n";
            }
            break;
            
        case 'repository_test':
            echo "=== Repository Test ===\n\n";
            
            try {
                echo "Testing QuranAyahRepository...\n";
                
                // Check if class can be loaded
                if (class_exists('IslamWiki\\Extensions\\QuranExtension\\Models\\QuranAyahRepository')) {
                    echo "✅ QuranAyahRepository class exists\n";
                    
                    // Try to create an instance
                    try {
                        $pdo = new PDO("mysql:host=localhost;dbname=islamwiki;charset=utf8mb4", "root", "");
                        $repository = new \IslamWiki\Extensions\QuranExtension\Models\QuranAyahRepository($pdo, []);
                        echo "✅ QuranAyahRepository instance created successfully\n";
                    } catch (Exception $e) {
                        echo "❌ Failed to create QuranAyahRepository: " . $e->getMessage() . "\n";
                    }
                } else {
                    echo "❌ QuranAyahRepository class not found\n";
                }
                
                echo "\nTesting QuranSurahRepository...\n";
                if (class_exists('IslamWiki\\Extensions\\QuranExtension\\Models\\QuranSurahRepository')) {
                    echo "✅ QuranSurahRepository class exists\n";
                    
                    try {
                        $pdo = new PDO("mysql:host=localhost;dbname=islamwiki;charset=utf8mb4", "root", "");
                        $repository = new \IslamWiki\Extensions\QuranExtension\Models\QuranSurahRepository($pdo);
                        echo "✅ QuranSurahRepository instance created successfully\n";
                    } catch (Exception $e) {
                        echo "❌ Failed to create QuranSurahRepository: " . $e->getMessage() . "\n";
                    }
                } else {
                    echo "❌ QuranSurahRepository class not found\n";
                }
                
            } catch (Exception $e) {
                echo "❌ Error testing repositories: " . $e->getMessage() . "\n";
            }
            break;
            
        case 'controller_test':
            echo "=== Controller Test ===\n\n";
            
            try {
                echo "Testing QuranController functionality...\n\n";
                
                if (class_exists('IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController')) {
                    echo "✅ QuranController class exists\n";
                    
                    // Check if base Controller exists
                    if (class_exists('IslamWiki\\Http\\Controllers\\Controller')) {
                        echo "✅ Base Controller class exists\n";
                    } else {
                        echo "❌ Base Controller class not found\n";
                    }
                    
                    // Check if required methods exist
                    $requiredMethods = ['ayahPage', 'surahPage', 'indexPage'];
                    foreach ($requiredMethods as $method) {
                        if (method_exists('IslamWiki\\Extensions\\QuranExtension\\Http\\Controllers\\QuranController', $method)) {
                            echo "✅ Method '$method' exists\n";
                        } else {
                            echo "❌ Method '$method' not found\n";
                        }
                    }
                    
                } else {
                    echo "❌ QuranController class not found\n";
                }
                
            } catch (Exception $e) {
                echo "❌ Error testing controller: " . $e->getMessage() . "\n";
            }
            break;
            
        case 'data_test':
            echo "=== Quran Data Test ===\n\n";
            
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=islamwiki;charset=utf8mb4", "root", "");
                
                echo "Testing Quran data retrieval...\n\n";
                
                // Test surah data
                echo "1. Testing Surah data...\n";
                $stmt = $pdo->query("SELECT * FROM quran_surahs WHERE surah_number = 1 LIMIT 1");
                $surah = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($surah) {
                    echo "✅ Surah 1 found: " . ($surah['name'] ?? 'Unknown') . "\n";
                } else {
                    echo "❌ Surah 1 not found in database\n";
                }
                
                // Test ayah data
                echo "\n2. Testing Ayah data...\n";
                $stmt = $pdo->query("SELECT * FROM quran_ayahs WHERE surah_number = 1 AND ayah_number = 1 LIMIT 1");
                $ayah = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($ayah) {
                    echo "✅ Ayah 1:1 found\n";
                    echo "   Arabic text length: " . strlen($ayah['text_arabic'] ?? '') . " characters\n";
                    echo "   Translation length: " . strlen($ayah['translation'] ?? '') . " characters\n";
                } else {
                    echo "❌ Ayah 1:1 not found in database\n";
                }
                
                // Test translation data
                echo "\n3. Testing Translation data...\n";
                $stmt = $pdo->query("SELECT * FROM quran_translations WHERE surah_number = 1 AND ayah_number = 1 LIMIT 1");
                $translation = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($translation) {
                    echo "✅ Translation for 1:1 found\n";
                    echo "   Translator: " . ($translation['translator'] ?? 'Unknown') . "\n";
                    echo "   Language: " . ($translation['language'] ?? 'Unknown') . "\n";
                } else {
                    echo "❌ Translation for 1:1 not found in database\n";
                }
                
            } catch (PDOException $e) {
                echo "❌ Error testing Quran data: " . $e->getMessage() . "\n";
            }
            break;
            
        default:
            echo "Unknown diagnostic type: $diagnose\n";
    }
}

echo "\n\n<!-- Quran System Diagnosis Complete -->\n";
echo "<!-- Run each diagnostic step to identify and fix the issues -->\n"; 