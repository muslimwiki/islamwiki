<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Core\Database\Schema;

/**
 * Database Schema Grammar
 *
 * Handles SQL generation for schema operations.
 */
class Grammar
{
    /**
     * Compile the SQL to determine if a table exists.
     */
    public function compileTableExists(): string
    {
        return "SELECT * FROM information_schema.tables WHERE table_schema = ? AND table_name = ?";
    }

    /**
     * Compile the SQL to get the column listing for a table.
     */
    public function compileColumnListing(string $table): string
    {
        return "SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ?";
    }

    /**
     * Compile a create table command.
     */
    public function compileCreateTable(Blueprint $blueprint, array $columns, array $commands): string
    {
        $sql = "CREATE TABLE {$blueprint->getTable()} (";
        $sql .= implode(', ', $columns);

        if (!empty($commands)) {
            $sql .= ', ' . implode(', ', $commands);
        }

        $sql .= ')';

        return $sql;
    }

    /**
     * Compile a drop table command.
     */
    public function compileDropTable(Blueprint $blueprint): string
    {
        return "DROP TABLE {$blueprint->getTable()}";
    }

    /**
     * Compile a drop table if exists command.
     */
    public function compileDropTableIfExists(Blueprint $blueprint): string
    {
        return "DROP TABLE IF EXISTS {$blueprint->getTable()}";
    }

    /**
     * Compile a rename table command.
     */
    public function compileRenameTable(Blueprint $blueprint, string $to): string
    {
        return "RENAME TABLE {$blueprint->getTable()} TO {$to}";
    }

    /**
     * Compile an add column command.
     */
    public function compileAddColumn(Blueprint $blueprint, array $columns): string
    {
        $sql = "ALTER TABLE {$blueprint->getTable()} ADD ";
        $sql .= implode(', ADD ', $columns);

        return $sql;
    }

    /**
     * Compile a drop column command.
     */
    public function compileDropColumn(Blueprint $blueprint, array $columns): string
    {
        $sql = "ALTER TABLE {$blueprint->getTable()} DROP COLUMN ";
        $sql .= implode(', DROP COLUMN ', $columns);

        return $sql;
    }

    /**
     * Compile a modify column command.
     */
    public function compileModifyColumn(Blueprint $blueprint, array $columns): string
    {
        $sql = "ALTER TABLE {$blueprint->getTable()} MODIFY ";
        $sql .= implode(', MODIFY ', $columns);

        return $sql;
    }

    /**
     * Compile a rename column command.
     */
    public function compileRenameColumn(Blueprint $blueprint, string $from, string $to): string
    {
        return "ALTER TABLE {$blueprint->getTable()} RENAME COLUMN {$from} TO {$to}";
    }

    /**
     * Compile an add index command.
     */
    public function compileAddIndex(Blueprint $blueprint, string $index, array $columns): string
    {
        $sql = "ALTER TABLE {$blueprint->getTable()} ADD INDEX {$index} (";
        $sql .= implode(', ', $columns);
        $sql .= ')';

        return $sql;
    }

    /**
     * Compile a drop index command.
     */
    public function compileDropIndex(Blueprint $blueprint, string $index): string
    {
        return "ALTER TABLE {$blueprint->getTable()} DROP INDEX {$index}";
    }

    /**
     * Compile an add foreign key command.
     */
    public function compileAddForeignKey(Blueprint $blueprint, string $name, array $columns, string $on, array $onColumns, string $onDelete = null, string $onUpdate = null): string
    {
        $sql = "ALTER TABLE {$blueprint->getTable()} ADD CONSTRAINT {$name} FOREIGN KEY (";
        $sql .= implode(', ', $columns);
        $sql .= ") REFERENCES {$on} (";
        $sql .= implode(', ', $onColumns);
        $sql .= ')';

        if ($onDelete) {
            $sql .= " ON DELETE {$onDelete}";
        }

        if ($onUpdate) {
            $sql .= " ON UPDATE {$onUpdate}";
        }

        return $sql;
    }

    /**
     * Compile a drop foreign key command.
     */
    public function compileDropForeignKey(Blueprint $blueprint, string $name): string
    {
        return "ALTER TABLE {$blueprint->getTable()} DROP FOREIGN KEY {$name}";
    }
}
