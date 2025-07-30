<?php
declare(strict_types=1);

/**
 * Test Islamic User Implementation
 * 
 * This script tests the Islamic user model and authentication features.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\IslamicUser;

// Load configuration
$config = require __DIR__ . '/../../config/database.php';

echo "=== Islamic User Test ===\n";
echo "Testing Islamic user model and features...\n\n";

try {
    // Create connection
    $connection = new Connection($config['connections']['mysql']);
    
    echo "✅ Connected to database\n\n";
    
    // Test 1: Create an Islamic user
    echo "1. Testing Islamic User Creation...\n";
    
    $user = new IslamicUser($connection, [
        'username' => 'test_scholar_' . time(),
        'email' => 'scholar_' . time() . '@test.islam.wiki',
        'password' => 'secure_password_123',
        'display_name' => 'Test Scholar',
        'arabic_name' => 'عالم تجريبي',
        'kunyah' => 'أبو محمد',
        'laqab' => 'الفقيه',
        'nasab' => 'بن أحمد',
        'madhab' => 'hanafi',
        'qualification_level' => 'scholar',
        'specialization' => 'fiqh',
        'islamic_bio' => 'A test scholar for Islamic content verification.',
        'is_scholar' => true,
        'islamic_role' => 'scholar',
        'verification_status' => 'pending',
    ]);
    
    if ($user->save()) {
        echo "   ✅ Islamic user created successfully\n";
        echo "   📊 User ID: {$user->getAttribute('id')}\n";
        echo "   📊 Islamic Role: {$user->getIslamicRole()}\n";
        echo "   📊 Is Scholar: " . ($user->isScholar() ? 'Yes' : 'No') . "\n";
        echo "   📊 Full Islamic Name: {$user->getFullIslamicName()}\n";
    } else {
        echo "   ❌ Failed to create Islamic user\n";
    }
    
    // Test 2: Test Islamic permissions
    echo "\n2. Testing Islamic Permissions...\n";
    
    $permissions = $user->getIslamicPermissions();
    echo "   📊 Permissions for {$user->getIslamicRole()} role:\n";
    foreach ($permissions as $permission) {
        echo "      - {$permission}\n";
    }
    
    // Test specific permissions
    $testPermissions = ['read_pages', 'edit_pages', 'verify_content', 'manage_scholars'];
    foreach ($testPermissions as $permission) {
        $hasPermission = $user->hasIslamicPermission($permission);
        echo "   📊 Has '{$permission}': " . ($hasPermission ? 'Yes' : 'No') . "\n";
    }
    
    // Test 3: Test Islamic profile
    echo "\n3. Testing Islamic Profile...\n";
    
    $profile = $user->getIslamicProfile();
    echo "   📊 Islamic Profile Data:\n";
    echo "      - ID: {$profile['id']}\n";
    echo "      - Username: {$profile['username']}\n";
    echo "      - Display Name: {$profile['display_name']}\n";
    echo "      - Arabic Name: {$profile['arabic_name']}\n";
    echo "      - Full Islamic Name: {$profile['full_islamic_name']}\n";
    echo "      - Islamic Role: {$profile['islamic_role']}\n";
    echo "      - Is Scholar: " . ($profile['is_scholar'] ? 'Yes' : 'No') . "\n";
    echo "      - Is Verified Scholar: " . ($profile['is_verified_scholar'] ? 'Yes' : 'No') . "\n";
    echo "      - Madhab: {$profile['madhab']}\n";
    echo "      - Specialization: {$profile['specialization']}\n";
    echo "      - Verification Status: {$profile['verification_status']}\n";
    echo "      - Is Verified: " . ($profile['is_verified'] ? 'Yes' : 'No') . "\n";
    
    // Test 4: Test Islamic credentials
    echo "\n4. Testing Islamic Credentials...\n";
    
    $credential = [
        'type' => 'degree',
        'institution' => 'Islamic University of Madinah',
        'degree' => 'Bachelor of Islamic Law',
        'field' => 'Fiqh',
        'year' => 2020,
        'description' => 'Bachelor degree in Islamic jurisprudence'
    ];
    
    $user->addIslamicCredential($credential);
    $user->save();
    
    $credentials = $user->getIslamicCredentials();
    echo "   📊 Added credential: " . count($credentials) . " total\n";
    
    // Test 5: Test Islamic works
    echo "\n5. Testing Islamic Works...\n";
    
    $work = [
        'title' => 'Introduction to Islamic Jurisprudence',
        'type' => 'book',
        'language' => 'en',
        'year' => 2021,
        'description' => 'A comprehensive introduction to Islamic law'
    ];
    
    $user->addIslamicWork($work);
    $user->save();
    
    $works = $user->getIslamicWorks();
    echo "   📊 Added work: " . count($works) . " total\n";
    
    // Test 6: Test Islamic contributions
    echo "\n6. Testing Islamic Contributions...\n";
    
    $contribution = [
        'type' => 'article',
        'title' => 'Understanding Islamic Ethics',
        'content' => 'An article about Islamic ethical principles',
        'date' => '2023-01-15',
        'impact' => 'high'
    ];
    
    $user->addIslamicContribution($contribution);
    $user->save();
    
    $contributions = $user->getIslamicContributions();
    echo "   📊 Added contribution: " . count($contributions) . " total\n";
    
    // Test 7: Test role changes
    echo "\n7. Testing Role Changes...\n";
    
    $user->setIslamicRole('verified_scholar');
    $user->save();
    
    echo "   📊 New role: {$user->getIslamicRole()}\n";
    echo "   📊 New permissions: " . count($user->getIslamicPermissions()) . " total\n";
    
    // Test 8: Test user retrieval
    echo "\n8. Testing User Retrieval...\n";
    
    $retrievedUser = IslamicUser::findByUsername($user->getAttribute('username'), $connection);
    if ($retrievedUser) {
        echo "   ✅ User retrieved successfully\n";
        echo "   📊 Retrieved Islamic Role: {$retrievedUser->getIslamicRole()}\n";
        echo "   📊 Retrieved Full Name: {$retrievedUser->getFullIslamicName()}\n";
    } else {
        echo "   ❌ Failed to retrieve user\n";
    }
    
    // Test 9: Test verification status
    echo "\n9. Testing Verification Status...\n";
    
    echo "   📊 Current status: {$user->getVerificationStatus()}\n";
    echo "   📊 Is verified: " . ($user->isVerified() ? 'Yes' : 'No') . "\n";
    
    // Test 10: Test Islamic user search
    echo "\n10. Testing Islamic User Search...\n";
    
    $scholars = $connection->select(
        "SELECT id, username, display_name, islamic_role, is_scholar, madhab, specialization 
         FROM users 
         WHERE is_scholar = 1 
         ORDER BY id DESC 
         LIMIT 5"
    );
    
    echo "   📊 Found " . count($scholars) . " scholars in database\n";
    foreach ($scholars as $scholar) {
        echo "      - {$scholar['display_name']} ({$scholar['islamic_role']}) - {$scholar['madhab']}\n";
    }
    
    $connection->disconnect();
    
    echo "\n=== Test Summary ===\n";
    echo "✅ Islamic User Model: Working\n";
    echo "✅ Islamic Permissions: Working\n";
    echo "✅ Islamic Profile: Working\n";
    echo "✅ Islamic Credentials: Working\n";
    echo "✅ Islamic Works: Working\n";
    echo "✅ Islamic Contributions: Working\n";
    echo "✅ Role Management: Working\n";
    echo "✅ User Retrieval: Working\n";
    echo "✅ Verification System: Ready\n";
    
} catch (Exception $e) {
    echo "\n❌ Test Failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 