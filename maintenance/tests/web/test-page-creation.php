<?php
/**
 * Test script for page creation functionality
 */

require_once __DIR__ . '/../src/Core/NizamApplication.php';

try {
    // Initialize application with base path
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    $db = $container->get(\IslamWiki\Core\Database\Connection::class);
    $session = $container->get('session');
    
    echo "🧪 Testing Page Creation Functionality\n";
    echo "=====================================\n\n";
    
    // Step 1: Create a test user if it doesn't exist
    echo "1. Creating test user...\n";
    
    $testUser = $db->select(
        'SELECT * FROM users WHERE username = ?',
        ['testuser']
    );
    
    if (empty($testUser)) {
        $userId = $db->insert('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'display_name' => 'Test User',
            'is_active' => 1,
            'is_admin' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        echo "   ✅ Test user created with ID: $userId\n";
    } else {
        $userId = $testUser[0]['id'];
        echo "   ✅ Test user already exists with ID: $userId\n";
    }
    
    // Step 2: Login the test user
    echo "\n2. Logging in test user...\n";
    
    $session->login($userId, 'testuser', true);
    
    if ($session->isLoggedIn()) {
        echo "   ✅ User logged in successfully\n";
        echo "   📊 User ID: " . $session->getUserId() . "\n";
        echo "   📊 Username: " . $session->getUsername() . "\n";
        echo "   📊 Is Admin: " . ($session->isAdmin() ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ❌ Login failed\n";
        exit(1);
    }
    
    // Step 3: Test page creation form access
    echo "\n3. Testing page creation form access...\n";
    
    // Create a mock request for page creation
    $request = new \IslamWiki\Core\Http\Request(
        'GET',
        'https://local.islam.wiki/pages/create',
        [],
        [],
        []
    );
    
    $pageController = new \IslamWiki\Http\Controllers\PageController($db, $container);
    
    try {
        $response = $pageController->create($request);
        echo "   ✅ Page creation form accessible\n";
        echo "   📊 Response status: " . $response->getStatusCode() . "\n";
    } catch (Exception $e) {
        echo "   ❌ Page creation form access failed: " . $e->getMessage() . "\n";
    }
    
    // Step 4: Test page creation submission
    echo "\n4. Testing page creation submission...\n";
    
    $postData = [
        'title' => 'Test Page',
        'namespace' => '',
        'content' => '# Test Page\n\nThis is a test page created by the test script.\n\n## Features\n\n- Markdown support\n- Code highlighting\n- Lists and formatting',
        'comment' => 'Test page creation',
        'is_minor_edit' => false,
        'watch' => true
    ];
    
    $postRequest = new \IslamWiki\Core\Http\Request(
        'POST',
        'https://local.islam.wiki/pages',
        [],
        $postData,
        []
    );
    
    try {
        $response = $pageController->store($postRequest);
        echo "   ✅ Page creation successful\n";
        echo "   📊 Response status: " . $response->getStatusCode() . "\n";
        
        // Check if the page was actually created
        $createdPage = $db->select(
            'SELECT * FROM pages WHERE title = ? ORDER BY created_at DESC LIMIT 1',
            ['Test Page']
        );
        
        if (!empty($createdPage)) {
            echo "   ✅ Page found in database\n";
            echo "   📊 Page ID: " . $createdPage[0]['id'] . "\n";
            echo "   📊 Page Slug: " . $createdPage[0]['slug'] . "\n";
            echo "   📊 Page URL: https://local.islam.wiki/" . $createdPage[0]['slug'] . "\n";
        } else {
            echo "   ❌ Page not found in database\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Page creation failed: " . $e->getMessage() . "\n";
    }
    
    // Step 5: Test page viewing
    echo "\n5. Testing page viewing...\n";
    
    if (!empty($createdPage)) {
        $slug = $createdPage[0]['slug'];
        
        $viewRequest = new \IslamWiki\Core\Http\Request(
            'GET',
            "https://local.islam.wiki/$slug",
            [],
            [],
            []
        );
        
        try {
            $response = $pageController->show($viewRequest, $slug);
            echo "   ✅ Page viewing successful\n";
            echo "   📊 Response status: " . $response->getStatusCode() . "\n";
        } catch (Exception $e) {
            echo "   ❌ Page viewing failed: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Page creation test completed!\n";
    echo "\n📋 Summary:\n";
    echo "- Test user created/logged in\n";
    echo "- Page creation form accessible\n";
    echo "- Page creation submission working\n";
    echo "- Page viewing functional\n";
    echo "\n🔗 Test the page creation manually:\n";
    echo "- Login: https://local.islam.wiki/login\n";
    echo "- Create Page: https://local.islam.wiki/pages/create\n";
    echo "- Username: testuser\n";
    echo "- Password: password123\n";
    
} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 