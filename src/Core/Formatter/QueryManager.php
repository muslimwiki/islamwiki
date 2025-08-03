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
use Psr\Log\LoggerInterface;

/**
 * Bayan Query Manager
 * 
 * Handles complex graph queries, traversals, and search operations
 * for the Islamic knowledge graph system.
 */
class QueryManager
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
     * Create a new QueryManager instance.
     */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * Get related nodes for a given node.
     */
    public function getRelatedNodes(int $nodeId, string $relationshipType = null, int $limit = 10): array
    {
        try {
            $sql = "SELECT DISTINCT 
                        n.id, n.type, n.title, n.content, n.metadata,
                        e.type as relationship_type, e.attributes as relationship_attributes,
                        CASE 
                            WHEN e.source_id = ? THEN 'outgoing'
                            ELSE 'incoming'
                        END as direction
                    FROM bayan_nodes n
                    INNER JOIN bayan_edges e ON (e.source_id = n.id OR e.target_id = n.id)
                    WHERE (e.source_id = ? OR e.target_id = ?)
                    AND e.deleted_at IS NULL
                    AND n.deleted_at IS NULL
                    AND n.id != ?";
            
            $params = [$nodeId, $nodeId, $nodeId, $nodeId];

            if ($relationshipType) {
                $sql .= " AND e.type = ?";
                $params[] = $relationshipType;
            }

            $sql .= " ORDER BY e.created_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['metadata'] = json_decode($result['metadata'], true) ?: [];
                $result['relationship_attributes'] = json_decode($result['relationship_attributes'], true) ?: [];
            }
            
            return $results;
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
     * Find paths between two nodes using breadth-first search.
     */
    public function findPaths(int $sourceId, int $targetId, int $maxDepth = 3): array
    {
        try {
            $paths = [];
            $visited = [];
            $queue = [[$sourceId]];
            
            while (!empty($queue) && count($paths) < 10) { // Limit to 10 paths
                $currentPath = array_shift($queue);
                $currentNode = end($currentPath);
                
                if ($currentNode === $targetId) {
                    $paths[] = $this->enrichPath($currentPath);
                    continue;
                }
                
                if (count($currentPath) >= $maxDepth) {
                    continue;
                }
                
                $pathKey = implode('-', $currentPath);
                if (isset($visited[$pathKey])) {
                    continue;
                }
                $visited[$pathKey] = true;
                
                // Get neighbors
                $neighbors = $this->getNeighbors($currentNode);
                foreach ($neighbors as $neighbor) {
                    if (!in_array($neighbor['id'], $currentPath)) {
                        $newPath = array_merge($currentPath, [$neighbor['id']]);
                        $queue[] = $newPath;
                    }
                }
            }
            
            return $paths;
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
     * Search the knowledge graph with filters.
     */
    public function search(string $query, array $filters = [], int $limit = 20): array
    {
        try {
            $sql = "SELECT n.*, 
                           COUNT(e.id) as relationship_count,
                           GROUP_CONCAT(DISTINCT e.type) as relationship_types
                    FROM bayan_nodes n
                    LEFT JOIN bayan_edges e ON (e.source_id = n.id OR e.target_id = n.id) AND e.deleted_at IS NULL
                    WHERE n.deleted_at IS NULL";
            
            $params = [];
            $conditions = [];

            // Add search conditions
            if (!empty($query)) {
                $conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
                $params[] = "%{$query}%";
                $params[] = "%{$query}%";
            }

            // Add filters
            if (!empty($filters['type'])) {
                $conditions[] = "n.type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['relationship_type'])) {
                $conditions[] = "e.type = ?";
                $params[] = $filters['relationship_type'];
            }

            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }

            $sql .= " GROUP BY n.id ORDER BY n.created_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['metadata'] = json_decode($result['metadata'], true) ?: [];
                $result['relationship_types'] = $result['relationship_types'] ? explode(',', $result['relationship_types']) : [];
            }
            
            return $results;
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
     * Get nodes with the most relationships (hubs).
     */
    public function getHubNodes(int $limit = 10): array
    {
        try {
            $sql = "SELECT n.*, COUNT(e.id) as relationship_count
                    FROM bayan_nodes n
                    LEFT JOIN bayan_edges e ON (e.source_id = n.id OR e.target_id = n.id) AND e.deleted_at IS NULL
                    WHERE n.deleted_at IS NULL
                    GROUP BY n.id
                    HAVING relationship_count > 0
                    ORDER BY relationship_count DESC
                    LIMIT ?";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$limit]);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['metadata'] = json_decode($result['metadata'], true) ?: [];
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan hub nodes', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get nodes by centrality (degree, betweenness, etc.).
     */
    public function getNodesByCentrality(string $centralityType = 'degree', int $limit = 10): array
    {
        try {
            switch ($centralityType) {
                case 'degree':
                    return $this->getHubNodes($limit);
                case 'betweenness':
                    // Simplified betweenness calculation
                    return $this->getNodesByBetweenness($limit);
                default:
                    return $this->getHubNodes($limit);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan nodes by centrality', [
                'centrality_type' => $centralityType,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get graph statistics and metrics.
     */
    public function getGraphMetrics(): array
    {
        try {
            $metrics = [];
            
            // Total nodes and edges
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM bayan_nodes WHERE deleted_at IS NULL) as total_nodes,
                        (SELECT COUNT(*) FROM bayan_edges WHERE deleted_at IS NULL) as total_edges";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $basic = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $metrics['total_nodes'] = (int) $basic['total_nodes'];
            $metrics['total_edges'] = (int) $basic['total_edges'];
            
            // Average degree
            if ($metrics['total_nodes'] > 0) {
                $metrics['average_degree'] = round(2 * $metrics['total_edges'] / $metrics['total_nodes'], 2);
            } else {
                $metrics['average_degree'] = 0;
            }
            
            // Node type distribution
            $sql = "SELECT type, COUNT(*) as count 
                    FROM bayan_nodes 
                    WHERE deleted_at IS NULL 
                    GROUP BY type";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $metrics['node_types'] = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            // Edge type distribution
            $sql = "SELECT type, COUNT(*) as count 
                    FROM bayan_edges 
                    WHERE deleted_at IS NULL 
                    GROUP BY type";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $metrics['edge_types'] = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            return $metrics;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan graph metrics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get neighbors of a node.
     */
    protected function getNeighbors(int $nodeId): array
    {
        try {
            $sql = "SELECT DISTINCT n.id, n.type, n.title, e.type as relationship_type
                    FROM bayan_nodes n
                    INNER JOIN bayan_edges e ON (e.source_id = n.id OR e.target_id = n.id)
                    WHERE (e.source_id = ? OR e.target_id = ?)
                    AND e.deleted_at IS NULL
                    AND n.deleted_at IS NULL
                    AND n.id != ?";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$nodeId, $nodeId, $nodeId]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan neighbors', [
                'node_id' => $nodeId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Enrich a path with node and relationship details.
     */
    protected function enrichPath(array $path): array
    {
        try {
            $enrichedPath = [];
            
            for ($i = 0; $i < count($path) - 1; $i++) {
                $currentNode = $path[$i];
                $nextNode = $path[$i + 1];
                
                // Get node details
                $nodeManager = new NodeManager($this->connection, $this->logger);
                $currentNodeData = $nodeManager->findById($currentNode);
                $nextNodeData = $nodeManager->findById($nextNode);
                
                // Get relationship details
                $edgeManager = new EdgeManager($this->connection, $this->logger);
                $relationships = $edgeManager->findByNodes($currentNode, $nextNode);
                
                $enrichedPath[] = [
                    'from_node' => $currentNodeData,
                    'to_node' => $nextNodeData,
                    'relationships' => $relationships
                ];
            }
            
            return $enrichedPath;
        } catch (\Exception $e) {
            $this->logger->error('Failed to enrich Bayan path', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get nodes by betweenness centrality (simplified).
     */
    protected function getNodesByBetweenness(int $limit = 10): array
    {
        try {
            // Simplified betweenness calculation
            $sql = "SELECT n.*, 
                           COUNT(DISTINCT e1.id) + COUNT(DISTINCT e2.id) as betweenness_score
                    FROM bayan_nodes n
                    LEFT JOIN bayan_edges e1 ON e1.source_id = n.id AND e1.deleted_at IS NULL
                    LEFT JOIN bayan_edges e2 ON e2.target_id = n.id AND e2.deleted_at IS NULL
                    WHERE n.deleted_at IS NULL
                    GROUP BY n.id
                    HAVING betweenness_score > 0
                    ORDER BY betweenness_score DESC
                    LIMIT ?";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$limit]);
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['metadata'] = json_decode($result['metadata'], true) ?: [];
            }
            
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan nodes by betweenness', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
} 