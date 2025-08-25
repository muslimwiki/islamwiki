<?php
/**
 * Skin Assets Server
 * 
 * This script serves skin assets (CSS, JS, images) directly
 * to test the SkinManager functionality.
 */

// Set proper content types
$contentTypes = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'ico' => 'image/x-icon'
];

// Get the requested asset path
$requestUri = $_SERVER['REQUEST_URI'];
$assetPath = parse_url($requestUri, PHP_URL_PATH);

// Extract skin, type, and filename from /skins/{skin}/{type}/{filename}
if (preg_match('/^\/skins\/([^\/]+)\/([^\/]+)\/(.+)$/', $assetPath, $matches)) {
    $skinName = $matches[1];
    $assetType = $matches[2];
    $filename = $matches[3];
    
    // Determine file extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    
    // Set content type
    if (isset($contentTypes[$extension])) {
        header('Content-Type: ' . $contentTypes[$extension]);
    }
    
    // Set cache headers
    header('Cache-Control: public, max-age=31536000'); // 1 year
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
    
    // Build file path
    $basePath = dirname(__DIR__);
    $filePath = $basePath . '/skins/' . $skinName . '/' . $assetType . '/' . $filename;
    
    // Check if file exists
    if (file_exists($filePath) && is_readable($filePath)) {
        // Serve the file
        readfile($filePath);
        exit;
    } else {
        // File not found
        http_response_code(404);
        echo "Asset not found: {$filename}";
        exit;
    }
} else {
    // Invalid asset path
    http_response_code(400);
    echo "Invalid asset path";
    exit;
} 