<?php
/**
 * Hadith Import Script for Islam Wiki
 * 
 * This script imports hadith data from sunnah.com into the Islam Wiki database.
 * Usage: php import_hadiths.php --api-key=YOUR_API_KEY
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from command line.\n");
}

// Parse command line arguments
$options = getopt("", ["api-key:"]);
$apiKey = $options['api-key'] ?? null;

if (!$apiKey) {
    die("Error: API key is required. Usage: php import_hadiths.php --api-key=YOUR_API_KEY\n");
}

// Database configuration
$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('HADITH_DB_DATABASE') ?: 'islamwiki_hadith',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'port' => getenv('DB_PORT') ?: 3306,
    'charset' => 'utf8mb4'
];

// API Configuration
$apiBaseUrl = 'https://api.sunnah.com/v1';
$headers = [
    'x-api-key: ' . $apiKey,
    'Accept: application/json'
];

// Initialize cURL
$ch = curl_init();

/**
 * Make an API request to sunnah.com
 */
function makeApiRequest($url, $headers) {
    global $ch;
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        die('API request failed: ' . curl_error($ch) . "\n");
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200) {
        die("API request failed with HTTP code: $httpCode\nResponse: $response\n");
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die('Failed to decode API response: ' . json_last_error_msg() . "\n");
    }
    
    return $data;
}

/**
 * Get database connection
 */
function getDbConnection($config) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};port={$config['port']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage() . "\n");
    }
}

// Main execution
function main() {
    global $apiBaseUrl, $headers, $dbConfig, $apiKey;
    
    echo "Starting hadith import process...\n";
    
    // Initialize database connection
    $pdo = getDbConnection($dbConfig);
    
    // Step 1: Get collections
    echo "Fetching collections from sunnah.com...\n";
    $collections = makeApiRequest("$apiBaseUrl/collections", $headers);
    
    if (empty($collections['data'])) {
        die("No collections found. Check your API key and try again.\n");
    }
    
    echo "Found " . count($collections['data']) . " collections.\n";
    
    // Process each collection
    foreach ($collections['data'] as $collection) {
        $collectionName = $collection['name'];
        echo "\nProcessing collection: {$collection['title']} ($collectionName)\n";
        
        // Check if collection exists in database
        $stmt = $pdo->prepare("SELECT id FROM hadith_collections WHERE name = ?");
        $stmt->execute([$collectionName]);
        $existingCollection = $stmt->fetch();
        
        if ($existingCollection) {
            echo "  - Collection already exists (ID: {$existingCollection['id']})\n";
            $collectionId = $existingCollection['id'];
        } else {
            // Insert new collection
            $stmt = $pdo->prepare("
                INSERT INTO hadith_collections 
                (name, title, arabic_name, description, total_hadith, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $collectionName,
                $collection['title'] ?? '',
                $collection['arabicTitle'] ?? '',
                $collection['summary'] ?? '',
                $collection['hadiths'] ?? 0
            ]);
            
            $collectionId = $pdo->lastInsertId();
            echo "  - Added collection (ID: $collectionId)\n";
        }
        
        // Get books for this collection
        echo "  Fetching books...\n";
        $books = makeApiRequest("$apiBaseUrl/collections/$collectionName/books", $headers);
        
        if (empty($books['data'])) {
            echo "  - No books found for this collection.\n";
            continue;
        }
        
        echo "  Found " . count($books['data']) . " books.\n";
        
        // Process each book
        foreach ($books['data'] as $book) {
            $bookNumber = $book['bookNumber'];
            echo "    - Processing book {$book['englishTitle']} (#$bookNumber)\n";
            
            // Check if book exists in database
            $stmt = $pdo->prepare("
                SELECT id FROM hadith_books 
                WHERE collection_id = ? AND book_number = ?
            ");
            $stmt->execute([$collectionId, $bookNumber]);
            $existingBook = $stmt->fetch();
            
            if ($existingBook) {
                echo "      Book already exists (ID: {$existingBook['id']})\n";
                $bookId = $existingBook['id'];
            } else {
                // Insert new book
                $stmt = $pdo->prepare("
                    INSERT INTO hadith_books 
                    (collection_id, book_number, title, arabic_title, 
                     book_description, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                
                ");
                
                $stmt->execute([
                    $collectionId,
                    $bookNumber,
                    $book['englishTitle'] ?? '',
                    $book['arabicTitle'] ?? '',
                    $book['intro'] ?? ''
                ]);
                
                $bookId = $pdo->lastInsertId();
                echo "      Added book (ID: $bookId)\n";
            }
            
            // Get hadiths for this book
            echo "      Fetching hadiths...\n";
            $page = 1;
            $limit = 100;
            $totalImported = 0;
            
            do {
                $hadiths = makeApiRequest(
                    "$apiBaseUrl/collections/$collectionName/hadiths?book=$bookNumber&page=$page&limit=$limit",
                    $headers
                );
                
                if (empty($hadiths['data'])) {
                    break;
                }
                
                // Process each hadith in this page
                foreach ($hadiths['data'] as $hadith) {
                    // Check if hadith already exists
                    $stmt = $pdo->prepare("
                        SELECT id FROM hadiths 
                        WHERE collection_id = ? AND hadith_number = ?
                    
                    ");
                    $stmt->execute([$collectionId, $hadith['hadithNumber']]);
                    $existingHadith = $stmt->fetch();
                    
                    if ($existingHadith) {
                        continue; // Skip if already exists
                    }
                    
                    // Insert new hadith
                    $stmt = $pdo->prepare("
                        INSERT INTO hadiths 
                        (collection_id, book_id, hadith_number, 
                         arabic_text, english_text, 
                         grade, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                    
                    ");
                    
                    $stmt->execute([
                        $collectionId,
                        $bookId,
                        $hadith['hadithNumber'],
                        $hadith['arabicText'] ?? '',
                        $hadith['englishText'] ?? '',
                        $hadith['grade'] ?? null
                    ]);
                    
                    $totalImported++;
                }
                
                $page++;
                
                // Be nice to the API
                sleep(1);
                
            } while (!empty($hadiths['data']) && count($hadiths['data']) === $limit);
            
            echo "      Imported $totalImported hadiths for this book.\n";
        }
    }
    
    echo "\nHadith import completed successfully!\n";
}

// Run the main function
main();

// Clean up
curl_close($ch);
