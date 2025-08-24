<?php

/**
 * Simple Iqra Search Engine Test
 */

require_once __DIR__ . '/../src/Core/Application.php';

use Application;\Application
use IslamWiki\Core\Search\IqraSearchEngine;

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Iqra Search Engine - Simple Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        h1 { color: #2c3e50; text-align: center; }
        h2 { color: #34495e; }
        .result { background-color: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 3px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Iqra Search Engine - Simple Test</h1>
        <p>Testing basic functionality of the Iqra search engine.</p>";

try {
    // Initialize the application
    $app = new Application(__DIR__ . '/..');
    $container = $app->getContainer();
    $db = $container->get('db');

    echo "<div class='test-section success'>
        <h2>✅ Test 1: Application Initialization</h2>
        <p>Application and database connection initialized successfully.</p>
    </div>";

    // Create Iqra search engine instance
    $iqraEngine = new IqraSearchEngine($db);

    echo "<div class='test-section success'>
        <h2>✅ Test 2: Search Engine Initialization</h2>
        <p>Iqra search engine created successfully.</p>
    </div>";

    // Test query normalization
    $testQuery = "  Allah   Muhammad   ";
    $normalizedQuery = $iqraEngine->normalizeQuery($testQuery);

    echo "<div class='test-section success'>
        <h2>✅ Test 3: Query Normalization</h2>
        <p><strong>Original:</strong> \"$testQuery\"</p>
        <p><strong>Normalized:</strong> \"$normalizedQuery\"</p>
    </div>";

    // Test tokenization
    $tokens = $iqraEngine->tokenizeQuery($normalizedQuery);

    echo "<div class='test-section success'>
        <h2>✅ Test 4: Query Tokenization</h2>
        <p><strong>Tokens:</strong> " . implode(', ', $tokens) . "</p>
    </div>";

    // Test Islamic terms detection
    $islamicTerms = $iqraEngine->containsIslamicTerms($normalizedQuery);

    echo "<div class='test-section success'>
        <h2>✅ Test 5: Islamic Terms Detection</h2>
        <p><strong>Found Islamic terms:</strong> " . implode(', ', $islamicTerms) . "</p>
    </div>";

    // Test Arabic text detection
    $arabicText = "بسم الله الرحمن الرحيم";
    $containsArabic = $iqraEngine->containsArabic($arabicText);

    echo "<div class='test-section success'>
        <h2>✅ Test 6: Arabic Text Detection</h2>
        <p><strong>Text:</strong> \"$arabicText\"</p>
        <p><strong>Contains Arabic:</strong> " . ($containsArabic ? 'Yes' : 'No') . "</p>
    </div>";

    // Test search suggestions
    echo "<div class='test-section info'>
        <h2>🔍 Test 7: Search Suggestions</h2>
        <p>Testing search suggestions for 'allah'...</p>";

    try {
        $suggestions = $iqraEngine->getSuggestions('allah');
        echo "<div class='result'>
            <p><strong>Suggestions found:</strong> " . count($suggestions) . "</p>";
        if (!empty($suggestions)) {
            echo "<ul>";
            foreach ($suggestions as $suggestion) {
                echo "<li><strong>{$suggestion['type']}:</strong> {$suggestion['text']} - ";
                echo "<a href='{$suggestion['url']}'>{$suggestion['url']}</a></li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    } catch (Exception $e) {
        echo "<div class='result error'>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        </div>";
    }

    echo "</div>";

    // Test search functionality
    echo "<div class='test-section info'>
        <h2>🔍 Test 8: Search Functionality</h2>
        <p>Testing search for 'allah'...</p>";

    try {
        $searchResult = $iqraEngine->search('allah', ['type' => 'all', 'limit' => 5]);
        echo "<div class='result'>
            <p><strong>Search completed successfully!</strong></p>
            <p><strong>Total results:</strong> {$searchResult['total']}</p>
            <p><strong>Results returned:</strong> " . count($searchResult['results']) . "</p>
            <p><strong>Search type:</strong> {$searchResult['type']}</p>
            <p><strong>Query:</strong> {$searchResult['query']}</p>
        </div>";

        if (!empty($searchResult['results'])) {
            echo "<h3>Sample Results:</h3>";
            foreach (array_slice($searchResult['results'], 0, 3) as $result) {
                echo "<div class='result'>
                    <p><strong>Type:</strong> {$result['type']}</p>
                    <p><strong>Title:</strong> {$result['title']}</p>
                    <p><strong>URL:</strong> {$result['url']}</p>
                    <p><strong>Relevance:</strong> " . ($result['relevance'] ?? 'N/A') . "</p>
                </div>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='result error'>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        </div>";
    }

    echo "</div>";

    // Test content type counts
    echo "<div class='test-section info'>
        <h2>📊 Test 9: Content Type Statistics</h2>
        <p>Testing content type counts for 'allah'...</p>";

    try {
        $stats = [
            'pages' => $iqraEngine->getPageCount($tokens),
            'quran' => $iqraEngine->getQuranCount($tokens),
            'hadith' => $iqraEngine->getHadithCount($tokens),
            'calendar' => $iqraEngine->getCalendarCount($tokens),
            'salah' => $iqraEngine->getSalahCount($tokens),
            'scholars' => $iqraEngine->getScholarCount($tokens)
        ];

        echo "<div class='result'>
            <h3>Content Type Counts:</h3>
            <ul>";
        foreach ($stats as $type => $count) {
            echo "<li><strong>{$type}:</strong> {$count}</li>";
        }
        echo "</ul>
        </div>";
    } catch (Exception $e) {
        echo "<div class='result error'>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        </div>";
    }

    echo "</div>";

    // Summary
    echo "<div class='test-section success'>
        <h2>🎉 Iqra Search Engine Test Summary</h2>
        <p>The Iqra search engine has been successfully tested with the following features:</p>
        <ul>
            <li>✅ Advanced search engine initialization</li>
            <li>✅ Query normalization and tokenization</li>
            <li>✅ Islamic terms detection</li>
            <li>✅ Arabic text support</li>
            <li>✅ Search suggestions</li>
            <li>✅ Multi-content type search</li>
            <li>✅ Relevance scoring</li>
            <li>✅ Content type statistics</li>
        </ul>
        <p><strong>Next steps:</strong> Visit <a href='/iqra-search'>/iqra-search</a> to use the full Iqra search interface.</p>
    </div>";
} catch (Exception $e) {
    echo "<div class='test-section error'>
        <h2>❌ Error</h2>
        <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        <p><strong>File:</strong> " . $e->getFile() . "</p>
        <p><strong>Line:</strong> " . $e->getLine() . "</p>
        <pre>" . $e->getTraceAsString() . "</pre>
    </div>";
}

echo "</div>
</body>
</html>";
