<?php

declare(strict_types=1);

namespace IslamWiki\Core\Database\Schema;

use Closure;
use BadMethodCallException;
use IslamWiki\Core\Database\Connection;

class Blueprint
{
    protected string $table;
    protected string $prefix;
    protected array $columns = [];
    protected array $commands = [];
    public ?string $engine = null;
    public string $charset = '';
    public string $collation = '';
    public bool $temporary = false;

    public function __construct(string $table, ?Closure $callback = null, string $prefix = '')
    {
        $this->table = $table;
        $this->prefix = $prefix;
        $callback?->__invoke($this);
    }

    public function build(Connection $connection, $grammar): void
    {
        foreach ($this->toSql($connection, $grammar) as $statement) {
            $connection->statement($statement);
        }
    }

    public function toSql(Connection $connection, $grammar): array
    {
        error_log("[Blueprint] toSql() called with " . count($this->commands) . " commands");
        $this->addImpliedCommands();
        $this->addFluentCommands();
        error_log("[Blueprint] After adding commands: " . count($this->commands) . " commands");

        $statements = [];

        // Handle create table command
        if ($this->creating()) {
            error_log("[Blueprint] Creating table, compiling columns and commands");
            $columns = [];
            $commands = [];

            // Compile columns (remove duplicates)
            $seenColumns = [];
            foreach ($this->columns as $column) {
                $columnKey = $column->name;
                if (!isset($seenColumns[$columnKey])) {
                    $columns[] = $this->compileColumn($column);
                    $seenColumns[$columnKey] = true;
                }
            }

            // Compile commands (indexes, etc.) - filter out empty commands and duplicates
            $seenCommands = [];
            foreach ($this->commands as $command) {
                if ($command->name !== 'create' && !empty($this->compileCommand($command))) {
                    $commandSql = $this->compileCommand($command);
                    $commandKey = $command->name . '_' . md5($commandSql);
                    if (!isset($seenCommands[$commandKey])) {
                        $commands[] = $commandSql;
                        $seenCommands[$commandKey] = true;
                    }
                }
            }

            $sql = $grammar->compileCreateTable($this, $columns, $commands);
            error_log("[Blueprint] Generated CREATE TABLE SQL: $sql");
            $statements[] = $sql;
        }

        error_log("[Blueprint] Returning " . count($statements) . " statements");
        return $statements;
    }

    protected function compileColumn($column): string
    {
        // Handle special column types
        $type = $this->getSqlType($column->type);

        $sql = $column->name . ' ' . $type;

        // Handle enum parameters
        if ($type === 'ENUM' && isset($column->allowed)) {
            $enumValues = array_map([$this, 'quote'], $column->allowed);
            $sql .= '(' . implode(', ', $enumValues) . ')';
        } elseif (isset($column->length)) {
            $sql .= '(' . $column->length . ')';
        } elseif (isset($column->precision) && isset($column->scale)) {
            $sql .= '(' . $column->precision . ',' . $column->scale . ')';
        }

        if (isset($column->unsigned) && $column->unsigned) {
            $sql .= ' UNSIGNED';
        }

        if (isset($column->autoIncrement) && $column->autoIncrement) {
            $sql .= ' AUTO_INCREMENT';
        }

        if (isset($column->nullable) && $column->nullable) {
            $sql .= ' NULL';
        } else {
            $sql .= ' NOT NULL';
        }

        if (isset($column->default) && $column->default !== null && $column->default !== '') {
            $defaultValue = $column->default;
            if (is_bool($defaultValue)) {
                $defaultValue = $defaultValue ? 1 : 0;
            }
            $sql .= ' DEFAULT ' . $this->quote($defaultValue);
        }

        if (isset($column->primary) && $column->primary) {
            $sql .= ' PRIMARY KEY';
        }

        return $sql;
    }

    protected function getSqlType(string $type): string
    {
        $typeMap = [
            'unsignedBigInteger' => 'BIGINT',
            'unsignedInteger' => 'INT',
            'bigInteger' => 'BIGINT',
            'integer' => 'INT',
            'string' => 'VARCHAR',
            'text' => 'TEXT',
            'timestamp' => 'TIMESTAMP',
            'boolean' => 'BOOLEAN',
            'enum' => 'ENUM',
            'date' => 'DATE',
            'time' => 'TIME',
            'decimal' => 'DECIMAL',
        ];

        return $typeMap[$type] ?? strtoupper($type);
    }

    protected function compileCommand($command): string
    {
        switch ($command->name) {
            case 'unique':
                return 'UNIQUE KEY ' . $command->index . ' (' . implode(', ', $command->columns) . ')';
            case 'index':
                return 'KEY ' . $command->index . ' (' . implode(', ', $command->columns) . ')';
            case 'primary':
                return 'PRIMARY KEY (' . implode(', ', $command->columns) . ')';
            default:
                return '';
        }
    }

