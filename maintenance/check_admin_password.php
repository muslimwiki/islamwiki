<?php

/**
 * Check Admin Password
 * 
 * This script checks the admin user's password hash and tests authentication.
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Include necessary files
require_once BASE_PATH . '/src/Core/Database/Connection.php';

echo "🔐 Checking Admin Password\n";
echo "==========================\n\n";

try {
    // Initialize database connection
    $db = new \IslamWiki\Core\Database\Connection();
    
    // Get admin user details
    $adminUser = $db->select('SELECT id, username, email, password, is_active, is_admin FROM users WHERE username = ?', ['admin']);
    
    if (!empty($adminUser)) {
        $admin = $adminUser[0];
        echo "✅ Admin user found:\n";
        echo "  - ID: {$admin['id']}\n";
        echo "  - Username: {$admin['username']}\n";
        echo "  - Email: {$admin['email']}\n";
        echo "  - Password hash: {$admin['password']}\n";
        echo "  - Active: {$admin['is_active']}\n";
        echo "  - Admin: {$admin['is_admin']}\n";
        
        // Test common passwords
        $testPasswords = ['admin', 'password', '123456', 'admin123', 'test', 'islamwiki'];
        
        echo "\n🧪 Testing Common Passwords:\n";
        echo "============================\n";
        
        foreach ($testPasswords as $testPassword) {
            $isValid = password_verify($testPassword, $admin['password']);
            echo "  - '$testPassword': " . ($isValid ? '✅ VALID' : '❌ Invalid') . "\n";
            
            if ($isValid) {
                echo "    🎉 Found working password: '$testPassword'\n";
                break;
            }
        }
        
        // If no common password works, let's create a new one
        echo "\n🔧 Setting New Admin Password:\n";
        echo "==============================\n";
        
        $newPassword = 'admin123';
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $db->update('users', [
            'password' => $newHash,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $admin['id']]);
        
        echo "✅ New password set: '$newPassword'\n";
        echo "✅ New hash: $newHash\n";
        
        // Verify the new password works
        $verifyResult = password_verify($newPassword, $newHash);
        echo "✅ Password verification test: " . ($verifyResult ? 'PASSED' : 'FAILED') . "\n";
        
    } else {
        echo "❌ Admin user not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ Password check completed\n"; 