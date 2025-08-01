<?php
/**
 * Test Skin Output
 * 
 * Test page to see the actual HTML output with skin CSS.
 * 
 * @package IslamWiki\Tests
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IslamWiki\Core\Application;

// Initialize application
$app = new Application(__DIR__ . '/..');
$container = $app->getContainer();

// Get services
$session = $container->get('session');
$viewRenderer = $container->get('view');

// Login user with GreenSkin
$session->login(1, 'testuser');

// Process middleware to update skin data
$request = new \IslamWiki\Core\Http\Request('GET', '/test');
$skinMiddleware = new \IslamWiki\Http\Middleware\SkinMiddleware($app);
$skinMiddleware->handle($request, function($req) {
    return new \IslamWiki\Core\Http\Response(200, [], 'Test response');
});

// Render a simple test page
$html = $viewRenderer->render('layouts/app.twig', [
    'title' => 'Skin Test Page',
    'user' => null,
    'content' => '
    <div class="container">
        <h1>Skin Test Page</h1>
        <p>This page should show the current skin styling.</p>
        <div class="card">
            <div class="card-header">
                <h3>Test Card</h3>
            </div>
            <div class="card-body">
                <p>This card should be styled according to the current skin.</p>
                <button class="btn btn-primary">Test Button</button>
            </div>
        </div>
    </div>
    '
]);

// Output the HTML
echo $html; 