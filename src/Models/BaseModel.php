<?php

declare(strict_types=1);

namespace IslamWiki\Models;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Query\Builder;

/**
 * BaseModel
 *
 * Lightweight base model that provides a DB connection, table name,
 * and simple query helpers. Concrete models should set $table.
 */
abstract class BaseModel
{
    /** @var Connection|null */
    protected ?Connection $connection;

    /** @var string */
    protected string $table = '';

    public function __construct(?Connection $connection = null)
    {
        $this->connection = $connection;
    }

    /**
     * Start a new query for this model's table.
     */
    protected function newQuery(): Builder
    {
        // Connection has a ->table() helper that returns a Builder
        return $this->connection->table($this->table);
    }

    /**
     * Insert a new record.
     */
    protected function insert(array $attributes)
    {
        return $this->newQuery()->insert($attributes);
    }

    /**
     * Update records by where conditions.
     */
    protected function updateBy(array $where, array $attributes)
    {
        $query = $this->newQuery();
        foreach ($where as $col => $val) {
            $query = $query->where($col, $val);
        }
        return $query->update($attributes);
    }

    /**
     * Delete records by where conditions.
     */
    protected function deleteBy(array $where)
    {
        $query = $this->newQuery();
        foreach ($where as $col => $val) {
            $query = $query->where($col, $val);
        }
        return $query->delete();
    }

    /**
     * Find first row matching conditions.
     */
    protected function firstWhere(array $where)
    {
        $query = $this->newQuery();
        foreach ($where as $col => $val) {
            $query = $query->where($col, $val);
        }
        return $query->first();
    }

    /**
     * Get all rows (optionally with simple where conditions).
     */
    protected function getAll(array $where = [])
    {
        $query = $this->newQuery();
        foreach ($where as $col => $val) {
            $query = $query->where($col, $val);
        }
        return $query->get();
    }
}
