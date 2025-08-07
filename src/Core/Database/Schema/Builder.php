<?php

declare(strict_types=1);

namespace IslamWiki\Core\Database\Schema;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Query\Builder as QueryBuilder;
use Closure;
use RuntimeException;

class Builder
{
    /**
     * The database connection instance.
     */
    protected Connection $connection;

    /**
     * The schema grammar instance.
     */
    protected $grammar;

    /**
     * The Blueprint resolver callback.
     */
    protected $resolver;

    /**
     * The default string length for migrations.
     */
    public static int $defaultStringLength = 255;

    /**
     * Create a new database Schema manager.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }

    /**
     * Determine if the given table exists.
     */
    public function hasTable(string $table): bool
    {
        $table = $this->connection->getTablePrefix() . $table;
        return count($this->getTables($table)) > 0;
    }

    /**
     * Get the column listing for a given table.
     */
    public function getColumnListing(string $table): array
    {
        $results = $this->connection->select(
            $this->grammar->compileColumnListing(
                $this->connection->getTablePrefix() . $table
            )
        );

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Determine if the given table has a given column.
     */
    public function hasColumn(string $table, string $column): bool
    {
        return in_array(
            strtolower($column),
            array_map('strtolower', $this->getColumnListing($table))
        );
    }

    /**
     * Determine if the given table has given columns.
     */
    public function hasColumns(string $table, array $columns): bool
    {
        $tableColumns = array_map('strtolower', $this->getColumnListing($table));

        foreach ($columns as $column) {
            if (!in_array(strtolower($column), $tableColumns)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Execute the blueprint to build / modify the table.
     */
    public function build(Blueprint $blueprint): void
    {
        error_log("[Schema] build() called");
        $statements = $blueprint->toSql($this->connection, $this->grammar);
        error_log("[Schema] Generated statements: " . json_encode($statements));
        foreach ($statements as $sql) {
            error_log("[Schema] Executing SQL: $sql");
            $this->connection->statement($sql);
        }
    }

    /**
     * Create a new table on the schema.
     */
    public function create(string $table, \Closure $callback): void
    {
        error_log("[Schema] create() called for table: $table");
        $this->build($this->createBlueprint($table, $callback));
    }

    /**
     * Drop a table from the schema.
     */
    public function drop(string $table): void
    {
        $this->build($this->createBlueprint($table, function (Blueprint $blueprint) {
            $blueprint->drop();
        }));
    }

    /**
     * Drop a table from the schema if it exists.
     */
    public function dropIfExists(string $table): void
    {
        $this->build($this->createBlueprint($table, function (Blueprint $blueprint) {
            $blueprint->dropIfExists();
        }));
    }

    /**
     * Drop columns from a table schema.
     */
    public function dropColumns(string $table, string|array $columns): void
    {
        $this->table($table, function (Blueprint $blueprint) use ($columns) {
            $blueprint->dropColumn($columns);
        });
    }

    /**
     * Rename a table on the schema.
     */
    public function rename(string $from, string $to): void
    {
        $this->build($this->createBlueprint($from, function (Blueprint $blueprint) use ($to) {
            $blueprint->rename($to);
        }));
    }

    /**
     * Modify a table on the schema.
     */
    public function table(string $table, Closure $callback): void
    {
        $this->build($this->createBlueprint($table, $callback));
    }

    /**
     * Create a new command set with a Closure.
     */
    protected function createBlueprint(string $table, \Closure $callback = null): Blueprint
    {
        error_log("[Schema] createBlueprint() called for table: $table");
        $blueprint = new Blueprint($table, $callback);

        if ($callback) {
            error_log("[Schema] Executing callback for table: $table");
            $blueprint->create(); // Add the create command
            $callback($blueprint);
        }

        return $blueprint;
    }

    /**
     * Get the database connection instance.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Set the database connection instance.
     */
    public function setConnection(Connection $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Get the schema grammar instance.
     */
    public function getGrammar()
    {
        return $this->grammar;
    }

    /**
     * Set the schema grammar instance.
     */
    public function withGrammar($grammar): self
    {
        $this->grammar = $grammar;
        return $this;
    }

    /**
     * Get the tables that exist on the database.
     */
    protected function getTables(string $table): array
    {
        return $this->connection->select(
            $this->grammar->compileTableExists(),
            [$this->connection->getDatabaseName(), $table]
        );
    }
}
