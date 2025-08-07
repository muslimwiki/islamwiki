<?php

declare(strict_types=1);

namespace IslamWiki\Core\Database\Query;

class Grammar
{
    /**
     * The grammar table prefix.
     */
    protected string $tablePrefix = '';

    /**
     * The components that make up a select clause.
     */
    protected array $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'lock',
    ];

    /**
     * Compile a select query into SQL.
     */
    public function compileSelect(Builder $query): string
    {
        if (($query->unions || $query->havings) && $query->aggregate) {
            return $this->compileUnionAggregate($query);
        }

        $sql = [];

        foreach ($this->selectComponents as $component) {
            if (isset($query->$component)) {
                $method = 'compile' . ucfirst($component);
                if (method_exists($this, $method)) {
                    $sql[$component] = $this->$method($query, $query->$component);
                }
            }
        }

        return $this->concatenate($sql);
    }

    /**
     * Compile the "select *" portion of the query.
     */
    protected function compileColumns(Builder $query, $columns): string
    {
        if (!is_null($query->aggregate)) {
            return '';
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        return $select . $this->columnize($columns);
    }

    /**
     * Compile the "from" portion of the query.
     */
    protected function compileFrom(Builder $query, string $table): string
    {
        return 'from ' . $this->wrapTable($table);
    }

    /**
     * Compile the "where" portions of the query.
     */
    protected function compileWheres(Builder $query): string
    {
        if (empty($query->wheres)) {
            return '';
        }

        return 'where ' . $this->compileWheresToArray($query);
    }

    /**
     * Get an array of all the where clauses for the query.
     */
    protected function compileWheresToArray(Builder $query): string
    {
        $sql = [];

        foreach ($query->wheres as $where) {
            $method = 'where' . ucfirst($where['type']);
            if (method_exists($this, $method)) {
                $sql[] = $where['boolean'] . ' ' . $this->$method($query, $where);
            }
        }

        return ltrim(implode(' ', $sql), 'and ');
    }

    /**
     * Compile a basic where clause.
     */
    protected function whereBasic(Builder $query, array $where): string
    {
        $value = $this->parameter($where['value']);
        return $this->wrap($where['column']) . ' ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a where in clause.
     */
    protected function whereIn(Builder $query, array $where): string
    {
        if (empty($where['values'])) {
            return '0 = 1';
        }

        $values = $this->parameterize($where['values']);
        return $this->wrap($where['column']) . ' in (' . $values . ')';
    }

    /**
     * Compile the "group by" portions of the query.
     */
    protected function compileGroups(Builder $query, array $groups): string
    {
        return 'group by ' . $this->columnize($groups);
    }

    /**
     * Compile the "order by" portions of the query.
     */
    protected function compileOrders(Builder $query, array $orders): string
    {
        if (empty($orders)) {
            return '';
        }

        return 'order by ' . implode(', ', $this->compileOrdersToArray($query, $orders));
    }

    /**
     * Compile the query orders to an array.
     */
    protected function compileOrdersToArray(Builder $query, array $orders): array
    {
        return array_map(function ($order) {
            return !isset($order['sql'])
                ? $this->wrap($order['column']) . ' ' . $order['direction']
                : $order['sql'];
        }, $orders);
    }

    /**
     * Compile the "limit" portions of the query.
     */
    protected function compileLimit(Builder $query, int $limit): string
    {
        return 'limit ' . (int) $limit;
    }

    /**
     * Compile the "offset" portions of the query.
     */
    protected function compileOffset(Builder $query, int $offset): string
    {
        return 'offset ' . (int) $offset;
    }

    /**
     * Compile an insert statement into SQL.
     */
    public function compileInsert(Builder $query, array $values): string
    {
        $table = $this->wrapTable($query->from);

        if (empty($values)) {
            return "insert into {$table} default values";
        }

        // Handle single record vs array of records
        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));
        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '(' . $this->parameterize($record) . ')';
        }

        $parameters = implode(', ', $parameters);

        return "insert into {$table} ({$columns}) values {$parameters}";
    }

    /**
     * Compile an update statement into SQL.
     */
    public function compileUpdate(Builder $query, array $values): string
    {
        $table = $this->wrapTable($query->from);
        $columns = [];

        foreach ($values as $key => $value) {
            $columns[] = $this->wrap($key) . ' = ' . $this->parameter($value);
        }

        $columns = implode(', ', $columns);
        $where = $this->compileWheres($query);

        return trim("update {$table} set {$columns} {$where}");
    }

    /**
     * Compile a delete statement into SQL.
     */
    public function compileDelete(Builder $query): string
    {
        $table = $this->wrapTable($query->from);
        $where = $this->compileWheres($query);

        return trim("delete from {$table} {$where}");
    }

    /**
     * Wrap a table in keyword identifiers.
     */
    public function wrapTable($table): string
    {
        if ($this->isExpression($table)) {
            return $this->getValue($table);
        }

        return $this->wrap($this->tablePrefix . $table, true);
    }

    /**
     * Wrap a value in keyword identifiers.
     */
    public function wrap($value, $prefixAlias = false): string
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        if (str_contains(strtolower($value ?? ''), ' as ')) {
            return $this->wrapAliasedValue($value, $prefixAlias);
        }

        return $this->wrapSegments(explode('.', $value ?? ''));
    }

    /**
     * Wrap a value that has an alias.
     */
    protected function wrapAliasedValue(string $value, bool $prefixAlias = false): string
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        if ($prefixAlias) {
            $segments[1] = $this->tablePrefix . $segments[1];
        }

        return $this->wrap($segments[0]) . ' as ' . $this->wrapValue($segments[1]);
    }

    /**
     * Wrap the given value segments.
     */
    protected function wrapSegments(array $segments): string
    {
        return implode('.', array_map([$this, 'wrapValue'], $segments));
    }

    /**
     * Wrap a single string in keyword identifiers.
     */
    protected function wrapValue(string $value): string
    {
        if ($value === '*' || $value === null) {
            return $value ?? '';
        }

        return '`' . str_replace('`', '``', $value) . '`';
    }

    /**
     * Convert an array of column names into a delimited string.
     */
    public function columnize(array $columns): string
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    /**
     * Create query parameter place-holders for an array.
     */
    public function parameterize(array $values): string
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     */
    public function parameter($value): string
    {
        return $this->isExpression($value) ? $this->getValue($value) : '?';
    }

    /**
     * Get the value of a raw expression.
     */
    public function getValue($expression)
    {
        return $expression->getValue();
    }

    /**
     * Determine if the given value is a raw expression.
     */
    public function isExpression($value): bool
    {
        return $value instanceof Expression;
    }

    /**
     * Concatenate an array of segments, removing empties.
     */
    protected function concatenate(array $segments): string
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string) $value !== '';
        }));
    }

    /**
     * Set the table prefix.
     */
    public function setTablePrefix(string $prefix): self
    {
        $this->tablePrefix = $prefix;
        return $this;
    }

    /**
     * Get the table prefix.
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }
}
