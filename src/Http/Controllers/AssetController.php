<?php
declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\AsasContainer;
use IslamWiki\Core\Database\Connection;

/**
 * AssetController handles serving of static assets with security headers
 */
class AssetController extends Controller
{
    public function __construct(Connection $db, AsasContainer $container)
    {
        parent::__construct($db, $container);
    }

    /**
     * Serve CSS files with proper headers
     */
    public function serveCss(Request $request, string $filename): Response
    {
        try {
            // error_log("AssetController::serveCss called with filename: " . $filename);
            
            $filePath = dirname(dirname(dirname(__DIR__))) . '/resources/assets/css/' . $filename;
            // error_log("Looking for file at: " . $filePath);
            
            if (!file_exists($filePath)) {
                // error_log("File not found: " . $filePath);
                return new Response(404, ['Content-Type' => 'text/plain'], 'CSS file not found');
            }

            error_log("File found, reading content...");
            $content = file_get_contents($filePath);
            error_log("Content length: " . strlen($content));
            
            $response = new Response(200, [
                'Content-Type' => 'text/css; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY'
            ], $content);
            
            // error_log("Response created with status: " . $response->getStatusCode());
            // error_log("AssetController::serveCss completed successfully");
            return $response;
        } catch (\Exception $e) {
            error_log("AssetController::serveCss exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Serve JavaScript files with proper headers
     */
    public function serveJs(Request $request, string $filename): Response
    {
        // error_log("AssetController::serveJs called with filename: " . $filename);
        
        $filePath = dirname(dirname(dirname(__DIR__))) . '/resources/assets/js/' . $filename;
        // error_log("Looking for file at: " . $filePath);
        
        if (!file_exists($filePath)) {
            // error_log("File not found: " . $filePath);
            return new Response(404, ['Content-Type' => 'text/plain'], 'JavaScript file not found');
        }

        // error_log("File found, reading content...");
        $content = file_get_contents($filePath);
        // error_log("Content length: " . strlen($content));
        
        return new Response(200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY'
        ], $content);
    }

    /**
     * Serve skin CSS files with proper headers
     */
    public function serveSkinCss(Request $request, string $skin, string $filename): Response
    {
        try {
            // error_log("AssetController::serveSkinCss called with skin: $skin, filename: $filename");
            
            $filePath = dirname(dirname(dirname(__DIR__))) . "/skins/$skin/css/$filename";
            // error_log("Looking for file at: " . $filePath);
            
            if (!file_exists($filePath)) {
                // error_log("File not found: " . $filePath);
                return new Response(404, ['Content-Type' => 'text/plain'], 'Skin asset file not found');
            }

            // error_log("File found, reading content...");
            $content = file_get_contents($filePath);
            // error_log("Content length: " . strlen($content));
            
            // Determine content type based on file extension
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $contentType = 'text/plain';
            
            if ($extension === 'css') {
                $contentType = 'text/css; charset=utf-8';
            } elseif ($extension === 'js') {
                $contentType = 'application/javascript; charset=utf-8';
            }
            
            $response = new Response(200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY'
            ], $content);
            
            // error_log("Response created with status: " . $response->getStatusCode());
            // error_log("AssetController::serveSkinCss completed successfully");
            return $response;
        } catch (\Exception $e) {
            // error_log("AssetController::serveSkinCss exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Serve skin JavaScript files with proper headers
     */
    public function serveSkinJs(Request $request, string $skin, string $filename): Response
    {
        try {
            error_log("AssetController::serveSkinJs called with skin: $skin, filename: $filename");
            
            $filePath = dirname(dirname(dirname(__DIR__))) . "/skins/$skin/js/$filename";
            error_log("Looking for file at: " . $filePath);
            
            if (!file_exists($filePath)) {
                error_log("File not found: " . $filePath);
                return new Response(404, ['Content-Type' => 'text/plain'], 'Skin JavaScript file not found');
            }

            error_log("File found, reading content...");
            $content = file_get_contents($filePath);
            error_log("Content length: " . strlen($content));
            
            $response = new Response(200, [
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY'
            ], $content);
            
            error_log("Response created with status: " . $response->getStatusCode());
            error_log("AssetController::serveSkinJs completed successfully");
            return $response;
        } catch (\Exception $e) {
            error_log("AssetController::serveSkinJs exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test method for debugging asset serving
     */
    public function test(Request $request): Response
    {
        return new Response(200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ], 'AssetController test method working!');
    }
} 