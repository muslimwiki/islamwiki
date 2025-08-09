<?php

declare(strict_types=1);

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Query\Builder;
use IslamWiki\Core\Database\Connection;
use DateTime;

class Page
{
    /**
     * The database connection instance.
     */
    protected Connection $connection;

    /**
     * The table associated with the model.
     */
    protected string $table = 'pages';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'id',
        'title',
        'slug',
        'content',
        'content_format',
        'revision_comment',
        'is_locked',
        'namespace',
        'parent_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'is_locked' => 'boolean',
        'parent_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The model's attributes.
     */
    protected array $attributes = [];

    /**
     * Create a new page instance.
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
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Set a given attribute on the model.
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $this->castAttribute($key, $value);
        return $this;
    }

    /**
     * Get an attribute from the model.
     */
    public function getAttribute(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return null;
    }

    /**
     * Magic method to allow direct property access.
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Cast an attribute to a native PHP type.
     */
    protected function castAttribute(string $key, mixed $value): mixed
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
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            case 'datetime':
                return $value instanceof DateTime ? $value : new DateTime($value);
            default:
                return $value;
        }
    }

    /**
     * Get a new query builder for the model's table.
     */
    protected function newQuery(): Builder
    {
        return $this->connection->table($this->table);
    }

    /**
     * Find a page by its ID.
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
     * Find a page by its slug.
     */
    public static function findBySlug(string $slug, Connection $connection): ?self
    {
        $instance = new static($connection);
        $data = $instance->newQuery()
            ->where('slug', '=', $slug)
            ->orderBy('id', 'desc')
            ->first();

        if (!$data) {
            return null;
        }



        return new static($connection, (array) $data);
    }

    /**
     * Save the model to the database.
     */
    public function save(): bool
    {
        $attributes = $this->getDirty();

        if ($this->exists()) {
            $this->performUpdate($attributes);
        } else {
            $this->performInsert($attributes);
        }

        return true;
    }

    /**
     * Perform a model insert operation.
     */
    protected function performInsert(array $attributes): void
    {
        $now = new DateTime();

        $attributes['created_at'] = $now;
        $attributes['updated_at'] = $now;

        $id = $this->newQuery()->insertGetId($attributes);

        $this->setAttribute('id', $id);
    }

    /**
     * Perform a model update operation.
     */
    protected function performUpdate(array $attributes): void
    {
        $attributes['updated_at'] = new DateTime();

        $this->newQuery()
            ->where('id', '=', $this->getAttribute('id'))
            ->update($attributes);
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
     * Create a new revision of the page.
     */
    public function createRevision(int $userId, string $comment = ''): Revision
    {
        $revision = new Revision($this->connection, [
            'page_id' => $this->getAttribute('id'),
            'user_id' => $userId,
            'title' => $this->getAttribute('title'),
            'content' => $this->getAttribute('content'),
            'content_format' => $this->getAttribute('content_format', 'markdown'),
            'comment' => $comment,
        ]);

        $revision->save();

        return $revision;
    }

    /**
     * Get all revisions for the page.
     */
    public function revisions(): array
    {
        if (!$this->exists()) {
            return [];
        }

        $revisions = $this->connection->table('page_revisions')
            ->where('page_id', '=', $this->getAttribute('id'))
            ->orderBy('id', 'desc')
            ->get();

        return array_map(function ($data) {
            return new Revision($this->connection, (array) $data);
        }, $revisions);
    }

    /**
     * Get the latest revision of the page.
     */
    public function latestRevision(): ?Revision
    {
        $revisions = $this->revisions();
        return $revisions[0] ?? null;
    }

    /**
     * Get the page's namespace.
     */
    public function getNamespace(): string
    {
        return $this->getAttribute('namespace', '');
    }

    /**
     * Get the page's full title including namespace.
     */
    public function getFullTitle(): string
    {
        $namespace = $this->getNamespace();
        return $namespace ? "$namespace:{$this->getAttribute('title')}" : $this->getAttribute('title');
    }

    /**
     * Check if the page is locked.
     */
    public function isLocked(): bool
    {
        return (bool) $this->getAttribute('is_locked', false);
    }

    /**
     * Lock the page.
     */
    public function lock(): bool
    {
        $this->setAttribute('is_locked', true);
        return $this->save();
    }

    /**
     * Unlock the page.
     */
    public function unlock(): bool
    {
        $this->setAttribute('is_locked', false);
        return $this->save();
    }

    /**
     * Get the page's URL.
     */
    public function getUrl(): string
    {
        return '/wiki/' . urlencode($this->getAttribute('slug'));
    }

    /**
     * Get the edit URL for the page.
     */
    public function getEditUrl(): string
    {
        return $this->getUrl() . '/edit';
    }

    /**
     * Get the history URL for the page.
     */
    public function getHistoryUrl(): string
    {
        return $this->getUrl() . '/history';
    }
}
