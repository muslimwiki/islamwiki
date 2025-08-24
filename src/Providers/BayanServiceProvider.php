<?php

/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Container, either version 3 of the License, or
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

use IslamWiki\Core\Formatter\BayanFormatter;
use IslamWiki\Core\Formatter\NodeManager;
use IslamWiki\Core\Formatter\EdgeManager;
use IslamWiki\Core\Formatter\QueryManager;
use Container;\Container
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
    public function register(Container $container): void
    {
        // Register BayanFormatter as singleton (acts as Bayan manager)
        $container->set(BayanFormatter::class, function () use ($container) {
            $connection = $container->get('db');
            $logger = $container->get(LoggerInterface::class);
            return new BayanFormatter($connection, $logger);
        });

        // Also expose under simple key 'bayan' for places resolving by string alias
        $container->set('bayan', function (Container $c) {
            return $c->get(BayanFormatter::class);
        });

        // Register NodeManager as singleton
        $container->set(NodeManager::class, function () use ($container) {
            $connection = $container->get('db');
            $logger = $container->get(LoggerInterface::class);
            return new NodeManager($connection, $logger);
        });

        // Register EdgeManager as singleton
        $container->set(EdgeManager::class, function () use ($container) {
            $connection = $container->get('db');
            $logger = $container->get(LoggerInterface::class);
            return new EdgeManager($connection, $logger);
        });

        // Register QueryManager as singleton
        $container->set(QueryManager::class, function () use ($container) {
            $connection = $container->get('db');
            $logger = $container->get(LoggerInterface::class);
            return new QueryManager($connection, $logger);
        });

        // Register aliases for easier access (alias => abstract)
        // This container stores aliases as: aliases[alias] = abstract
        $container->alias(BayanFormatter::class, 'bayan');
        // Backward-compatibility: map the historical BayanManager class name to BayanFormatter
        $container->alias(BayanFormatter::class, 'IslamWiki\\Core\\Formatter\\BayanManager');
        $container->alias('bayan.nodes', NodeManager::class);
        $container->alias('bayan.edges', EdgeManager::class);
        $container->alias('bayan.queries', QueryManager::class);
    }

    /**
     * Boot the Bayan service provider.
     */
    public function boot(Container $container): void
    {
        // Log that Bayan system is ready
        $logger = $container->get(LoggerInterface::class);
        $logger->info('Bayan knowledge graph system initialized');
    }
}
