<?php
declare(strict_types=1);



namespace IslamWiki\Core\Database\Query;

use PDO;
use PDOStatement;
use RuntimeException;
use IslamWiki\Core\Database\Connection;

class Builder
{
    protected Connection $connection;
    protected Grammar $grammar;
    protected array $bindings = [];
    public $columns;
    public $from;
    public $wheres = [];
    public $orders;
    public $limit;
    public $offset;
    public $distinct = false;
    public $unions = [];
    public $havings = [];
    public $aggregate = null;

    public function __construct(Connection $connection, Grammar $grammar = null)
    {
        $this->connection = $connection;
        $this->grammar = $grammar ?? new Grammar();
    }

    public function select($columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function from(string $table): self
    {
        $this->from = $table;
        return $this;
    }

    public function where($column, $operator = null, $value = null, string $boolean = 'and'): self
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        if (func_num_args() === 2) {
            [$value, $operator] = [$operator, '='];
        }

        $this->wheres[] = [
            'type' => 'Basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];
        
        $this->addBinding($value, 'where');
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null): self
    {
        return $this->where($column, $operator, $value, 'or');
    }

    public function orderBy($column, string $direction = 'asc'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtolower($direction) === 'asc' ? 'asc' : 'desc',
        ];
        return $this;
    }

    public function limit(int $value): self
    {
        $this->limit = $value >= 0 ? $value : 0;
        return $this;
    }

    public function offset(int $value): self
    {
        $this->offset = max(0, $value);
        return $this;
    }

    public function get($columns = ['*']): array
    {
        if (!empty($columns)) {
            $this->select($columns);
        }

        $sql = $this->toSql();
        $bindings = $this->getBindings();
        
        return $this->connection->select($sql, $bindings);
    }

    public function first($columns = ['*'])
    {
        $results = $this->limit(1)->get($columns);
        return $results[0] ?? null;
    }

    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    public function insert(array $values): bool
    {
        if (empty($values)) {
            return true;
        }
        
        $sql = $this->grammar->compileInsert($this, $values);
        $bindings = $this->cleanBindings($values);
        
        $this->connection->insert($sql, $bindings);
        return true;
    }

    public function insertGetId(array $values, $sequence = null): int
    {
        if (empty($values)) {
            return 0;
        }
        
        $sql = $this->grammar->compileInsert($this, $values);
        $bindings = $this->cleanBindings($values);
        
        $id = $this->connection->insert($sql, $bindings);
        return (int) $id;
    }

    public function update(array $values): int
    {
        $sql = $this->grammar->compileUpdate($this, $values);
        $bindings = $this->cleanBindings(
            array_merge($values, $this->getBindings())
        );
        
        return $this->connection->update($sql, $bindings);
    }

    public function delete($id = null): int
    {
        if (!is_null($id)) {
            $this->where('id', '=', $id);
        }
        
        $sql = $this->grammar->compileDelete($this);
        $bindings = $this->cleanBindings($this->getBindings());
        
        return $this->connection->delete($sql, $bindings);
    }

    public function toSql(): string
    {
        return $this->grammar->compileSelect($this);
    }

    public function getBindings(): array
    {
        return array_merge(
            $this->bindings['where'] ?? [],
            $this->bindings['order'] ?? []
        );
    }

    public function addBinding($value, string $type = 'where'): self
    {
        if (is_array($value)) {
            $this->bindings[$type] = array_merge($this->bindings[$type] ?? [], $value);
        } else {
            $this->bindings[$type][] = $value;
        }
        return $this;
    }

    protected function cleanBindings(array $bindings): array
    {
        return array_values(array_filter($bindings, function ($binding) {
            return !$binding instanceof Expression;
        }));
    }

    protected function addArrayOfWheres(array $column, string $boolean, string $method = 'where'): self
    {
        foreach ($column as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $this->$method(...array_values($value));
            } else {
                $this->$method($key, '=', $value, $boolean);
            }
        }
        return $this;
    }

    public function max($column)
    {
        $sql = "SELECT MAX(" . $this->grammar->wrap($column) . ") as aggregate FROM " . $this->grammar->wrapTable($this->from);
        $result = $this->connection->select($sql, $this->getBindings());
        
        if (empty($result) || !isset($result[0]['aggregate'])) {
            return null;
        }
        
        return $result[0]['aggregate'];
    }

    public function pluck($column, $key = null): array
    {
        if ($key === null) {
            $results = $this->get([$column]);
        } else {
            $results = $this->get([$column, $key]);
        }
        
        if (empty($results)) {
            return [];
        }
        
        if ($key === null) {
            return array_column($results, $column);
        }
        
        return array_column($results, $column, $key);
    }
}
