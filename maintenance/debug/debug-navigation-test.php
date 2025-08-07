<?php

/**
 * Debug Navigation Test
 *
 * Test page to simulate page navigation and test user dropdown flash fix
 *
 * @package IslamWiki
 * @version 0.0.44
 * @license AGPL-3.0-only
 */

// Include the main application
require_once __DIR__ . '/../public/app.php';

// Mock user data for testing
$mockUser = [
    'id' => 1,
    'username' => 'admin',
    'email' => 'admin@example.com',
    'is_admin' => true,
    'created_at' => '2025-01-01 00:00:00',
];

// Get the application instance
$app = $GLOBALS['app'];
$container = $app->getContainer();

// Get static data manager
$staticDataManager = $container->get('static.data');
$staticData = $staticDataManager->getStaticData('test');

// Get skin data
$skinData = [
    'css' => file_get_contents(__DIR__ . '/../skins/Muslim/css/muslim.css'),
    'js' => file_get_contents(__DIR__ . '/../skins/Muslim/js/muslim.js'),
    'name' => 'Muslim',
    'version' => '0.0.44',
    'config' => [],
];

// Render the test page
$view = $container->get('view');
$html = $view->render('debug/navigation-test.twig', [
    'title' => 'Navigation Test - IslamWiki',
    'user' => $mockUser,
    'skin_css' => $skinData['css'],
    'skin_js' => $skinData['js'],
    'active_skin' => $skinData['name'],
    'skin_version' => $skinData['version'],
    'skin_config' => $skinData['config'],
    // Add static data
    'static_data' => $staticData,
    'site_info' => $staticData['site'],
    'navigation' => $staticData['navigation'],
    'footer' => $staticData['footer'],
    'features' => $staticData['features'],
    'social' => $staticData['social'],
    'components' => $staticData['components'],
]);

echo $html;
