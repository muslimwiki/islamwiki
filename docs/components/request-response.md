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
# Request and Response Components

## Overview

The Request and Response components handle HTTP message handling in the IslamWiki application. They provide an object-oriented interface for working with HTTP requests and responses.

## Version
0.0.1

## Request Component

The `Request` class represents an HTTP request. It provides methods to access request parameters, headers, and other request data.

### Key Features
- Access to request method, URI, and headers
- Query parameter and POST data access
- File upload handling
- JSON request body parsing
- Session and cookie management

### Example Usage

```php
use IslamWiki\Core\Http\Request;

// Get the current request
$request = Request::capture();

// Get request method
$method = $request->getMethod();

// Get query parameters
$id = $request->query('id');

// Get POST data
$name = $request->post('name');

// Get JSON request body
$data = $request->json()->all();
```

## Response Component

The `Response` class represents an HTTP response. It provides methods to set the response status, headers, and body.

### Key Features
- Set response status code and reason phrase
- Set response headers
- Set response body
- JSON response helper methods
- File download support
- Redirect responses

### Example Usage

```php
use IslamWiki\Core\Http\Response;

// Create a simple text response
$response = new Response('Hello, World!', 200);

// Create a JSON response
$response = Response::json(['message' => 'Success']);

// Create a redirect response
$response = Response::redirect('/dashboard');

// Set response headers
$response = $response->withHeader('Content-Type', 'application/json');

// Set response status code
$response = $response->withStatus(201, 'Created');
```

## Middleware Support

Both Request and Response components work with middleware for processing requests and responses in the application's HTTP layer.

### Example Middleware

```php
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

class ExampleMiddleware
{
    public function handle(Request $request, callable $next)
    {
        // Process the request
        $response = $next($request);
        
        // Process the response
        return $response->withHeader('X-Example', 'Value');
    }
}
```

## Version History

### 0.0.1 (2025-07-26)
- Initial implementation
- Basic request and response handling
- Header and status code management
- JSON request/response support
