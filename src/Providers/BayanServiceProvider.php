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
 * MERCHANTABILITY or FITNESS FOR ANY PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace IslamWiki\Providers;

use IslamWiki\Core\Formatter\BayanManager;
use IslamWiki\Core\Formatter\NodeManager;
use IslamWiki\Core\Formatter\EdgeManager;
use IslamWiki\Core\Formatter\QueryManager;
use IslamWiki\Core\Container\Asas;
use Psr\Log\LoggerInterface;

/**
 * Bayan Service Provider
 * 
 * Registers the Bayan knowledge graph system with the application container.
 */
class BayanServiceProvider
{
    /**
     * Register Bayan services with the container.
     */
    public function register(Asas $container): void
    {
        // Register BayanManager as singleton
        $container->singleton(BayanManager::class, function () use ($container) {
            $connection = $container->get('db');
            $logger = $container->get(LoggerInterface::class);
            return new BayanManager($connection, $logger);
        });

        // Register NodeManager as singleton
        $container->singleton(NodeManager::class, function () use ($container) {
            $connection = $container->get('db');
            $logger = $container->get(LoggerInterface::class);
            return new NodeManager($connection, $logger);
        });

        // Register EdgeManager as singleton
        $container->singleton(EdgeManager::class, function () use ($container) {
            $connection = $container->get('db');
            $logger = $container->get(LoggerInterface::class);
            return new EdgeManager($connection, $logger);
        });

        // Register QueryManager as singleton
        $container->singleton(QueryManager::class, function () use ($container) {
            $connection = $container->get('db');
            $logger = $container->get(LoggerInterface::class);
            return new QueryManager($connection, $logger);
        });

        // Register aliases for easier access
        $container->alias('bayan', BayanManager::class);
        $container->alias('bayan.nodes', NodeManager::class);
        $container->alias('bayan.edges', EdgeManager::class);
        $container->alias('bayan.queries', QueryManager::class);
    }

    /**
     * Boot the Bayan service provider.
     */
    public function boot(Asas $container): void
    {
        // Log that Bayan system is ready
        $logger = $container->get(LoggerInterface::class);
        $logger->info('Bayan knowledge graph system initialized');
    }
} 