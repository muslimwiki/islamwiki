<?php

/**
 * Create User Settings Table
 *
 * Manually creates the user_settings table for storing individual user preferences
 *
 * @package IslamWiki\Scripts
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;

echo "Creating user_settings table...\n";

try {
    // Initialize the application
    $app = new NizamApplication(__DIR__ . '/..');
    $container = $app->getContainer();
    $db = $container->get('db');

    // Create user_settings table
    $sql = "
    CREATE TABLE IF NOT EXISTS user_settings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        settings JSON NOT NULL COMMENT 'JSON object containing user settings',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_settings (user_id),
        INDEX idx_user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $result = $db->exec($sql);

    if ($result !== false) {
        echo "✅ user_settings table created successfully!\n";

        // Test the table
        $stmt = $db->prepare("DESCRIBE user_settings");
        $stmt->execute();
        $columns = $stmt->fetchAll();

        echo "\nTable structure:\n";
        foreach ($columns as $column) {
            $field = is_array($column) ? $column['Field'] : $column->Field;
            $type = is_array($column) ? $column['Type'] : $column->Type;
            echo "- {$field}: {$type}\n";
        }
    } else {
        echo "❌ Failed to create user_settings table\n";
    }
} catch (\Throwable $e) {
    echo "❌ Error creating user_settings table: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
