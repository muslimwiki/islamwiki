<?php
/**
 * Test Page Creation Form
 * 
 * This script tests the page creation form functionality.
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

echo "=== Test Page Creation Form ===\n\n";

// Step 1: Test the form page
echo "1. Testing page creation form...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/pages/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Status Code: $httpCode\n";

if ($httpCode === 200) {
    echo "   ✅ Page creation form accessible\n";
    
    // Check if the form contains expected elements
    if (strpos($response, 'name="title"') !== false) {
        echo "   ✅ Form contains title field\n";
    } else {
        echo "   ❌ Form missing title field\n";
    }
    
    if (strpos($response, 'name="content"') !== false) {
        echo "   ✅ Form contains content field\n";
    } else {
        echo "   ❌ Form missing content field\n";
    }
    
    if (strpos($response, 'method="POST"') !== false) {
        echo "   ✅ Form uses POST method\n";
    } else {
        echo "   ❌ Form not using POST method\n";
    }
    
} else {
    echo "   ❌ Page creation form not accessible\n";
}

// Step 2: Test form submission
echo "\n2. Testing form submission...\n";

$testData = [
    'title' => 'Test Page ' . date('Y-m-d H:i:s'),
    'namespace' => '',
    'content' => "# Test Page\n\nThis is a test page created by the test script.\n\n## Features\n\n- Markdown support\n- Code highlighting\n- Lists and formatting\n\n## Code Example\n\n```php\n<?php\necho 'Hello World!';\n?>\n```",
    'comment' => 'Test page creation',
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
    'User-Agent: Test Script'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "   HTTP Status Code: $httpCode\n";
if ($location) {
    echo "   Redirect Location: $location\n";
}

if ($httpCode === 302 && $location) {
    echo "   ✅ Form submission successful (redirected)\n";
    echo "   📄 Page should be created at: $location\n";
} elseif ($httpCode === 200) {
    echo "   ✅ Form submission successful (no redirect)\n";
} else {
    echo "   ❌ Form submission failed\n";
    echo "   Response: " . substr($response, 0, 500) . "...\n";
}

// Step 3: Check if page was created in database
echo "\n3. Checking database for created page...\n";

// Use the same slug generation as the application
$title = $testData['title'];
$namespace = $testData['namespace'];

$slug = $title;
$slug = mb_strtolower($slug, 'UTF-8');
$slug = str_replace(' ', '-', $slug);
$slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $slug);
$slug = preg_replace('/-+/', '-', $slug);
$slug = trim($slug, '-');

if (!empty($namespace)) {
    $slug = $namespace . ':' . $slug;
}
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if ($page) {
    echo "   ✅ Page found in database\n";
    echo "   📊 Page ID: {$page['id']}\n";
    echo "   📊 Page Title: {$page['title']}\n";
    echo "   📊 Page Slug: {$page['slug']}\n";
    echo "   📊 Content Length: " . strlen($page['content']) . " characters\n";
    
    // Check if revision was created
    $stmt = $pdo->prepare("SELECT * FROM page_history WHERE page_id = ?");
    $stmt->execute([$page['id']]);
    $revision = $stmt->fetch();
    
    if ($revision) {
        echo "   ✅ Page revision created\n";
        echo "   📊 Revision ID: {$revision['id']}\n";
    } else {
        echo "   ❌ No page revision found\n";
    }
} else {
    echo "   ❌ Page not found in database\n";
}

echo "\n=== Test Complete ===\n"; 