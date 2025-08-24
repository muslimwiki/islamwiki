<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

/**
 * Configuration Management CLI Tool
 *
 * Command-line interface for managing IslamWiki configuration.
 *
 * Usage:
 *   php scripts/config.php list [category]
 *   php scripts/config.php get <key>
 *   php scripts/config.php set <key> <value>
 *   php scripts/config.php validate
 *   php scripts/config.php export [--file=filename]
 *   php scripts/config.php import --file=filename
 *   php scripts/config.php backup [--name=backup_name] [--description=description]
 *   php scripts/config.php restore <backup_id>
 *   php scripts/config.php backups
 *   php scripts/config.php audit [--limit=100] [--offset=0]
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use Application;\Application
use IslamWiki\Core\Configuration\ConfigurationManager;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get configuration manager
$configManager = $container->get(ConfigurationManager::class);

// Parse command line arguments
$command = $argv[1] ?? 'help';
$args = array_slice($argv, 2);

// Parse options
$options = [];
foreach ($args as $arg) {
    if (strpos($arg, '--') === 0) {
        $parts = explode('=', substr($arg, 2), 2);
        $options[$parts[0]] = $parts[1] ?? true;
    }
}

echo "IslamWiki Configuration Management CLI\n";
echo "=====================================\n\n";

try {
    switch ($command) {
        case 'list':
            handleList($configManager, $args, $options);
            break;

        case 'get':
            handleGet($configManager, $args, $options);
            break;

        case 'set':
            handleSet($configManager, $args, $options);
            break;

        case 'validate':
            handleValidate($configManager, $args, $options);
            break;

        case 'export':
            handleExport($configManager, $args, $options);
            break;

        case 'import':
            handleImport($configManager, $args, $options);
            break;

        case 'backup':
            handleBackup($configManager, $args, $options);
            break;

        case 'restore':
            handleRestore($configManager, $args, $options);
            break;

        case 'backups':
            handleBackups($configManager, $args, $options);
            break;

        case 'audit':
            handleAudit($configManager, $args, $options);
            break;

        case 'help':
        default:
            showHelp();
            break;
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";

/**
 * Handle list command
 */
function handleList(ConfigurationManager $configManager, array $args, array $options): void
{
    $category = $args[0] ?? null;

    if ($category) {
        // List configuration for specific category
        $config = $configManager->getCategory($category);
        echo "Configuration for category '{$category}':\n";
        echo str_repeat('-', 50) . "\n";

        foreach ($config as $key => $value) {
            $type = $configManager->getValue("{$category}.{$key}.type", 'string');
            echo "  {$key}: " . formatValue($value, $type) . "\n";
        }
    } else {
        // List all categories
        $categories = $configManager->getCategories();
        echo "Available configuration categories:\n";
        echo str_repeat('-', 50) . "\n";

        foreach ($categories as $category) {
            $count = count($configManager->getCategory($category['name']));
            echo "  {$category['name']}: {$count} settings\n";
        }
    }
}

/**
 * Handle get command
 */
function handleGet(ConfigurationManager $configManager, array $args, array $options): void
{
    if (empty($args[0])) {
        throw new Exception("Key is required. Usage: php scripts/config.php get <key>");
    }

    $key = $args[0];
    $value = $configManager->getValue($key);

    if ($value === null) {
        echo "✗ Configuration key '{$key}' not found.\n";
        return;
    }

    echo "Configuration value for '{$key}':\n";
    echo str_repeat('-', 50) . "\n";
    echo formatValue($value, gettype($value)) . "\n";
}

/**
 * Handle set command
 */
function handleSet(ConfigurationManager $configManager, array $args, array $options): void
{
    if (empty($args[0]) || !isset($args[1])) {
        throw new Exception("Key and value are required. Usage: php scripts/config.php set <key> <value>");
    }

    $key = $args[0];
    $value = $args[1];

    // Handle boolean values
    if (in_array(strtolower($value), ['true', 'false'])) {
        $value = strtolower($value) === 'true';
    }

    // Handle numeric values
    if (is_numeric($value)) {
        $value = strpos($value, '.') !== false ? (float)$value : (int)$value;
    }

    $success = $configManager->setValue($key, $value);

    if ($success) {
        echo "✓ Configuration key '{$key}' set to: " . formatValue($value, gettype($value)) . "\n";
    } else {
        echo "✗ Failed to set configuration key '{$key}'.\n";
    }
}

/**
 * Handle validate command
 */
function handleValidate(ConfigurationManager $configManager, array $args, array $options): void
{
    echo "Validating configuration...\n";
    echo str_repeat('-', 50) . "\n";

    $errors = $configManager->validateConfiguration();

    if (empty($errors)) {
        echo "✓ All configuration values are valid.\n";
    } else {
        echo "✗ Configuration validation errors found:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
    }
}

/**
 * Handle export command
 */
function handleExport(ConfigurationManager $configManager, array $args, array $options): void
{
    $filename = $options['file'] ?? 'islamwiki_config_' . date('Y-m-d_H-i-s') . '.json';

    echo "Exporting configuration to '{$filename}'...\n";

    $config = $configManager->exportConfiguration();
    $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if (file_put_contents($filename, $json)) {
        echo "✓ Configuration exported successfully to '{$filename}'.\n";
    } else {
        echo "✗ Failed to export configuration.\n";
    }
}

/**
 * Handle import command
 */
