<?php

/**
 * Configuration Debug Test for Version 0.0.28
 *
 * Debug test to understand configuration update issues.
 *
 * @package IslamWiki
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

require_once __DIR__ . '/../../src/helpers.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Container;
use IslamWiki\Core\Configuration\ConfigurationManager;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\Logger;

echo "==========================================\n";
echo "Configuration Debug Test\n";
echo "Version: 0.0.28\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "==========================================\n\n";

try {
    // Initialize container
    echo "Test 1: Initializing Container...\n";
    $container = new Container();

    // Manually register required services
    $container->singleton(Connection::class, function () {
        return new Connection([
            'host' => 'localhost',
            'database' => 'islamwiki',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
    });

    $container->singleton(Logger::class, function () {
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

    // Test 3: Check if test_setting exists
    echo "Test 3: Checking if test_setting exists...\n";
    $testValue = $configManager->getValue('core.test_setting');
    echo "✅ Current value: " . ($testValue ?: 'null') . "\n";

    // Test 4: Check core category
    echo "Test 4: Checking core category...\n";
    $coreConfig = $configManager->getCategory('core');
    echo "✅ Core config keys: " . implode(', ', array_keys($coreConfig)) . "\n";

    // Test 5: Try to set a value that exists
    echo "Test 5: Testing with existing configuration...\n";
    $updateResult = $configManager->setValue('core.site_name', 'Test Site Name');
    echo "✅ Update result: " . ($updateResult ? 'Success' : 'Failed') . "\n";

    // Test 6: Verify the update
    $newValue = $configManager->getValue('core.site_name');
    echo "✅ New value: {$newValue}\n";

    // Test 7: Try to create a new configuration
    echo "Test 7: Testing with new configuration...\n";
    $db = $container->get(Connection::class);

    // Insert a test configuration
    $db->table('configuration')->insert([
        'category' => 'core',
        'key_name' => 'test_setting',
        'value' => 'initial_value',
        'type' => 'string',
        'description' => 'Test setting for debugging',
        'is_sensitive' => false,
        'is_required' => false,
        'validation_rules' => json_encode(['string']),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    echo "✅ Test configuration inserted\n";

    // Reload configuration
    $configManager->loadConfiguration();

    // Try to update the new configuration
    $updateResult2 = $configManager->setValue('core.test_setting', 'updated_value');
    echo "✅ Update result 2: " . ($updateResult2 ? 'Success' : 'Failed') . "\n";

    // Verify the update
    $finalValue = $configManager->getValue('core.test_setting');
    echo "✅ Final value: {$finalValue}\n";

    echo "==========================================\n";
    echo "✅ Debug Test Complete!\n";
    echo "==========================================\n";
} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
