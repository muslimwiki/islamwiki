<?php

/**
 * Debug Static Data Test
 *
 * Test page to verify the new static data system works correctly
 *
 * @package IslamWiki
 * @version 0.0.44
 * @license AGPL-3.0-only
 */

// Include the main application
require_once __DIR__ . '/../public/app.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Static Data Test - IslamWiki</title>
    
    <!-- Safa CSS Framework -->
    <link rel="stylesheet" href="/css/safa.css">
    
    <!-- Skin CSS -->
    <link rel="stylesheet" href="/skins/Muslim/css/muslim.css">
    
    <!-- ZamZam.js -->
    <script src="/js/zamzam.js"></script>
</head>
<body>
    <div class="container">
        <h1>Static Data System Test</h1>
        
        <div class="test-section">
            <h2>Static Data Available</h2>
            <p>This page tests the new static data system that provides:</p>
            <ul>
                <li>Global navigation data</li>
                <li>Site information</li>
                <li>Footer data</li>
                <li>Skin-specific components</li>
                <li>Feature configurations</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>Navigation Test</h2>
            <p>The navigation should be loaded from the static data system:</p>
            <div class="nav-test">
                <h3>Main Navigation:</h3>
                <ul>
                    <li><a href="/">🏠 Home</a></li>
                    <li><a href="/pages">📚 Browse</a></li>
                    <li><a href="/quran">📖 Quran</a></li>
                    <li><a href="/hadith">📜 Hadith</a></li>
                </ul>
            </div>
        </div>
        
        <div class="test-section">
            <h2>Component System Test</h2>
            <p>The following components should be loaded dynamically:</p>
            <ul>
                <li>Header component (skin-specific)</li>
                <li>Footer component (skin-specific)</li>
                <li>Breadcrumbs component (if enabled)</li>
                <li>Pagination component (if enabled)</li>
                <li>Sidebar component (if enabled)</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h2>Features Test</h2>
            <p>Feature configurations should be available:</p>
            <ul>
                <li>Search: <span id="search-enabled">Loading...</span></li>
                <li>User Menu: <span id="user-menu-enabled">Loading...</span></li>
                <li>Breadcrumbs: <span id="breadcrumbs-enabled">Loading...</span></li>
                <li>Pagination: <span id="pagination-enabled">Loading...</span></li>
            </ul>
        </div>
    </div>
    
    <style>
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .test-section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .nav-test ul { list-style: none; padding: 0; }
        .nav-test li { margin: 10px 0; }
        .nav-test a { text-decoration: none; color: #2c5aa0; }
        .nav-test a:hover { text-decoration: underline; }
    </style>
</body>
</html> 