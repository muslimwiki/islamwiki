<?php

/**
 * Bayan Controller
 *
 * Handles HTTP requests for the Bayan knowledge graph system.
 *
 * @package IslamWiki\Http\Controllers
 * @version 0.0.3.0
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\Container;
use Psr\Log\LoggerInterface;

/**
 * Bayan Controller - Handles Knowledge Graph Functionality
 */
class BayanController extends Controller
{
    /**
     * Display the Bayan knowledge graph dashboard.
     */
    public function index(Request $request): Response
    {
        try {
            // Get basic statistics
            $statistics = $this->getBayanStatistics();
            
            // Get hub nodes
            $hubNodes = $this->getHubNodes(5);
            
            // Get recent nodes
            $recentNodes = $this->getRecentNodes(10);
            
            // Build simple graph data
            $graph = $this->buildGraphData($hubNodes);
            
            return $this->view('bayan/index', [
                'statistics' => $statistics,
                'hubNodes' => $hubNodes,
                'recentNodes' => $recentNodes,
                'graph' => $graph,
                'title' => 'Bayan Knowledge Graph - IslamWiki'
            ], 200);
            
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Show a specific node in the knowledge graph.
     */
    public function show(Request $request, string $nodeId): Response
    {
        try {
            $node = $this->getNode($nodeId);
            if (!$node) {
                return new Response(404, [], 'Node not found');
            }
            
            $relatedNodes = $this->getRelatedNodes($nodeId, 10);
            
            return $this->view('bayan/show', [
                'node' => $node,
                'relatedNodes' => $relatedNodes,
                'title' => $node['title'] . ' - Bayan - IslamWiki'
            ], 200);
            
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Search nodes in the knowledge graph.
     */
    public function search(Request $request): Response
    {
        try {
            $query = $request->getQueryParams()['q'] ?? '';
            $type = $request->getQueryParams()['type'] ?? 'all';
            $page = max(1, (int)($request->getQueryParams()['page'] ?? 1));
            $limit = 20;
            
            if (empty($query)) {
                return $this->view('bayan/search', [
                    'query' => '',
                    'results' => [],
                    'title' => 'Search Bayan - IslamWiki'
                ], 200);
            }
            
            $results = $this->searchNodes($query, $type, $page, $limit);
            $totalResults = $this->getTotalSearchResults($query, $type);
            
            return $this->view('bayan/search', [
                'query' => $query,
                'type' => $type,
                'results' => $results,
                'totalResults' => $totalResults,
                'currentPage' => $page,
                'totalPages' => ceil($totalResults / $limit),
                'title' => "Search: {$query} - Bayan - IslamWiki"
            ], 200);
            
        } catch (\Exception $e) {
            return new Response(500, [], 'Internal Server Error');
        }
    }

    /**
     * Get Bayan statistics.
     */
    private function getBayanStatistics(): array
    {
        try {
            $sql = "SELECT COUNT(*) as total_nodes FROM bayan_nodes";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            $totalNodes = (int)($result['total_nodes'] ?? 0);
            
            return [
                'total_nodes' => $totalNodes,
                'total_edges' => 0, // TODO: Implement edge counting
                'node_types' => 0,   // TODO: Implement type counting
                'last_updated' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'total_nodes' => 0,
                'total_edges' => 0,
                'node_types' => 0,
                'last_updated' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Get hub nodes (nodes with most connections).
     */
    private function getHubNodes(int $limit): array
    {
        try {
            $sql = "SELECT id, title, type, created_at FROM bayan_nodes ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get recent nodes.
     */
    private function getRecentNodes(int $limit): array
    {
        try {
            $sql = "SELECT id, title, type, created_at FROM bayan_nodes ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get a specific node.
     */
    private function getNode(string $nodeId): ?array
    {
        try {
            $sql = "SELECT id, title, type, content, created_at FROM bayan_nodes WHERE id = ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$nodeId]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get related nodes.
     */
    private function getRelatedNodes(string $nodeId, ?string $type, int $limit): array
    {
        try {
            $sql = "SELECT id, title, type, created_at FROM bayan_nodes WHERE id != ? ORDER BY created_at DESC LIMIT ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$nodeId, $limit]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Search nodes.
     */
    private function searchNodes(string $query, string $type, int $page, int $limit): array
    {
        try {
            $offset = ($page - 1) * $limit;
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT id, title, type, created_at FROM bayan_nodes 
                    WHERE title LIKE ? OR content LIKE ? 
                    ORDER BY created_at DESC LIMIT ? OFFSET ?";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm, $limit, $offset]);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get total search results count.
     */
    private function getTotalSearchResults(string $query, string $type): int
    {
        try {
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT COUNT(*) as count FROM bayan_nodes WHERE title LIKE ? OR content LIKE ?";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
            
            $result = $stmt->fetch();
            return (int)($result['count'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Build graph data for visualization.
     */
    private function buildGraphData(array $hubNodes): array
    {
        $graph = ['nodes' => [], 'edges' => []];
        
        foreach ($hubNodes as $node) {
            $graph['nodes'][] = [
                'id' => $node['id'],
                'label' => $node['title'],
                'type' => $node['type'] ?? 'node',
                'isHub' => true
            ];
        }
        
        return $graph;
    }
}
