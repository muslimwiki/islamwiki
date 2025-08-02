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

namespace IslamWiki\Core\Bayan;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\Logger;
use Psr\Log\LoggerInterface;

/**
 * Bayan Knowledge Graph Manager
 * 
 * Manages the Islamic knowledge graph system for connecting concepts,
 * verses, hadith, scholars, and other Islamic knowledge entities.
 */
class BayanManager
{
    /**
     * Database connection instance.
     */
    protected Connection $connection;

    /**
     * Logger instance.
     */
    protected LoggerInterface $logger;

    /**
     * Node manager for handling knowledge nodes.
     */
    protected NodeManager $nodeManager;

    /**
     * Edge manager for handling relationships.
     */
    protected EdgeManager $edgeManager;

    /**
     * Query manager for complex graph queries.
     */
    protected QueryManager $queryManager;

    /**
     * Create a new BayanManager instance.
     */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
        
        $this->nodeManager = new NodeManager($connection, $logger);
        $this->edgeManager = new EdgeManager($connection, $logger);
        $this->queryManager = new QueryManager($connection, $logger);
    }

    /**
     * Create a new knowledge node.
     */
    public function createNode(array $data): ?int
    {
        try {
            return $this->nodeManager->create($data);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Bayan node', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create a relationship between two nodes.
     */
    public function createRelationship(int $sourceId, int $targetId, string $type, array $attributes = []): ?int
    {
        try {
            return $this->edgeManager->create($sourceId, $targetId, $type, $attributes);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Bayan relationship', [
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Find nodes by type and criteria.
     */
    public function findNodes(string $type, array $criteria = []): array
    {
        try {
            return $this->nodeManager->findByType($type, $criteria);
        } catch (\Exception $e) {
            $this->logger->error('Failed to find Bayan nodes', [
                'type' => $type,
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get related nodes for a given node.
     */
    public function getRelatedNodes(int $nodeId, string $relationshipType = null, int $limit = 10): array
    {
        try {
            return $this->queryManager->getRelatedNodes($nodeId, $relationshipType, $limit);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get related Bayan nodes', [
                'node_id' => $nodeId,
                'relationship_type' => $relationshipType,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Perform a graph traversal to find paths between nodes.
     */
    public function findPaths(int $sourceId, int $targetId, int $maxDepth = 3): array
    {
        try {
            return $this->queryManager->findPaths($sourceId, $targetId, $maxDepth);
        } catch (\Exception $e) {
            $this->logger->error('Failed to find Bayan paths', [
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'max_depth' => $maxDepth,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Search the knowledge graph.
     */
    public function search(string $query, array $filters = [], int $limit = 20): array
    {
        try {
            return $this->queryManager->search($query, $filters, $limit);
        } catch (\Exception $e) {
            $this->logger->error('Failed to search Bayan graph', [
                'query' => $query,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get graph statistics.
     */
    public function getStatistics(): array
    {
        try {
            return [
                'total_nodes' => $this->nodeManager->getCount(),
                'total_relationships' => $this->edgeManager->getCount(),
                'node_types' => $this->nodeManager->getTypeCounts(),
                'relationship_types' => $this->edgeManager->getTypeCounts(),
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan statistics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get the node manager.
     */
    public function getNodeManager(): NodeManager
    {
        return $this->nodeManager;
    }

    /**
     * Get the edge manager.
     */
    public function getEdgeManager(): EdgeManager
    {
        return $this->edgeManager;
    }

    /**
     * Get the query manager.
     */
    public function getQueryManager(): QueryManager
    {
        return $this->queryManager;
    }
} 