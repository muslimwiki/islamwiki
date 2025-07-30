<?php
declare(strict_types=1);
use IslamWiki\Core\Routing\Router;
use IslamWiki\Http\Controllers\TestController;

// Test route
Router::map('GET', '/test', 'IslamWiki\Http\Controllers\TestController@test');
