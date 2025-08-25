<?php

declare(strict_types=1);

namespace IslamWiki\Http\Views;

use IslamWiki\Core\Http\Response;

/**
 * Simple View class for rendering HTML responses
 */
class View
{
    /**
     * Render a simple HTML page
     */
    public static function render(string $title, string $content, array $data = []): Response
    {
        $html = self::generateHtml($title, $content, $data);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render an error page
     */
    public static function error(int $statusCode, string $title, string $message): Response
    {
        $html = self::generateErrorHtml($statusCode, $title, $message);
        return new Response($statusCode, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Generate HTML structure
     */
    private static function generateHtml(string $title, string $content, array $data): string
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' - IslamWiki</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #2c5aa0; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .nav { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .nav a { color: #2c5aa0; text-decoration: none; margin-right: 20px; }
        .nav a:hover { text-decoration: underline; }
        .content { line-height: 1.6; }
        .footer { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #666; }
        .language-switch { text-align: right; margin-bottom: 20px; }
        .language-switch a { color: #2c5aa0; text-decoration: none; margin-left: 15px; }
        .breadcrumb { background: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .breadcrumb a { color: #2c5aa0; text-decoration: none; }
        .breadcrumb span { color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="language-switch">
            <a href="/en">English</a>
            <a href="/ar">العربية</a>
        </div>
        
        <div class="header">
            <h1>IslamWiki</h1>
            <p>Islamic Knowledge Platform</p>
        </div>
        
        <div class="nav">
            <a href="/en">Home</a>
            <a href="/en/wiki/Home">Wiki</a>
            <a href="/en/search">Search</a>
            <a href="/en/forums">Forums</a>
            <a href="/en/calendar">Calendar</a>
            <a href="/en/salah">Salah Times</a>
            <a href="/en/quran">Quran</a>
            <a href="/en/hadith">Hadith</a>
            <a href="/en/learn">Learn</a>
            <a href="/en/community">Community</a>
            <a href="/en/help">Help</a>
            <a href="/en/about">About</a>
        </div>
        
        <div class="breadcrumb">
            <a href="/en">Home</a> <span>></span> ' . htmlspecialchars($title) . '
        </div>
        
        <div class="content">
            <h2>' . htmlspecialchars($title) . '</h2>
            ' . $content . '
        </div>
        
        <div class="footer">
            <p>&copy; 2024 IslamWiki. All rights reserved.</p>
            <p><a href="/en/privacy">Privacy Policy</a> | <a href="/en/terms">Terms of Service</a> | <a href="/en/contact">Contact</a></p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Generate error HTML
     */
    private static function generateErrorHtml(int $statusCode, string $title, string $message): string
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' - IslamWiki</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .error-code { font-size: 72px; color: #dc3545; margin: 0; }
        .error-title { color: #333; margin: 20px 0; }
        .error-message { color: #666; margin-bottom: 30px; }
        .back-link { color: #2c5aa0; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error-code">' . $statusCode . '</h1>
        <h2 class="error-title">' . htmlspecialchars($title) . '</h2>
        <p class="error-message">' . htmlspecialchars($message) . '</p>
        <a href="/en" class="back-link">← Back to Home</a>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Render a dashboard view
     */
    public static function dashboard(array $data = []): Response
    {
        $content = '
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Quick Actions</h3>
                <ul>
                    <li><a href="/en/wiki/Home">Browse Wiki</a></li>
                    <li><a href="/en/search">Search Content</a></li>
                    <li><a href="/en/forums">Visit Forums</a></li>
                    <li><a href="/en/calendar">View Calendar</a></li>
                </ul>
            </div>
            
            <div class="dashboard-card">
                <h3>Recent Activity</h3>
                <ul>
                    <li>Last login: ' . ($data["lastLogin"] ?? "Unknown") . '</li>
                    <li>Pages viewed: ' . ($data["pagesViewed"] ?? "0") . '</li>
                    <li>Contributions: ' . ($data["contributions"] ?? "0") . '</li>
                </ul>
            </div>
            
            <div class="dashboard-card">
                <h3>Islamic Resources</h3>
                <ul>
                    <li><a href="/en/quran">Quran</a></li>
                    <li><a href="/en/hadith">Hadith</a></li>
                    <li><a href="/en/fatwas">Fatwas</a></li>
                    <li><a href="/en/scholars">Scholars</a></li>
                </ul>
            </div>
        </div>
        
        <style>
            .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
            .dashboard-card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #2c5aa0; }
            .dashboard-card h3 { margin-top: 0; color: #2c5aa0; }
            .dashboard-card ul { list-style: none; padding: 0; }
            .dashboard-card li { padding: 8px 0; border-bottom: 1px solid #dee2e6; }
            .dashboard-card li:last-child { border-bottom: none; }
            .dashboard-card a { color: #2c5aa0; text-decoration: none; }
            .dashboard-card a:hover { text-decoration: underline; }
        </style>';
        
        return self::render("Dashboard", $content, $data);
    }
    
    /**
     * Render a wiki page view
     */
    public static function wikiPage(string $pageName, array $data = []): Response
    {
        $content = '
        <div class="wiki-content">
            <div class="wiki-header">
                <h1>' . htmlspecialchars($pageName) . '</h1>
                <div class="wiki-actions">
                    <a href="/en/wiki/' . urlencode($pageName) . '/edit" class="btn btn-primary">Edit</a>
                    <a href="/en/wiki/' . urlencode($pageName) . '/history" class="btn btn-secondary">History</a>
                    <a href="/en/wiki/' . urlencode($pageName) . '/discuss" class="btn btn-info">Discuss</a>
                </div>
            </div>
            
            <div class="wiki-body">
                <p>This is the content for the wiki page: <strong>' . htmlspecialchars($pageName) . '</strong></p>
                <p>In a real application, this would contain the actual wiki content, formatting, and media.</p>
                
                <h3>Page Information</h3>
                <ul>
                    <li><strong>Created:</strong> ' . ($data["created"] ?? "Unknown") . '</li>
                    <li><strong>Last Modified:</strong> ' . ($data["lastModified"] ?? "Unknown") . '</li>
                    <li><strong>Author:</strong> ' . ($data["author"] ?? "Unknown") . '</li>
                    <li><strong>Categories:</strong> ' . ($data["categories"] ?? "None") . '</li>
                </ul>
            </div>
        </div>
        
        <style>
            .wiki-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e9ecef; }
            .wiki-actions { display: flex; gap: 10px; }
            .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; color: white; }
            .btn-primary { background: #2c5aa0; }
            .btn-secondary { background: #6c757d; }
            .btn-info { background: #17a2b8; }
            .btn:hover { opacity: 0.9; }
            .wiki-body { line-height: 1.8; }
            .wiki-body h3 { color: #2c5aa0; margin-top: 30px; }
            .wiki-body ul { background: #f8f9fa; padding: 20px; border-radius: 5px; }
        </style>';
        
        return self::render("Wiki: " . $pageName, $content, $data);
    }
    
    /**
     * Render a search results view
     */
    public static function searchResults(string $query, array $results = []): Response
    {
        $content = '
        <div class="search-results">
            <div class="search-header">
                <h3>Search Results for: "' . htmlspecialchars($query) . '"</h3>
                <p>Found ' . count($results) . ' results</p>
            </div>
            
            <div class="search-form">
                <form action="/en/search" method="POST">
                    <input type="text" name="query" value="' . htmlspecialchars($query) . '" placeholder="Search IslamWiki..." style="width: 70%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <button type="submit" style="padding: 10px 20px; background: #2c5aa0; color: white; border: none; border-radius: 4px; margin-left: 10px;">Search</button>
                </form>
            </div>';
        
        if (empty($results)) {
            $content .= '
            <div class="no-results">
                <p>No results found for "' . htmlspecialchars($query) . '".</p>
                <p>Try:</p>
                <ul>
                    <li>Using different keywords</li>
                    <li>Checking your spelling</li>
                    <li>Using more general terms</li>
                </ul>
            </div>';
        } else {
            $content .= '<div class="results-list">';
            foreach ($results as $result) {
                $content .= '
                <div class="result-item">
                    <h4><a href="' . htmlspecialchars($result["url"] ?? "#") . '">' . htmlspecialchars($result["title"] ?? "Untitled") . '</a></h4>
                    <p>' . htmlspecialchars($result["snippet"] ?? "No description available") . '</p>
                    <small>Category: ' . htmlspecialchars($result["category"] ?? "General") . ' | Last updated: ' . htmlspecialchars($result["updated"] ?? "Unknown") . '</small>
                </div>';
            }
            $content .= '</div>';
        }
        
        $content .= '</div>
        
        <style>
            .search-header { margin-bottom: 20px; }
            .search-form { margin-bottom: 30px; }
            .no-results { background: #f8f9fa; padding: 20px; border-radius: 5px; }
            .results-list { margin-top: 20px; }
            .result-item { padding: 20px; border: 1px solid #e9ecef; border-radius: 5px; margin-bottom: 15px; }
            .result-item h4 { margin-top: 0; }
            .result-item h4 a { color: #2c5aa0; text-decoration: none; }
            .result-item h4 a:hover { text-decoration: underline; }
            .result-item small { color: #666; }
        </style>';
        
        return self::render("Search Results", $content, ["query" => $query, "results" => $results]);
    }
} 