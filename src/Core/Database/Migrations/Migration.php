<?
declare(strict_types=1);
php\np



namespace IslamWiki\Core\Database\Migrations;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Schema\Builder as SchemaBuilder;

abstract class Migration
{
    /**
     * The database connection instance.
     */
    protected Connection $connection;

    /**
     * The name of the database connection to use.
     */
    protected ?string $connectionName = null;

    /**
     * Create a new migration instance.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Run the migrations.
     */
    abstract public function up(): void;

    /**
     * Reverse the migrations.
     */
    abstract public function down(): void;

    /**
     * Get the migration connection name.
     */
    public function getConnection(): ?string
    {
        return $this->connectionName;
    }

    /**
     * Set the migration connection name.
     */
    public function setConnection(?string $name): self
    {
        $this->connectionName = $name;
        return $this;
    }

    /**
     * Get the database connection.
     */
    public function getConnectionInstance(): Connection
    {
        return $this->connection;
    }

    /**
     * Run an SQL statement.
     */
    public function execute(string $query, array $bindings = []): bool
    {
        return $this->connection->statement($query, $bindings);
    }

    /**
     * Get a schema builder instance for the connection.
     */
    public function schema(): SchemaBuilder
    {
        return $this->connection->getSchemaBuilder();
    }

    /**
     * Determine if the given table exists.
     */
    public function hasTable(string $table): bool
    {
        return $this->connection->getSchemaBuilder()->hasTable($table);
    }

    /**
     * Determine if the given column exists.
     */
    public function hasColumn(string $table, string $column): bool
    {
        return $this->connection->getSchemaBuilder()->hasColumn($table, $column);
    }
}
