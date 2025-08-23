<?php

declare(strict_types=1);

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Http\Controllers\PageController;

return function (\IslamWiki\Core\NizamApplication $app) {
    $container = $app->getContainer();
    $db = $container->get(Connection::class);

    // Create only the essential PageController
    $pageController = new PageController($db, $container);

    // Homepage redirect
    $app->get('/', function($request) {
        return new \IslamWiki\Core\Http\Response(301, ['Location' => '/wiki/Main_Page'], '');
    });

    // Essential wiki routes
    $app->get('/wiki', [$pageController, 'index']);
    $app->get('/wiki/{slug}', [$pageController, 'show']);
    $app->get('/wiki/{slug}/history', [$pageController, 'history']);
    $app->get('/wiki/{slug}/edit', [$pageController, 'edit']);
    $app->post('/wiki/{slug}', [$pageController, 'update']);
    $app->delete('/wiki/{slug}', [$pageController, 'destroy']);
    $app->get('/create', [$pageController, 'create']);
    $app->post('/create', [$pageController, 'store']);
};
