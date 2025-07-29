<?
declare(strict_types=1);
php\np



namespace IslamWiki\Core\Database\Schema;

class ForeignKeyDefinition
{
    /**
     * The schema builder instance.
     */
    protected Blueprint $blueprint;

    /**
     * The foreign key command definition.
     */
    protected array $command;

    /**
     * Create a new foreign key definition instance.
     */
    public function __construct(Blueprint $blueprint, array $command)
    {
        $this->command = $command;
        $this->blueprint = $blueprint;
    }

    /**
     * Set the referenced table.
     */
    public function references($columns): self
    {
        $this->command['references'] = (array) $columns;
        return $this;
    }

    /**
     * Set the referenced columns.
     */
    public function on($table): self
    {
        $this->command['on'] = $table;
        return $this;
    }

    /**
     * Set the "on delete" behavior.
     */
    public function onDelete(?string $action): self
    {
        $this->command['onDelete'] = $action;
        return $this;
    }

    /**
     * Set the "on update" behavior.
     */
    public function onUpdate(?string $action): self
    {
        $this->command['onUpdate'] = $action;
        return $this;
    }

    /**
     * Cascade on delete.
     */
    public function cascadeOnDelete(): self
    {
        return $this->onDelete('cascade');
    }

    /**
     * Restrict on delete.
     */
    public function restrictOnDelete(): self
    {
        return $this->onDelete('restrict');
    }

    /**
     * Set null on delete.
     */
    public function nullOnDelete(): self
    {
        return $this->onDelete('set null');
    }

    /**
     * Cascade on update.
     */
    public function cascadeOnUpdate(): self
    {
        return $this->onUpdate('cascade');
    }

    /**
     * Get the blueprint instance.
     */
    public function getBlueprint(): Blueprint
    {
        return $this->blueprint;
    }

    /**
     * Get the foreign key command definition.
     */
    public function getCommand(): array
    {
        return $this->command;
    }
}
