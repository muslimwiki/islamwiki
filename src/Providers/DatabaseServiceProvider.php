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

namespace IslamWiki\Providers;

use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Database\Migrations\Migrator;

/**
 * Database Service Provider
 * 
 * Handles database connection and migration services.
 */
class DatabaseServiceProvider
{
    /**
     * Register database services with the container.
     */
    public function register(AsasContainer $container): void
    {
        // Register database connection
        $container->bind('db', function () {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? 'islamwiki';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
            
            $config = [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => $charset,
                'driver' => 'mysql'
            ];
            
            return new Connection($config);
        });

        // Register migrator
        $container->bind('migrator', function () use ($container) {
            $connection = $container->get('db');
            $migrationPath = dirname(__DIR__, 2) . '/database/migrations';
            return new Migrator($connection, $migrationPath);
        });

        // Register database connection as singleton
        $container->singleton(Connection::class, function () use ($container) {
            return $container->get('db');
        });
    }
} 