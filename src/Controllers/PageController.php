<?php

namespace App\Controllers;

use App\Models\Page;

class PageController
{
    public function home()
    {
        return '<!DOCTYPE html>
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
        </html>';
    }

    public function show($slug = null)
    {
        if (!$slug) {
            http_response_code(400);
            return ['error' => 'Missing slug parameter'];
        }

        // For testing, return a simple response
        return [
            'id' => 1,
            'title' => 'Test Page',
            'slug' => $slug,
            'content' => 'This is a test page content.',
            'is_published' => true
        ];
    }
}
