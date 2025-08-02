<?php
/**
 * Create Admin User Script
 * 
 * Creates the admin user with username: admin, password: password
 * 
 * @package IslamWiki
 * @version 0.0.34
 * @license AGPL-3.0-only
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include necessary files
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/Core/Database/Connection.php';

use IslamWiki\Core\Database\Connection;

try {
    // Initialize database connection
    $db = new Connection();
    
    // Check if admin user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "✅ Admin user already exists!\n";
        echo "Username: admin\n";
        echo "Password: password\n";
        exit(0);
    }
    
    // Create admin user
    $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO users (username, email, password, display_name, is_admin, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $result = $stmt->execute([
        'admin',
        'admin@islam.wiki',
        $hashedPassword,
        'Administrator',
        true
    ]);
    
    if ($result) {
        echo "✅ Admin user created successfully!\n";
        echo "Username: admin\n";
        echo "Password: password\n";
        echo "Email: admin@islam.wiki\n";
        echo "Admin privileges: Yes\n";
    } else {
        echo "❌ Failed to create admin user\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}
?> 