<?php

declare(strict_types=1);



namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use DateTime;

class User
{
    /**
     * The database connection instance.
     */
    protected Connection $connection;

    /**
     * The table associated with the model.
     */
    protected string $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'id',
        'username',
        'email',
        'password',
        'display_name',
        'bio',
        'website',
        'location',
        'timezone',
        'language',
        'is_admin',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected array $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id' => 'integer',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
    ];

    /**
     * The model's attributes.
     */
    protected array $attributes = [];

    /**
     * Create a new user instance.
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
        // Only hash password if it's not already hashed (i.e., when setting a new password)
        if ($key === 'password' && !str_starts_with($value, '$2y$')) {
            $value = $this->hashPassword($value);
        }
        
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
     * Hash the given password.
     */
    protected function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify the given password against the user's hashed password.
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->getAttribute('password') ?: '');
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
     * Find a user by their ID.
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
     * Find a user by their username.
     */
    public static function findByUsername(string $username, Connection $connection): ?self
    {
        $instance = new static($connection);
        $data = $instance->newQuery()
            ->where('username', '=', $username)
            ->first();
        
        if (!$data) {
            return null;
        }
        
        return new static($connection, (array) $data);
    }

    /**
     * Find a user by their email address.
     */
    public static function findByEmail(string $email, Connection $connection): ?self
    {
        $instance = new static($connection);
        $data = $instance->newQuery()
            ->where('email', '=', $email)
            ->first();
        
        if (!$data) {
            return null;
        }
        
        return new static($connection, (array) $data);
    }

    /**
     * Save the user to the database.
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
     * Record that the user has logged in.
     */
    public function recordLogin(): void
    {
        $this->setAttribute('last_login_at', new DateTime());
        $this->save();
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->getAttribute('is_admin', false);
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->getAttribute('is_active', true);
    }

    /**
     * Get the user's display name.
     */
    public function getDisplayName(): string
    {
        return $this->getAttribute('display_name') ?: $this->getAttribute('username');
    }

    /**
     * Get the URL to the user's profile.
     */
    public function getProfileUrl(): string
    {
        return '/user/' . urlencode($this->getAttribute('username'));
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrl(int $size = 80): string
    {
        $hash = md5(strtolower(trim($this->getAttribute('email'))));
        return sprintf('https://www.gravatar.com/avatar/%s?s=%d&d=identicon', $hash, $size);
    }

    /**
     * Get the user's contributions.
     */
    public function getContributions(int $limit = 50): array
    {
        if (!$this->exists()) {
            return [];
        }
        
        $query = $this->connection->table('page_revisions')
            ->where('user_id', '=', $this->getAttribute('id'))
            ->orderBy('created_at', 'desc')
            ->limit($limit);
        
        $revisions = [];
        
        foreach ($query->get() as $data) {
            $revisions[] = new Revision($this->connection, (array) $data);
        }
        
        return $revisions;
    }
}