    protected function quote($value): string
    {
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }
        return (string) $value;
    }

    protected function addImpliedCommands(): void
    {
        if ($this->columns && !$this->creating()) {
            array_unshift($this->commands, $this->createCommand('add'));
        }
        $this->addFluentIndexes();
    }

    protected function addFluentIndexes(): void
    {
        $processedColumns = [];
        foreach ($this->columns as $column) {
            $columnKey = $column->name;
            if (isset($processedColumns[$columnKey])) {
                continue; // Skip already processed columns
            }

            foreach (['primary', 'unique', 'index'] as $index) {
                if ($column->$index ?? false) {
                    $this->$index($column->name);
                    $column->$index = false;
                }
            }
            $processedColumns[$columnKey] = true;
        }
    }

    public function addFluentCommands(): void
    {
        foreach (['engine', 'charset', 'collation', 'temporary'] as $property) {
            if (isset($this->$property) && $this->$property !== null && $this->$property !== '') {
                $this->addCommand($property, [$property => $this->$property]);
            }
        }
    }

    public function create(): Fluent
    {
        return $this->addCommand('create');
    }

    public function drop(): Fluent
    {
        return $this->addCommand('drop');
    }

    public function dropIfExists(): Fluent
    {
        return $this->addCommand('dropIfExists');
    }

    public function dropColumn($columns): Fluent
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        return $this->addCommand('dropColumn', compact('columns'));
    }

    public function index($columns, ?string $name = null): Fluent
    {
        return $this->indexCommand('index', $columns, $name);
    }

    public function primary($columns, ?string $name = null): Fluent
    {
        return $this->indexCommand('primary', $columns, $name);
    }

    public function unique($columns, ?string $name = null): Fluent
    {
        return $this->indexCommand('unique', $columns, $name);
    }

    protected function indexCommand(string $type, $columns, ?string $index): Fluent
    {
        $columns = (array) $columns;
        $index = $index ?: $this->createIndexName($type, $columns);
        return $this->addCommand($type, compact('index', 'columns'));
    }

    protected function createIndexName(string $type, array $columns): string
    {
        $index = strtolower($this->prefix . $this->table . '_' . implode('_', $columns) . '_' . $type);

        // If the index name is too long, use a hash instead
        if (strlen($index) > 64) {
            $hash = substr(md5(implode('_', $columns)), 0, 8);
            $index = strtolower($this->prefix . $this->table . '_' . $hash . '_' . $type);
        }

        return $index;
    }

    protected function addCommand(string $name, array $parameters = []): Fluent
    {
        $this->commands[] = $command = $this->createCommand($name, $parameters);
        return $command;
    }

    protected function createCommand(string $name, array $parameters = []): Fluent
    {
        return new Fluent(array_merge(compact('name'), $parameters));
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    protected function addColumn(string $type, string $name, array $parameters = []): Fluent
    {
        $this->columns[] = $column = new Fluent(array_merge(
            compact('type', 'name'),
            $parameters
        ));
        return $column;
    }

    public function id(string $column = 'id'): Fluent
    {
        $column = $this->unsignedBigInteger($column);
        $column->autoIncrement = true;
        $column->primary = true;
        return $column;
    }

    public function increments(string $column): Fluent
    {
        $column = $this->unsignedInteger($column, true);
        $column->primary = true;
        return $column;
    }

    public function unsignedInteger(string $column, bool $autoIncrement = false): Fluent
    {
        return $this->addColumn('integer', $column, ['autoIncrement' => $autoIncrement, 'unsigned' => true]);
    }

    public function string(string $column, ?int $length = null): Fluent
    {
        $length = $length ?: 255;
        return $this->addColumn('string', $column, compact('length'));
    }

    public function text(string $column): Fluent
    {
        return $this->addColumn('text', $column);
    }

    public function json(string $column): Fluent
    {
        return $this->addColumn('json', $column);
    }

    public function integer(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('integer', $column, compact('autoIncrement', 'unsigned'));
    }

    public function bigInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
    }

    public function unsignedBigInteger(string $column): Fluent
    {
        return $this->addColumn('unsignedBigInteger', $column, ['unsigned' => true]);
    }

    public function timestamp(string $column, int $precision = 0): Fluent
    {
        return $this->addColumn('timestamp', $column, compact('precision'));
    }

    public function timestamps(int $precision = 0): void
    {
        $this->timestamp('created_at', $precision)->nullable();
        $this->timestamp('updated_at', $precision)->nullable();
    }

    public function softDeletes(string $column = 'deleted_at', int $precision = 0): Fluent
    {
        return $this->timestamp($column, $precision)->nullable();
    }

    public function foreign($columns, ?string $name = null): ForeignKeyDefinition
    {
        $columns = (array) $columns;
        $name = $name ?: $this->createIndexName('foreign', $columns);
        $command = $this->addCommand('foreign', compact('columns', 'name'));
        return new ForeignKeyDefinition($this, $command->getAttributes());
    }

    public function boolean(string $column): Fluent
    {
        return $this->addColumn('boolean', $column);
    }

    public function enum(string $column, array $allowed): Fluent
    {
        return $this->addColumn('enum', $column, ['allowed' => $allowed]);
    }

    public function date(string $column): Fluent
    {
        return $this->addColumn('date', $column);
    }

    public function time(string $column): Fluent
    {
        return $this->addColumn('time', $column);
    }

    public function decimal(string $column, int $precision = 8, int $scale = 2): Fluent
    {
        return $this->addColumn('decimal', $column, compact('precision', 'scale'));
    }

    public function rememberToken(): Fluent
    {
        return $this->string('remember_token', 100)->nullable();
    }

    public function foreignId(string $column): Fluent
    {
        return $this->unsignedBigInteger($column);
    }

    public function creating(): bool
    {
        error_log("[Blueprint] creating() called, checking " . count($this->commands) . " commands");
        foreach ($this->commands as $command) {
            error_log("[Blueprint] Command: " . $command->name);
            if ($command->name === 'create') {
                error_log("[Blueprint] Found create command, returning true");
                return true;
            }
        }
        error_log("[Blueprint] No create command found, returning false");
        return false;
    }
}
