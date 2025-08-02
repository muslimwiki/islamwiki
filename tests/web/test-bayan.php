<?php

/**
 * Test Bayan Knowledge Graph System
 */

require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Initialize the application
    $app = new \IslamWiki\Core\Application(__DIR__ . '/..');
    
    // Get Bayan manager directly since container binding isn't working
    $container = $app->getContainer();
    $bayanManager = new \IslamWiki\Core\Bayan\BayanManager(
        $container->get('db'),
        $container->get(\Psr\Log\LoggerInterface::class)
    );
    
    echo "<h1>Bayan Knowledge Graph Test</h1>\n";
    
    // Test creating a node
    echo "<h2>Creating Test Node</h2>\n";
    $nodeData = [
        'type' => 'concept',
        'title' => 'Tawhid (Monotheism)',
        'content' => 'Tawhid is the Islamic concept of monotheism, the belief in the oneness of Allah. It is the fundamental principle of Islam.',
        'metadata' => [
            'category' => 'Aqeedah',
            'importance' => 'fundamental'
        ]
    ];
    
    $nodeId = $bayanManager->createNode($nodeData);
    echo "<p>✅ Created node with ID: {$nodeId}</p>\n";
    
    // Test creating another node
    echo "<h2>Creating Second Node</h2>\n";
    $nodeData2 = [
        'type' => 'concept',
        'title' => 'Shirk (Polytheism)',
        'content' => 'Shirk is the sin of practicing idolatry or polytheism, i.e., the deification or worship of anyone or anything other than Allah.',
        'metadata' => [
            'category' => 'Aqeedah',
            'opposite_of' => 'Tawhid'
        ]
    ];
    
    $nodeId2 = $bayanManager->createNode($nodeData2);
    echo "<p>✅ Created second node with ID: {$nodeId2}</p>\n";
    
    // Test creating a relationship
    echo "<h2>Creating Relationship</h2>\n";
    $relationshipId = $bayanManager->createRelationship($nodeId, $nodeId2, 'opposes', [
        'strength' => 'strong',
        'reason' => 'Tawhid and Shirk are opposite concepts in Islamic theology'
    ]);
    echo "<p>✅ Created relationship with ID: {$relationshipId}</p>\n";
    
    // Test getting statistics
    echo "<h2>Graph Statistics</h2>\n";
    $stats = $bayanManager->getStatistics();
    echo "<pre>" . print_r($stats, true) . "</pre>\n";
    
    // Test searching
    echo "<h2>Search Results</h2>\n";
    $searchResults = $bayanManager->search('Tawhid');
    echo "<p>Found " . count($searchResults) . " nodes matching 'Tawhid'</p>\n";
    
    if (!empty($searchResults)) {
        echo "<ul>\n";
        foreach ($searchResults as $result) {
            echo "<li><strong>{$result['title']}</strong> ({$result['type']}) - {$result['content']}</li>\n";
        }
        echo "</ul>\n";
    }
    
    // Test getting related nodes
    echo "<h2>Related Nodes</h2>\n";
    $relatedNodes = $bayanManager->getRelatedNodes($nodeId);
    echo "<p>Found " . count($relatedNodes) . " related nodes for node {$nodeId}</p>\n";
    
    if (!empty($relatedNodes)) {
        echo "<ul>\n";
        foreach ($relatedNodes as $related) {
            echo "<li><strong>{$related['title']}</strong> via {$related['relationship_type']}</li>\n";
        }
        echo "</ul>\n";
    }
    
    echo "<h2>✅ Bayan System Test Complete!</h2>\n";
    
} catch (\Exception $e) {
    echo "<h1>❌ Error Testing Bayan System</h1>\n";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
} 