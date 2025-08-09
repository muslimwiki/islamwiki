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
use Psr\Log\LoggerInterface;

/**
 * Bayan Controller
 *
 * Handles HTTP requests for the Bayan knowledge graph system.
 */
class BayanController
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
    public function __construct(BayanFormatter $bayanManager, LoggerInterface $logger)
    {
        $this->bayanManager = $bayanManager;
        $this->logger = $logger;
    }

    /**
     * Display the Bayan knowledge graph dashboard.
     */
    public function index(Request $request): Response
    {
        try {
            $statistics = $this->bayanManager->getStatistics();
            $hubNodes = $this->bayanManager->getQueryManager()->getHubNodes(5);
            // Use QueryManager search for consistency with dashboard
            $recentNodes = $this->bayanManager->getQueryManager()->search('', [], 10);

            $data = [
                'statistics' => $statistics,
                'hub_nodes' => $hubNodes,
                'recent_nodes' => $recentNodes,
                'title' => 'Bayan Knowledge Graph'
            ];

            return new Response(200, ['Content-Type' => 'text/html'], $this->renderView('bayan/index.twig', $data));
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

            return new Response(200, ['Content-Type' => 'text/html'], $this->renderView('bayan/show.twig', $data));
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

            return new Response(200, ['Content-Type' => 'text/html'], $this->renderView('bayan/search.twig', $data));
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
            return new Response(200, ['Content-Type' => 'text/html'], $this->renderView('bayan/create.twig', [
                'title' => 'Create New Node'
            ]));
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

    /**
     * Render a view template.
     */
    protected function renderView(string $template, array $data = []): string
    {
        // This is a simplified view renderer
        // In a real implementation, you would use a proper template engine
        $templatePath = dirname(__DIR__, 2) . '/resources/views/' . $template;

        if (!file_exists($templatePath)) {
            return '<h1>Bayan Knowledge Graph</h1><p>Template not found: ' . htmlspecialchars($template) . '</p>';
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
