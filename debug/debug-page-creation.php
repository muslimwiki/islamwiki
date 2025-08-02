<?php
/**
 * Debug Page Creation
 * 
 * This script tests page creation step by step to identify issues.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

// Initialize database connection
$pdo = new PDO(
    "mysql:host={$wgDBserver};dbname={$wgDBname};charset=utf8mb4",
    $wgDBuser,
    $wgDBpassword,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

echo "=== Debug Page Creation ===\n\n";

// Step 1: Test database connection
echo "1. Testing database connection...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM pages");
    $result = $stmt->fetch();
    echo "   ✅ Database connection successful\n";
    echo "   📊 Current pages in database: {$result['count']}\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Test direct page insertion
echo "\n2. Testing direct page insertion...\n";
try {
    $testTitle = 'Debug Test Page ' . date('Y-m-d H:i:s');
    $testSlug = strtolower(str_replace(' ', '-', $testTitle));
    $testContent = "# Debug Test Page\n\nThis is a test page for debugging.\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO pages (title, slug, content, namespace, content_format, created_by, updated_by, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        $testTitle,
        $testSlug,
        $testContent,
        '',
        'markdown',
        1, // created_by
        1, // updated_by
        $now,
        $now
    ]);
    
    $pageId = $pdo->lastInsertId();
    echo "   ✅ Direct page insertion successful\n";
    echo "   📊 Page ID: $pageId\n";
    echo "   📊 Page Title: $testTitle\n";
    echo "   📊 Page Slug: $testSlug\n";
    
    // Clean up
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$pageId]);
    echo "   🧹 Test page cleaned up\n";
    
} catch (Exception $e) {
    echo "   ❌ Direct page insertion failed: " . $e->getMessage() . "\n";
}

// Step 3: Test Page model
echo "\n3. Testing Page model...\n";
try {
    // Create a mock Connection class for testing
    class MockConnection {
        private $pdo;
        
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }
        
        public function table($table) {
            return new MockQueryBuilder($this->pdo, $table);
        }
    }
    
    class MockQueryBuilder {
        private $pdo;
        private $table;
        
        public function __construct($pdo, $table) {
            $this->pdo = $pdo;
            $this->table = $table;
        }
        
        public function insertGetId($data) {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return $this->pdo->lastInsertId();
        }
    }
    
    $connection = new MockConnection($pdo);
    
    $testTitle = 'Model Test Page ' . date('Y-m-d H:i:s');
    $testSlug = strtolower(str_replace(' ', '-', $testTitle));
    $testContent = "# Model Test Page\n\nThis is a test page using the Page model.\n";
    
    $page = new \IslamWiki\Models\Page($connection, [
        'title' => $testTitle,
        'slug' => $testSlug,
        'content' => $testContent,
        'namespace' => '',
        'content_format' => 'markdown',
    ]);
    
    $result = $page->save();
    echo "   ✅ Page model save successful: " . ($result ? 'true' : 'false') . "\n";
    echo "   📊 Page ID: " . $page->getAttribute('id') . "\n";
    echo "   📊 Page Title: " . $page->getAttribute('title') . "\n";
    echo "   📊 Page Slug: " . $page->getAttribute('slug') . "\n";
    
    // Clean up
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$page->getAttribute('id')]);
    echo "   🧹 Test page cleaned up\n";
    
} catch (Exception $e) {
    echo "   ❌ Page model test failed: " . $e->getMessage() . "\n";
    echo "   📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Step 4: Test form submission with detailed response
echo "\n4. Testing form submission with detailed response...\n";

$testData = [
    'title' => 'Form Test Page ' . date('Y-m-d H:i:s'),
    'namespace' => '',
    'content' => "# Form Test Page\n\nThis is a test page created via form submission.\n",
    'comment' => 'Debug test page creation',
    'content_format' => 'markdown'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/pages');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: Debug Script'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "   HTTP Status Code: $httpCode\n";
if ($location) {
    echo "   Redirect Location: $location\n";
}

echo "   Response Length: " . strlen($response) . " characters\n";
echo "   Response Preview: " . substr($response, 0, 200) . "...\n";

// Check if page was created
$slug = strtolower(str_replace(' ', '-', $testData['title']));
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if ($page) {
    echo "   ✅ Page found in database after form submission\n";
    echo "   📊 Page ID: {$page['id']}\n";
    echo "   📊 Page Title: {$page['title']}\n";
    echo "   📊 Page Slug: {$page['slug']}\n";
    
    // Clean up
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$page['id']]);
    echo "   🧹 Test page cleaned up\n";
} else {
    echo "   ❌ Page not found in database after form submission\n";
}

echo "\n=== Debug Complete ===\n"; 