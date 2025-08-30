<?php

namespace App\Http\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;
use App\Models\Page;

class PageController
{
    public function home(ServerRequestInterface $request): Response
    {
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            '<!DOCTYPE html>
            <html>
            <head>
                <title>Welcome to IslamWiki</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
                    .container { max-width: 800px; margin: 0 auto; }
                    h1 { color: #2c3e50; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Welcome to IslamWiki</h1>
                    <p>Your Islamic knowledge base is running successfully!</p>
                    <p>Try accessing a page: <a href="/pages/test-page">Test Page</a></p>
                </div>
            </body>
            </html>'
        );
    }

    public function show(ServerRequestInterface $request, $slug = null): Response
    {
        // If $slug is null, try to get it from route parameters
        if ($slug === null) {
            $routeParams = $request->getAttribute('routeParams', []);
            $slug = $routeParams['slug'] ?? null;
        }

        if (!$slug) {
            return new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'Missing slug parameter'
            ]));
        }

        $page = Page::where('slug', $slug)->first();

        if (!$page) {
            return new Response(404, ['Content-Type' => 'text/html'], 
                '<!DOCTYPE html>
                <html>
                <head><title>Page Not Found</title></head>
                <body>
                    <h1>404 - Page Not Found</h1>
                    <p>The requested page "' . htmlspecialchars($slug) . '" was not found.</p>
                </body>
                </html>'
            );
        }

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            '<!DOCTYPE html>
            <html>
            <head>
                <title>' . htmlspecialchars($page->title) . ' - IslamWiki</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
                    .container { max-width: 800px; margin: 0 auto; }
                    h1 { color: #2c3e50; }
                    .content { margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>' . htmlspecialchars($page->title) . '</h1>
                    <div class="content">' . nl2br(htmlspecialchars($page->content)) . '</div>
                    <p><a href="/">Back to Home</a></p>
                </div>
            </body>
            </html>'
        );
    }
}