function handleImport(ConfigurationManager $configManager, array $args, array $options): void
{
    if (empty($options['file'])) {
        throw new Exception("File is required. Usage: php scripts/config.php import --file=filename");
    }

    $filename = $options['file'];

    if (!file_exists($filename)) {
        throw new Exception("File '{$filename}' not found.");
    }

    echo "Importing configuration from '{$filename}'...\n";

    $json = file_get_contents($filename);
    $config = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON file: " . json_last_error_msg());
    }

    $success = $configManager->importConfiguration($config);

    if ($success) {
        echo "✓ Configuration imported successfully from '{$filename}'.\n";
    } else {
        echo "✗ Failed to import configuration.\n";
    }
}

/**
 * Handle backup command
 */
function handleBackup(ConfigurationManager $configManager, array $args, array $options): void
{
    $name = $options['name'] ?? 'CLI_Backup_' . date('Y-m-d_H-i-s');
    $description = $options['description'] ?? 'Configuration backup created via CLI';

    echo "Creating configuration backup '{$name}'...\n";

    $success = $configManager->createBackup($name, null, $description);

    if ($success) {
        echo "✓ Configuration backup '{$name}' created successfully.\n";
    } else {
        echo "✗ Failed to create configuration backup.\n";
    }
}

/**
 * Handle restore command
 */
function handleRestore(ConfigurationManager $configManager, array $args, array $options): void
{
    if (empty($args[0])) {
        throw new Exception("Backup ID is required. Usage: php scripts/config.php restore <backup_id>");
    }

    $backupId = (int)$args[0];

    echo "Restoring configuration from backup ID {$backupId}...\n";

    $success = $configManager->restoreBackup($backupId);

    if ($success) {
        echo "✓ Configuration restored successfully from backup ID {$backupId}.\n";
    } else {
        echo "✗ Failed to restore configuration from backup ID {$backupId}.\n";
    }
}

/**
 * Handle backups command
 */
function handleBackups(ConfigurationManager $configManager, array $args, array $options): void
{
    echo "Available configuration backups:\n";
    echo str_repeat('-', 50) . "\n";

    $backups = $configManager->getBackups();

    if (empty($backups)) {
        echo "No backups found.\n";
        return;
    }

    foreach ($backups as $backup) {
        $date = date('Y-m-d H:i:s', strtotime($backup['created_at']));
        echo "  ID: {$backup['id']} | Name: {$backup['backup_name']} | Date: {$date}\n";
        if (!empty($backup['description'])) {
            echo "      Description: {$backup['description']}\n";
        }
        echo "\n";
    }
}

/**
 * Handle audit command
 */
function handleAudit(ConfigurationManager $configManager, array $args, array $options): void
{
    $limit = (int)($options['limit'] ?? 100);
    $offset = (int)($options['offset'] ?? 0);

    echo "Configuration audit log (limit: {$limit}, offset: {$offset}):\n";
    echo str_repeat('-', 50) . "\n";

    $auditLog = $configManager->getAuditLog($limit, $offset);

    if (empty($auditLog)) {
        echo "No audit log entries found.\n";
        return;
    }

    foreach ($auditLog as $entry) {
        $date = date('Y-m-d H:i:s', strtotime($entry['created_at']));
        $changeType = strtoupper($entry['change_type']);
        echo "  [{$date}] {$changeType} - {$entry['category']}.{$entry['key_name']}\n";
        if (!empty($entry['old_value']) || !empty($entry['new_value'])) {
            echo "      Old: " . formatValue($entry['old_value'], 'string') . "\n";
            echo "      New: " . formatValue($entry['new_value'], 'string') . "\n";
        }
        echo "\n";
    }
}

/**
 * Show help information
 */
function showHelp(): void
{
    echo "Usage: php scripts/config.php <command> [options]\n\n";
    echo "Commands:\n";
    echo "  list [category]           List all configuration or specific category\n";
    echo "  get <key>                 Get configuration value\n";
    echo "  set <key> <value>         Set configuration value\n";
    echo "  validate                  Validate all configuration\n";
    echo "  export [--file=filename]  Export configuration to JSON file\n";
    echo "  import --file=filename    Import configuration from JSON file\n";
    echo "  backup [--name=name] [--description=desc]  Create configuration backup\n";
    echo "  restore <backup_id>       Restore configuration from backup\n";
    echo "  backups                   List all configuration backups\n";
    echo "  audit [--limit=100] [--offset=0]  Show configuration audit log\n";
    echo "  help                      Show this help message\n\n";
    echo "Examples:\n";
    echo "  php scripts/config.php list\n";
    echo "  php scripts/config.php list Core\n";
    echo "  php scripts/config.php get app.name\n";
    echo "  php scripts/config.php set app.debug true\n";
    echo "  php scripts/config.php validate\n";
    echo "  php scripts/config.php export --file=config.json\n";
    echo "  php scripts/config.php import --file=config.json\n";
    echo "  php scripts/config.php backup --name=my_backup --description=Test backup\n";
    echo "  php scripts/config.php restore 1\n";
    echo "  php scripts/config.php backups\n";
    echo "  php scripts/config.php audit --limit=50\n";
}

/**
 * Format value for display
 */
function formatValue($value, string $type): string
{
    if ($value === null) {
        return 'null';
    }

    switch ($type) {
        case 'boolean':
            return $value ? 'true' : 'false';
        case 'array':
        case 'json':
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        default:
            return (string)$value;
    }
}
