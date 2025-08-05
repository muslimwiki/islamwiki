<?php

/**
 * Bayan Web Interface Test
 */

require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Initialize the application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    $container = $app->getContainer();
    
    // Create Bayan manager
    $bayanManager = new \IslamWiki\Core\Bayan\BayanManager(
        $container->get('db'),
        $container->get(\Psr\Log\LoggerInterface::class)
    );
    
    // Get statistics
    $statistics = $bayanManager->getStatistics();
    $hubNodes = $bayanManager->getQueryManager()->getHubNodes(5);
    $recentNodes = $bayanManager->getNodeManager()->search('', [], 10);
    
    // Render a simple dashboard
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bayan Knowledge Graph</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100">
        <div class="container mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Bayan Knowledge Graph</h1>
                <p class="text-lg text-gray-600">Explore the interconnected world of Islamic knowledge</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Nodes</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= $statistics['total_nodes'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Relationships</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= $statistics['total_relationships'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Node Types</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count($statistics['node_types'] ?? []) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Relationship Types</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count($statistics['relationship_types'] ?? []) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-4 mb-8">
                <a href="/bayan/search" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search Knowledge Graph
                </a>
                <a href="/bayan/create" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create New Node
                </a>
            </div>

            <!-- Recent Nodes -->
            <?php if (!empty($recentNodes)): ?>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Recent Nodes</h2>
                    <p class="text-sm text-gray-600">Recently added to the knowledge graph</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach ($recentNodes as $node): ?>
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:shadow-md transition duration-200">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900">
                                    <a href="/bayan/node/<?= $node['id'] ?>" class="hover:text-blue-600"><?= htmlspecialchars($node['title']) ?></a>
                                </h3>
                                <p class="text-sm text-gray-600"><?= ucfirst($node['type']) ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars(substr($node['content'], 0, 150)) ?><?= strlen($node['content']) > 150 ? '...' : '' ?></p>
                            </div>
                            <div class="ml-4 text-right">
                                <p class="text-xs text-gray-500"><?= date('M j, Y', strtotime($node['created_at'])) ?></p>
                                <?php if (isset($node['relationship_count']) && $node['relationship_count'] > 0): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                    <?= $node['relationship_count'] ?> connections
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    
} catch (\Exception $e) {
    echo "<h1>❌ Error in Bayan Web Interface</h1>\n";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
} 