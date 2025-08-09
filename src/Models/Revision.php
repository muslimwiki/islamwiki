<?php

declare(strict_types=1);



namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use DateTime;

class Revision
{
    /**
     * The database connection instance.
     */
    protected Connection $connection;

    /**
     * The table associated with the model.
     */
    protected string $table = 'page_revisions';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'page_id',
        'user_id',
        'title',
        'content',
        'content_format',
        'comment',
        'is_minor_edit',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'page_id' => 'integer',
        'user_id' => 'integer',
        'is_minor_edit' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * The model's attributes.
     */
    protected array $attributes = [];

    /**
     * Create a new revision instance.
     */
    public function __construct(Connection $connection, array $attributes = [])
    {
        $this->connection = $connection;
        $this->fill($attributes);
    }

    /**
     * Fill the model with an array of attributes.
     */
    public function fill(array $attributes): self
    {
        foreach ($this->fillable as $key) {
            if (array_key_exists($key, $attributes)) {
                $this->setAttribute($key, $attributes[$key]);
            }
        }

        return $this;
    }

    /**
     * Set a given attribute on the model.
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $this->castAttribute($key, $value);
        return $this;
    }

    /**
     * Get an attribute from the model.
     */
    public function getAttribute(string $key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return null;
    }

    /**
     * Cast an attribute to a native PHP type.
     */
    protected function castAttribute(string $key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        $type = $this->casts[$key] ?? null;

        if (is_null($type)) {
            return $value;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'datetime':
                return $value instanceof DateTime ? $value : new DateTime($value);
            default:
                return $value;
        }
    }

    /**
     * Get a new query builder for the model's table.
     */
    protected function newQuery()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Find a revision by its ID.
     */
    public static function find(int $id, Connection $connection): ?self
    {
        $instance = new static($connection);
        $data = $instance->newQuery()->where('id', '=', $id)->first();
        
        if (!$data) {
            return null;
        }
        
        return new static($connection, (array) $data);
    }

    /**
     * Save the revision to the database.
     */
    public function save(): bool
    {
        $attributes = $this->getDirty();
        
        if ($this->exists()) {
            return false; // Revisions are immutable
        }
        
        $this->performInsert($attributes);
        return true;
    }

    /**
     * Perform a model insert operation.
     */
    protected function performInsert(array $attributes): void
    {
        $attributes['created_at'] = new DateTime();
        
        if (empty($attributes['ip_address'])) {
            $attributes['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        
        if (empty($attributes['user_agent'])) {
            $attributes['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }
        
        $id = $this->newQuery()->insertGetId($attributes);
        
        $this->setAttribute('id', $id);
    }

    /**
     * Determine if the model exists in the database.
     */
    public function exists(): bool
    {
        return isset($this->attributes['id']);
    }

    /**
     * Get the attributes that have been changed since last sync.
     */
    public function getDirty(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key === 'id') {
                continue;
            }
            
            $dirty[$key] = $value;
        }
        
        return $dirty;
    }

    /**
     * Get the page this revision belongs to.
     */
    public function page(): ?Page
    {
        if (empty($this->attributes['page_id'])) {
            return null;
        }
        
        return Page::find($this->attributes['page_id'], $this->connection);
    }

    /**
     * Get the user who created this revision.
     */
    public function user(): ?User
    {
        if (empty($this->attributes['user_id'])) {
            return null;
        }
        
        return User::find($this->attributes['user_id'], $this->connection);
    }

    /**
     * Get the formatted date of this revision.
     */
    public function getFormattedDate(string $format = 'Y-m-d H:i:s'): string
    {
        $date = $this->getAttribute('created_at');
        return $date ? $date->format($format) : '';
    }

    /**
     * Get the size of the content in bytes.
     */
    public function getSize(): int
    {
        return mb_strlen($this->getAttribute('content', ''), '8bit');
    }

    /**
     * Get the size of the content in a human-readable format.
     */
    public function getFormattedSize(): string
    {
        $size = $this->getSize();
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($size > 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Check if this is a minor edit.
     */
    public function isMinorEdit(): bool
    {
        return (bool) $this->getAttribute('is_minor_edit', false);
    }

    /**
     * Get the comment for this revision.
     */
    public function getComment(): string
    {
        return (string) $this->getAttribute('comment', '');
    }

    /**
     * Get the diff between this revision and another one.
     */
    public function diffWith(Revision $other): string
    {
        $oldContent = $other->getAttribute('content', '');
        $newContent = $this->getAttribute('content', '');
        
        // Simple line-based diff for now
        $oldLines = explode("\n", $oldContent);
        $newLines = explode("\n", $newContent);
        
        $diff = [];
        $maxLines = max(count($oldLines), count($newLines));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';
            
            if ($oldLine !== $newLine) {
                $diff[] = sprintf("- %s\n+ %s", $oldLine, $newLine);
            } else {
                $diff[] = '  ' . $oldLine;
            }
        }
        
        return implode("\n", $diff);
    }
}
