<?php
/**
 * Simple Asset Server for PHP Development Server
 * This bypasses routing issues with PHP's built-in server
 */

// Get the requested file path
$requestUri = $_SERVER['REQUEST_URI'];
$filePath = '';

// Only handle asset requests, let others pass through
if (preg_match('/^\/assets\/(css|js)\/(.+)$/', $requestUri, $matches)) {
    $type = $matches[1];
    $filename = $matches[2];
    
    // Security: prevent directory traversal
    if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
        http_response_code(403);
        exit('Forbidden');
    }
    
    $filePath = dirname(__DIR__) . "/resources/assets/$type/$filename";
    
    // Set appropriate content type
    if ($type === 'css') {
        header('Content-Type: text/css; charset=utf-8');
    } else {
        header('Content-Type: application/javascript; charset=utf-8');
    }
    
} elseif (preg_match('/^\/skins\/([^\/]+)\/(css|js)\/(.+)$/', $requestUri, $matches)) {
    $skin = $matches[1];
    $type = $matches[2];
    $filename = $matches[3];
    
    // Security: prevent directory traversal
    if (strpos($skin, '..') !== false || strpos($skin, '/') !== false || 
        strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
        http_response_code(403);
        exit('Forbidden');
    }
    
    $filePath = dirname(__DIR__) . "/skins/$skin/$type/$filename";
    
    // Set appropriate content type
    if ($type === 'css') {
        header('Content-Type: text/css; charset=utf-8');
    } else {
        header('Content-Type: application/javascript; charset=utf-8');
    }
    
} else {
    // Not an asset request, pass through to main application
    include 'index.php';
    exit;
}

// Add cache headers
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', filemtime($filePath)));

// Check if file exists and serve it
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    if ($content !== false) {
        echo $content;
    } else {
        http_response_code(500);
        exit('Error reading file');
    }
} else {
    http_response_code(404);
    exit('File not found: ' . $filename);
} 