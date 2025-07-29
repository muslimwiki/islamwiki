<!--
This file is part of IslamWiki.

Copyright (C) 2025 IslamWiki Contributors

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
-->
# Router Component

## Overview

The Router component handles HTTP request routing in the IslamWiki application. It maps HTTP requests to controller actions based on URL patterns and HTTP methods.

## Version
0.0.1

## Features

- Supports all HTTP methods (GET, POST, PUT, DELETE, etc.)
- Simple route definition syntax
- Controller-based routing with dependency injection
- Route parameters with pattern matching
- Middleware support (planned)
- Comprehensive error handling with custom error pages
- Development-friendly error pages with stack traces in debug mode

## Basic Usage

### Defining Routes

```php
use IslamWiki\Core\Router;

// Basic route
Router::map('GET', '/', 'HomeController@index');

// Route with parameters
Router::map('GET', '/user/{id}', 'UserController@show');

// Multiple HTTP methods
Router::map(['GET', 'POST'], '/profile', 'ProfileController@handle');
```

### Route Parameters

Route parameters can be defined using curly braces `{}`:

```php
// Required parameter
Router::map('GET', '/user/{id}', 'UserController@show');

// Optional parameter
Router::map('GET', '/page/{slug?}', 'PageController@show');
```

### Controller Routing

Controllers should be in the `IslamWiki\Http\Controllers` namespace and extend the base `Controller` class:

```php
namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

class UserController extends Controller
{
    public function show(Request $request, string $id): Response
    {
        // Your code here
    }
}
```

## API Reference

### `Router::map(string|array $methods, string $pattern, callable|string $handler)`

Define a route with the given HTTP methods, URL pattern, and handler.

**Parameters:**
- `$methods`: HTTP method (e.g., 'GET', 'POST') or array of methods
- `$pattern`: URL pattern with optional parameters
- `$handler`: Callable or controller action string (e.g., 'Controller@method')

### `Router::run()`

Execute the router and dispatch the matched route.

## Examples

### Basic Route

```php
Router::map('GET', '/about', function() {
    return new Response('About Us');
});
```

### Controller Action

```php
// routes/web.php
Router::map('GET', '/users', 'UserController@index');

// src/Http/Controllers/UserController.php
class UserController extends Controller 
{
    public function index(Request $request): Response
    {
        $users = [/* ... */];
        return $this->render('users/index', ['users' => $users]);
    }
}
```

## Version History

### 0.0.1 (2025-07-26)
- Initial implementation
- Basic routing with HTTP method support
- Controller action routing
- Route parameter support
