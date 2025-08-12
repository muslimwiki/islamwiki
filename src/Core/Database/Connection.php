<?php

declare(strict_types=1);

namespace IslamWiki\Core\Database;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Connection
{
    /**
     * The active PDO connection.
     */
    protected ?PDO $pdo = null;

    /**
     * The database configuration.
     */
    protected array $config = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'islamwiki',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ];

    /**
     * Create a new database connection instance.
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get the PDO connection.
     */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    /**
     * Determine if the connection has an active PDO instance.
     */
    public function isConnected(): bool
    {
        return $this->pdo instanceof PDO;
    }

    /**
     * Connect to the database.
     */
    protected function connect(): void
    {
        $config = $this->config;
        $dsn = $this->getDsn($config);
        $options = $config['options'] ?? [];

        try {
            $this->pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $options
            );

            // Set timezone to UTC
            $this->pdo->exec('SET time_zone = "+00:00"');
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Connection failed: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create a DSN string from the configuration.
     */
    protected function getDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';
        $port = $config['port'] ?? '';

        $dsn = "{$driver}:host={$host}";

        if ($port !== '') {
            $dsn .= ";port={$port}";
        }

        $dsn .= ";dbname={$database};charset={$charset}";

        return $dsn;
    }

    /**
     * Execute a query and return the statement.
     */
    public function query(string $query, array $bindings = []): PDOStatement
    {
        $statement = $this->getPdo()->prepare($query);
        $this->bindValues($statement, $this->prepareBindings($bindings));
        $statement->execute();
        return $statement;
    }

    /**
     * Execute a query and return the first column of the first row.
     */
    public function scalar(string $query, array $bindings = [])
    {
        $statement = $this->query($query, $bindings);
        return $statement->fetchColumn();
    }

    /**
     * Execute a query and return the first row.
     */
    public function first(string $query, array $bindings = [])
    {
        $statement = $this->query($query, $bindings);
        return $statement->fetch();
    }

    /**
     * Execute a query and return all rows.
     */
    public function select(string $query, array $bindings = []): array
    {
        $statement = $this->query($query, $bindings);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute an INSERT statement and return the last insert ID.
     */
    public function insert(string $query, array $bindings = []): string
    {
        $this->query($query, $bindings);
        return $this->getPdo()->lastInsertId();
    }

    /**
     * Execute an UPDATE statement and return the number of affected rows.
     */
    public function update(string $query, array $bindings = []): int
    {
        $statement = $this->query($query, $bindings);
        return $statement->rowCount();
    }

    /**
     * Execute a DELETE statement and return the number of affected rows.
     */
    public function delete(string $query, array $bindings = []): int
    {
        $statement = $this->query($query, $bindings);
        return $statement->rowCount();
    }

    /**
     * Execute a raw SQL statement.
     */
    public function statement(string $query, array $bindings = []): bool
    {
        $statement = $this->query($query, $bindings);
        return $statement->rowCount() > 0;
    }

    /**
     * Begin a database transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->getPdo()->beginTransaction();
    }

    /**
     * Commit the active database transaction.
     */
    public function commit(): bool
    {
        if ($this->inTransaction()) {
            return $this->getPdo()->commit();
        }
        return false;
    }

    /**
     * Check if a transaction is active.
     */
    public function inTransaction(): bool
    {
        return $this->getPdo()->inTransaction();
    }

    /**
     * Rollback the active database transaction.
     */
    public function rollBack(): bool
    {
        if ($this->inTransaction()) {
            return $this->getPdo()->rollBack();
        }
        return false;
    }

    /**
     * Execute a Closure within a transaction.
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Bind values to a statement.
     */
    protected function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Prepare the query bindings for execution.
     */
    protected function prepareBindings(array $bindings): array
    {
        foreach ($bindings as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $bindings[$key] = $value->format('Y-m-d H:i:s');
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    /**
     * Get the table prefix.
     */
    public function getTablePrefix(): string
    {
        return $this->config['prefix'] ?? '';
    }

    /**
     * Set the table prefix.
     */
    public function setTablePrefix(string $prefix): self
    {
        $this->config['prefix'] = $prefix;
        return $this;
    }

    /**
     * Get the database connection name.
     */
    public function getName(): string
    {
        return $this->config['name'] ?? 'default';
    }

    /**
     * Get the PDO driver name.
     */
    public function getDriverName(): string
    {
        return $this->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Get the database configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Disconnect from the underlying PDO connection.
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder(): Schema\Builder
    {
        return new Schema\Builder($this);
    }

    /**
     * Get the schema grammar instance.
     */
    public function getSchemaGrammar()
    {
        return new Schema\Grammar();
    }

    /**
     * Get the database name.
     */
    public function getDatabaseName(): string
    {
        return $this->config['database'] ?? '';
    }

    /**
     * Begin a fluent query against a database table.
     */
    public function table(string $table): Query\Builder
    {
        $builder = new Query\Builder($this);
        return $builder->from($table);
    }

    /**
     * Dynamically pass methods to the default connection.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->getPdo()->$method(...$parameters);
    }
}
