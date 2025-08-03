<?php
/**
 * Iqra Search Engine Web Interface
 * 
 * A simple web interface for testing the Iqra search engine functionality.
 */

// Include necessary files
require_once __DIR__ . '/../src/Core/Container/Asas.php';
require_once __DIR__ . '/../src/Core/Database/Connection.php';
require_once __DIR__ . '/../src/Core/Logging/Logger.php';
require_once __DIR__ . '/../src/Http/Controllers/Controller.php';
require_once __DIR__ . '/../src/Core/Search/IqraSearchEngine.php';
require_once __DIR__ . '/../src/Http/Controllers/IqraSearchController.php';

use IslamWiki\Core\Container\Asas;
use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Logging\Logger;
use IslamWiki\Core\Search\IqraSearchEngine;
use IslamWiki\Http\Controllers\IqraSearchController;

// Initialize components
$container = new Asas();
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logger = new Logger($logDir, 'debug');
$container->instance(Logger::class, $logger);

// Try to create database connection (will fail gracefully if not configured)
$db = null;
try {
    $dbConfig = [
        'host' => 'localhost',
        'database' => 'islamwiki',
        'username' => 'islamwiki',
        'password' => 'islamwiki123'
    ];
    $db = new Connection($dbConfig);
    $container->instance(Connection::class, $db);
} catch (Exception $e) {
    // Database not available, but we can still demonstrate the search engine
    $logger->warning('Database connection failed: ' . $e->getMessage());
}

// Create search engine instance
$searchEngine = null;
if ($db) {
    $searchEngine = new IqraSearchEngine($db);
}

// Handle search request
$searchResults = [];
$searchStats = [];
$query = $_GET['q'] ?? '';
$type = $_GET['type'] ?? 'all';
$error = null;

