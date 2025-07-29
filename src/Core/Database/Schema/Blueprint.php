<?
declare(strict_types=1);
php\np



namespace IslamWiki\Core\Database\Schema;

use Closure;
use BadMethodCallException;
use Illuminate\Support\Traits\Macroable;
use IslamWiki\Core\Database\Connection;

class Blueprint
{
    use Macroable;

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
        $this->addImpliedCommands();
        $this->addFluentCommands();
        
        $statements = [];
        foreach ($this->commands as $command) {
            $method = 'compile' . ucfirst($command['name']);
            if (method_exists($grammar, $method)) {
                if ($sql = $grammar->$method($this, $command, $connection)) {
                    $statements = array_merge($statements, (array) $sql);
                }
            }
        }
        return $statements;
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
        foreach ($this->columns as $column) {
            foreach (['primary', 'unique', 'index'] as $index) {
                if ($column->$index ?? false) {
                    $this->$index($column->name);
                    $column->$index = false;
                }
            }
        }
    }

    public function addFluentCommands(): void
    {
        foreach (['engine', 'charset', 'collation', 'temporary'] as $property) {
            if ($this->$property !== null) {
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
        return strtolower($this->prefix . $this->table . '_' . implode('_', $columns) . '_' . $type);
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
            compact('type', 'name'), $parameters
        ));
        return $column;
    }

    public function id(string $column = 'id'): Fluent
    {
        return $this->unsignedBigInteger($column, true);
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

    public function integer(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('integer', $column, compact('autoIncrement', 'unsigned'));
    }

    public function bigInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
    }

    public function unsignedBigInteger(string $column, bool $autoIncrement = false): Fluent
    {
        return $this->bigInteger($column, $autoIncrement, true);
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
        return $this->addCommand('foreign', compact('columns', 'name'));
    }

    public function creating(): bool
    {
        return collect($this->commands)->contains('name', 'create');
    }
}
