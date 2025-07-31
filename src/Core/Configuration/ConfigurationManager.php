<?php
declare(strict_types=1);

/**
 * Enhanced Configuration Manager
 * 
 * Advanced configuration management system with categories, validation,
 * audit logging, backup functionality, and extension integration.
 * 
 * @package IslamWiki\Core\Configuration
 * @version 0.0.20
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Core\Configuration;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container;
use IslamWiki\Core\Logging\Logger;

class ConfigurationManager
{
    /**
     * The database connection.
     */
    private Connection $db;

    /**
     * The service container.
     */
    private Container $container;

    /**
     * The logger instance.
     */
    private Logger $logger;

    /**
     * Configuration cache.
     */
    private array $configCache = [];

    /**
     * Configuration categories cache.
     */
    private array $categoriesCache = [];

    /**
     * Validation rules cache.
     */
    private array $validationCache = [];

    /**
     * Create a new configuration manager instance.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->db = $container->get(Connection::class);
        $this->logger = $container->get(Logger::class);
        $this->loadConfiguration();
    }

    /**
     * Load configuration from database.
     */
    public function loadConfiguration(): void
    {
        try {
            // Load categories
            $this->loadCategories();
            
            // Load configuration values
            $this->loadConfigurationValues();
            
            $this->logger->info('Configuration loaded successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to load configuration: ' . $e->getMessage());
            // Don't throw the exception, just log it
            // This allows the application to start even if configuration tables don't exist yet
        }
    }

    /**
     * Get configuration value.
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        $category = $this->getCategoryFromKey($key);
        $keyName = $this->getKeyNameFromKey($key);
        
        if (isset($this->configCache[$category][$keyName])) {
            $config = $this->configCache[$category][$keyName];
            return $this->castValue($config['value'], $config['type']);
        }
        
        return $default;
    }

    /**
     * Set configuration value.
     */
    public function setValue(string $key, mixed $value, ?int $userId = null): bool
    {
        try {
            $category = $this->getCategoryFromKey($key);
            $keyName = $this->getKeyNameFromKey($key);
            
            // Get current value for audit
            $oldValue = $this->getValue($key);
            
            // Validate the new value
            if (!$this->validateValue($category, $keyName, $value)) {
                return false;
            }
            
            // Update the configuration
            $type = $this->getConfigurationType($category, $keyName);
            $serializedValue = $this->serializeValue($value, $type);
            
            $this->db->table('configuration')
                ->where('category', $category)
                ->where('key_name', $keyName)
                ->update([
                    'value' => $serializedValue,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            // Update cache
            $this->configCache[$category][$keyName]['value'] = $serializedValue;
            
            // Log the change
            $this->logConfigurationChange($category, $keyName, $oldValue, $value, $userId);
            
            $this->logger->info("Configuration updated: {$key} = " . json_encode($value));
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to set configuration {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get configuration by category.
     */
    public function getCategory(string $category): array
    {
        if (!isset($this->configCache[$category])) {
            return [];
        }
        
        $result = [];
        foreach ($this->configCache[$category] as $key => $config) {
            $result[$key] = [
                'value' => $this->castValue($config['value'], $config['type']),
                'type' => $config['type'],
                'description' => $config['description'],
                'is_sensitive' => $config['is_sensitive'],
                'is_required' => $config['is_required'],
                'validation_rules' => $config['validation_rules']
            ];
        }
        
        return $result;
    }

    /**
     * Get all configuration categories.
     */
    public function getCategories(): array
    {
        return $this->categoriesCache;
    }

    /**
     * Validate configuration.
     */
    public function validateConfiguration(): array
    {
        $errors = [];
        $warnings = [];
        
        foreach ($this->configCache as $category => $configs) {
            foreach ($configs as $key => $config) {
                $validationResult = $this->validateConfigurationItem($category, $key, $config);
                
                if (!empty($validationResult['errors'])) {
                    $errors = array_merge($errors, $validationResult['errors']);
                }
                
                if (!empty($validationResult['warnings'])) {
                    $warnings = array_merge($warnings, $validationResult['warnings']);
                }
            }
        }
        
        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'valid' => empty($errors)
        ];
    }

    /**
     * Export configuration.
     */
    public function exportConfiguration(): array
    {
        $export = [
            'version' => '0.0.20',
            'exported_at' => date('Y-m-d H:i:s'),
            'categories' => [],
            'configuration' => []
        ];
        
        foreach ($this->categoriesCache as $category) {
            $export['categories'][] = $category;
        }
        
        foreach ($this->configCache as $category => $configs) {
            foreach ($configs as $key => $config) {
                $export['configuration'][] = [
                    'category' => $category,
                    'key_name' => $key,
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'description' => $config['description'],
                    'is_sensitive' => $config['is_sensitive'],
                    'is_required' => $config['is_required'],
                    'validation_rules' => $config['validation_rules']
                ];
            }
        }
        
        return $export;
    }

    /**
     * Import configuration.
     */
    public function importConfiguration(array $config): bool
    {
        try {
            // Validate import structure
            if (!isset($config['configuration']) || !is_array($config['configuration'])) {
                throw new \InvalidArgumentException('Invalid configuration import format');
            }
            
            $imported = 0;
            $errors = [];
            
            foreach ($config['configuration'] as $item) {
                if (!isset($item['category'], $item['key_name'], $item['value'])) {
                    $errors[] = "Invalid configuration item: missing required fields";
                    continue;
                }
                
                if ($this->setValue($item['category'] . '.' . $item['key_name'], $item['value'])) {
                    $imported++;
                } else {
                    $errors[] = "Failed to import configuration: {$item['category']}.{$item['key_name']}";
                }
            }
            
            if (!empty($errors)) {
                $this->logger->warning('Configuration import completed with errors: ' . implode(', ', $errors));
            }
            
            $this->logger->info("Configuration import completed: {$imported} items imported");
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Configuration import failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create configuration backup.
     */
    public function createBackup(string $backupName, ?int $userId = null, ?string $description = null): bool
    {
        try {
            $configurationData = $this->exportConfiguration();
            
            $this->db->table('configuration_backups')->insert([
                'backup_name' => $backupName,
                'configuration_data' => json_encode($configurationData),
                'created_by' => $userId,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->logger->info("Configuration backup created: {$backupName}");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to create configuration backup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Restore configuration from backup.
     */
    public function restoreBackup(int $backupId): bool
    {
        try {
            $backup = $this->db->table('configuration_backups')
                ->where('id', $backupId)
                ->first();
            
            if (!$backup) {
                throw new \InvalidArgumentException('Backup not found');
            }
            
            $configurationData = json_decode($backup['configuration_data'], true);
            
            if (!$configurationData) {
                throw new \InvalidArgumentException('Invalid backup data');
            }
            
            return $this->importConfiguration($configurationData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to restore configuration backup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get configuration audit log.
     */
    public function getAuditLog(int $limit = 100, int $offset = 0): array
    {
        return $this->db->table('configuration_audit')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();
    }

    /**
     * Get configuration backups.
     */
    public function getBackups(): array
    {
        return $this->db->table('configuration_backups')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Load configuration categories.
     */
    private function loadCategories(): void
    {
        $categories = $this->db->table('configuration_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        foreach ($categories as $category) {
            $this->categoriesCache[$category['name']] = $category;
        }
    }

    /**
     * Load configuration values.
     */
    private function loadConfigurationValues(): void
    {
        $configs = $this->db->table('configuration')->get();
        
        foreach ($configs as $config) {
            $this->configCache[$config['category']][$config['key_name']] = $config;
        }
    }

    /**
     * Get category from configuration key.
     */
    private function getCategoryFromKey(string $key): string
    {
        $parts = explode('.', $key, 2);
        return $parts[0] ?? 'core';
    }

    /**
     * Get key name from configuration key.
     */
    private function getKeyNameFromKey(string $key): string
    {
        $parts = explode('.', $key, 2);
        return $parts[1] ?? $key;
    }

    /**
     * Cast value to appropriate type.
     */
    private function castValue(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }
        
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array', 'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value
        };
    }

    /**
     * Serialize value for storage.
     */
    private function serializeValue(mixed $value, string $type): string
    {
        return match ($type) {
            'array', 'json' => is_array($value) ? json_encode($value) : (string) $value,
            'boolean' => $value ? 'true' : 'false',
            default => (string) $value
        };
    }

    /**
     * Get configuration type.
     */
    private function getConfigurationType(string $category, string $keyName): string
    {
        return $this->configCache[$category][$keyName]['type'] ?? 'string';
    }

    /**
     * Validate configuration value.
     */
    private function validateValue(string $category, string $keyName, mixed $value): bool
    {
        if (!isset($this->configCache[$category][$keyName])) {
            return false;
        }
        
        $config = $this->configCache[$category][$keyName];
        $validationRules = json_decode($config['validation_rules'], true) ?? [];
        
        foreach ($validationRules as $rule) {
            if (!$this->validateRule($rule, $value)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate a single rule.
     */
    private function validateRule(string $rule, mixed $value): bool
    {
        if (str_starts_with($rule, 'required')) {
            return !empty($value);
        }
        
        if (str_starts_with($rule, 'min:')) {
            $min = (int) substr($rule, 4);
            return strlen((string) $value) >= $min;
        }
        
        if (str_starts_with($rule, 'max:')) {
            $max = (int) substr($rule, 4);
            return strlen((string) $value) <= $max;
        }
        
        if (str_starts_with($rule, 'in:')) {
            $allowed = explode(',', substr($rule, 3));
            return in_array($value, $allowed);
        }
        
        if ($rule === 'integer') {
            return is_numeric($value) && (int) $value == $value;
        }
        
        if ($rule === 'boolean') {
            return is_bool($value) || in_array($value, ['true', 'false', '0', '1', 0, 1]);
        }
        
        return true;
    }

    /**
     * Validate configuration item.
     */
    private function validateConfigurationItem(string $category, string $key, array $config): array
    {
        $errors = [];
        $warnings = [];
        
        // Check required fields
        if ($config['is_required'] && empty($config['value'])) {
            $errors[] = "Required configuration missing: {$category}.{$key}";
        }
        
        // Validate value if present
        if (!empty($config['value'])) {
            $validationRules = json_decode($config['validation_rules'], true) ?? [];
            foreach ($validationRules as $rule) {
                if (!$this->validateRule($rule, $config['value'])) {
                    $errors[] = "Configuration validation failed: {$category}.{$key} (rule: {$rule})";
                }
            }
        }
        
        return [
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Log configuration change.
     */
    private function logConfigurationChange(string $category, string $keyName, mixed $oldValue, mixed $newValue, ?int $userId): void
    {
        try {
            $request = $this->container->get('request') ?? null;
            
            $this->db->table('configuration_audit')->insert([
                'user_id' => $userId,
                'category' => $category,
                'key_name' => $keyName,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'change_type' => 'update',
                'ip_address' => $request?->getServerParams()['REMOTE_ADDR'] ?? null,
                'user_agent' => $request?->getServerParams()['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to log configuration change: ' . $e->getMessage());
        }
    }
} 