if (!empty($query) && $searchEngine) {
    try {
        $startTime = microtime(true);
        $searchResults = $searchEngine->search($query, [
            'type' => $type,
            'limit' => 20,
            'page' => 1
        ]);
        $searchTime = microtime(true) - $startTime;
        
        $searchStats = [
            'query' => $query,
            'total_results' => $searchResults['total'],
            'results_count' => count($searchResults['results']),
            'search_time' => round($searchTime, 3),
            'type' => $type
        ];
        
        // Get analytics
        $analytics = $searchEngine->getSearchAnalytics($query);
        $suggestions = $searchEngine->getSuggestions($query);
        
    } catch (Exception $e) {
        $error = 'Search failed: ' . $e->getMessage();
        $logger->error($error);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iqra Search Engine - IslamWiki</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .search-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: border-color 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .search-button {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .search-button:hover {
            transform: translateY(-2px);
        }
        
        .search-options {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-option select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .results-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .results-stats {
            color: #666;
            font-size: 0.9rem;
        }
        
        .result-item {
            padding: 20px;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .result-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .result-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .result-type.page { background: #e3f2fd; color: #1976d2; }
        .result-type.quran { background: #f3e5f5; color: #7b1fa2; }
        .result-type.hadith { background: #e8f5e8; color: #388e3c; }
        .result-type.calendar { background: #fff3e0; color: #f57c00; }
        .result-type.prayer { background: #fce4ec; color: #c2185b; }
        .result-type.scholar { background: #f1f8e9; color: #689f38; }
        
        .result-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .result-excerpt {
            color: #666;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        
        .result-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #888;
        }
        
        .result-relevance {
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .analytics-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-top: 30px;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .analytics-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .analytics-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .analytics-list {
            list-style: none;
        }
        
        .analytics-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .analytics-list li:last-child {
            border-bottom: none;
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #c62828;
            margin-bottom: 20px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-results h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        mark {
            background: #fff3cd;
            color: #856404;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        .demo-mode {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #4caf50;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .search-options {
                justify-content: center;
            }
            
            .results-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Iqra Search Engine</h1>
            <p>Advanced Islamic content search powered by intelligent relevance scoring</p>
        </div>
        
        <div class="search-container">
            <form method="GET" class="search-form">
                <input 
                    type="text" 
                    name="q" 
                    value="<?= htmlspecialchars($query) ?>" 
                    placeholder="Search for Islamic content (e.g., 'allah', 'quran', 'hadith', 'prayer')"
                    class="search-input"
                    required
                >
                <button type="submit" class="search-button">Search</button>
            </form>
            
            <div class="search-options">
                <div class="search-option">
                    <label for="type">Content Type:</label>
                    <select name="type" id="type" onchange="this.form.submit()">
                        <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Content</option>
                        <option value="pages" <?= $type === 'pages' ? 'selected' : '' ?>>Wiki Pages</option>
                        <option value="quran" <?= $type === 'quran' ? 'selected' : '' ?>>Quran Verses</option>
                        <option value="hadith" <?= $type === 'hadith' ? 'selected' : '' ?>>Hadith</option>
                        <option value="calendar" <?= $type === 'calendar' ? 'selected' : '' ?>>Calendar Events</option>
                        <option value="prayer" <?= $type === 'prayer' ? 'selected' : '' ?>>Prayer Times</option>
                        <option value="scholars" <?= $type === 'scholars' ? 'selected' : '' ?>>Islamic Scholars</option>
                    </select>
                </div>
            </div>
        </div>
        
        <?php if (!$db): ?>
        <div class="demo-mode">
            <strong>Demo Mode:</strong> Database connection not available. This is a demonstration of the Iqra search engine's text processing capabilities.
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="error-message">
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($searchResults)): ?>
        <div class="results-container">
            <div class="results-header">
                <h2>Search Results</h2>
                <div class="results-stats">
                    <?= $searchStats['total_results'] ?> total results • 
                    <?= $searchStats['search_time'] ?>s search time
                </div>
            </div>
            
            <?php foreach ($searchResults['results'] as $result): ?>
            <div class="result-item">
                <span class="result-type <?= $result['type'] ?>"><?= ucfirst($result['type']) ?></span>
                <div class="result-title"><?= htmlspecialchars($result['title']) ?></div>
                <?php if (!empty($result['excerpt'])): ?>
                <div class="result-excerpt"><?= $result['excerpt'] ?></div>
                <?php endif; ?>
                <div class="result-meta">
                    <span>Relevance: <span class="result-relevance"><?= $result['relevance'] ?? 'N/A' ?></span></span>
                    <?php if (!empty($result['url'])): ?>
                    <span>URL: <?= htmlspecialchars($result['url']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($analytics)): ?>
        <div class="analytics-container">
            <h2>Search Analytics</h2>
            <div class="analytics-grid">
                <div class="analytics-card">
                    <h3>Query Analysis</h3>
                    <ul class="analytics-list">
                        <li><strong>Original Query:</strong> <?= htmlspecialchars($analytics['query_analysis']['original_query']) ?></li>
                        <li><strong>Normalized:</strong> <?= htmlspecialchars($analytics['query_analysis']['normalized_query']) ?></li>
                        <li><strong>Word Count:</strong> <?= $analytics['query_analysis']['word_count'] ?></li>
                        <li><strong>Contains Arabic:</strong> <?= $analytics['query_analysis']['contains_arabic'] ? 'Yes' : 'No' ?></li>
                        <li><strong>Islamic Terms:</strong> <?= implode(', ', $analytics['query_analysis']['contains_islamic_terms']) ?></li>
                    </ul>
                </div>
                
                <div class="analytics-card">
                    <h3>Relevance Insights</h3>
                    <ul class="analytics-list">
                        <li><strong>High Relevance Terms:</strong> <?= implode(', ', $analytics['relevance_insights']['high_relevance_terms']) ?></li>
                        <li><strong>Related Topics:</strong> <?= implode(', ', $analytics['relevance_insights']['related_topics']) ?></li>
                    </ul>
                </div>
                
                <?php if (!empty($suggestions)): ?>
                <div class="analytics-card">
                    <h3>Search Suggestions</h3>
                    <ul class="analytics-list">
                        <?php foreach ($suggestions as $suggestion): ?>
                        <li><strong><?= ucfirst($suggestion['type']) ?>:</strong> <?= htmlspecialchars($suggestion['text']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php elseif (!empty($query)): ?>
        <div class="results-container">
            <div class="no-results">
                <h3>No Results Found</h3>
                <p>No results found for "<?= htmlspecialchars($query) ?>" in <?= ucfirst($type) ?> content.</p>
                <p>Try different keywords or search in "All Content" to find more results.</p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (empty($query)): ?>
        <div class="results-container">
            <div class="no-results">
                <h3>Welcome to Iqra Search Engine</h3>
                <p>Enter a search query above to discover Islamic knowledge across:</p>
                <ul style="text-align: left; max-width: 400px; margin: 20px auto;">
                    <li>📖 Quran verses and translations</li>
                    <li>📚 Hadith collections and narrations</li>
                    <li>📅 Islamic calendar events</li>
                    <li>🕌 Prayer times and locations</li>
                    <li>👨‍🎓 Islamic scholars and works</li>
                    <li>📄 Wiki pages and articles</li>
                </ul>
                <p><strong>Example searches:</strong> "allah", "quran", "hadith", "prayer", "ramadan", "صلاة"</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-submit form when type changes
        document.getElementById('type').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Focus on search input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.search-input').focus();
        });
    </script>
</body>
</html> 