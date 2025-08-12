<?php

/**
 * Test Styling Page
 *
 * This script tests if the CSS styling is working correctly
 * for profile and settings pages.
 *
 * @package IslamWiki\Maintenance\Debug
 * @version 0.0.34
 * @license AGPL-3.0-only
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\NizamApplication;

// Define base path
define('BASE_PATH', dirname(__DIR__, 2));

// Initialize the application
$app = new NizamApplication(BASE_PATH);
$container = $app->getContainer();

// Get view renderer
$view = $container->get('view');

// Mock data for testing
$mockUser = [
    'id' => 1,
    'username' => 'testuser',
    'display_name' => 'Test User',
    'email' => 'test@example.com',
    'bio' => 'This is a test user for styling verification.',
    'avatar' => null
];

$mockUserSettings = [
    'skin' => 'Bismillah',
    'theme' => 'light',
    'language' => 'en',
    'timezone' => 'UTC',
    'notifications' => 'daily',
    'privacy_level' => 'public'
];

$mockUserStats = [
    'total_pages' => 5,
    'recent_edits' => 12,
    'watchlist_items' => 3,
    'member_since' => '2025-01-01',
    'last_active' => '2025-08-07 10:30:00'
];

$mockRecentActivity = [
    [
        'title' => 'Islamic Prayer Times',
        'slug' => 'prayer-times',
        'created_at' => '2025-08-07 09:15:00'
    ],
    [
        'title' => 'Quranic Ayahs',
        'slug' => 'quran-ayahs',
        'created_at' => '2025-08-06 14:30:00'
    ]
];

// Render the test page
$html = $view->render(
    new \IslamWiki\Core\Http\Response(),
    'profile/index.twig',
    [
        'title' => 'Profile Styling Test - IslamWiki',
        'user' => $mockUser,
        'userSettings' => $mockUserSettings,
        'userStats' => $mockUserStats,
        'recentActivity' => $mockRecentActivity,
        'activeSkin' => 'Bismillah',
        'isOwnProfile' => true,
        'canEdit' => true,
        'currentUserId' => 1
    ]
);

// Output the HTML
echo $html->getBody();
