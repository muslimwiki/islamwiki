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
 * Bayan Node Manager
 * 
 * Manages knowledge graph nodes representing Islamic concepts,
 * verses, hadith, scholars, and other entities.
 */
class NodeManager
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
     * Create a new NodeManager instance.
     */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * Create a new knowledge node.
     */
    public function create(array $data): ?int
    {
        try {
            $requiredFields = ['type', 'title', 'content'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new \InvalidArgumentException("Missing required field: {$field}");
                }
            }

            $sql = "INSERT INTO bayan_nodes (type, title, content, metadata, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            $metadata = isset($data['metadata']) ? json_encode($data['metadata']) : '{}';
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                $data['type'],
                $data['title'],
                $data['content'],
                $metadata
            ]);

            $nodeId = $this->connection->lastInsertId();
            
            $this->logger->info('Created Bayan node', [
                'node_id' => $nodeId,
                'type' => $data['type'],
                'title' => $data['title']
            ]);

            return (int) $nodeId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Bayan node', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Find a node by ID.
     */
    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM bayan_nodes WHERE id = ? AND deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result) {
                $result['metadata'] = json_decode($result['metadata'], true) ?: [];
            }
            
            return $result ?: null;
        } catch (\Exception $e) {
            $this->logger->error('Failed to find Bayan node by ID', [
                'node_id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Find nodes by type and criteria.
     */
    public function findByType(string $type, array $criteria = []): array
    {
        try {
            $sql = "SELECT * FROM bayan_nodes WHERE type = ? AND deleted_at IS NULL";
            $params = [$type];

            if (!empty($criteria)) {
                $conditions = [];
                foreach ($criteria as $key => $value) {
                    if ($key === 'title' || $key === 'content') {
                        $conditions[] = "{$key} LIKE ?";
                        $params[] = "%{$value}%";
                    } elseif ($key === 'metadata') {
                        $conditions[] = "JSON_EXTRACT(metadata, ?) = ?";
                        $params[] = "$.{$value['key']}";
                        $params[] = $value['value'];
                    }
                }
                
                if (!empty($conditions)) {
                    $sql .= " AND " . implode(" AND ", $conditions);
                }
            }

            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['metadata'] = json_decode($result['metadata'], true) ?: [];
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to find Bayan nodes by type', [
                'type' => $type,
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Update a node.
     */
    public function update(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE bayan_nodes SET updated_at = NOW()";
            $params = [];

            $allowedFields = ['title', 'content', 'metadata'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $sql .= ", {$field} = ?";
                    $params[] = $field === 'metadata' ? json_encode($data[$field]) : $data[$field];
                }
            }

            $sql .= " WHERE id = ? AND deleted_at IS NULL";
            $params[] = $id;

            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                $this->logger->info('Updated Bayan node', [
                    'node_id' => $id,
                    'updated_fields' => array_keys($data)
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update Bayan node', [
                'node_id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Soft delete a node.
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "UPDATE bayan_nodes SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([$id]);

            if ($result) {
                $this->logger->info('Deleted Bayan node', ['node_id' => $id]);
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete Bayan node', [
                'node_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get total count of nodes.
     */
    public function getCount(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM bayan_nodes WHERE deleted_at IS NULL";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan node count', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get count by node types.
     */
    public function getTypeCounts(): array
    {
        try {
            $sql = "SELECT type, COUNT(*) as count 
                    FROM bayan_nodes 
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
            $this->logger->error('Failed to get Bayan node type counts', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Search nodes by content.
     */
    public function search(string $query, array $filters = [], int $limit = 20): array
    {
        try {
            $sql = "SELECT * FROM bayan_nodes WHERE deleted_at IS NULL";
            $params = [];

            // Add search conditions
            if (!empty($query)) {
                $sql .= " AND (title LIKE ? OR content LIKE ?)";
                $params[] = "%{$query}%";
                $params[] = "%{$query}%";
            }

            // Add filters
            if (!empty($filters['type'])) {
                $sql .= " AND type = ?";
                $params[] = $filters['type'];
            }

            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['metadata'] = json_decode($result['metadata'], true) ?: [];
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to search Bayan nodes', [
                'query' => $query,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
} 