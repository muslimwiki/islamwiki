<?php
// Enable error reporting
error_reporting(-1);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/php_errors.log');

// Simple debug function
function debug($message, $data = null) {
    $output = "[DEBUG] " . $message . "\n";
    if ($data !== null) {
        ob_start();
        var_dump($data);
        $output .= "Data: " . ob_get_clean() . "\n";
    }
    fwrite(STDERR, $output);
    fwrite(STDOUT, $output);
}

// Require Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

// Check if database manager exists
if (!class_exists('Illuminate\\Database\\Capsule\\Manager')) {
    die("Error: Database manager not found. Please run 'composer require illuminate/database'\n");
}

try {
    // Set up database
    $db = new \Illuminate\Database\Capsule\Manager;
    $db->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);
    $db->setAsGlobal();
    $db->bootEloquent();
    
    // Enable query logging
    $db->connection()->enableQueryLog();
    
    debug("Database connection established");
    
    // Check if pages table exists
    $tables = $db->connection()->getPdo()->query("SELECT name FROM sqlite_master WHERE type='table' AND name='pages'")->fetchAll();
    debug("Tables in database:", $tables);
    
    // Create pages table if it doesn't exist
    $db->connection()->getPdo()->exec("
        CREATE TABLE IF NOT EXISTS pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content TEXT NOT NULL,
            is_published BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    debug("Created pages table");
    
    // Insert a test page
    $db->connection()->getPdo()->exec("
        INSERT INTO pages (title, slug, content, is_published)
        VALUES ('Test Page', 'test-page', 'This is a test page', 1)
    ");
    
    debug("Inserted test page");
    
    // Query the test page
    $page = $db->connection()->getPdo()->query("SELECT * FROM pages WHERE slug = 'test-page'")->fetch();
    debug("Retrieved page:", $page);
    
    debug("Queries executed:", $db->connection()->getQueryLog());
    
} catch (\Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}

echo "Test completed successfully!\n";
