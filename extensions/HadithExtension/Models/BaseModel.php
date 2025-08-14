<?php

declare(strict_types=1);

namespace IslamWiki\Extensions\HadithExtension\Models;

use IslamWiki\Core\Database\Connection;
use Exception;
use RuntimeException;

/**
 * BaseModel
 * 
 * Base model class that provides common database functionality
 * for all Hadith models.
 */
abstract class BaseModel
{
    /**
     * @var Connection Database connection instance
     */
    protected Connection $db;
    
    /**
     * @var string The table name for the model
     */
    protected string $table;
    
    /**
     * @var string The primary key for the model
     */
    protected string $primaryKey = 'id';
    
    /**
     * @var array The model's attributes
     */
    protected array $attributes = [];
    
    /**
     * @var array The model's fillable attributes
     */
    protected array $fillable = [];
    
    /**
     * @var bool Whether the model exists in the database
     */
    protected bool $exists = false;

    /**
     * Constructor
     * 
     * @param Connection|null $db Database connection
     */
    public function __construct(?Connection $db = null)
    {
        if (!$db) {
            // Try to get from container if available
            try {
                $container = app();
                if ($container && method_exists($container, 'get')) {
                    $db = $container->get('db');
                }
            } catch (Exception $e) {
                error_log("Could not get database connection from container: " . $e->getMessage());
            }
        }

        if (!$db) {
            throw new RuntimeException("Database connection is required for " . static::class);
        }

        $this->db = $db;
    }
    
    /**
     * Get the table name
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Get the primary key
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }
    
    /**
     * Get the primary key value
     */
    public function getKey()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }
    
    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * Get an attribute from the model
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
    
    /**
     * Set an attribute on the model
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }
    
    /**
     * Get all of the model's attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Save the model to the database
     */
    public function save(): bool
    {
        $attributes = $this->getAttributes();
        $now = date('Y-m-d H:i:s');
        
        if ($this->exists) {
            // Update existing record
            $id = $this->getKey();
            $attributes['updated_at'] = $now;
            
            $result = $this->db->update(
                $this->getTable(),
                $attributes,
                [$this->getKeyName() => $id]
            );
            
            return $result !== false;
        } else {
            // Insert new record
            $attributes['created_at'] = $now;
            $attributes['updated_at'] = $now;
            
            $id = $this->db->insert($this->getTable(), $attributes);
            
            if ($id) {
                $this->setAttribute($this->getKeyName(), $id);
                $this->exists = true;
                return true;
            }
            
            return false;
        }
    }
    
    /**
     * Find a model by its primary key
     */
    public static function find($id, ?Connection $db = null): ?self
    {
        $instance = new static($db);
        
        $row = $instance->db->selectOne(
            $instance->getTable(),
            '*',
            [$instance->getKeyName() => $id]
        );
        
        if ($row) {
            $instance->fill((array)$row);
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
    
    /**
     * Get all models
     */
    public static function all(?Connection $db = null): array
    {
        $instance = new static($db);
        $rows = $instance->db->select($instance->getTable());
        
        $models = [];
        foreach ($rows as $row) {
            $model = new static($db);
            $model->fill((array)$row);
            $model->exists = true;
            $models[] = $model;
        }
        
        return $models;
    }
    
    /**
     * Delete the model from the database
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        
        $result = $this->db->delete(
            $this->getTable(),
            [$this->getKeyName() => $this->getKey()]
        );
        
        if ($result) {
            $this->exists = false;
            return true;
        }
        
        return false;
    }
    
    /**
     * Magic getter for attributes
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic setter for attributes
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Check if an attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Convert the model to an array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
