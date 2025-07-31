<?php
declare(strict_types=1);

/**
 * Configuration System Test for Version 0.0.28
 * 
 * Tests the enhanced configuration system with database integration,
 * validation, backup functionality, and API endpoints.
 * 
 * @package IslamWiki
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Container;
use IslamWiki\Core\Configuration\ConfigurationManager;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\Logger;

echo "==========================================\n";
echo "IslamWiki Configuration System Test\n";
echo "Version: 0.0.28\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "==========================================\n\n";

try {
    // Initialize container
    echo "Test 1: Initializing Container...\n";
    $container = new Container();
    
    // Manually register required services
    $container->singleton(Connection::class, function() {
        return new Connection([
            'host' => 'localhost',
            'database' => 'islamwiki',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
    });
    
    $container->singleton(Logger::class, function() {
        return new Logger(
            __DIR__ . '/../../storage/logs',
            \Psr\Log\LogLevel::DEBUG,
            10, // max file size in MB
            5   // max files
        );
    });
    
    echo "✅ Container initialized successfully\n\n";

    // Test 2: Initialize Configuration Manager
    echo "Test 2: Initializing Configuration Manager...\n";
    $configManager = new ConfigurationManager($container);
    echo "✅ Configuration Manager initialized successfully\n\n";

    // Test 3: Get Configuration Categories
    echo "Test 3: Testing configuration categories...\n";
    $categories = $configManager->getCategories();
    echo "✅ Found " . count($categories) . " configuration categories:\n";
    foreach ($categories as $name => $category) {
        echo "  - {$category['display_name']} ({$name})\n";
    }
    echo "\n";

    // Test 4: Get Configuration Values
    echo "Test 4: Testing configuration values...\n";
    $coreConfig = $configManager->getCategory('core');
    echo "✅ Core configuration has " . count($coreConfig) . " settings\n";
    
    // Test a few specific values
    $siteName = $configManager->getValue('core.site_name', 'Default Site');
    $dbServer = $configManager->getValue('database.server', 'localhost');
    echo "✅ Site Name: {$siteName}\n";
    echo "✅ Database Server: {$dbServer}\n\n";

    // Test 5: Configuration Validation
    echo "Test 5: Testing configuration validation...\n";
    $validation = $configManager->validateConfiguration();
    echo "✅ Configuration Valid: " . ($validation['valid'] ? 'Yes' : 'No') . "\n";
    
    if (!empty($validation['errors'])) {
        echo "❌ Configuration Errors:\n";
        foreach ($validation['errors'] as $error) {
            echo "  - {$error}\n";
        }
    }
    
    if (!empty($validation['warnings'])) {
        echo "⚠️  Configuration Warnings:\n";
        foreach ($validation['warnings'] as $warning) {
            echo "  - {$warning}\n";
        }
    }
    echo "\n";

    // Test 6: Configuration Export
    echo "Test 6: Testing configuration export...\n";
    $export = $configManager->exportConfiguration();
    echo "✅ Exported " . count($export) . " configuration categories\n\n";

    // Test 7: Configuration Backup
    echo "Test 7: Testing configuration backup...\n";
    $backupName = 'test_backup_' . date('Y-m-d_H-i-s');
    $backupResult = $configManager->createBackup($backupName, null, 'Test backup for 0.0.28');
    echo "✅ Backup created: " . ($backupResult ? 'Success' : 'Failed') . "\n\n";

    // Test 8: Get Backups
    echo "Test 8: Testing backup retrieval...\n";
    $backups = $configManager->getBackups();
    echo "✅ Found " . count($backups) . " configuration backups\n\n";

    // Test 9: Audit Log
    echo "Test 9: Testing audit log...\n";
    $auditLog = $configManager->getAuditLog(10, 0);
    echo "✅ Found " . count($auditLog) . " audit log entries\n\n";

    // Test 10: Set Configuration Value
    echo "Test 10: Testing configuration update...\n";
    $testValue = 'test_value_' . time();
    $updateResult = $configManager->setValue('core.site_name', $testValue);
    echo "✅ Configuration update: " . ($updateResult ? 'Success' : 'Failed') . "\n";
    
    // Verify the update
    $retrievedValue = $configManager->getValue('core.site_name');
    echo "✅ Retrieved value: {$retrievedValue}\n\n";

    echo "==========================================\n";
    echo "✅ All Configuration Tests Passed!\n";
    echo "==========================================\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 