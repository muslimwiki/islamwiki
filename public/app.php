<?php

declare(strict_types=1);

/**
 * IslamWiki Application Entry Point
 * 
 * Main application bootstrap and routing configuration.
 * 
 * @package IslamWiki\Public
 * @version 0.0.1
 * @author IslamWiki Development Team
 * @license AGPL-3.0
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment configuration
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize the Asas container
$container = new IslamWiki\Core\Container\AsasContainer();

// Bootstrap the application
$bootstrap = new IslamWiki\Core\AsasBootstrap($container);
$bootstrap->bootstrap();

// Initialize routing system
$logger = $container->get('logger');
$router = new IslamWiki\Core\Routing\SabilRouting($container, $logger);

// Define routes for the beautiful Islamic design
$router->get('/', function() use ($container) {
    // Render the beautiful homepage with Islamic design
    try {
        $view = $container->get('view');
        $html = $view->render('pages/home.twig', [
            'title' => 'IslamWiki - Authentic Islamic Knowledge',
            'current_language' => 'en'
        ]);
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], $html);
    } catch (\Exception $e) {
        // Fallback if view service fails
        return new \IslamWiki\Core\Http\Response(
            500,
            ['Content-Type' => 'text/html'],
            '<h1>Error: View service not available</h1><p>' . $e->getMessage() . '</p>'
        );
    }
}, []);

$router->get('/quran', function() use ($container) {
    try {
        $view = $container->get('view');
        $html = $view->render('quran/index.twig', [
            'title' => 'Quran - IslamWiki',
            'current_language' => 'en'
        ]);
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], $html);
    } catch (\Exception $e) {
        return new \IslamWiki\Core\Http\Response(
            500,
            ['Content-Type' => 'text/html'],
            '<h1>Error: View service not available</h1><p>' . $e->getMessage() . '</p>'
        );
    }
}, []);

$router->get('/hadith', function() use ($container) {
    try {
        $view = $container->get('view');
        $html = $view->render('hadith/index.twig', [
            'title' => 'Hadith - IslamWiki',
            'current_language' => 'en'
        ]);
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], $html);
    } catch (\Exception $e) {
        return new \IslamWiki\Core\Http\Response(
            500,
            ['Content-Type' => 'text/html'],
            '<h1>Error: View service not available</h1><p>' . $e->getMessage() . '</p>'
        );
    }
}, []);

$router->get('/wiki', function() use ($container) {
    try {
        $view = $container->get('view');
        $html = $view->render('wiki/index.twig', [
            'title' => 'Wiki - IslamWiki',
            'current_language' => 'en'
        ]);
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], $html);
    } catch (\Exception $e) {
        return new \IslamWiki\Core\Http\Response(
            500,
            ['Content-Type' => 'text/html'],
            '<h1>Error: View service not available</h1><p>' . $e->getMessage() . '</p>'
        );
    }
}, []);

$router->get('/search', function() use ($container) {
    try {
        $view = $container->get('view');
        $html = $view->render('search/index.twig', [
            'title' => 'Search - IslamWiki',
            'current_language' => 'en'
        ]);
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], $html);
    } catch (\Exception $e) {
        return new \IslamWiki\Core\Http\Response(
            500,
            ['Content-Type' => 'text/html'],
            '<h1>Error: View service not available</h1><p>' . $e->getMessage() . '</p>'
        );
    }
}, []);

$router->get('/admin', function() use ($container) {
    try {
        $view = $container->get('view');
        $html = $view->render('admin/dashboard.twig', [
            'title' => 'Admin Dashboard - IslamWiki',
            'current_language' => 'en'
        ]);
        return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/html'], $html);
    } catch (\Exception $e) {
        return new \IslamWiki\Core\Http\Response(
            500,
            ['Content-Type' => 'text/html'],
            '<h1>Error: View service not available</h1><p>' . $e->getMessage() . '</p>'
        );
    }
}, []);

// Handle the request
$request = new IslamWiki\Core\Http\Request(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/',
    [],
    null,
    '1.1',
    $_SERVER
);

$response = $router->handle($request);

// Send the response
$response->send();
