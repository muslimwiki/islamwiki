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

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Formatter\BayanFormatter;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;
use Psr\Log\LoggerInterface;

/**
 * Bayan Controller
 *
 * Handles HTTP requests for the Bayan knowledge graph system.
 */
class BayanController extends Controller
{
    /**
     * Bayan manager instance.
     */
    protected BayanFormatter $bayanManager;

    /**
     * Logger instance.
     */
    protected LoggerInterface $logger;

    /**
     * Create a new BayanController instance.
     */
    public function __construct(Connection $db, AsasContainer $container)
    {
        parent::__construct($db, $container);
        $this->logger = $container->get(\Psr\Log\LoggerInterface::class);
        // Resolve via container when bound; otherwise construct directly
        if ($container->has(BayanFormatter::class)) {
            $this->bayanManager = $container->get(BayanFormatter::class);
        } else {
            $this->bayanManager = new BayanFormatter($db, $this->logger);
        }
    }

    /**
     * Display the Bayan knowledge graph dashboard.
     */
    public function index(Request $request): Response
    {
        try {
            $statistics = $this->bayanManager->getStatistics();
            $queryManager = $this->bayanManager->getQueryManager();
            $hubNodes = $queryManager->getHubNodes(5);
            // Use QueryManager search for consistency with dashboard
            $recentNodes = $queryManager->search('', [], 10);

            // Graph data: take top hubs and a few neighbors each
            $graph = [ 'nodes' => [], 'edges' => [] ];
            $nodeIndexById = [];
            $maxHubs = min(5, count($hubNodes));
            for ($i = 0; $i < $maxHubs; $i++) {
                $hubNode = $hubNodes[$i];
                $hubId = (int)($hubNode['id'] ?? $hubNode->id ?? 0);
                if ($hubId === 0) { continue; }
                if (!isset($nodeIndexById[$hubId])) {
                    $nodeIndexById[$hubId] = true;
                    $graph['nodes'][] = [
                        'id' => $hubId,
                        'label' => $hubNode['title'] ?? $hubNode->title ?? ('Node ' . $hubId),
                        'type' => $hubNode['type'] ?? $hubNode->type ?? 'node',
                        'isHub' => true,
                    ];
                }
                $neighbors = $queryManager->getRelatedNodes($hubId, null, 6);
                foreach ($neighbors as $neighbor) {
                    $neighborId = (int)($neighbor['id'] ?? $neighbor->id ?? 0);
                    if ($neighborId === 0) { continue; }
                    if (!isset($nodeIndexById[$neighborId])) {
                        $nodeIndexById[$neighborId] = true;
                        $graph['nodes'][] = [
                            'id' => $neighborId,
                            'label' => $neighbor['title'] ?? $neighbor->title ?? ('Node ' . $neighborId),
                            'type' => $neighbor['type'] ?? $neighbor->type ?? 'node',
                            'isHub' => false,
                        ];
                    }
                    $graph['edges'][] = [ 'source' => $hubId, 'target' => $neighborId ];
                }
            }

            // Metrics for distributions
            $metrics = $queryManager->getGraphMetrics();

            $data = [
                'statistics' => $statistics,
                'hub_nodes' => $hubNodes,
                'recent_nodes' => $recentNodes,
                'graph' => $graph,
                'node_types' => $metrics['node_types'] ?? [],
                'edge_types' => $metrics['edge_types'] ?? [],
                'title' => 'Bayan Knowledge Graph'
            ];

            return $this->view('bayan/index', $data, 200);
        } catch (\Exception $e) {
            $this->logger->error('Failed to display Bayan dashboard', [
                'error' => $e->getMessage()
            ]);
            return new Response(500, ['Content-Type' => 'text/html'], 'Internal Server Error: ' . htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * Display a specific node and its relationships.
     */
    public function show(Request $request, int $id): Response
    {
        try {
            $node = $this->bayanManager->getNodeManager()->findById($id);

            if (!$node) {
                return new Response(404, ['Content-Type' => 'text/html'], 'Node not found');
            }

            $relatedNodes = $this->bayanManager->getRelatedNodes($id);
            $relationships = $this->bayanManager->getEdgeManager()->findByNode($id);

            $data = [
                'node' => $node,
                'related_nodes' => $relatedNodes,
                'relationships' => $relationships,
                'title' => $node['title']
            ];

            return $this->view('bayan/show', $data, 200);
        } catch (\Exception $e) {
            $this->logger->error('Failed to display Bayan node', [
                'node_id' => $id,
                'error' => $e->getMessage()
            ]);
            return new Response(500, ['Content-Type' => 'text/html'], 'Internal Server Error');
        }
    }

    /**
     * Search the knowledge graph.
     */
    public function search(Request $request): Response
    {
        try {
            $query = $request->getQueryParam('q', '');
            $type = $request->getQueryParam('type', '');
            $relationshipType = $request->getQueryParam('relationship_type', '');

            $filters = [];
            if ($type) {
                $filters['type'] = $type;
            }
            if ($relationshipType) {
                $filters['relationship_type'] = $relationshipType;
            }

            $results = $this->bayanManager->search($query, $filters);
            $statistics = $this->bayanManager->getStatistics();

            $data = [
                'query' => $query,
                'filters' => $filters,
                'results' => $results,
                'statistics' => $statistics,
                'title' => 'Search Bayan Knowledge Graph'
            ];

            return $this->view('bayan/search', $data, 200);
        } catch (\Exception $e) {
            $this->logger->error('Failed to search Bayan graph', [
                'query' => $query ?? '',
                'error' => $e->getMessage()
            ]);
            return new Response(500, ['Content-Type' => 'text/html'], 'Internal Server Error');
        }
    }

    /**
     * Create a new node.
     */
    public function create(Request $request): Response
    {
        if ($request->getMethod() === 'GET') {
            return $this->view('bayan/create', [
                'title' => 'Create New Node'
            ], 200);
        }

        try {
            $data = $request->getParsedBody();

            $requiredFields = ['type', 'title', 'content'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                        'error' => "Missing required field: {$field}"
                    ]));
                }
            }

            $nodeId = $this->bayanManager->createNode($data);

            if ($nodeId) {
                $this->logger->info('Created Bayan node via API', [
                    'node_id' => $nodeId,
                    'type' => $data['type'],
                    'title' => $data['title']
                ]);

                return new Response(201, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'node_id' => $nodeId,
                    'message' => 'Node created successfully'
                ]));
            } else {
                return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                    'error' => 'Failed to create node'
                ]));
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Bayan node via API', [
                'data' => $data ?? [],
                'error' => $e->getMessage()
            ]);
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Create a relationship between nodes.
     */
    public function createRelationship(Request $request): Response
    {
        try {
            $data = $request->getParsedBody();

            $requiredFields = ['source_id', 'target_id', 'type'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                        'error' => "Missing required field: {$field}"
                    ]));
                }
            }

            $attributes = $data['attributes'] ?? [];
            $edgeId = $this->bayanManager->createRelationship(
                (int) $data['source_id'],
                (int) $data['target_id'],
                $data['type'],
                $attributes
            );

            if ($edgeId) {
                $this->logger->info('Created Bayan relationship via API', [
                    'edge_id' => $edgeId,
                    'source_id' => $data['source_id'],
                    'target_id' => $data['target_id'],
                    'type' => $data['type']
                ]);

                return new Response(201, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'edge_id' => $edgeId,
                    'message' => 'Relationship created successfully'
                ]));
            } else {
                return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                    'error' => 'Failed to create relationship'
                ]));
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Bayan relationship via API', [
                'data' => $data ?? [],
                'error' => $e->getMessage()
            ]);
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Get graph statistics.
     */
    public function statistics(Request $request): Response
    {
        try {
            $statistics = $this->bayanManager->getStatistics();
            $metrics = $this->bayanManager->getQueryManager()->getGraphMetrics();

            $data = array_merge($statistics, $metrics);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode($data));
        } catch (\Exception $e) {
            $this->logger->error('Failed to get Bayan statistics', [
                'error' => $e->getMessage()
            ]);
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Internal server error'
            ]));
        }
    }

    /**
     * Find paths between nodes.
     */
    public function findPaths(Request $request): Response
    {
        try {
            $sourceId = (int) $request->getQueryParam('source_id', 0);
            $targetId = (int) $request->getQueryParam('target_id', 0);
            $maxDepth = (int) $request->getQueryParam('max_depth', 3);

            if (!$sourceId || !$targetId) {
                return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                    'error' => 'Both source_id and target_id are required'
                ]));
            }

            $paths = $this->bayanManager->findPaths($sourceId, $targetId, $maxDepth);

            return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'paths' => $paths,
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'max_depth' => $maxDepth
            ]));
        } catch (\Exception $e) {
            $this->logger->error('Failed to find Bayan paths', [
                'source_id' => $sourceId ?? 0,
                'target_id' => $targetId ?? 0,
                'error' => $e->getMessage()
            ]);
            return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Internal server error'
            ]));
        }
    }

    // Uses base Controller::view()
}
