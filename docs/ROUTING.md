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
# Routing and Response Handling Guide

This document explains how routing and response handling works in the IslamWiki application.

## Table of Contents
- [Basic Routing](#basic-routing)
- [Controller Methods](#controller-methods)
- [Response Types](#response-types)
- [Error Handling](#error-handling)
- [Best Practices](#best-practices)

## Basic Routing

Routes are defined in `routes/web.php` and use the `Router` class. The basic syntax is:

```php
use IslamWiki\Core\Router;

// Simple GET route
Router::get('/path', 'Full\Namespace\To\Controller@method');

// Route with parameters
Router::get('/article/{id}', 'ArticleController@show');

// Multiple HTTP methods
Router::match(['GET', 'POST'], '/submit', 'FormController@handle');
```

## Controller Methods

Controller methods should:
1. Accept a `Request` object as a parameter
2. Return a `Response` object

Example controller method:

```php
namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

class PageController
{
    public function show(Request $request, string $id): Response
    {
        // Your logic here
        return Response::create(
            body: $this->view->render('pages/show.twig', ['id' => $id]),
            status: 200,
            headers: ['Content-Type' => 'text/html']
        );
    }
}
```

## Response Types

### HTML Response
```php
return new Response(
    status: 200,
    headers: ['Content-Type' => 'text/html'],
    body: '<h1>Hello World</h1>'
);
```

### JSON Response
```php
return Response::json(
    data: ['status' => 'success', 'data' => $data],
    status: 200
);
```

### Redirect
```php
return Response::redirect(
    url: '/dashboard',
    status: 302
);
```

### Error Response
```php
return Response::error(
    message: 'Resource not found',
    status: 404
);
```

## Error Handling

The router automatically catches exceptions and converts them to appropriate HTTP responses. For custom error handling:

```php
try {
    // Your code
} catch (\Exception $e) {
    return Response::error(
        message: $e->getMessage(),
        status: 500
    );
}
```

## Best Practices

1. **Always return a Response object** from controller methods
2. **Use named routes** for better maintainability
3. **Keep controllers thin** by moving business logic to service classes
4. **Validate input** in controller methods
5. **Use appropriate HTTP status codes**
6. **Set proper Content-Type headers** for each response
7. **Handle errors gracefully** with meaningful error messages

## Common Issues and Solutions

### Blank Page
If you see a blank page:
1. Check that your controller method returns a Response object
2. Verify there are no output buffering issues
3. Check the error logs in `storage/logs/`

### Headers Already Sent
If you get "headers already sent" errors:
1. Ensure there's no output before headers (including whitespace before `<?php`)
2. Check for UTF-8 BOM in your files
3. Make sure all files use `declare(strict_types=1);` as the first line after `<?php`

### Route Not Found
If a route returns 404:
1. Check the route definition in `routes/web.php`
2. Verify the controller and method exist
3. Check for typos in the route pattern
