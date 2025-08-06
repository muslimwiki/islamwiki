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

namespace IslamWiki\Core\Formatter;

use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Bayan Edge Manager
 * 
 * Manages relationships (edges) between knowledge graph nodes,
 * representing connections between Islamic concepts, verses, hadith, etc.
 */
class EdgeManager
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
     * Create a new EdgeManager instance.
     */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * Create a relationship between two nodes.
     */
    public function create(int $sourceId, int $targetId, string $type, array $attributes = []): ?int
    {
        try {
            // Validate that both nodes exist
            $nodeManager = new NodeManager($this->connection, $this->logger);
            $sourceNode = $nodeManager->findById($sourceId);
            $targetNode = $nodeManager->findById($targetId);

            if (!$sourceNode || !$targetNode) {
                throw new \InvalidArgumentException('Source or target node does not exist');
            }

            // Check if relationship already exists
            $existing = $this->findByNodes($sourceId, $targetId, $type);
            if (!empty($existing)) {
                $this->logger->warning('Relationship already exists', [
                    'source_id' => $sourceId,
                    'target_id' => $targetId,
                    'type' => $type
                ]);
                return $existing[0]['id'];
            }

            $sql = "INSERT INTO bayan_edges (source_id, target_id, type, attributes, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $attributesJson = json_encode($attributes);
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                $sourceId,
                $targetId,
                $type,
                $attributesJson
            ]);

            $edgeId = $this->connection->lastInsertId();
            
            $this->logger->info('Created Bayan relationship', [
                'edge_id' => $edgeId,
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'type' => $type
            ]);

            return (int) $edgeId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Bayan relationship', [
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Find relationships by source and target nodes.
     */
    public function findByNodes(int $sourceId, int $targetId, string $type = null): array
    {
        try {
            $sql = "SELECT * FROM bayan_edges WHERE source_id = ? AND target_id = ? AND deleted_at IS NULL";
            $params = [$sourceId, $targetId];

            if ($type) {
                $sql .= " AND type = ?";
                $params[] = $type;
            }

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['attributes'] = json_decode($result['attributes'], true) ?: [];
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to find Bayan relationships by nodes', [
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Find all relationships for a node (both incoming and outgoing).
     */
    public function findByNode(int $nodeId, string $direction = 'both'): array
    {
        try {
            $sql = "SELECT * FROM bayan_edges WHERE deleted_at IS NULL";
            $params = [];

            if ($direction === 'outgoing') {
                $sql .= " AND source_id = ?";
                $params[] = $nodeId;
            } elseif ($direction === 'incoming') {
                $sql .= " AND target_id = ?";
                $params[] = $nodeId;
            } else {
                $sql .= " AND (source_id = ? OR target_id = ?)";
                $params[] = $nodeId;
                $params[] = $nodeId;
            }

            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['attributes'] = json_decode($result['attributes'], true) ?: [];
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to find Bayan relationships by node', [
                'node_id' => $nodeId,
                'direction' => $direction,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Find relationships by type.
     */
    public function findByType(string $type, int $limit = 100): array
    {
        try {
            $sql = "SELECT * FROM bayan_edges WHERE type = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$type, $limit]);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['attributes'] = json_decode($result['attributes'], true) ?: [];
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to find Bayan relationships by type', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Update relationship attributes.
     */
    public function update(int $id, array $attributes): bool
    {
        try {
            $sql = "UPDATE bayan_edges SET attributes = ?, updated_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            $attributesJson = json_encode($attributes);
            
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([$attributesJson, $id]);

            if ($result) {
                $this->logger->info('Updated Bayan relationship', [
                    'edge_id' => $id,
                    'attributes' => $attributes
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update Bayan relationship', [
                'edge_id' => $id,
                'attributes' => $attributes,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Soft delete a relationship.
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "UPDATE bayan_edges SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([$id]);

            if ($result) {
                $this->logger->info('Deleted Bayan relationship', ['edge_id' => $id]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete Bayan relationship', [
                'edge_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get total count of relationships.
     */
    public function getCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM bayan_edges WHERE deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan relationship count', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get count by relationship types.
     */
    public function getTypeCounts(): array
    {
        try {
            $sql = "SELECT type, COUNT(*) as count 
                    FROM bayan_edges 
                    WHERE deleted_at IS NULL 
                    GROUP BY type 
                    ORDER BY count DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $counts = [];
            
            foreach ($results as $result) {
                $counts[$result['type']] = (int) $result['count'];
            }
            
            return $counts;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan relationship type counts', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Find bidirectional relationships between two nodes.
     */
    public function findBidirectional(int $node1Id, int $node2Id): array
    {
        try {
            $sql = "SELECT * FROM bayan_edges 
                    WHERE deleted_at IS NULL 
                    AND ((source_id = ? AND target_id = ?) OR (source_id = ? AND target_id = ?))
                    ORDER BY created_at DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$node1Id, $node2Id, $node2Id, $node1Id]);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['attributes'] = json_decode($result['attributes'], true) ?: [];
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to find bidirectional Bayan relationships', [
                'node1_id' => $node1Id,
                'node2_id' => $node2Id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
} 