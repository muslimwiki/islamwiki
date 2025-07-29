<?php
declare(strict_types=1);

use IslamWiki\Core\Routing\FastRouter;
use IslamWiki\Http\Controllers\Auth\LoginController;
use IslamWiki\Http\Controllers\Auth\RegisterController;
use IslamWiki\Http\Controllers\Auth\ForgotPasswordController;
use IslamWiki\Http\Controllers\Auth\ResetPasswordController;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Http\Controllers\ProfileController;
use IslamWiki\Http\Controllers\DashboardController;
use IslamWiki\Http\Controllers\HomeController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/** @var FastRouter $router */

// Homepage
$router->get('/', 'IslamWiki\Http\Controllers\HomeController@index');

// Authentication Routes
$router->get('/login', 'IslamWiki\Http\Controllers\Auth\LoginController@showLoginForm');
$router->post('/login', 'IslamWiki\Http\Controllers\Auth\LoginController@login');
    
$router->get('/register', 'IslamWiki\Http\Controllers\Auth\RegisterController@showRegistrationForm');
$router->post('/register', 'IslamWiki\Http\Controllers\Auth\RegisterController@register');
    
$router->get('/forgot-password', 'IslamWiki\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm');
$router->post('/forgot-password', 'IslamWiki\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail');
    
$router->get('/reset-password/{token}', 'IslamWiki\Http\Controllers\Auth\ResetPasswordController@showResetForm');
$router->post('/reset-password', 'IslamWiki\Http\Controllers\Auth\ResetPasswordController@reset');

// Authenticated Routes
// Middleware can be added as a third parameter, e.g., ['auth']

// Logout
$router->post('/logout', 'IslamWiki\Http\Controllers\Auth\LoginController@logout');

// Dashboard
$router->get('/dashboard', 'IslamWiki\Http\Controllers\DashboardController@index');

// Profile
$router->get('/profile', 'IslamWiki\Http\Controllers\ProfileController@show');
$router->put('/profile', 'IslamWiki\Http\Controllers\ProfileController@update');
$router->put('/profile/password', 'IslamWiki\Http\Controllers\ProfileController@updatePassword');

// Test routes for debugging
$router->get('/test', 'IslamWiki\Http\Controllers\TestController@test');
$router->get('/test/error', 'IslamWiki\Http\Controllers\TestController@testError');
$router->get('/test/debug', 'IslamWiki\Http\Controllers\TestController@testDebug');

// Test closure route for router debugging
$router->get('/test-closure', function($request) {
    error_log('Test closure route called');
    return new \IslamWiki\Core\Http\Response(200, ['Content-Type' => 'text/plain'], 'Test closure route works!');
});

// Pages
$router->get('/pages', 'IslamWiki\Http\Controllers\PageController@index');
$router->get('/pages/create', 'IslamWiki\Http\Controllers\PageController@create');
$router->post('/pages', 'IslamWiki\Http\Controllers\PageController@store');
$router->get('/pages/{id}', 'IslamWiki\Http\Controllers\PageController@show');
$router->get('/pages/{id}/edit', 'IslamWiki\Http\Controllers\PageController@edit');
$router->put('/pages/{id}', 'IslamWiki\Http\Controllers\PageController@update');
$router->delete('/pages/{id}', 'IslamWiki\Http\Controllers\PageController@destroy');

// Additional page routes
$router->get('/{slug}', 'IslamWiki\Http\Controllers\PageController@show');
$router->get('/{slug}/history', 'IslamWiki\Http\Controllers\PageController@history');
$router->get('/{slug}/edit', 'IslamWiki\Http\Controllers\PageController@edit');
$router->put('/{slug}', 'IslamWiki\Http\Controllers\PageController@update');
$router->delete('/{slug}', 'IslamWiki\Http\Controllers\PageController@destroy');

// Watchlist
$router->post('/{slug}/watch', 'IslamWiki\Http\Controllers\PageController@watch');
$router->delete('/{slug}/unwatch', 'IslamWiki\Http\Controllers\PageController@unwatch');
