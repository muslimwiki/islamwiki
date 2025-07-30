<?php

declare(strict_types=1);



namespace IslamWiki\Core\Database\Migrations;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Schema\Builder as SchemaBuilder;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Exception;

class Migrator
{
    /**
     * The database connection instance.
     */
    protected Connection $connection;

    /**
     * The schema builder instance.
     */
    protected SchemaBuilder $schema;

    /**
     * The path to the migration files.
     */
    protected string $migrationPath;

    /**
     * The name of the migration table.
     */
    protected string $migrationTable = 'migrations';
    protected string $currentMigrationFile = '';

    /**
     * Create a new migrator instance.
     */
    public function __construct(Connection $connection, string $migrationPath)
    {
        $this->connection = $connection;
        $this->schema = $connection->getSchemaBuilder();
        $this->migrationPath = rtrim($migrationPath, '/');
        
        $this->ensureMigrationsTableExists();
    }

    /**
     * Ensure the migrations table exists.
     */
    protected function ensureMigrationsTableExists(): void
    {
        if ($this->schema->hasTable($this->migrationTable)) {
            return;
        }
        $this->schema->create($this->migrationTable, function ($table) {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
            $table->timestamps();
        });
    }

    /**
     * Run the pending migrations.
     */
    public function run(): array
    {
        $this->ensureMigrationsTableExists();
        $ran = [];
        $batch = $this->getNextBatchNumber();
        $migrations = $this->getPendingMigrations();
        
        error_log("[Migrator] Batch: $batch");
        error_log("[Migrator] Pending migrations: " . json_encode($migrations));

        foreach ($migrations as $migration) {
            error_log("[Migrator] Running migration: $migration");
            $this->runMigration($migration, $batch);
            $ran[] = $migration;
        }

        return $ran;
    }

    /**
     * Run a migration up.
     */
    public function runMigration(string $migration, int $batch): void
    {
        $this->currentMigrationFile = $migration;
        $migration = $this->resolve($migration);
        
        $this->connection->beginTransaction();
        
        try {
            $migration->up();
            
            // Log migration within the same transaction
            $this->logMigration($migration, $batch);
            
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Rollback the last batch of migrations.
     */
    public function rollback(): array
    {
        $migrations = $this->getLastBatchMigrations();
        $rolledBack = [];

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration);
            $rolledBack[] = $migration;
        }

        return $rolledBack;
    }

    /**
     * Rollback a migration.
     */
    protected function rollbackMigration(string $migration): void
    {
        $migration = $this->resolve($migration);
        
        $this->connection->beginTransaction();
        
        try {
            $migration->down();
            $this->deleteMigration($migration);
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Get all migration files.
     */
    public function getMigrationFiles(): array
    {
        $files = [];
        
        if (!is_dir($this->migrationPath)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->migrationPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $regex = new RegexIterator($iterator, '/^(.+)\.php$/i', RegexIterator::GET_MATCH);

        foreach ($regex as $match) {
            $files[] = basename($match[1], '.php');
        }

        sort($files);
        
        return $files;
    }

    /**
     * Get the pending migrations.
     */
    public function getPendingMigrations(): array
    {
        $ran = $this->getRanMigrations();
        $files = $this->getMigrationFiles();
        
        return array_diff($files, $ran);
    }

    /**
     * Get the migrations that have already run.
     */
    public function getRanMigrations(): array
    {
        return $this->connection->table($this->migrationTable)
            ->orderBy('batch')
            ->orderBy('migration')
            ->pluck('migration');
    }

    /**
     * Get the last batch of migrations.
     */
    public function getLastBatchMigrations(): array
    {
        $batch = $this->getLastBatchNumber();
        
        return $this->connection->table($this->migrationTable)
            ->where('batch', '=', $batch)
            ->orderBy('migration', 'desc')
            ->pluck('migration');
    }

    /**
     * Get the next batch number.
     */
    public function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Get the last batch number.
     */
    public function getLastBatchNumber(): int
    {
        $this->ensureMigrationsTableExists();
        $batch = $this->connection->table($this->migrationTable)
            ->max('batch');
        return $batch ?? 0;
    }

    /**
     * Log that a migration was run.
     */
    protected function logMigration(Migration $migration, int $batch): void
    {
        // Get the migration name from the file name, not the class name
        $migrationName = $this->getMigrationName($this->currentMigrationFile);
        
        $this->connection->table($this->migrationTable)->insert([
            'migration' => $migrationName,
            'batch' => $batch,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Delete a migration from the log.
     */
    protected function deleteMigration(string $migration): void
    {
        $migrationName = $this->getMigrationName($migration);
        
        $this->connection->table($this->migrationTable)
            ->where('migration', '=', $migrationName)
            ->delete();
    }

    /**
     * Resolve a migration instance from a file.
     */
    public function resolve(string $migration): Migration
    {
        $file = $this->migrationPath . '/' . $migration . '.php';
        
        if (!file_exists($file)) {
            throw new Exception("Migration file not found: {$file}");
        }
        
        // Get the migration class factory function
        $migrationFactory = require $file;
        
        // Create the migration instance with the connection
        $migrationClass = $migrationFactory($this->connection);
        
        if (!$migrationClass instanceof Migration) {
            throw new Exception("Migration {$migration} must be an instance of " . Migration::class);
        }
        
        // Set the connection on the migration instance
        $migrationClass->setConnection($this->connection->getName());
        
        return $migrationClass;
    }

    /**
     * Get the name of the migration.
     */
    protected function getMigrationName(string $migration): string
    {
        return str_replace('.php', '', basename($migration));
    }

    /**
     * Get the migration path.
     */
    public function getMigrationPath(): string
    {
        return $this->migrationPath;
    }

    /**
     * Set the migration path.
     */
    public function setMigrationPath(string $path): self
    {
        $this->migrationPath = $path;
        return $this;
    }
}
