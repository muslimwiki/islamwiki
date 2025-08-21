<?php

declare(strict_types=1);

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use DateTime;

/**
 * Template Model
 *
 * Represents a MediaWiki-style template with parameters and content.
 * Templates can be used in wiki pages to insert reusable content.
 */
class Template
{
    /**
     * @var Connection Database connection
     */
    protected Connection $connection;

    /**
     * @var string Table name
     */
    protected string $table = 'templates';

    /**
     * @var array Template attributes
     */
    protected array $attributes = [];

    /**
     * @var array Fillable attributes
     */
    protected array $fillable = [
        'name',
        'content',
        'parameters',
        'description',
        'category',
        'author',
        'is_active',
        'is_system',
        'usage_count',
        'last_used_at',
    ];

    /**
     * @var array Castable attributes
     */
    protected array $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Create a new template instance
     */
    public function __construct(Connection $connection, array $attributes = [])
    {
        $this->connection = $connection;
        $this->fill($attributes);
    }

    /**
     * Fill the model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Set an attribute
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $this->castAttribute($key, $value);
        return $this;
    }

    /**
     * Get an attribute
     */
    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Cast an attribute to its proper type
     */
    protected function castAttribute(string $key, mixed $value): mixed
    {
        if (!isset($this->casts[$key])) {
            return $value;
        }

        $castType = $this->casts[$key];

        switch ($castType) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'datetime':
                if ($value instanceof DateTime) {
                    return $value;
                }
                if (is_string($value) && !empty($value)) {
                    return new DateTime($value);
                }
                return null;
            default:
                return $value;
        }
    }

    /**
     * Find a template by name
     */
    public static function findByName(Connection $connection, string $name): ?self
    {
        $data = $connection->table('templates')
            ->where('name', '=', $name)
            ->where('is_active', '=', true)
            ->first();

        return $data ? new self($connection, (array) $data) : null;
    }

    /**
     * Get all active templates
     */
    public static function getAllActive(Connection $connection): array
    {
        $data = $connection->table('templates')
            ->where('is_active', '=', true)
            ->orderBy('name')
            ->get();

        return array_map(function ($item) use ($connection) {
            return new self($connection, (array) $item);
        }, $data);
    }

    /**
     * Get templates by category
     */
    public static function getByCategory(Connection $connection, string $category): array
    {
        $data = $connection->table('templates')
            ->where('category', '=', $category)
            ->where('is_active', '=', true)
            ->orderBy('name')
            ->get();

        return array_map(function ($item) use ($connection) {
            return new self($connection, (array) $item);
        }, $data);
    }

    /**
     * Get system templates
     */
    public static function getSystemTemplates(Connection $connection): array
    {
        $data = $connection->table('templates')
            ->where('is_system', '=', true)
            ->where('is_active', '=', true)
            ->orderBy('name')
            ->get();

        return array_map(function ($item) use ($connection) {
            return new self($connection, (array) $item);
        }, $data);
    }

    /**
     * Save the template
     */
    public function save(): bool
    {
        $data = $this->getDirty();

        if (empty($data)) {
            return true;
        }

        if (isset($this->attributes['id'])) {
            // Update existing template
            $success = $this->connection->table($this->table)
                ->where('id', '=', $this->attributes['id'])
                ->update($data);
        } else {
            // Create new template
            $id = $this->connection->table($this->table)->insertGetId($data);
            if ($id) {
                $this->setAttribute('id', $id);
                $success = true;
            } else {
                $success = false;
            }
        }

        if ($success) {
            $this->syncOriginal();
        }

        return $success;
    }

    /**
     * Get dirty attributes (attributes that have changed)
     */
    protected function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            if ($this->casts[$key] === 'array' && is_array($value)) {
                $dirty[$key] = json_encode($value);
            } else {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Sync original attributes
     */
    protected function syncOriginal(): void
    {
        // This would track original values for dirty checking
        // For now, we'll just mark everything as clean
    }

    /**
     * Render the template with parameters
     */
    public function render(array $parameters = []): string
    {
        $content = $this->getAttribute('content');
        $templateParams = $this->getAttribute('parameters') ?? [];

        // Process parameters with defaults
        $processedParams = $this->processParameters($parameters, $templateParams);

        // Replace placeholders in content
        $rendered = $this->replacePlaceholders($content, $processedParams);

        // Track usage
        $this->incrementUsage();

        return $rendered;
    }

    /**
     * Process parameters with defaults
     */
    protected function processParameters(array $provided, array $templateParams): array
    {
        $processed = [];

        foreach ($templateParams as $name => $config) {
            if (isset($provided[$name])) {
                $processed[$name] = $provided[$name];
            } elseif (isset($config['default'])) {
                $processed[$name] = $config['default'];
            } else {
                $processed[$name] = '';
            }
        }

        // Add any additional provided parameters
        foreach ($provided as $name => $value) {
            if (!isset($processed[$name])) {
                $processed[$name] = $value;
            }
        }

        return $processed;
    }

    /**
     * Replace placeholders in template content
     */
    protected function replacePlaceholders(string $content, array $parameters): string
    {
        foreach ($parameters as $name => $value) {
            $placeholder = '{{' . $name . '}}';
            $content = str_replace($placeholder, (string) $value, $content);
        }

        return $content;
    }

    /**
     * Increment usage count
     */
    protected function incrementUsage(): void
    {
        $this->connection->table($this->table)
            ->where('id', '=', $this->getAttribute('id'))
            ->update([
                'usage_count' => $this->getAttribute('usage_count') + 1,
                'last_used_at' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Get template name
     */
    public function getName(): string
    {
        return $this->getAttribute('name') ?? '';
    }

    /**
     * Get template description
     */
    public function getDescription(): string
    {
        return $this->getAttribute('description') ?? '';
    }

    /**
     * Get template category
     */
    public function getCategory(): string
    {
        return $this->getAttribute('category') ?? '';
    }

    /**
     * Check if template is active
     */
    public function isActive(): bool
    {
        return (bool) $this->getAttribute('is_active');
    }

    /**
     * Check if template is a system template
     */
    public function isSystem(): bool
    {
        return (bool) $this->getAttribute('is_system');
    }

    /**
     * Get usage count
     */
    public function getUsageCount(): int
    {
        return (int) $this->getAttribute('usage_count');
    }

    /**
     * Get last used timestamp
     */
    public function getLastUsedAt(): ?DateTime
    {
        return $this->getAttribute('last_used_at');
    }

    /**
     * Get template parameters
     */
    public function getParameters(): array
    {
        return $this->getAttribute('parameters') ?? [];
    }

    /**
     * Get template content
     */
    public function getContent(): string
    {
        return $this->getAttribute('content') ?? '';
    }
} 